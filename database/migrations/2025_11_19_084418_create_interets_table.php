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
        Schema::create('interets', function (Blueprint $table) {
            $table->id();
            $table->string('details')->nullable();
            $table->string('nom')->nullable();
            $table->string('document')->nullable();
            $table->date('date_Notification')->nullable()->nullable();
            $table->foreignId('poste_id')->nullable()->constrained('postes')->onDelete('set null');
            $table->string('mesure_prise')->nullable();
            $table->enum('etat_interet', ['Actif', 'Inactif'])->default('Actif');
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
        Schema::dropIfExists('interets');
    }
};
