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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('nom');
            $table->string('email')->nullable();

            $table->string('siege_social')->nullable();
            $table->text('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('contact_principal')->nullable();
            $table->string('secteur_activite')->nullable();

            $table->string('numero_siret', 14)->nullable()->unique();
            $table->string('code_naf', 10)->nullable();

            $table->string('logo')->nullable();
            $table->string('site_web')->nullable();

            $table->text('notes')->nullable();

            $table->enum('statut', ['actif', 'inactif', 'prospect'])
                  ->default('prospect');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
