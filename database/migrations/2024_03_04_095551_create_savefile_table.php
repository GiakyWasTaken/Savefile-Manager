<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $table = 'savefile';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('savefile', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->timestamps();
            $table->foreignId('fk_id_game')->constrained('game');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savefile');
        // Backup the files adding the timestamp to the file name
        $timestamp = date('Y_m_d_His');
        // Copy each file individually
        foreach (Storage::files('saves/') as $file) {
            Storage::copy($file, 'backups/' . basename($file) . '_' . $timestamp . '.bak');
        }
        Storage::deleteDirectory('saves/');
    }
};
