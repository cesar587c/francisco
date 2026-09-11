<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Respondent extends Model
{
    use HasFactory;

    public const STEP_WELCOME = 0;
    public const STEP_QUESTION_1 = 1;
    public const STEP_QUESTION_2 = 2;
    public const STEP_CONFIRM_2 = 3;
    public const STEP_COMPLETED = 4;

    protected $fillable = [
        'phone',
        'name',
        'conversation_step',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'conversation_step' => 'integer',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }
}
