<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIngestToken
{
    /**
     * Protege os endpoints que o bot do WhatsApp usa para alimentar o dashboard.
     * O bot deve enviar o header X-Ingest-Token com o valor de INGEST_API_TOKEN.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Ingest-Token');
        $expected = config('dashboard.ingest_token');

        if (! $expected || ! $token || ! hash_equals($expected, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
