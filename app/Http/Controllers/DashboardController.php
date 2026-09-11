<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(): View
    {
        return view('dashboard', [
            'apiBaseUrl' => url('/api/ingest'),
            'ingestToken' => config('dashboard.ingest_token'),
        ]);
    }
}
