<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_at',
        'total_respondents',
        'completed_respondents',
        'left_wing_count',
        'right_wing_count',
        'center_count',
        'other_count',
        'report',
        'raw_data',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'raw_data' => 'array',
    ];
}
