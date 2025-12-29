<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_activites', function (Blueprint $table) {
            $table->id();

            // Qui a fait l’action ?
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Quoi a été fait ?
            $table->string('action'); // create, update, delete, login, etc.

            // Sur quelle table ?
            $table->string('table_cible')->nullable();

            // Sur quel enregistrement ?
            $table->unsignedBigInteger('enregistrement_id')->nullable();

            // Description détaillée
            $table->text('description')->nullable();

            // Informations d’avant / après
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Informations techniques
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Succès ou échec
            $table->enum('status', ['success', 'failed'])->default('success');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_activites');
    }
};
