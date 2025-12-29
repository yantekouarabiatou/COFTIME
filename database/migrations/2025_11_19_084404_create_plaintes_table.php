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
        Schema::create('plaintes', function (Blueprint $table) {
            $table->id();
             $table->string('Reference')->unique();
            $table->date('dates')->nullable();
            $table->string('motif_plainte')->nullable();
            $table->string('nom_client')->nullable();
            $table->string('requete_client')->nullable();
            $table->string('document')->nullable();
            $table->string('action_mener')->nullable();
            $table->string('action_entreprises')->nullable();
            $table->text('communication_personnel')->nullable();
            $table->enum('etat_plainte', ['En cours', 'Résolue', 'Fermée'])->default('En cours');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plaintes');
    }
};
