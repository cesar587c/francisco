<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use Illuminate\Http\JsonResponse;

class RespondentController extends Controller
{
    public function destroy(Respondent $respondent): JsonResponse
    {
        $id = $respondent->id;
        $respondent->delete();

        return response()->json(['success' => true, 'deleted' => $id]);
    }
}
