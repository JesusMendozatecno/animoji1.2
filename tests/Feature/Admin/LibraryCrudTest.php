<?php

namespace Tests\Feature\Admin;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 📚 Pruebas del CRUD de la Biblioteca del panel administrativo.
 *
 * Cubre los tres pilares del requisito de gestión del catálogo:
 * autorización (rol + permiso), operaciones CRUD reales sobre la BD y
 * paginación server-side de 12 elementos por página.
 */
class LibraryCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** Crea un usuario con los roles indicados, ya verificado. */
    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole($role);

        return $user;
    }

    // ─────────────────────────────────────────────
    // Autorización
    // ─────────────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/biblioteca/anime')->assertRedirect('/login');
    }

    public function test_plain_user_cannot_access_the_library(): void
    {
        $this->actingAs($this->makeUser('user'))
            ->get('/admin/biblioteca/anime')
            ->assertForbidden();
    }

    public function test_superadmin_can_view_a_section(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/manga')
            ->assertOk()
            ->assertViewIs('admin.biblioteca')
            ->assertSee('Valoración (0-10)')
            ->assertViewHas('section', 'manga')
            ->assertViewHas('items', fn ($items) => $items->count() === 0);
    }

    public function test_unknown_section_returns_404(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/inexistente')
            ->assertNotFound();
    }

    // ─────────────────────────────────────────────
    // Crear
    // ─────────────────────────────────────────────

    /**
     * Regresión: dejar "Estado" y "Valoración" vacíos provocaba un 500
     * (SQLSTATE[23000]: Column 'status'/'rating' cannot be null).
     * Deben aplicarse los valores por defecto de la BD.
     */
    public function test_superadmin_can_create_a_record_omitting_optional_fields(): void
    {
        $response = $this->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/manga', [
                'title' => 'Manga de Prueba',
                'author' => 'Autor de Prueba',
                'status' => '',
                'rating' => '',
                'year' => '',
            ]);

        $response->assertRedirect('/admin/biblioteca/manga');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mangas', [
            'title' => 'Manga de Prueba',
            'author' => 'Autor de Prueba',
            'status' => 'ongoing',   // valor por defecto de la columna NOT NULL
            'slug' => 'manga-de-prueba',
        ]);
    }

    public function test_title_is_required_when_creating(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->followingRedirects()
            ->actingAs($admin)
            ->from('/admin/biblioteca/manga')
            ->post('/admin/biblioteca/manga', ['title' => '', 'author' => 'X'])
            ->assertOk()
            ->assertSee('El título es obligatorio.');
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/manga', [
                'title' => 'Estado Inválido',
                'author' => 'Autor',
                'status' => 'no-existe',
            ])
            ->assertSessionHasErrors('status');
    }

    // ─────────────────────────────────────────────
    // Editar
    // ─────────────────────────────────────────────

    public function test_superadmin_can_update_a_record(): void
    {
        $manga = Manga::create([
            'title' => 'Título Original',
            'slug' => 'titulo-original',
            'author' => 'Autor Original',
            'status' => 'ongoing',
        ]);

        $this->actingAs($this->makeUser('superadmin'))
            ->put("/admin/biblioteca/manga/{$manga->id}", [
                'title' => 'Título Actualizado',
                'author' => 'Autor Actualizado',
                'status' => 'finished',
            ])
            ->assertRedirect('/admin/biblioteca/manga');

        $this->assertDatabaseHas('mangas', [
            'id' => $manga->id,
            'title' => 'Título Actualizado',
            'status' => 'finished',
        ]);
    }

    // ─────────────────────────────────────────────
    // Borrar
    // ─────────────────────────────────────────────

    public function test_superadmin_can_delete_a_record(): void
    {
        $manga = Manga::create([
            'title' => 'Para Borrar',
            'slug' => 'para-borrar',
            'author' => 'Autor',
            'status' => 'ongoing',
        ]);

        $this->actingAs($this->makeUser('superadmin'))
            ->delete("/admin/biblioteca/manga/{$manga->id}")
            ->assertRedirect('/admin/biblioteca/manga');

        $this->assertDatabaseMissing('mangas', ['id' => $manga->id]);
    }

    public function test_deleting_a_record_removes_its_favorites(): void
    {
        $manga = Manga::create([
            'title' => 'Con Favorito',
            'slug' => 'con-favorito',
            'author' => 'Autor',
            'status' => 'ongoing',
        ]);

        $user = $this->makeUser('user');
        $user->favorites()->create([
            'favoritable_type' => $manga->getMorphClass(),
            'favoritable_id' => $manga->id,
        ]);

        $this->actingAs($this->makeUser('superadmin'))
            ->delete("/admin/biblioteca/manga/{$manga->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'favoritable_id' => $manga->id,
            'favoritable_type' => $manga->getMorphClass(),
        ]);
    }

    // ─────────────────────────────────────────────
    // Búsqueda y paginación
    // ─────────────────────────────────────────────

    public function test_search_is_performed_server_side(): void
    {
        Manga::create(['title' => 'Naruto', 'slug' => 'naruto', 'author' => 'Kishimoto', 'status' => 'ongoing']);
        Manga::create(['title' => 'Berserk', 'slug' => 'berserk', 'author' => 'Miura', 'status' => 'ongoing']);

        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/manga?q=Berserk')
            ->assertOk()
            ->assertViewIs('admin.biblioteca')
            ->assertViewHas('items', fn ($items) => $items->count() === 1
                && $items->first()->title === 'Berserk')
            ->assertViewHas('filters', fn (array $filters) => $filters['q'] === 'Berserk');
    }

    public function test_catalog_titles_are_escaped_in_library_cards(): void
    {
        Manga::create([
            'title' => '<script>alert("xss")</script>',
            'slug' => 'script-title',
            'author' => 'Autor',
            'status' => 'ongoing',
        ]);

        $response = $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/manga');

        $response
            ->assertOk()
            ->assertSee('data-open-detail', false)
            ->assertDontSee('<table', false)
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_records_are_ordered_alphabetically_and_paginated_twelve_per_page(): void
    {
        foreach (range(1, 14) as $i) {
            Manga::create([
                'title' => sprintf('Obra %02d', $i),
                'slug' => sprintf('obra-%02d', $i),
                'author' => 'Autor '.$i,
                'status' => 'ongoing',
            ]);
        }

        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/manga')
            ->assertOk()
            ->assertSee('/images/manga/cover-1.jpg', false)
            ->assertViewHas('items', fn ($items) => $items->count() === 12
                && $items->total() === 14
                && $items->perPage() === 12
                && $items->first()->title === 'Obra 01');

        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/manga?page=2')
            ->assertOk()
            ->assertViewHas('items', fn ($items) => $items->count() === 2
                && $items->first()->title === 'Obra 13');
    }

    public function test_catalog_order_is_case_insensitive_and_detail_modal_has_section_fields(): void
    {
        foreach (['zeta', 'Beta', 'alfa'] as $title) {
            Manga::create([
                'title' => $title,
                'slug' => strtolower($title),
                'author' => 'Autor '.$title,
                'status' => 'ongoing',
            ]);
        }

        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/manga')
            ->assertOk()
            ->assertSee('library-detail-dialog', false)
            ->assertSee('Autor', false)
            ->assertViewHas('items', fn ($items) => $items->pluck('title')->all() === ['alfa', 'Beta', 'zeta']);
    }

    public function test_each_library_section_shows_its_own_cover_and_detail_fields(): void
    {
        $sections = [
            ['anime', Anime::class, ['title' => 'Anime de ficha', 'slug' => 'anime-de-ficha', 'studio' => 'Estudio de ficha'], 'Estudio'],
            ['manga', Manga::class, ['title' => 'Manga de ficha', 'slug' => 'manga-de-ficha', 'author' => 'Autor de ficha', 'status' => 'ongoing'], 'Autor'],
            ['juegos', Game::class, ['title' => 'Juego de ficha', 'slug' => 'juego-de-ficha', 'platforms' => ['PC']], 'Plataformas'],
            ['musica', MusicTrack::class, ['title' => 'Canción de ficha', 'slug' => 'cancion-de-ficha', 'artist' => 'Artista de ficha'], 'Artista o banda'],
        ];

        foreach ($sections as [$section, $model, $attributes, $fieldLabel]) {
            $model::create($attributes);

            $this->actingAs($this->makeUser('superadmin'))
                ->get('/admin/biblioteca/'.$section)
                ->assertOk()
                ->assertSee('/images/'.$section.'/cover-1.jpg', false)
                ->assertSee($fieldLabel, false)
                ->assertSee('data-open-detail', false);
        }
    }

    // ─────────────────────────────────────────────
    // Otras secciones
    // ─────────────────────────────────────────────

    public function test_superadmin_can_create_an_anime(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/anime', [
                'title' => 'Anime de Prueba',
                'studio' => 'Estudio Prueba',
                'year' => '2024',
                'status' => 'ongoing',
                'rating' => '4.5',
            ])
            ->assertRedirect('/admin/biblioteca/anime');

        $this->assertDatabaseHas('animes', [
            'title' => 'Anime de Prueba',
            'studio' => 'Estudio Prueba',
            'rating' => 4.5,
        ]);
    }

    public function test_superadmin_can_create_a_music_track_with_forced_values(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/musica', [
                'title' => 'Canción de Prueba',
                'artist' => 'Artista Prueba',
                'album' => 'Álbum Prueba',
            ])
            ->assertRedirect('/admin/biblioteca/musica');

        $this->assertDatabaseHas('music_tracks', [
            'title' => 'Canción de Prueba',
            'artist' => 'Artista Prueba',
        ]);
    }

    public function test_superadmin_can_save_an_anime_rating_on_the_ten_point_scale(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/anime', [
                'title' => 'Anime con diez puntos',
                'rating' => '8.7',
            ])
            ->assertRedirect('/admin/biblioteca/anime');

        $this->assertDatabaseHas('animes', [
            'title' => 'Anime con diez puntos',
            'rating' => 8.7,
        ]);
    }

    public function test_superadmin_can_save_a_manga_rating_on_the_ten_point_scale(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/manga', [
                'title' => 'Manga con diez puntos',
                'rating' => '9.6',
            ])
            ->assertRedirect('/admin/biblioteca/manga');

        $this->assertDatabaseHas('mangas', [
            'title' => 'Manga con diez puntos',
            'rating' => 9.6,
        ]);
    }

    public function test_superadmin_cannot_save_an_anime_rating_above_ten_points(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->from('/admin/biblioteca/anime')
            ->post('/admin/biblioteca/anime', [
                'title' => 'Anime fuera de rango',
                'rating' => '10.1',
            ])
            ->assertRedirect('/admin/biblioteca/anime')
            ->assertSessionHasErrors([
                'rating' => 'La valoración debe estar entre 0 y 10.',
            ]);

        $this->assertDatabaseMissing('animes', ['title' => 'Anime fuera de rango']);
    }

    public function test_superadmin_cannot_save_a_game_rating_above_five_points(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->from('/admin/biblioteca/juegos')
            ->post('/admin/biblioteca/juegos', [
                'title' => 'Juego fuera de rango',
                'rating' => '5.1',
            ])
            ->assertRedirect('/admin/biblioteca/juegos')
            ->assertSessionHasErrors([
                'rating' => 'La valoración debe estar entre 0 y 5.',
            ]);

        $this->assertDatabaseMissing('games', ['title' => 'Juego fuera de rango']);
    }

    // ─────────────────────────────────────────────
    // 🎵 Subida de audio e imagen de la canción
    // ─────────────────────────────────────────────

    /** WAV mínimo con firma RIFF/WAVE válida para las validaciones. */
    private function wavContent(): string
    {
        $samples = str_repeat("\x7f", 2000);

        return 'RIFF'.pack('V', 36 + strlen($samples)).'WAVE'
            .'fmt '.pack('V', 16).pack('v', 1).pack('v', 1)
            .pack('V', 8000).pack('V', 8000).pack('v', 1).pack('v', 8)
            .'data'.pack('V', strlen($samples)).$samples;
    }

    /** PNG mínimo (1x1) válido. */
    private function pngContent(): string
    {
        return base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );
    }

    /**
     * Registrar una canción con archivo de audio e imagen de portada debe
     * guardar ambos en el disco público con URL /storage/... utilizable
     * por el reproductor (regresión: la ruta sin /storage daba 404).
     */
    public function test_superadmin_can_register_a_music_track_with_audio_and_cover_files(): void
    {
        Storage::fake('public');

        // El formulario debe ofrecer la subida de imagen de la canción
        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/biblioteca/musica')
            ->assertOk()
            ->assertSee('Imagen de la canción', false)
            ->assertSee('Archivo de audio', false);

        $response = $this->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/musica', [
                'title' => 'Canción con Archivos',
                'artist' => 'Artista Con Archivos',
                'audio_file' => UploadedFile::fake()->createWithContent('cancion.wav', $this->wavContent()),
                'cover_file' => UploadedFile::fake()->createWithContent('portada.png', $this->pngContent()),
            ]);

        $response->assertRedirect('/admin/biblioteca/musica');
        $response->assertSessionHas('success');

        $track = MusicTrack::query()->where('title', 'Canción con Archivos')->firstOrFail();

        $this->assertStringStartsWith('/storage/media/audios/', $track->audio_url);
        $this->assertStringStartsWith('/storage/media/portadas/', $track->cover);

        Storage::disk('public')->assertExists(str_replace('/storage/', '', $track->audio_url));
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $track->cover));

        // El catálogo debe ofrecer la pista al reproductor con su botón de play
        $this->actingAs($this->makeUser('user'))
            ->get('/musica')
            ->assertOk()
            ->assertViewHas(
                'musicPlayerTracks',
                fn ($tracks) => $tracks->pluck('url')->contains($track->audio_url),
            );
    }

    /** Un formato de imagen no admitido debe rechazarse con mensaje en español. */
    public function test_invalid_cover_file_is_rejected(): void
    {
        $this->from('/admin/biblioteca/musica')
            ->actingAs($this->makeUser('superadmin'))
            ->post('/admin/biblioteca/musica', [
                'title' => 'Portada Inválida',
                'artist' => 'Artista',
                'cover_file' => UploadedFile::fake()->createWithContent('nota.txt', 'no es una imagen'),
            ])
            ->assertRedirect('/admin/biblioteca/musica')
            ->assertSessionHasErrors('cover_file');

        $this->assertDatabaseMissing('music_tracks', ['title' => 'Portada Inválida']);
    }
}
