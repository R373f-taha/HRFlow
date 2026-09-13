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
      Schema::create('employees', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->unique()
        ->constrained()
        ->cascadeOnDelete();

    $table->string('employee_number')
        ->unique();

    $table->foreignId('department_id')
        ->constrained()
        ->restrictOnDelete();

    $table->foreignId('job_title_id')
        ->constrained()
        ->restrictOnDelete();

    $table->foreignId('manager_id')
        ->nullable()
        ->constrained('employees')
        ->nullOnDelete();

    $table->string('employment_type');

    $table->date('hire_date');

    $table->date('termination_date')
        ->nullable();

    $table->string('termination_reason')
        ->nullable();

    $table->string('status')
        ->default('active');

    $table->string('national_id')
        ->unique();

    $table->string('phone')
        ->nullable();

    $table->text('address')
        ->nullable();

    $table->timestamps();


    //we cannot use softDeletes here because employee doesn`t deleted but his service finishes
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
