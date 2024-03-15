<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'console';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('console', function (Blueprint $table) {
            $table->id();
            $table->string('console_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('console');
    }
};
