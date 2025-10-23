<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
       Schema::create('criteria_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('criteria_axis_id')->constrained('criteria_axes')->onDelete('cascade');
    $table->string('question_text');
    $table->json('options'); // [ "قبل شهر 3", "بعد شهر 3", ... ]
    $table->json('points_mapping'); // { "قبل شهر 3": 10, "بعد شهر 3": 8, ... }
    $table->boolean('attachment_required')->default(true);
    $table->string('path');
    $table->decimal('max_points', 5, 2)->default(0);
    $table->decimal('weight', 5, 2)->default(1); // 👈 الوزن الخاص بالسؤال
    $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('criteria_questions');
    }
};
