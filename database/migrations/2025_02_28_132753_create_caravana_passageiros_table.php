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
        Schema::create('caravana_passageiros', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->dateTime('data');
            $table->integer('passageiro_id')->unsigned();
            $table->foreign('passageiro_id')
                ->references('id')
                ->on('passageiros');
            $table->integer('caravana_id')->unsigned();
            $table->foreign('caravana_id')
                ->references('id')
                ->on('caravanas');
            $table->enum('status', ['Pendente', 'Confirmado', 'Cancelado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caravana_passageiros');
    }
};
