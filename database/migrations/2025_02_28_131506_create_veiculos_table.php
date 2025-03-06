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
        Schema::create('veiculos', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('placa');
            $table->string('marca');
            $table->string('antt');
            $table->enum('tipo', ['Van', 'Ônibus', 'Micro-ônibus']);
            $table->integer('capacidade');
            $table->string('motorista');
            $table->string('contato_motorista', 11);
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
        Schema::dropIfExists('veiculos');
    }
};
