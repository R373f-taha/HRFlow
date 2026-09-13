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
    Schema::create('performance_reviews', function (Blueprint $table) {
    $table->id();

    $table->foreignId('performance_cycle_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('employee_id')
        ->constrained()
        ->restrictOnDelete();

    $table->foreignId('manager_id')
        ->constrained('employees')
        ->restrictOnDelete();

    $table->unsignedTinyInteger('overall_rating');

    $table->text('strengths')
        ->nullable();

    $table->text('areas_for_improvement')
        ->nullable();

    $table->text('goals')
        ->nullable();

    $table->timestamps();
$table->unique(
    ['performance_cycle_id', 'employee_id', 'manager_id'],
    'performance_review_unique'
);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
