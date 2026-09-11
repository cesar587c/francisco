<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'respondent_id',
        'question',
        'answer',
    ];

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }
}
