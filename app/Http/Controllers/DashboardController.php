<?php

namespace App\Http\Controllers;

use App\Services\IngestTokenManager;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(): View
    {
        return view('dashboard', [
            'apiBaseUrl' => url('/api/ingest'),
            'ingestToken' => IngestTokenManager::current(),
        ]);
    }

    public function regenerateToken(): JsonResponse
    {
        return response()->json(['token' => IngestTokenManager::regenerate()]);
    }
}
