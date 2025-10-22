<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('performance_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('executive_name');
            $table->string('email');
            $table->string('phone');
            $table->string('license_number');
            $table->enum('path', ['strategic', 'operational', 'hr']);
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('final_rank', ['bronze', 'silver', 'gold', 'diamond'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('performance_certificates');
    }
};
