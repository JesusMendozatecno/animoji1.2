<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\Admin\ContentItemsController;
use App\Http\Controllers\Web\Admin\CustomizationController;
use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\GalleryController;
use App\Http\Controllers\Web\Admin\LibraryController;
use App\Http\Controllers\Web\Admin\UsuariosController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\ColeccionController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PersonalController;
use App\Http\Controllers\Web\WatchController;
use App\Http\Middleware\PreventBrowserCache;
use App\Support\Sections;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// ── Organización personal por sección (favoritos, listas, pendientes, seguir)
// Se registran ANTES de las rutas con {slug} para que ganen las literales.
foreach (Sections::KEYS as $sectionKey) {
    Route::middleware(['auth', PreventBrowserCache::class])->group(function () use ($sectionKey) {
        Route::get('/'.$sectionKey.'/favoritos', [PersonalController::class, 'favorites'])
            ->defaults('section', $sectionKey)
            ->name($sectionKey.'.favoritos');

        Route::get('/'.$sectionKey.'/listas', [PersonalController::class, 'lists'])
            ->defaults('section', $sectionKey)
            ->name($sectionKey.'.listas');

        Route::post('/'.$sectionKey.'/listas', [PersonalController::class, 'storeList'])
            ->defaults('section', $sectionKey)
            ->name($sectionKey.'.listas.store');

        Route::get('/'.$sectionKey.'/pendientes', [PersonalController::class, 'pendings'])
            ->defaults('section', $sectionKey)
            ->name($sectionKey.'.pendientes');

        Route::get('/'.$sectionKey.'/seguir', [PersonalController::class, 'history'])
            ->defaults('section', $sectionKey)
            ->name($sectionKey.'.seguir');
    });
}

Route::middleware(['auth', PreventBrowserCache::class])->group(function () {
    // Operaciones personales (sección validada en servidor contra el modelo)
    Route::post('/personal/favoritos', [PersonalController::class, 'toggleFavorite'])
        ->name('personal.favoritos.toggle');

    Route::get('/personal/buscar', [PersonalController::class, 'search'])
        ->name('personal.buscar');

    Route::post('/personal/pendientes', [PersonalController::class, 'togglePending'])
        ->name('personal.pendientes.toggle');

    Route::post('/personal/progreso', [PersonalController::class, 'storeProgress'])
        ->name('personal.progreso');

    // Listas personales (propiedad verificada en servidor)
    Route::put('/listas/{list}', [PersonalController::class, 'updateList'])->name('listas.update');
    Route::delete('/listas/{list}', [PersonalController::class, 'destroyList'])->name('listas.destroy');
    Route::post('/listas/{list}/items', [PersonalController::class, 'addListItem'])->name('listas.items.store');
    Route::delete('/listas/{list}/items/{item}', [PersonalController::class, 'removeListItem'])->name('listas.items.destroy');
});

Route::get('/anime', [CatalogController::class, 'anime'])
    ->middleware(PreventBrowserCache::class)
    ->name('anime.index');
Route::get('/anime/{anime:slug}/ver/{episode}', [WatchController::class, 'watch'])
    ->middleware(PreventBrowserCache::class)
    ->name('anime.watch');
Route::get('/anime/{anime:slug}', [CatalogController::class, 'animeShow'])
    ->middleware(PreventBrowserCache::class)
    ->name('anime.show');

Route::get('/manga', [CatalogController::class, 'manga'])
    ->middleware(PreventBrowserCache::class)
    ->name('manga.index');
Route::get('/manga/{manga:slug}/leer/{chapter}', [WatchController::class, 'read'])
    ->middleware(PreventBrowserCache::class)
    ->name('manga.read');
Route::get('/manga/{manga:slug}', [CatalogController::class, 'mangaShow'])
    ->middleware(PreventBrowserCache::class)
    ->name('manga.show');

Route::get('/musica', [CatalogController::class, 'music'])
    ->middleware(PreventBrowserCache::class)
    ->name('music.index');
Route::get('/musica/{track:slug}', [CatalogController::class, 'musicShow'])
    ->middleware(PreventBrowserCache::class)
    ->name('music.show');

Route::get('/juegos', [CatalogController::class, 'games'])
    ->middleware(PreventBrowserCache::class)
    ->name('games.index');
Route::get('/juegos/{game:slug}', [CatalogController::class, 'gameShow'])
    ->middleware(PreventBrowserCache::class)
    ->name('games.show');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', PreventBrowserCache::class])
    ->name('dashboard');

