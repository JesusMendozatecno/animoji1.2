<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\ProgressController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'version' => 'v1',
    ]))->name('api.health');

    Route::get('/anime', [CatalogController::class, 'anime'])->name('api.anime.index');
    Route::get('/anime/{anime:slug}', [CatalogController::class, 'animeShow'])->name('api.anime.show');

    Route::get('/manga', [CatalogController::class, 'manga'])->name('api.manga.index');
    Route::get('/manga/{manga:slug}', [CatalogController::class, 'mangaShow'])->name('api.manga.show');

    Route::get('/music', [CatalogController::class, 'music'])->name('api.music.index');
    Route::get('/music/{track:slug}', [CatalogController::class, 'musicShow'])->name('api.music.show');

    Route::get('/games', [CatalogController::class, 'games'])->name('api.games.index');
    Route::get('/games/{game:slug}', [CatalogController::class, 'gameShow'])->name('api.games.show');

    Route::post('/auth/register', [AuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('api.auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('api.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

        Route::get('/me/progress', [ProgressController::class, 'index'])->name('api.progress.index');
        Route::post('/me/progress', [ProgressController::class, 'sync'])->name('api.progress.sync');
    });
});
