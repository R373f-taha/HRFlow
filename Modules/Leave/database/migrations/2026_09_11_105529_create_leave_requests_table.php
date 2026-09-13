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
Schema::create('leave_requests', function (Blueprint $table) {
    $table->id();

    $table->foreignId('employee_id')
        ->constrained()
        ->restrictOnDelete();

    $table->foreignId('leave_type_id')
        ->constrained()
        ->restrictOnDelete();

    $table->date('start_date');

    $table->date('end_date');

    $table->decimal('days_count', 8, 2);

    $table->string('status')
        ->default('pending');

  

    $table->foreignId('manager_approved_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->timestamp('manager_approved_at')
        ->nullable();

    $table->foreignId('admin_approved_by')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->timestamp('admin_approved_at')
        ->nullable();

    $table->text('rejection_reason')
        ->nullable();

    $table->string('document_path')
        ->nullable();

    $table->timestamps();

    $table->index([
        'employee_id',
        'start_date',
        'end_date',
    ]);

    $table->index('status');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
