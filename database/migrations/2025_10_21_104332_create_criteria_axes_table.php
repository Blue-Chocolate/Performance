<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('criteria_axes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // مثال: المسار الاستراتيجي
            $table->string('description')->nullable();
            $table->enum('path', ['strategic', 'operational', 'hr']); // تابع لأي مسار
            $table->timestamps();
            $table->integer('weight')->nullable();

        });
    }

    public function down(): void {
        Schema::dropIfExists('criteria_axes');
    }
};

