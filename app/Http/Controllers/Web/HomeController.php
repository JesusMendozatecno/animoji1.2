<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Admin\CustomizationController;
use App\Models\User;
use App\Services\CatalogService;
use App\Support\Navigation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    public function __invoke(CatalogService $catalog, Request $request): View
    {
        return view('home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'user' => $request->user(),
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $request->user()?->customizing,
            'stats' => [
                ...$catalog->counts(),
                'users' => User::count(),
            ],
        ]);
    }
}
