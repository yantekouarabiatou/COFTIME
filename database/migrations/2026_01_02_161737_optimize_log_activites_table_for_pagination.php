<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('log_activites', function (Blueprint $table) {
            // Index principal pour le tri par date (le plus important)
            $table->index('created_at');

            // Index composé pour les utilisateurs normaux qui ne voient que leurs logs
            // Très efficace pour : WHERE user_id = X ORDER BY created_at DESC
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::table('log_activites', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};