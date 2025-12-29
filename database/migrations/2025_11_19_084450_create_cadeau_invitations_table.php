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
        Schema::create('cadeau_invitations', function (Blueprint $table) {
            $table->id();
             $table->string('nom')->nullable();
            $table->date('date')->nullable();
            $table->string('cadeau_hospitalite')->nullable();
            $table->string('document')->nullable();
            $table->text('description')->nullable();
            $table->decimal('valeurs', 10, 2)->nullable();
            $table->enum('action_prise', ['accepté', 'refusé', 'en_attente']);
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
        Schema::dropIfExists('cadeau_invitations');
    }
};
