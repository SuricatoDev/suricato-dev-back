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
            $table->dateTime('data_partida');
            $table->dateTime('data_retorno');
            $table->string('origem');
            $table->string('destino');
            $table->integer('numero_vagas');
            $table->float('valor');
            $table->integer('organizador_id')->unsigned();
            $table->foreign('organizador_id')
                ->references('id')
                ->on('organizadores');
            $table->integer('evento_id')->unsigned();
            $table->foreign('evento_id')
                ->references('id')
                ->on('eventos');
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
