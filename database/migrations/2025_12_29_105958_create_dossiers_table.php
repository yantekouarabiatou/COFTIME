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
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique()->nullable();
            $table->string('nom');

            $table->enum('type_dossier', [
                'audit',
                'conseil',
                'formation',
                'expertise',
                'autre'
            ])->default('audit');

            $table->text('description')->nullable();

            $table->date('date_ouverture');
            $table->date('date_cloture_prevue')->nullable();
            $table->date('date_cloture_reelle')->nullable();

            $table->enum('statut', [
                'ouvert',
                'en_cours',
                'suspendu',
                'cloture',
                'archive'
            ])->default('ouvert');

            $table->decimal('budget', 12, 2)->nullable();
            $table->decimal('frais_dossier', 12, 2)->nullable();

            $table->string('document')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('client_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
