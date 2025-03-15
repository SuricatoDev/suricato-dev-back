<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passageiros', function (Blueprint $table) {
            $table->unsignedInteger('id'); // Usa o mesmo ID do user
            $table->primary('id'); // Define como chave primária
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade'); // Chave estrangeira vinculada a users
            $table->string('cpf')->unique();
            $table->string('rg');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passageiros');
    }
};
