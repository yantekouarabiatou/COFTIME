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
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->onDelete('cascade');
            $table->decimal('heures', 4, 2);
            $table->text('travaux')->nullable();
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dossier_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
