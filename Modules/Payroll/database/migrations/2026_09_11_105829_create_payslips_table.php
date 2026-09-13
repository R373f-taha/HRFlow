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
    {Schema::create('payslips', function (Blueprint $table) {
    $table->id();

    $table->foreignId('payroll_run_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('employee_id')
        ->constrained()
        ->restrictOnDelete();

    $table->decimal('basic_salary', 12, 2);

    $table->decimal('total_allowances', 12, 2)
        ->default(0);

    $table->decimal('total_deductions', 12, 2)
        ->default(0);

    $table->decimal('unpaid_leave_deduction', 12, 2)
        ->default(0);

    $table->decimal('net_salary', 12, 2);

    $table->timestamps();

    $table->unique([
        'payroll_run_id',
        'employee_id',
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
