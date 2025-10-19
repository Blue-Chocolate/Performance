<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('axis_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('axis_id')->constrained()->onDelete('cascade');
    $table->boolean('q1')->nullable();
    $table->boolean('q2')->nullable();
    $table->boolean('q3')->nullable();
    $table->boolean('q4')->nullable();
    $table->string('attachment_1')->nullable();
    $table->string('attachment_2')->nullable();
    $table->string('attachment_3')->nullable();
    $table->decimal('admin_score', 5, 2)->nullable(); // 0.00 - 100.00
    $table->timestamps();
    $table->unique(['organization_id', 'axis_id']); // one response per axis per org
            $table->unsignedInteger('score')->nullable()->after('response_text');

});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
