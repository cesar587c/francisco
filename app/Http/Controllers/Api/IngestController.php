<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint usado pelo bot do WhatsApp (processo externo em Node.js) para
 * alimentar o dashboard: cria/atualiza o respondente, salva respostas e
 * atualiza o andamento da conversa, tudo em uma única chamada.
 */
class IngestController extends Controller
{
    /**
     * POST /api/ingest/respondents
     *
     * Pode ser chamado várias vezes ao longo da conversa: a cada chamada,
     * cria o respondente se ainda não existir (phone é a chave única),
     * atualiza o nome, grava/atualiza as respostas informadas em "answers"
     * e recalcula o andamento da conversa — a menos que conversation_step
     * ou completed sejam informados explicitamente.
     */
    public function upsertRespondent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'name' => ['required', 'string'],
            'answers' => ['sometimes', 'array'],
            'answers.*' => ['nullable', 'string'],
            'conversation_step' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $phone = preg_replace('/\D+/', '', $data['phone']);

        if ($phone === '') {
            return response()->json(['error' => 'Telefone inválido.'], 422);
        }

        $respondent = Respondent::firstOrNew(['phone' => $phone]);
        $isNew = ! $respondent->exists;

        $respondent->name = $data['name'];

        if ($isNew) {
            $respondent->conversation_step = Respondent::STEP_WELCOME;
            $respondent->completed = false;
        }

        $respondent->save();

        foreach ($data['answers'] ?? [] as $question => $answer) {
            $respondent->responses()->updateOrCreate(
                ['question' => $question],
                ['answer' => $answer]
            );
        }

        if (array_key_exists('conversation_step', $data)) {
            $respondent->conversation_step = $data['conversation_step'];
        } elseif (! empty($data['answers'])) {
            $respondent->conversation_step = min(
                Respondent::STEP_COMPLETED,
                $respondent->responses()->count()
            );
        }

        if (array_key_exists('completed', $data)) {
            $respondent->completed = $data['completed'];
        } elseif ($respondent->conversation_step >= Respondent::STEP_COMPLETED) {
            $respondent->completed = true;
        }

        $respondent->save();

        return response()->json($respondent->load('responses'));
    }
}
