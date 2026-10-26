<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Copia los datos de la MariaDB local de XAMPP a Supabase (Postgres).
 *
 * Uso (con .env apuntando a Supabase y `php artisan migrate` ya ejecutado):
 *
 *     php artisan animoji:importar-supabase
 *
 * Opciones:
 *   --destino=pgsql   conexión de destino (por defecto la conexión principal).
 *   --forzar          reimporta aunque la tabla de destino ya tenga filas.
 *   --solo=usuarios   importa solo esas tablas (repetible).
 *
 * No toca tablas de ejecución (sesiones, caché, jobs) ni la tabla de
 * migraciones: cada base de datos lleva la suya.
 */
class ImportarSupabase extends Command
{
    protected $signature = 'animoji:importar-supabase
        {--destino= : Conexión de destino (por defecto la principal)}
        {--forzar : Reimportar aunque el destino ya tenga filas}
        {--solo=* : Importar únicamente estas tablas}';

    protected $description = 'Copia los datos de MariaDB local a Supabase (Postgres) conservando ids y relaciones';

    /** Tablas de ejecución que no se copian. */
    private const EXCLUIR = [
        'migrations', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs',
    ];

    /**
     * Orden topológico según las claves foráneas reales del esquema.
     * (Padres antes que hijos; `comments` es auto-referente y se ordena por id.)
     */
    private const ORDEN = [
        'users',
        'roles', 'permissions', 'role_has_permissions', 'model_has_roles', 'model_has_permissions',
        'animes', 'mangas', 'games', 'music_tracks',
        'system_images', 'route_overrides', 'characters',
        'help_developers', 'help_manuals', 'help_tutorials',
        'episodes', 'chapters', 'manga_pages',
        'user_lists', 'list_items',
        'favorites', 'pending_items', 'media_progress',
        'posts', 'comments', 'reports', 'support_requests', 'audit_events',
        'personal_access_tokens',
    ];

    public function handle(): int
    {
        $destino = $this->option('destino') ?: config('database.default');
        $dest = DB::connection($destino);

        if ($dest->getDriverName() !== 'pgsql') {
            $this->error("El destino «{$destino}» no es Postgres. Configura DB_URL/DB_CONNECTION con la cadena de Supabase primero.");

            return self::FAILURE;
        }

        $src = DB::connection('mariadb_local');

        try {
            $src->getPdo();
        } catch (\Throwable $e) {
            $this->error('No se pudo conectar a la MariaDB local: '.$e->getMessage());

            return self::FAILURE;
        }

        // Verifica que el destino ya tenga el esquema migrado.
        $tablasDestino = array_map(
            fn ($t) => strtolower($t->tablename ?? ''),
            $dest->select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'")
        );
        if (! in_array('users', $tablasDestino, true)) {
            $this->error('El destino no tiene el esquema de ANIMOJI. Ejecuta primero: php artisan migrate --force');

            return self::FAILURE;
        }

        $solo = array_filter((array) $this->option('solo'));
        $origen = array_values(array_filter(
            $src->select("SELECT table_name AS t FROM information_schema.tables WHERE table_schema = DATABASE()"),
            fn ($r) => in_array(strtolower($r->t), self::ORDEN, true)
        ));

        $tablas = array_map(fn ($r) => strtolower($r->t), $origen);
        if ($solo) {
            $tablas = array_values(array_intersect($tablas, $solo));
        } else {
            // Respeta el orden declarado, incluyendo las que estén vacías.
            $tablas = array_values(array_intersect(self::ORDEN, $tablas));
        }

        $this->info('Importando '.count($tablas).' tablas de MariaDB → '.$destino);

        $forzar = (bool) $this->option('forzar');
        $copiadas = 0;
        $omitidas = 0;

        foreach ($tablas as $tabla) {
            if (in_array($tabla, self::EXCLUIR, true)) {
                continue;
            }

            if (! $this->tablaExiste($src, $tabla)) {
                $this->line("  - {$tabla}: no existe en el origen, se omite.");
                continue;
            }

            if (! $this->tablaExiste($dest, $tabla)) {
                $this->line("  - {$tabla}: no existe en el destino, se omite.");
                continue;
            }

            $enDestino = (int) $dest->table($tabla)->count();
            if ($enDestino > 0 && ! $forzar) {
                $this->line("  - {$tabla}: ya tiene {$enDestino} fila(s) en el destino (usa --forzar para reimportar).");
                $omitidas++;

                continue;
            }

            $tipos = $this->columnasDestino($dest, $tabla);
            $ordenar = $this->tieneColumna($src, $tabla, 'id');
            $consulta = $dest; // solo para evitar análisis estáticos
            unset($consulta);

            $filas = 0;
            $query = $src->table($tabla);
            if ($ordenar) {
                $query->orderBy('id');
            }

            // Limpieza opcional del destino para reimportar.
            if ($enDestino > 0 && $forzar) {
                $dest->table($tabla)->delete();
            }

            $query->chunk(200, function ($lote) use ($dest, $tabla, $tipos, &$filas) {
                $filasConvertidas = array_map(
                    fn ($fila) => $this->convertir($fila, $tipos),
                    $lote->toArray()
                );
                if ($filasConvertidas !== []) {
                    $dest->table($tabla)->insert($filasConvertidas);
                }
                $filas += count($filasConvertidas);
            });

            $this->line("  ✔ {$tabla}: {$filas} fila(s)");
            $copiadas++;
        }

        $this->secuencias($dest);
        $this->info("Importación terminada: {$copiadas} tablas copiadas, {$omitidas} ya existían.");

        return self::SUCCESS;
    }

