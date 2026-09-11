<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_analyses', function (Blueprint $table) {
            $table->id();
            $table->timestamp('generated_at')->useCurrent();
            $table->unsignedInteger('total_respondents');
            $table->unsignedInteger('completed_respondents');
            $table->unsignedInteger('left_wing_count');
            $table->unsignedInteger('right_wing_count');
            $table->unsignedInteger('center_count');
            $table->unsignedInteger('other_count');
            $table->longText('report');
            $table->json('raw_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_analyses');
    }
};
