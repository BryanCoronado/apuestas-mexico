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
        Schema::create('rifas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->unique();
            $table->string('titulo');
            $table->string('titulo_corto', 120);
            $table->dateTime('fecha');
            $table->unsignedTinyInteger('cifras');
            $table->unsignedInteger('emision');
            $table->boolean('es_zodiaco')->default(false);
            $table->boolean('es_gratuita')->default(false);
            $table->boolean('abierta')->default(false);
            $table->json('paquetes')->nullable();
            $table->text('texto_precios')->nullable();
            $table->text('premios_secundarios')->nullable();
            $table->json('imagenes_premios')->nullable();
            $table->string('imagen_premio')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rifas');
    }
};
