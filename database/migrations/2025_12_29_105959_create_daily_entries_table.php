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
        Schema::create('daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('jour');
            $table->decimal('heures_theoriques', 4, 2)->default(0);
            $table->decimal('heures_totales', 5, 2)->default(0);
            $table->boolean('is_weekend')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'jour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_entries');
    }
};
