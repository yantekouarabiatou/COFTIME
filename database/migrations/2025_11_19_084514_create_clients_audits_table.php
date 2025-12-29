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
        Schema::create('clients_audits', function (Blueprint $table) {
            $table->id();
            $table->string('nom_client')->nullable();
            $table->string('adresse')->nullable();
            $table->string('document')->nullable();
            $table->string('siege_social')->nullable();
            $table->decimal('frais_audit', 10, 2)->nullable();
            $table->decimal('frais_autres', 10, 2)->nullable();
            $table->enum('statut', ['actif', 'inactif', 'en_cours']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients_audits');
    }
};
