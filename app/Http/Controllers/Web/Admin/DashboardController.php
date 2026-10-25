<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Services\CatalogService;
use App\Support\Navigation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CatalogService $catalog): View
    {
        return view('admin.dashboard', [
            'user' => $request->user(),
            'stats' => [
                ...$catalog->counts(),
                'users' => User::count(),
                'admins' => User::role('admin')->count(),
                'pendingReports' => Report::query()->where('status', 'pending')->count(),
            ],
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $request->user()->customizing,
        ]);
    }

}
