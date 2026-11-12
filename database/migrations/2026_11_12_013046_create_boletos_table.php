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
        Schema::create('boletos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rifa_id')->constrained()->cascadeOnDelete();
            $table->string('numero', 10);
            $table->string('nombre')->nullable();
            $table->string('apellido')->nullable();
            $table->string('celular', 20)->nullable()->index();
            $table->string('estado')->nullable();
            $table->boolean('pagado')->default(false);
            $table->dateTime('fecha_pago')->nullable();
            $table->dateTime('fecha_reserva')->useCurrent();
            $table->string('modificado_por')->nullable();
            $table->boolean('senalado')->default(false);
            $table->timestamps();

            $table->unique(['rifa_id', 'numero']);
            $table->index(['rifa_id', 'pagado']);
            $table->index(['rifa_id', 'fecha_reserva']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};
