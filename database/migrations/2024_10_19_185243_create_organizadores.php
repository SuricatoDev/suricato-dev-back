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
            $table->string('cnpj', 14)->unique()->notNull();
            $table->boolean('cadastur')->default(false);
            $table->string('inscricao_estadual', 14);
            $table->string('inscricao_municipal', 14);
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('organizadores');
    }
};
