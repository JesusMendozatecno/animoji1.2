<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\Favorite;
use App\Models\HelpManual;
use App\Models\MediaProgress;
use App\Models\SystemImage;
use App\Models\User;
use App\Support\Sections;
use App\Support\Themes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form (panel de Cuenta).
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $sidebarImage = SystemImage::query()->where('zone', 'cuenta.sidebar')->first();
        $historyTypes = collect(Sections::KEYS)
            ->flatMap(fn (string $section): array => Sections::historyTypes($section))
            ->unique()
            ->values()
            ->all();
        $history = $user->progress()
            ->whereIn('progressable_type', $historyTypes)
            ->with(['progressable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                Episode::class => ['anime'],
                Chapter::class => ['manga'],
            ])])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(function (MediaProgress $progress): ?array {
                $entity = $progress->progressable;

                if (! $entity) {
                    return null;
                }

                $section = match (true) {
                    $entity instanceof Episode => $entity->anime ? Sections::sectionOf($entity->anime) : null,
                    $entity instanceof Chapter => $entity->manga ? Sections::sectionOf($entity->manga) : null,
                    default => Sections::sectionOf($entity),
                };

                if (! $section) {
                    return null;
                }

                return array_merge(Sections::card($entity, $section), [
                    'percentage' => (float) $progress->percentage,
                    'updated_at' => $progress->updated_at,
                ]);
            })
            ->filter()
            ->values();
        $manuals = HelpManual::query()
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('module')
            ->orderBy('title')
            ->limit(30)
            ->get();

        return view('profile.edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'stats' => [
                'anime' => MediaProgress::query()->where('user_id', $user->id)->where('progressable_type', 'anime')->count(),
                'manga' => MediaProgress::query()->where('user_id', $user->id)->where('progressable_type', 'manga')->count(),
                'music' => MediaProgress::query()->where('user_id', $user->id)->where('progressable_type', 'music')->count(),
                'games' => MediaProgress::query()->where('user_id', $user->id)->where('progressable_type', 'game')->count(),
                'favorites' => Favorite::query()->where('user_id', $user->id)->count(),
            ],
            // Personalización: color de las imágenes del index
            'index_theme' => $user->index_theme,
            'themes' => Themes::indexThemes(),
            'sidebarImage' => $sidebarImage,
            'customizing' => (bool) $user->customizing,
            'history' => $history,
            'manuals' => $manuals,
        ]);
    }

    /**
     * Guarda el color de las imágenes del index elegido en Personalización.
     * Home.vue carga entonces /images/index/<color>/ en vez de las genéricas.
     */
    public function theme(Request $request): RedirectResponse
    {
        $request->validate([
            'index_theme' => ['nullable', 'string', Rule::in(array_keys(Themes::indexThemes()))],
        ]);

        $request->user()->update(['index_theme' => $request->input('index_theme') ?: null]);

        return Redirect::route('profile.edit', ['section' => 'preferencias'])
            ->with('success', 'Los cambios se guardaron correctamente.')
            ->with('profile_section', 'preferencias');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')
            ->with('success', 'La información se actualizó correctamente.')
            ->with('profile_section', 'cuenta');
    }

    /**
     * Sube la foto de perfil (ya recortada en cuadrado por el cliente).
     *
     * - Archivo validado en servidor (MIME real, tamaño, extensiones).
     * - Se convierte a JPG sin metadatos (GD) y se guarda en
     *   public/images/usuarios/<nombreusuario>.jpg
     * - Sincronía BD ↔ archivo: si el guardado falla, no se queda un avatar
     *   fantasma; el archivo anterior solo se borra tras confirmar el nuevo.
     */
    public function avatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:max_width=4000,max_height=4000',
            ],
        ], [
            'avatar.required' => 'Selecciona una imagen para tu foto de perfil.',
            'avatar.file' => 'El archivo seleccionado no es válido.',
            'avatar.image' => 'La imagen no es válida.',
            'avatar.mimes' => 'La imagen supera el formato permitido. Usa JPG, PNG o WebP.',
            'avatar.max' => 'La imagen supera el tamaño máximo permitido (4 MB).',
            'avatar.dimensions' => 'Las dimensiones de la imagen son demasiado grandes.',
        ]);

        $file = $request->file('avatar');

        // MIME real del contenido
        if (! in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return back()->withErrors(['avatar' => 'El archivo no es una imagen válida.']);
        }

        // Crear/imagen desde el contenido validado
        $source = @imagecreatefromstring((string) $file->get());
        if ($source === false) {
            return back()->withErrors(['avatar' => 'No se pudo procesar la imagen. Inténtalo con otra.']);
        }

        $user = $request->user();
        $dir = public_path('images/usuarios');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Nombre: patrón «nombreusuario.jpg» sobre un identificador estable.
        // El nombre visible puede cambiar/repetirse, así que se usa el slug
        // del nombre + id si otro usuario ya ocupa ese nombre de archivo.
        $slug = Str::slug($user->name);
        $slug = $slug !== '' ? $slug : 'usuario';
        $filename = $slug.'.jpg';
        if ($this->avatarTaken($filename, $user->id)) {
            $filename = $slug.'-'.$user->id.'.jpg';
        }

        $relPath = 'images/usuarios/'.$filename;
        $fullPath = $dir.DIRECTORY_SEPARATOR.$filename;

        // Guardar el JPG (cuadrado ya recortado) sin metadatos
        $stored = imagejpeg($source, $fullPath, 88);
        imagedestroy($source);

        if (! $stored || ! is_file($fullPath)) {
            return back()->withErrors(['avatar' => 'No se pudo guardar la imagen. Inténtalo nuevamente.']);
        }

        $previous = $user->avatar;

        // Persistir en BD; si falla, se retira el archivo recién creado
        try {
            $user->avatar = $relPath;
            $user->save();
        } catch (\Throwable $e) {
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return back()->withErrors(['avatar' => 'No se pudo completar la operación. Inténtalo nuevamente.']);
        }

        // Solo tras confirmar el nuevo, retirar el anterior (si ya nadie lo usa)
        if ($previous && $previous !== $relPath) {
            $old = public_path($previous);
            if (is_file($old) && ! $this->avatarTaken(basename($previous), $user->id)) {
                @unlink($old);
            }
        }

        return Redirect::route('profile.edit')
            ->with('success', 'Tu foto de perfil se guardó correctamente.')
            ->with('profile_section', 'cuenta');
    }

    /** ¿Otro usuario ya usa ese nombre de archivo de avatar? */
    private function avatarTaken(string $filename, int $currentUserId): bool
    {
        return User::query()
            ->where('id', '!=', $currentUserId)
            ->where('avatar', 'images/usuarios/'.$filename)
            ->exists();
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Nunca dejar al sistema sin superadministradores (requisito 2.2.7)
        if ($user->hasRole('superadmin')) {
            $totalSuperadmins = User::role('superadmin')->count();

            if ($totalSuperadmins <= 1) {
                return back()->withErrors([
                    'password' => 'No puedes eliminar la cuenta: eres el último superadministrador del sistema.',
                ]);
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
