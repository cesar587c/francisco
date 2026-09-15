<?php

use App\Http\Controllers\Api\IngestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — alimentação do dashboard pelo bot do WhatsApp
|--------------------------------------------------------------------------
|
| Rotas stateless protegidas pelo header X-Ingest-Token (ver
| App\Http\Middleware\EnsureIngestToken e config/dashboard.php).
| Não usam sessão/cookies — feitas para serem chamadas pelo processo
| Node.js do bot.
|
*/
Route::middleware('ingest.token')->prefix('ingest')->group(function () {
    Route::post('/respondents', [IngestController::class, 'upsertRespondent']);
});
