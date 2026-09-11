<?php

namespace App\Http\Middleware;

use App\Services\IngestTokenManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIngestToken
{
    /**
     * Protege os endpoints que o bot do WhatsApp usa para alimentar o dashboard.
     * O bot deve enviar o header X-Ingest-Token com o token gerado pelo painel
     * (aba Integração), guardado na tabela settings.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Ingest-Token');
        $expected = IngestTokenManager::current();

        if (! $token || ! hash_equals($expected, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
