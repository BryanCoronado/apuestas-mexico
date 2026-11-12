<?php

namespace Database\Factories;

use App\Models\Rifa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rifa>
 */
class RifaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titulo = strtoupper(fake()->words(2, true));

        return [
            'numero' => fake()->unique()->numberBetween(1, 99999),
            'titulo' => 'GRAN RIFA '.$titulo,
            'titulo_corto' => $titulo,
            'fecha' => now()->addDays(fake()->numberBetween(3, 60))->setTime(20, 30),
            'cifras' => 5,
            'emision' => Rifa::emisionPara(5, false),
            'es_zodiaco' => false,
            'es_gratuita' => false,
            'abierta' => false,
            'paquetes' => [
                ['cantidad' => 1, 'precio' => 50],
                ['cantidad' => 5, 'precio' => 200],
            ],
            'texto_precios' => '1 boleto x $50',
            'premios_secundarios' => null,
            'imagenes_premios' => [],
            'imagen_premio' => null,
            'created_at' => now()->subDays(30),
        ];
    }

    public function abierta(): static
    {
        return $this->state(fn (array $attributes) => [
            'abierta' => true,
        ]);
    }
}
