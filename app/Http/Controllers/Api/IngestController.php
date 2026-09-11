<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints usados pelo bot do WhatsApp (processo externo em Node.js) para
 * alimentar o dashboard: criar respondentes, salvar respostas e atualizar
 * o andamento da conversa.
 */
class IngestController extends Controller
{
    /**
     * POST /api/ingest/respondents
     * Cria o respondente se não existir (phone é único) ou apenas atualiza o nome.
     */
    public function upsertRespondent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'name' => ['required', 'string'],
        ]);

        $respondent = Respondent::firstOrNew(['phone' => $data['phone']]);
        $respondent->name = $data['name'];

        if (! $respondent->exists) {
            $respondent->conversation_step = Respondent::STEP_WELCOME;
            $respondent->completed = false;
        }

        $respondent->save();

        return response()->json($respondent);
    }

    /**
     * POST /api/ingest/responses
     * Registra uma resposta (resposta_1, resposta_2, etc.) de um respondente existente.
     */
    public function storeResponse(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
        ]);

        $respondent = Respondent::where('phone', $data['phone'])->first();

        if (! $respondent) {
            return response()->json(['error' => 'Respondente não encontrado.'], 404);
        }

        $response = $respondent->responses()->create([
            'question' => $data['question'],
            'answer' => $data['answer'],
        ]);

        return response()->json($response, 201);
    }

    /**
     * PATCH /api/ingest/respondents/{phone}/state
     * Atualiza o passo da conversa e se a pesquisa foi concluída.
     */
    public function updateState(Request $request, string $phone): JsonResponse
    {
        $data = $request->validate([
            'conversation_step' => ['required', 'integer', 'min:0', 'max:4'],
            'completed' => ['required', 'boolean'],
            'name' => ['sometimes', 'string'],
        ]);

        $respondent = Respondent::where('phone', $phone)->first();

        if (! $respondent) {
            return response()->json(['error' => 'Respondente não encontrado.'], 404);
        }

        $respondent->fill($data)->save();

        return response()->json($respondent);
    }
}
