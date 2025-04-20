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
        Schema::create('suporte', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 255);
            $table->string('descricao');
            $table->enum('status', ['Pendente', 'Em andamento', 'Concluído'])
                ->default('Pendente');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suporte');
    }
};
