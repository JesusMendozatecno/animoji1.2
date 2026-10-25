<?php

namespace App\Support;

use App\Models\RouteOverride;

/**
 * REGISTRO DE NAVEGACIÓN DEL SISTEMA (modificador "Direcciones").
 *
 * Lista todos los botones/secciones que llevan de una parte del sistema a
 * otra, con su destino por defecto. El admin puede cambiar el destino de
 * cualquiera desde Administración → Personalización → Direcciones, y el
 * mapa resultante se comparte con las vistas Blade que lo necesitan.
 */
class Navigation
{
    public const NO_DESTINATION = '__none__';

    /**
     * Destinos posibles: ruta => nombre a mostrar.
     *
     * @return array<string, string>
     */
    public static function destinations(): array
    {
        return [
            '/' => 'Index (página de inicio)',
            '/login' => 'Iniciar sesión',
            '/register' => 'Registrarse',
            '/dashboard' => 'Panel de usuario (Mi Biblioteca)',
            '/coleccion' => 'Mi colección',
            '/profile' => 'Cuenta de usuario',
            '/anime' => 'Anime (catálogo)',
            '/manga' => 'Manga (catálogo)',
            '/musica' => 'Música (catálogo)',
            '/juegos' => 'Videojuegos (catálogo)',
            '/admin' => 'Panel administrativo',
            '/admin/biblioteca' => 'Biblioteca (catálogos)',
            '/admin/usuarios' => 'Usuarios y administradores',
            '/admin/galeria' => 'Galería de imágenes (admin)',
            '/admin/personalizacion' => 'Personalización de sistema',
        ];
    }

    /**
     * Agrupaciones (pestañas/secciones del sistema).
     *
     * @return array<string, string>
     */
    public static function sections(): array
    {
        return [
            'header' => 'Cabecera del index',
            'index' => 'Página de inicio (index)',
            'admin' => 'Panel administrativo',
            'usuario' => 'Panel de usuario',
        ];
    }