Route::get('/coleccion', ColeccionController::class)
    ->middleware(['auth', 'verified', PreventBrowserCache::class])
    ->name('coleccion');

Route::middleware(['auth', 'verified', 'role:superadmin', PreventBrowserCache::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        // Biblioteca: CRUD de los catálogos (anime, manga, juegos, música)
        Route::get('/biblioteca/{section?}', [LibraryController::class, 'index'])
            ->where('section', 'anime|manga|juegos|musica')
            ->name('biblioteca');
        Route::post('/biblioteca/{section}', [LibraryController::class, 'store'])
            ->where('section', 'anime|manga|juegos|musica')
            ->name('biblioteca.store');
        Route::put('/biblioteca/{section}/{id}', [LibraryController::class, 'update'])
            ->where('section', 'anime|manga|juegos|musica')
            ->name('biblioteca.update');
        Route::delete('/biblioteca/{section}/{id}', [LibraryController::class, 'destroy'])
            ->where('section', 'anime|manga|juegos|musica')
            ->name('biblioteca.destroy');

        // ── Episodios de anime (JSON, desde el modal de Biblioteca) ──
        Route::get('/biblioteca/anime/{anime}/episodios', [ContentItemsController::class, 'episodes'])
            ->name('episodios');
        Route::post('/biblioteca/anime/{anime}/episodios', [ContentItemsController::class, 'storeEpisode'])
            ->name('episodios.store');
        Route::put('/episodios/{episode}', [ContentItemsController::class, 'updateEpisode'])
            ->name('episodios.update');
        Route::delete('/episodios/{episode}', [ContentItemsController::class, 'destroyEpisode'])
            ->name('episodios.destroy');

        // ── Capítulos de manga ──
        Route::get('/biblioteca/manga/{manga}/capitulos', [ContentItemsController::class, 'chapters'])
            ->name('capitulos');
        Route::post('/biblioteca/manga/{manga}/capitulos', [ContentItemsController::class, 'storeChapter'])
            ->name('capitulos.store');
        Route::put('/capitulos/{chapter}', [ContentItemsController::class, 'updateChapter'])
            ->name('capitulos.update');
        Route::delete('/capitulos/{chapter}', [ContentItemsController::class, 'destroyChapter'])
            ->name('capitulos.destroy');

        // ── Páginas de un capítulo ──
        Route::get('/capitulos/{chapter}/paginas', [ContentItemsController::class, 'pages'])
            ->name('paginas');
        Route::post('/capitulos/{chapter}/paginas', [ContentItemsController::class, 'storePages'])
            ->name('paginas.store');
        Route::delete('/paginas/{page}', [ContentItemsController::class, 'destroyPage'])
            ->name('paginas.destroy');

        Route::get('/galeria', [GalleryController::class, 'index'])->name('galeria');
        Route::post('/galeria', [GalleryController::class, 'store'])->name('galeria.store');
        Route::put('/galeria/{file}', [GalleryController::class, 'rename'])->name('galeria.rename');
        Route::delete('/galeria/{file}', [GalleryController::class, 'destroy'])->name('galeria.destroy');

        // Usuarios y administradores
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios');
        Route::put('/usuarios/{user}', [UsuariosController::class, 'update'])->name('usuarios.update');
        Route::put('/usuarios/{user}/roles', [UsuariosController::class, 'updateRoles'])->name('usuarios.roles');
        Route::delete('/usuarios/{user}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');

        // Personalización de sistema: modo edición de imágenes del sistema
        Route::get('/personalizacion', [CustomizationController::class, 'index'])->name('customizations.index');
        Route::patch('/personalizacion/toggle', [CustomizationController::class, 'toggle'])->name('customizations.toggle');
        Route::get('/personalizacion/galeria', [CustomizationController::class, 'gallery'])->name('customizations.gallery');
        Route::put('/personalizacion/zona/{zone}', [CustomizationController::class, 'update'])->name('customizations.update');

        // Direcciones: modificador de rutas de los botones del sistema
        Route::put('/personalizacion/direcciones/{key}', [CustomizationController::class, 'updateRoute'])->name('direcciones.update');
    });

Route::middleware(['auth', PreventBrowserCache::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'avatar'])->name('profile.avatar');
    Route::patch('/profile/theme', [ProfileController::class, 'theme'])->name('profile.theme');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
