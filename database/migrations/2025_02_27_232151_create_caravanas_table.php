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
        Schema::create('caravanas', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('titulo');
            $table->text('descricao');
            $table->string('categoria');
            $table->dateTime('data_partida');
            $table->dateTime('data_retorno');
            $table->string('endereco_origem');
            $table->string('numero_origem')->nullable();
            $table->string('bairro_origem');
            $table->string('cep_origem');
            $table->string('cidade_origem');
            $table->string('estado_origem');
            $table->string('endereco_destino');
            $table->string('numero_destino')->nullable();
            $table->string('bairro_destino');
            $table->string('cep_destino');
            $table->string('cidade_destino');
            $table->string('estado_destino');
            $table->integer('numero_vagas');
            $table->integer('vagas_disponiveis');
            $table->float('valor');
            $table->integer('organizador_id')->unsigned();
            $table->foreign('organizador_id')
                ->references('id')
                ->on('organizadores');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caravanas');
    }
};
