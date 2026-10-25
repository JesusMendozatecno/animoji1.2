<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request, ProgressService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->forUser($request->user()),
        ]);
    }

    public function sync(Request $request, ProgressService $service): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:anime,manga,music,game'],
            'id' => ['required', 'integer'],
            'position' => ['required', 'integer', 'min:0'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $progress = $service->sync(
            $request->user(),
            $data['type'],
            (int) $data['id'],
            (int) $data['position'],
            (float) $data['percentage'],
        );

        return response()->json(['data' => $progress]);
    }
}
