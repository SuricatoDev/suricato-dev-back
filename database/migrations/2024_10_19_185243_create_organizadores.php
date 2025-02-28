<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('organizadores', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->string('razao_social');
            $table->string('cnpj')->unique();
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
