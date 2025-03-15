<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('organizadores', function (Blueprint $table) {
            $table->unsignedInteger('id'); // Usa o mesmo ID do user
            $table->primary('id'); // Define como chave primária
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade'); // Chave estrangeira vinculada a users
            $table->string('razao_social');
            $table->string('cnpj')->unique();
            $table->boolean('cadastur')->default(false);
            $table->string('inscricao_estadual');
            $table->string('inscricao_municipal');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('organizadores');
    }
};
