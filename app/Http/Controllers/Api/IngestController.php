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
     * atualiza o nome, grava/atualiza as respostas e recalcula o andamento
     * da conversa — a menos que conversation_step ou completed sejam
     * informados explicitamente. As respostas podem vir como um par
     * "question"/"answer" (uma por chamada) e/ou como um objeto "answers"
     * (várias de uma vez); os dois formatos podem ser usados juntos.
     */
    public function upsertRespondent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'name' => ['required', 'string'],
            'answers' => ['sometimes', 'array'],
            'answers.*' => ['nullable', 'string'],
            'question' => ['sometimes', 'required_with:answer', 'string'],
            'answer' => ['sometimes', 'required_with:question', 'string'],
            'conversation_step' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $phone = preg_replace('/\D+/', '', $data['phone']);

        if ($phone === '') {
            return response()->json(['error' => 'Telefone inválido.'], 422);
        }

        $answers = $data['answers'] ?? [];

        if (isset($data['question'], $data['answer'])) {
            $answers[$data['question']] = $data['answer'];
        }

        $respondent = Respondent::firstOrNew(['phone' => $phone]);
        $isNew = ! $respondent->exists;

        $respondent->name = $data['name'];

        if ($isNew) {
            $respondent->conversation_step = Respondent::STEP_WELCOME;
            $respondent->completed = false;
        }

        $respondent->save();

        foreach ($answers as $question => $answer) {
            $respondent->responses()->updateOrCreate(
                ['question' => $question],
                ['answer' => $answer]
            );
        }

        if (array_key_exists('conversation_step', $data)) {
            $respondent->conversation_step = $data['conversation_step'];
        } elseif (! empty($answers)) {
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
