<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('slogan')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->string('logo')->nullable(); // image
            $table->string('site_web')->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
