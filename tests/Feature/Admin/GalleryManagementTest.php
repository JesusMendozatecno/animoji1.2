<?php

namespace Tests\Feature\Admin;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\SystemImage;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class GalleryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/galeria')->assertRedirect('/login');
        $this->put('/admin/galeria/example.jpg', ['name' => 'Updated name'])
            ->assertRedirect('/login');
    }

    public function test_regular_user_cannot_manage_the_gallery(): void
    {
        $this->actingAs($this->makeUser('user'))
            ->get('/admin/galeria')
            ->assertForbidden();

        $this->put('/admin/galeria/example.jpg', ['name' => 'Updated name'])
            ->assertForbidden();
    }

    public function test_superadmin_can_search_and_paginate_gallery_images(): void
    {
        $admin = $this->makeUser('superadmin');
        $prefix = 'gallery-test-'.Str::lower(Str::random(12));
        $directory = public_path('images/galeria');
        $files = [];

        for ($index = 1; $index <= 11; $index++) {
            $filename = $prefix.'-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.jpg';
            $files[] = $directory.DIRECTORY_SEPARATOR.$filename;
            File::put(end($files), 'gallery test');
        }

        try {
            $this->actingAs($admin)
                ->get('/admin/galeria?q='.$prefix)
                ->assertOk()
                ->assertViewIs('admin.galeria')
                ->assertSee('href="'.route('dashboard').'"', false)
                ->assertSee('data-gallery-image-open', false)
                ->assertSee('data-gallery-rename-input', false)
                ->assertSee('data-gallery-rename-submit', false)
                ->assertSee('readonly', false)
                ->assertSee('Editar')
                ->assertSee('Guardar')
                ->assertDontSee('Volver al inicio')
                ->assertViewHas('filters', fn (array $filters) => $filters['q'] === $prefix)
                ->assertViewHas('items', fn ($items) => $items->total() === 11
                    && count($items->items()) === 10);

            $this->get('/admin/galeria?q='.$prefix.'&page=2')
                ->assertOk()
                ->assertViewHas('items', fn ($items) => $items->currentPage() === 2
                    && count($items->items()) === 1
                    && $items->items()[0]['name'] === $prefix.'-11');
        } finally {
            foreach ($files as $file) {
                File::delete($file);
            }
        }
    }

    public function test_superadmin_can_rename_an_image_and_its_existing_references(): void
    {
        $admin = $this->makeUser('superadmin');
        $filename = 'gallery-rename-'.Str::lower(Str::random(12)).'.jpg';
        $newFilename = 'Imagen renombrada.jpg';
        $currentPath = public_path('images/galeria/'.$filename);
        $newPath = public_path('images/galeria/'.$newFilename);
        File::put($currentPath, 'gallery test');
        SystemImage::query()->create([
            'zone' => 'admin.sidebar',
            'image_path' => 'images/galeria/'.$filename,
            'pos_x' => 50,
            'pos_y' => 50,
            'scale' => 1,
        ]);
        $anime = Anime::query()->create([
            'title' => 'Anime con portada',
            'slug' => 'anime-con-portada',
            'cover' => '/images/galeria/'.$filename,
        ]);
        $manga = Manga::query()->create([
            'title' => 'Manga con portada',
            'slug' => 'manga-con-portada',
            'cover' => 'images/galeria/'.$filename,
        ]);
        $game = Game::query()->create([
            'title' => 'Juego con portada',
            'slug' => 'juego-con-portada',
            'cover' => '/images/galeria/'.$filename,
        ]);
        $track = MusicTrack::query()->create([
            'title' => 'Música con portada',
            'slug' => 'musica-con-portada',
            'artist' => 'Artista de prueba',
            'cover' => 'images/galeria/'.$filename,
        ]);

        try {
            $response = $this->actingAs($admin)
                ->putJson(route('admin.galeria.rename', ['file' => $filename]), [
                    'name' => 'Imagen renombrada',
                ]);

            $response
                ->assertOk()
                ->assertJsonPath('name', 'Imagen renombrada')
                ->assertJsonPath('filename', $newFilename)
                ->assertJsonPath('image', '/images/galeria/Imagen%20renombrada.jpg');

            $this->assertFileExists($newPath);
            $this->assertFileDoesNotExist($currentPath);
            $this->assertDatabaseHas('system_images', [
                'zone' => 'admin.sidebar',
                'image_path' => 'images/galeria/'.$newFilename,
            ]);
            $this->assertSame('/images/galeria/'.$newFilename, $anime->refresh()->cover);
            $this->assertSame('images/galeria/'.$newFilename, $manga->refresh()->cover);
            $this->assertSame('/images/galeria/'.$newFilename, $game->refresh()->cover);
            $this->assertSame('images/galeria/'.$newFilename, $track->refresh()->cover);
        } finally {
            File::delete($currentPath);
            File::delete($newPath);
        }
    }

    public function test_renamed_image_is_found_on_its_page_in_the_full_gallery(): void
    {
        $admin = $this->makeUser('superadmin');
        $prefix = 'gallery-position-'.Str::lower(Str::random(10));
        $directory = public_path('images/galeria');
        $files = [];

        for ($index = 1; $index <= 11; $index++) {
            $files[] = $directory.DIRECTORY_SEPARATOR.$prefix.'-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.jpg';
            File::put(end($files), 'gallery test');
        }

        $filename = $prefix.'-source.jpg';
        $newName = $prefix.'-zzzz-target';
        $sourcePath = $directory.DIRECTORY_SEPARATOR.$filename;
        $renamedPath = $directory.DIRECTORY_SEPARATOR.$newName.'.jpg';
        $files[] = $sourcePath;
        File::put($sourcePath, 'gallery test');

        try {
            $response = $this->actingAs($admin)
                ->putJson(route('admin.galeria.rename', ['file' => $filename]), [
                    'name' => $newName,
                ]);

            $response
                ->assertOk()
                ->assertJsonPath('filename', $newName.'.jpg')
                ->assertJsonStructure(['list_url']);

            $listUrl = $response->json('list_url');
            parse_str((string) parse_url($listUrl, PHP_URL_QUERY), $query);
            $this->assertGreaterThan(1, (int) ($query['page'] ?? 0));
            $this->assertStringNotContainsString('q=', $listUrl);
            $this->assertFileExists($renamedPath);
            $this->assertFileDoesNotExist($sourcePath);

            $this->get($listUrl)
                ->assertOk()
                ->assertViewHas('items', fn ($items) => collect($items->items())
                    ->contains(fn (array $item) => $item['name'] === $newName));
        } finally {
            foreach ($files as $file) {
                File::delete($file);
            }

            File::delete($renamedPath);
        }
    }

    public function test_superadmin_cannot_rename_an_image_with_an_unsafe_or_existing_name(): void
    {
        $admin = $this->makeUser('superadmin');
        $filename = 'gallery-rename-invalid-'.Str::lower(Str::random(12)).'.jpg';
        $path = public_path('images/galeria/'.$filename);
        $existingPath = public_path('images/galeria/Nombre ocupado.jpg');
        File::put($path, 'gallery test');
        File::put($existingPath, 'existing image');

        try {
            $this->actingAs($admin)
                ->putJson(route('admin.galeria.rename', ['file' => $filename]), [
                    'name' => '../Nombre inseguro',
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('name');

            $this->putJson(route('admin.galeria.rename', ['file' => $filename]), [
                'name' => 'Nombre ocupado',
            ])
                ->assertUnprocessable()
                ->assertJsonPath('message', 'Ya existe una imagen con ese nombre.');

            $this->assertFileExists($path);
            $this->assertFileExists($existingPath);
        } finally {
            File::delete($path);
            File::delete($existingPath);
        }
    }

    public function test_gallery_escapes_names_from_filesystem_entries(): void
    {
        $admin = $this->makeUser('superadmin');
        $filename = 'gallery-safe&name-'.Str::lower(Str::random(8)).'.jpg';
        $path = public_path('images/galeria/'.$filename);
        File::put($path, 'gallery test');

        try {
            $this->actingAs($admin)
                ->get('/admin/galeria?q='.rawurlencode(pathinfo($filename, PATHINFO_FILENAME)))
                ->assertOk()
                ->assertSee('safe&amp;name', false)
                ->assertDontSee('safe&name</p>', false);
        } finally {
            File::delete($path);
        }
    }

    public function test_superadmin_can_upload_a_valid_image(): void
    {
        $admin = $this->makeUser('superadmin');
        $name = 'GalleryUpload-'.Str::lower(Str::random(12));
        $path = public_path('images/galeria/'.$name.'.jpg');

        try {
            $this->actingAs($admin)
                ->post('/admin/galeria', [
                    'image' => UploadedFile::fake()->image('upload.jpg'),
                    'name' => $name,
                ])
                ->assertRedirect(route('admin.galeria'))
                ->assertSessionHas('success');

            $this->assertFileExists($path);
        } finally {
            File::delete($path);
        }
    }

    public function test_superadmin_cannot_upload_an_image_using_an_executable_extension(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->from('/admin/galeria')
            ->post('/admin/galeria', [
                'image' => UploadedFile::fake()->image('payload.php'),
                'name' => 'Archivo sospechoso',
            ])
            ->assertRedirect('/admin/galeria')
            ->assertSessionHasErrors('image');

        $this->assertFileDoesNotExist(public_path('images/galeria/Archivo sospechoso.php'));
    }

    public function test_superadmin_can_delete_an_unused_image(): void
    {
        $admin = $this->makeUser('superadmin');
        $filename = 'gallery-delete-'.Str::lower(Str::random(12)).'.jpg';
        $path = public_path('images/galeria/'.$filename);
        File::put($path, 'gallery test');

        try {
            $this->actingAs($admin)
                ->delete(route('admin.galeria.destroy', ['file' => $filename]))
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertFileDoesNotExist($path);
        } finally {
            File::delete($path);
        }
    }

    public function test_superadmin_cannot_delete_an_image_used_by_a_customization_zone(): void
    {
        $admin = $this->makeUser('superadmin');
        $filename = 'gallery-in-use-'.Str::lower(Str::random(12)).'.jpg';
        $path = public_path('images/galeria/'.$filename);
        File::put($path, 'gallery test');
        SystemImage::create([
            'zone' => 'admin.sidebar',
            'image_path' => 'images/galeria/'.$filename,
            'pos_x' => 50,
            'pos_y' => 50,
            'scale' => 1,
        ]);

        try {
            $this->actingAs($admin)
                ->delete(route('admin.galeria.destroy', ['file' => $filename]))
                ->assertRedirect()
                ->assertSessionHas('error');

            $this->assertFileExists($path);
        } finally {
            File::delete($path);
        }
    }

    public function test_superadmin_cannot_delete_a_path_that_is_not_a_gallery_filename(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->delete('/admin/galeria/..private.jpg')
            ->assertUnprocessable();
    }
}
