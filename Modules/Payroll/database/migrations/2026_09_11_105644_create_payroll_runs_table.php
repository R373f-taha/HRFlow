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
  Schema::create('payroll_runs', function (Blueprint $table) {
    $table->id();

    $table->unsignedSmallInteger('year');

    $table->unsignedTinyInteger('month');

    $table->string('status')
        ->default('draft');

    $table->timestamp('processed_at')
        ->nullable();

    $table->timestamp('finalized_at')
        ->nullable();

    $table->timestamps();

    $table->unique([
        'year',
        'month',
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
