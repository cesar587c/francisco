<?php

namespace App\Services;

use App\Models\Respondent;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Gera o relatório da pesquisa sem depender de nenhuma IA externa.
 *
 * A pergunta 2 (resposta_2) já pede que o participante se autodeclare
 * esquerda/direita/centro, então a classificação política é só uma busca
 * por essas palavras na resposta — não precisa de um modelo de linguagem.
 * O "relatório qualitativo" é montado a partir de estatísticas e das
 * palavras mais citadas na resposta 1 (o que incomoda o participante).
 */
class SurveyReportService
{
    private const STOPWORDS = [
        'a', 'ao', 'aos', 'aquele', 'aquela', 'as', 'até', 'com', 'como', 'da', 'das',
        'de', 'dela', 'dele', 'deles', 'depois', 'do', 'dos', 'e', 'ela', 'elas', 'ele',
        'eles', 'em', 'entre', 'essa', 'esse', 'esta', 'está', 'estamos', 'este', 'estou',
        'eu', 'foi', 'for', 'isso', 'isto', 'já', 'lhe', 'mais', 'mas', 'me', 'mesmo',
        'meu', 'meus', 'minha', 'minhas', 'muito', 'na', 'não', 'nas', 'nem', 'no', 'nos',
        'nossa', 'nosso', 'num', 'numa', 'o', 'os', 'ou', 'para', 'pela', 'pelas', 'pelo',
        'pelos', 'por', 'qual', 'quando', 'que', 'quem', 'se', 'sem', 'ser', 'seu', 'seus',
        'só', 'sua', 'suas', 'também', 'te', 'tem', 'ter', 'teu', 'teus', 'tinha', 'tudo',
        'um', 'uma', 'você', 'vocês', 'aqui', 'ali', 'lá', 'isso', 'coisa', 'coisas',
    ];

    /**
     * @param  Collection<int, Respondent>  $respondents
     * @return array{summary: array, qualitativeReport: string, rawData: array}
     */
    public function generateFullReport(Collection $respondents): array
    {
        $completed = $respondents->filter(fn ($r) => $r->completed)->values();
        $surveyData = $completed->map(fn ($r) => $this->normalizeAnswers($r))->values();

        $counts = ['ESQUERDA' => 0, 'DIREITA' => 0, 'CENTRO' => 0, 'INDEFINIDO' => 0];
        $classifications = $surveyData->map(function (array $item) use (&$counts) {
            $stance = $this->classifyStance($item['politicalStance']);
            $counts[$stance]++;

            return $stance;
        });

        $summary = [
            'total' => $respondents->count(),
            'completed' => $completed->count(),
            'pending' => $respondents->count() - $completed->count(),
            'political' => $counts,
        ];

        return [
            'summary' => $summary,
            'qualitativeReport' => $this->buildReport($summary, $surveyData, $classifications),
            'rawData' => $surveyData->all(),
        ];
    }

    /**
     * Junta as respostas de um respondente em { publicIssue, politicalStance }.
     */
    private function normalizeAnswers(Respondent $respondent): array
    {
        $byQuestion = [];
        foreach ($respondent->responses as $response) {
            $byQuestion[$response->question] = $response->answer;
        }

        return [
            'name' => $respondent->name,
            'publicIssue' => $byQuestion['resposta_1'] ?? '',
            'politicalStance' => $byQuestion['resposta_2'] ?? '',
        ];
    }

    /**
     * Classifica a resposta livre da pergunta 2 procurando as próprias
     * palavras que a pergunta pede ("esquerda", "direita" ou "centro").
     */
    private function classifyStance(string $text): string
    {
        $normalized = Str::of($text)->lower()->ascii()->toString();

        return match (true) {
            str_contains($normalized, 'esquerd') => 'ESQUERDA',
            str_contains($normalized, 'direit') => 'DIREITA',
            str_contains($normalized, 'centr') => 'CENTRO',
            default => 'INDEFINIDO',
        };
    }

    /**
     * Conta as palavras mais citadas nas respostas da pergunta 1, ignorando
     * palavras muito curtas e as stopwords do português.
     *
     * @param  Collection<int, array>  $surveyData
     * @return Collection<string, int>
     */
    private function topKeywords(Collection $surveyData, int $limit = 8): Collection
    {
        $words = $surveyData->flatMap(function (array $item) {
            $text = Str::of($item['publicIssue'])->lower()->ascii()->toString();
            $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

            return array_filter(
                preg_split('/\s+/', trim($text)) ?: [],
                fn ($word) => mb_strlen($word) > 3 && ! in_array($word, self::STOPWORDS, true)
            );
        });

        return $words->countBy()->sortDesc()->take($limit);
    }

    /**
     * @param  Collection<int, array>  $surveyData
     * @param  Collection<int, string>  $classifications
     */
    private function buildReport(array $summary, Collection $surveyData, Collection $classifications): string
    {
        $pct = fn (int $part, int $total) => $total === 0 ? 0 : round($part / $total * 100);
        $political = $summary['political'];
        $completedTotal = $summary['completed'];

        $lines = [];

        $lines[] = '# Resumo Executivo';
        $lines[] = "{$summary['total']} pessoas participaram da pesquisa até agora. Dessas, {$completedTotal} completaram as duas perguntas ({$pct($completedTotal, $summary['total'])}%) e {$summary['pending']} ainda estão pendentes.";
        $lines[] = '';

        $lines[] = '# Espectro Político';
        $lines[] = 'Classificação com base na autodeclaração de cada participante na pergunta 2:';
        foreach (['ESQUERDA' => 'Esquerda', 'DIREITA' => 'Direita', 'CENTRO' => 'Centro', 'INDEFINIDO' => 'Indefinido'] as $key => $label) {
            $lines[] = "- {$label}: {$political[$key]} ({$pct($political[$key], $completedTotal)}%)";
        }
        $lines[] = '';

        $keywords = $this->topKeywords($surveyData);
        $lines[] = '# Principais Temas Citados';
        if ($keywords->isEmpty()) {
            $lines[] = 'Ainda não há respostas suficientes para identificar temas recorrentes.';
        } else {
            $lines[] = 'Palavras mais citadas nas respostas sobre o que incomoda os participantes:';
            $lines[] = $keywords->map(fn ($count, $word) => "{$word} ({$count}x)")->implode(', ').'.';
        }
        $lines[] = '';

        $lines[] = '# Amostra de Respostas';
        $sample = $surveyData->filter(fn ($item) => $item['publicIssue'] !== '')->take(5);
        if ($sample->isEmpty()) {
            $lines[] = 'Nenhuma resposta registrada ainda.';
        } else {
            foreach ($sample as $i => $item) {
                $stance = $classifications->get($i);
                $lines[] = "- \"{$item['publicIssue']}\" — {$stance}";
            }
        }
        $lines[] = '';

        $lines[] = '# Conclusões';
        $stanceLabels = ['ESQUERDA' => 'esquerda', 'DIREITA' => 'direita', 'CENTRO' => 'centro', 'INDEFINIDO' => 'indefinido'];
        $topStanceKey = collect($political)->sortDesc()->keys()->first();
        $topWord = $keywords->keys()->first();

        if ($completedTotal === 0) {
            $lines[] = 'Ainda não há pesquisas completas para tirar conclusões.';
        } else {
            $conclusion = "A maioria dos participantes que se posicionou se identifica como {$stanceLabels[$topStanceKey]}.";
            if ($topWord) {
                $conclusion .= " O tema mais citado nas respostas foi \"{$topWord}\".";
            }
            $lines[] = $conclusion;
        }

        return implode("\n", $lines);
    }
}
