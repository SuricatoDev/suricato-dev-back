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
        Schema::create('denuncias', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('descricao');
            $table->enum('status', ['Pendente', 'Em andamento', 'Concluído']);
            $table->integer('denunciante_id')->unsigned();
            $table->foreign('denunciante_id')
                ->references('id')
                ->on('users');
            $table->integer('passageiro_id')->unsigned();
            $table->foreign('passageiro_id')
                ->references('id')
                ->on('passageiros');
            $table->integer('organizador_id')->unsigned();
            $table->foreign('organizador_id')
                ->references('id')
                ->on('organizadores');
            $table->integer('caravana_id')->unsigned();
            $table->foreign('caravana_id')
                ->references('id')
                ->on('caravanas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denuncias');
    }
};