    /**
     * Todos los botones/enlaces navegables: key, sección, etiqueta y destino por defecto.
     *
     * @return list<array{key: string, section: string, label: string, default: string}>
     */
    public static function items(): array
    {
        return [
            // ── Cabecera ──
            ['key' => 'header.logo', 'section' => 'header', 'label' => 'Logo ANIMOJI', 'default' => '/'],
            ['key' => 'header.inicio', 'section' => 'header', 'label' => 'Inicio', 'default' => '/'],
            ['key' => 'header.explorar', 'section' => 'header', 'label' => 'Explorar', 'default' => '/anime'],
            ['key' => 'header.mi_biblioteca', 'section' => 'header', 'label' => 'Mi Biblioteca', 'default' => '/dashboard'],
            ['key' => 'header.buscar', 'section' => 'header', 'label' => 'Buscar', 'default' => '/anime'],
            ['key' => 'header.iniciar_sesion', 'section' => 'header', 'label' => 'Iniciar sesión (invitado)', 'default' => '/login'],
            ['key' => 'header.registrarme', 'section' => 'header', 'label' => 'Registrarme (invitado)', 'default' => '/register'],

            // ── Página de inicio ──
            ['key' => 'index.iniciar_sesion', 'section' => 'index', 'label' => 'Botón "Inicio de Sección" (hero)', 'default' => '/login'],
            ['key' => 'index.mi_lista', 'section' => 'index', 'label' => 'Botón "Mi Lista" (hero)', 'default' => '/dashboard'],
            ['key' => 'index.recientes', 'section' => 'index', 'label' => 'Botón "Recientes" (hero)', 'default' => '/anime'],
            ['key' => 'index.categoria_anime', 'section' => 'index', 'label' => 'Tarjeta ANIME ("Ver más")', 'default' => '/anime'],
            ['key' => 'index.categoria_manga', 'section' => 'index', 'label' => 'Tarjeta MANGA ("Ver más")', 'default' => '/manga'],
            ['key' => 'index.categoria_musica', 'section' => 'index', 'label' => 'Tarjeta MÚSICA ("Ver más")', 'default' => '/musica'],
            ['key' => 'index.categoria_juegos', 'section' => 'index', 'label' => 'Tarjeta VIDEOJUEGOS ("Ver más")', 'default' => '/juegos'],
            ['key' => 'index.banner_explorar', 'section' => 'index', 'label' => 'Banner "Explorar ahora"', 'default' => '/anime'],
            ['key' => 'footer.anime', 'section' => 'index', 'label' => 'Pie de página → Anime', 'default' => '/anime'],
            ['key' => 'footer.manga', 'section' => 'index', 'label' => 'Pie de página → Manga', 'default' => '/manga'],
            ['key' => 'footer.musica', 'section' => 'index', 'label' => 'Pie de página → Música', 'default' => '/musica'],
            ['key' => 'footer.juegos', 'section' => 'index', 'label' => 'Pie de página → Videojuegos', 'default' => '/juegos'],

            // ── Panel administrativo (menú lateral) ──
            ['key' => 'admin.menu.inicio', 'section' => 'admin', 'label' => 'Menú → Inicio', 'default' => '/admin'],
            ['key' => 'admin.menu.biblioteca', 'section' => 'admin', 'label' => 'Menú → Biblioteca', 'default' => '/admin/biblioteca'],
            ['key' => 'admin.menu.personalizacion', 'section' => 'admin', 'label' => 'Menú → Personalización', 'default' => '/admin/personalizacion'],
            ['key' => 'admin.menu.usuarios', 'section' => 'admin', 'label' => 'Menú → Usuarios', 'default' => '/admin/usuarios'],
            ['key' => 'admin.menu.anime', 'section' => 'admin', 'label' => 'Biblioteca → Anime', 'default' => '/anime'],
            ['key' => 'admin.menu.manga', 'section' => 'admin', 'label' => 'Biblioteca → Manga', 'default' => '/manga'],
            ['key' => 'admin.menu.musica', 'section' => 'admin', 'label' => 'Biblioteca → Música', 'default' => '/musica'],
            ['key' => 'admin.menu.juegos', 'section' => 'admin', 'label' => 'Biblioteca → Videojuegos', 'default' => '/juegos'],
            ['key' => 'admin.menu.coleccion', 'section' => 'admin', 'label' => 'Menú → Mi colección', 'default' => '/coleccion'],
            ['key' => 'admin.menu.galeria', 'section' => 'admin', 'label' => 'Menú → Imágenes (galería)', 'default' => '/admin/galeria'],

            // ── Panel de usuario (menú lateral) ──
            ['key' => 'usuario.menu.inicio', 'section' => 'usuario', 'label' => 'Menú → Inicio', 'default' => '/dashboard'],
            ['key' => 'usuario.menu.anime', 'section' => 'usuario', 'label' => 'Menú → Anime', 'default' => '/anime'],
            ['key' => 'usuario.menu.musica', 'section' => 'usuario', 'label' => 'Menú → Música', 'default' => '/musica'],
            ['key' => 'usuario.menu.manga', 'section' => 'usuario', 'label' => 'Menú → Manga', 'default' => '/manga'],
            ['key' => 'usuario.menu.juegos', 'section' => 'usuario', 'label' => 'Menú → Videojuegos', 'default' => '/juegos'],
            ['key' => 'usuario.menu.coleccion', 'section' => 'usuario', 'label' => 'Menú → Mi colección', 'default' => '/coleccion'],
            ['key' => 'usuario.menu.config', 'section' => 'usuario', 'label' => 'Menú → Configuración', 'default' => '/profile'],
        ];
    }

    /**
     * Solo las personalizaciones de "Direcciones" (clave → destino elegido).
     * Se comparte con el frontend como prop 'nav'; los botones usan su
     * destino por defecto solo cuando aquí no hay personalización.
     *
     * @return array<string, string>
     */
    public static function map(): array
    {
        $valid = array_column(self::items(), 'default', 'key');

        return RouteOverride::query()
            ->get()
            ->filter(fn ($o) => array_key_exists($o->key, $valid))
            ->pluck('path', 'key')
            ->all();
    }
}
