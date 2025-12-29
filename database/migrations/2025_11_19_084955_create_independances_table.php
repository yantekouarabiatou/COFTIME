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
        Schema::create('independances', function (Blueprint $table) {
            $table->id();
            $table->string('nom_client')->nullable();
            $table->string('adresse')->nullable();
            $table->string('siege_social')->nullable();
            $table->string('type_entite')->nullable();
            $table->decimal('frais_audit', 10, 2)->nullable();
            $table->decimal('frais_non_audit', 10, 2)->nullable();
            $table->integer('honoraire_audit_exercice')->nullable();
            $table->integer('honoraire_audit_travail')->nullable();
            $table->string('associes_mission')->nullable();
            $table->text('nombres_annees_experiences')->nullable();
            $table->text('question_independance')->nullable();
            $table->text('actions_recquise')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('autres_services_fournit')->nullable();
            $table->string('responsable_audit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('independances');
    }
};
