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
        Schema::create('caravana_imagens', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('ordem');
            $table->string('path');
            $table->integer('caravana_id')->unsigned();
            $table->foreign('caravana_id')
                ->references('id')
                ->on('caravanas')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caravana_imagens');
    }
};
