<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
      Schema::create('answers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('certificate_id')->constrained('performance_certificates')->onDelete('cascade');
    $table->foreignId('question_id')->constrained('criteria_questions')->onDelete('cascade');
    $table->string('selected_option');
    $table->decimal('points', 5, 2)->nullable(); // النقاط الأساسية من mapping
    $table->decimal('final_points', 5, 2)->nullable(); // النقاط × الوزن
    $table->string('attachment_path')->nullable();
    $table->timestamps();
});
    }

    public function down(): void {
        Schema::dropIfExists('answers');
    }
};
