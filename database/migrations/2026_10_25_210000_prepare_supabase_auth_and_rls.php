<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prepara Postgres/Supabase para que la app móvil acceda DIRECTAMENTE a la
 * base de datos con supabase-js, manteniendo intacto el acceso de Laravel
 * (que entra como dueño de las tablas y por tanto no está limitado por RLS).
 *
 * - users.auth_uid: enlaza cada fila de la tabla pública `users` con el UUID
 *   de Supabase Auth (necesario porque el id de Laravel es bigint y el de
 *   Supabase Auth es uuid).
 * - Trigger `auth.users`: cuando alguien se registra desde la app móvil se
 *   crea automáticamente su fila en public.users y se le asigna el rol `user`.
 * - Row Level Security: cada tabla queda aislada; el móvil solo lee el
 *   catálogo publicado y solo toca sus propios favoritos/listas/progreso.
 *
 * En MySQL/MariaDB (XAMPP) o SQLite (tests) la migración no hace nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // 1) Columna que enlaza con Supabase Auth.
        if (! Schema::hasColumn('users', 'auth_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->uuid('auth_uid')->nullable()->unique();
            });
        }

        // ¿Estamos en Supabase? (existe el esquema auth con su tabla users)
        $enSupabase = DB::selectOne(
            "SELECT to_regclass('auth.users') AS t"
        )->t !== null;

        if ($enSupabase) {
            $this->crearTriggerAltaMovil();
        }

        $this->habilitarRls();

        if ($enSupabase) {
            $this->crearPoliticas($enSupabase);
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users');
        DB::statement('DROP FUNCTION IF EXISTS public.handle_new_user()');

        $tablas = DB::select(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public'"
        );
        foreach ($tablas as $t) {
            DB::statement("DROP POLICY IF EXISTS lectura_publica ON public.{$t->tablename}");
            DB::statement("DROP POLICY IF EXISTS lectura_publica_filtrada ON public.{$t->tablename}");
            DB::statement("DROP POLICY IF EXISTS filas_propias ON public.{$t->tablename}");
            DB::statement("DROP POLICY IF EXISTS listas_propias ON public.{$t->tablename}");
            DB::statement("ALTER TABLE public.{$t->tablename} DISABLE ROW LEVEL SECURITY");
        }

        if (Schema::hasColumn('users', 'auth_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('auth_uid');
            });
        }
    }

    /**
     * Alta automática desde Supabase Auth (app móvil) → public.users.
     */
    private function crearTriggerAltaMovil(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.handle_new_user()
            RETURNS trigger
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = public
            AS $$
            BEGIN
                -- Si el correo ya existe en la web, solo enlaza el auth_uid.
                UPDATE users
                   SET auth_uid = new.id,
                       updated_at = now()
                 WHERE users.email = new.email
                   AND users.auth_uid IS NULL;

                IF FOUND THEN
                    RETURN new;
                END IF;

                INSERT INTO users (name, email, email_verified_at, password, created_at, updated_at, auth_uid)
                VALUES (
                    COALESCE(
                        NULLIF(new.raw_user_meta_data ->> 'name', ''),
                        split_part(new.email, '@', 1)
                    ),
                    new.email,
                    new.email_confirmed_at,
                    '',
                    now(),
                    now(),
                    new.id
                )
                ON CONFLICT (email) DO UPDATE SET auth_uid = EXCLUDED.auth_uid;

                -- Rol por defecto: user (spatie).
                INSERT INTO model_has_roles (role_id, model_type, model_id)
                SELECT r.id, 'App\\Models\\User', u.id
                  FROM users u
                  JOIN roles r ON r.name = 'user'
                 WHERE u.auth_uid = new.id
                ON CONFLICT DO NOTHING;

                RETURN new;
            END;
            $$;
        SQL);

        DB::statement('DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users');
        DB::statement(
            'CREATE TRIGGER on_auth_user_created
             AFTER INSERT ON auth.users
             FOR EACH ROW EXECUTE FUNCTION public.handle_new_user()'
        );
    }

    private function habilitarRls(): void
    {
        $tablas = DB::select(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
        );

        foreach ($tablas as $t) {
            // FORCE no: Laravel entra como dueño de las tablas y debe seguir
            // operando con libertad (sesiones, caché, escrituras admin…).
            DB::statement("ALTER TABLE public.{$t->tablename} ENABLE ROW LEVEL SECURITY");
            // Sin políticas = denegado para anon/authenticated.
            DB::statement("DROP POLICY IF EXISTS lectura_publica ON public.{$t->tablename}");
            DB::statement("DROP POLICY IF EXISTS lectura_publica_filtrada ON public.{$t->tablename}");
            DB::statement("DROP POLICY IF EXISTS filas_propias ON public.{$t->tablename}");
            DB::statement("DROP POLICY IF EXISTS listas_propias ON public.{$t->tablename}");
        }
    }

    private function crearPoliticas(bool $enSupabase): void
    {
        // ── Catálogo siempre visible ──
        $publicas = [
            'animes', 'mangas', 'games', 'characters',
            'system_images', 'help_developers', 'help_tutorials', 'route_overrides',
        ];
        foreach ($publicas as $tabla) {
            $this->siExiste($tabla, fn () => DB::statement(
                "CREATE POLICY lectura_publica ON public.{$tabla} FOR SELECT USING (true)"
            ));
        }

        // ── Catálogo solo si está publicado ──
        $filtradas = [
            'music_tracks', 'episodes', 'chapters', 'help_manuals',
        ];
        foreach ($filtradas as $tabla) {
            $this->siExiste($tabla, fn () => DB::statement(
                "CREATE POLICY lectura_publica_filtrada ON public.{$tabla}
                 FOR SELECT USING (status = 'published' OR status IS NULL)"
            ));
        }

        // ── Páginas del manga: visibles si su capítulo está publicado ──
        $this->siExiste('manga_pages', fn () => DB::statement(<<<'SQL'
            CREATE POLICY lectura_publica_filtrada ON public.manga_pages
            FOR SELECT USING (
                EXISTS (
                    SELECT 1 FROM public.chapters c
                     WHERE c.id = manga_pages.chapter_id
                       AND (c.status = 'published' OR c.status IS NULL)
                )
            )
        SQL));

        // ── Cuenta propia (app móvil) ──
        $this->siExiste('users', function () {
            DB::statement(<<<'SQL'
                CREATE POLICY filas_propias ON public.users
                FOR SELECT USING (auth_uid = auth.uid())
            SQL);
            DB::statement(<<<'SQL'
                CREATE POLICY actualizacion_propia ON public.users
                FOR UPDATE USING (auth_uid = auth.uid())
                WITH CHECK (auth_uid = auth.uid())
            SQL);
        });

        // ── Datos personales: cada usuario solo los suyos ──
        $personales = [
            'favorites', 'pending_items', 'media_progress',
            'user_lists', 'support_requests',
        ];
        foreach ($personales as $tabla) {
            $this->siExiste($tabla, fn () => DB::statement(<<<'SQL'
                CREATE POLICY filas_propias ON public.{$tabla}
                FOR ALL TO authenticated
                USING (
                    EXISTS (
                        SELECT 1 FROM public.users u
                         WHERE u.id = {$tabla}.user_id
                           AND u.auth_uid = auth.uid()
                    )
                )
                WITH CHECK (
                    EXISTS (
                        SELECT 1 FROM public.users u
                         WHERE u.id = {$tabla}.user_id
                           AND u.auth_uid = auth.uid()
                    )
                )
            SQL));
        }

        // ── Elementos de lista: vía la lista madre ──
        $this->siExiste('list_items', fn () => DB::statement(<<<'SQL'
            CREATE POLICY listas_propias ON public.list_items
            FOR ALL TO authenticated
            USING (
                EXISTS (
                    SELECT 1 FROM public.user_lists ul
                    JOIN public.users u ON u.id = ul.user_id
                     WHERE ul.id = list_items.user_list_id
                       AND u.auth_uid = auth.uid()
                )
            )
            WITH CHECK (
                EXISTS (
                    SELECT 1 FROM public.user_lists ul
                    JOIN public.users u ON u.id = ul.user_id
                     WHERE ul.id = list_items.user_list_id
                       AND u.auth_uid = auth.uid()
                )
            )
        SQL));
    }

    /**
     * Ejecuta $fn solo si la tabla existe en este Postgres.
     */
    private function siExiste(string $tabla, callable $fn): void
    {
        $existe = DB::selectOne(
            "SELECT to_regclass('public.{$tabla}') AS t"
        )->t !== null;

        if ($existe) {
            $fn();
        }
    }
};
