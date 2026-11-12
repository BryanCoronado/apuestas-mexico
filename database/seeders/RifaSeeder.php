<?php

namespace Database\Seeders;

use App\Models\Boleto;
use App\Models\Rifa;
use Illuminate\Database\Seeder;

class RifaSeeder extends Seeder
{
    /**
     * Rifas de demostración con algunos boletos apartados y pagados.
     */
    public function run(): void
    {
        $rifas = [
            ['numero' => 100, 'titulo_corto' => 'IPHONE Y BONO', 'fecha' => '2026-07-02 22:00:00', 'cifras' => 4, 'emision' => 60000, 'abierta' => false],
            ['numero' => 2, 'titulo_corto' => 'SUZUKI R750 2026', 'fecha' => '2026-11-17 20:30:00', 'cifras' => 6, 'emision' => 1000000, 'abierta' => true],
            ['numero' => 1, 'titulo_corto' => 'DUCATI V4 2020', 'fecha' => '2026-10-13 19:00:00', 'cifras' => 6, 'emision' => 1000000, 'abierta' => false],
        ];

        foreach ($rifas as $datos) {
            $rifa = Rifa::factory()->create([
                ...$datos,
                'titulo' => 'GRAN RIFA '.$datos['titulo_corto'],
                'created_at' => now()->subMonths(2),
            ]);

            $numeros = collect(range(1, 400))
                ->map(fn () => str_pad((string) random_int(0, min($rifa->emision, 10 ** $rifa->cifras) - 1), $rifa->cifras, '0', STR_PAD_LEFT))
                ->unique()
                ->values();

            $numeros->each(function (string $numero, int $indice) use ($rifa): void {
                Boleto::factory()
                    ->when($indice % 4 === 0, fn ($factory) => $factory->pagado())
                    ->create([
                        'rifa_id' => $rifa->id,
                        'numero' => $numero,
                        'fecha_reserva' => now()->subDays(random_int(1, 50)),
                    ]);
            });
        }
    }
}
