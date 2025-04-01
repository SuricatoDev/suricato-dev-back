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
            $table->foreign('id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // Chave estrangeira vinculada a users
            $table->string('cpf', 11)->unique()->notNull();
            $table->string('rg', 14);
            $table->string('contato_emergencia', 11);
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


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passageiros');
    }
};
