<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Models\SurveyAnalysis;
use App\Services\SurveyReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardDataController extends Controller
{
    public function index(): JsonResponse
    {
        $total = Respondent::count();
        $completed = Respondent::where('completed', true)->count();

        $respondents = Respondent::with('responses')
            ->orderByDesc('created_at')
            ->take(100)
            ->get();

        $lastAnalysis = SurveyAnalysis::orderByDesc('generated_at')->first();
        $todayCount = Respondent::where('created_at', '>=', Carbon::today())->count();

        return response()->json([
            'stats' => [
                'total' => $total,
                'completed' => $completed,
                'pending' => $total - $completed,
                'today' => $todayCount,
            ],
            'respondents' => $respondents,
            'lastAnalysis' => $lastAnalysis,
        ]);
    }

    public function store(SurveyReportService $reportService): JsonResponse
    {
        $respondents = Respondent::with('responses')->get();

        if ($respondents->isEmpty()) {
            return response()->json(['error' => 'Nenhum participante ainda.'], 400);
        }

        try {
            $report = $reportService->generateFullReport($respondents);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => $e->getMessage()], 500);
        }

        $analysis = SurveyAnalysis::create([
            'generated_at' => now(),
            'total_respondents' => $report['summary']['total'],
            'completed_respondents' => $report['summary']['completed'],
            'left_wing_count' => $report['summary']['political']['ESQUERDA'],
            'right_wing_count' => $report['summary']['political']['DIREITA'],
            'center_count' => $report['summary']['political']['CENTRO'],
            'other_count' => $report['summary']['political']['INDEFINIDO'],
            'report' => $report['qualitativeReport'],
            'raw_data' => $report['rawData'],
        ]);

        return response()->json([
            'analysis' => $analysis,
            'summary' => $report['summary'],
        ]);
    }
}
