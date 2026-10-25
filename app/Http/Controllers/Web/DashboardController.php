<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Admin\CustomizationController;
use App\Models\Anime;
use App\Models\User;
use App\Services\CatalogService;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CatalogService $catalog): View
    {
        $user = $request->user();

        return view('dashboard', [
            'user' => $user,
            'stats' => [
                ...$catalog->counts(),
                'users' => User::count(),
            ],
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $user->customizing,
            'items' => Anime::query()
                ->latest()
                ->limit(8)
                ->get(['title', 'slug', 'cover', 'year', 'status', 'rating'])
                ->toArray(),
        ]);
    }
}
