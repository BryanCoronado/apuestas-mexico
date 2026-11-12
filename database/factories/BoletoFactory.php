<?php

namespace Database\Factories;

use App\Models\Boleto;
use App\Models\Rifa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Boleto>
 */
class BoletoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rifa_id' => Rifa::factory(),
            'numero' => str_pad((string) fake()->unique()->numberBetween(0, 99999), 5, '0', STR_PAD_LEFT),
            'nombre' => strtoupper(fake()->firstName()),
            'apellido' => strtoupper(fake()->lastName().' '.fake()->lastName()),
            'celular' => fake()->numerify('33########'),
            'estado' => strtoupper(fake()->randomElement(['JALISCO', 'QUERÉTARO', 'CDMX', 'GUANAJUATO', 'NAYARIT', 'YUCATÁN'])),
            'pagado' => false,
            'fecha_pago' => null,
            'fecha_reserva' => now()->subDays(fake()->numberBetween(1, 20)),
            'modificado_por' => null,
            'senalado' => false,
        ];
    }

    public function pagado(): static
    {
        return $this->state(fn (array $attributes) => [
            'pagado' => true,
            'fecha_pago' => now()->subDay(),
        ]);
    }
}
