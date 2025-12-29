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
        Schema::create('assignations', function (Blueprint $table) {
            $table->id();
            $table->string('nom_prenom')->nullable();
            $table->string('fonction')->nullable();
            $table->string('signature')->nullable();
            $table->date('date')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plainte_id')->constrained('plaintes')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignations');
    }
};
