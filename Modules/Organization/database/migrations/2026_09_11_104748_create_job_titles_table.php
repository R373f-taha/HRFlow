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
      Schema::create('job_titles', function (Blueprint $table) {
    $table->id();

    $table->foreignId('department_id')->constrained() ->restrictOnDelete();

    $table->string('name');

    $table->string('grade')->nullable();

    $table->timestamps();

    $table->unique(['department_id','name',]);
    
});}
    public function down(): void
    {
        Schema::dropIfExists('job_titles');
    }
};