    /**
     * Ajusta valores al tipo real de la columna de destino:
     * - boolean ← tinyint/char de MySQL.
     * - json/jsonb ← cadena; vacía o inválida → NULL.
     */
    private function convertir(object $fila, array $tipos): array
    {
        $resultado = [];
        foreach ((array) $fila as $columna => $valor) {
            $tipo = strtolower($tipos[$columna] ?? '');

            if (in_array($tipo, ['bool', 'boolean'], true)) {
                if ($valor === null) {
                    $resultado[$columna] = null;
                } else {
                    $resultado[$columna] = filter_var(
                        $valor,
                        FILTER_VALIDATE_BOOLEAN,
                        FILTER_NULL_ON_FAILURE
                    ) ?? (bool) $valor;
                }

                continue;
            }

            if (in_array($tipo, ['json', 'jsonb'], true)) {
                if ($valor === null || $valor === '' || ! $this->esJsonValido($valor)) {
                    $resultado[$columna] = null;
                } else {
                    $resultado[$columna] = $valor;
                }

                continue;
            }

            $resultado[$columna] = $valor;
        }

        return $resultado;
    }

    private function esJsonValido(mixed $valor): bool
    {
        if (is_array($valor)) {
            return true;
        }
        if (! is_string($valor)) {
            return false;
        }
        json_decode($valor);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Mapa columna => tipo de la base de datos de destino.
     */
    private function columnasDestino($conexion, string $tabla): array
    {
        // El esquema real es el search_path de la conexión.
        $columnas = $conexion->select(
            'SELECT column_name, data_type
               FROM information_schema.columns
              WHERE table_name = ?
                AND table_schema = ANY (current_schemas(false))',
            [$tabla]
        );

        $mapa = [];
        foreach ($columnas as $c) {
            $mapa[strtolower($c->column_name)] = $c->data_type;
        }

        return $mapa;
    }

    private function tablaExiste($conexion, string $tabla): bool
    {
        if ($conexion->getDriverName() === 'pgsql') {
            return $conexion->selectOne(
                'SELECT to_regclass(?) AS t',
                ["public.{$tabla}"]
            )->t !== null;
        }

        $base = $conexion->getDatabaseName();
        return (int) $conexion->selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ? AND table_name = ?',
            [$base, $tabla]
        )->c > 0;
    }

    private function tieneColumna($conexion, string $tabla, string $columna): bool
    {
        $base = $conexion->getDatabaseName();
        $esquema = $conexion->getDriverName() === 'pgsql' ? 'public' : $base;

        return (int) $conexion->selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.columns
              WHERE table_schema = ? AND table_name = ? AND column_name = ?',
            [$esquema, $tabla, $columna]
        )->c > 0;
    }

    /**
     * Sincroniza las secuencias de id tras importar con ids explícitos,
     * para que las próximas inserciones no choquen con ids ya usados.
     */
    private function secuencias($dest): void
    {
        $tablas = $dest->select(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
        );

        foreach ($tablas as $t) {
            $tabla = $t->tablename;
            foreach (['id'] as $columna) {
                $existe = $dest->selectOne(
                    "SELECT to_regclass(?) IS NOT NULL AS ok",
                    ["public.{$tabla}"]
                );
                if (! $existe?->ok) {
                    continue;
                }
                $seq = $dest->selectOne(
                    'SELECT pg_get_serial_sequence(?, ?) AS s',
                    ["public.{$tabla}", $columna]
                )->s ?? null;
                if ($seq === null) {
                    continue;
                }
                $dest->select(
                    "SELECT setval(?, COALESCE((SELECT MAX({$columna}) FROM public.{$tabla}), 1),
                                    (SELECT MAX({$columna}) IS NOT NULL FROM public.{$tabla}))",
                    [$seq]
                );
            }
        }

        $this->line('  ✔ Secuencias de id sincronizadas.');
    }
}
