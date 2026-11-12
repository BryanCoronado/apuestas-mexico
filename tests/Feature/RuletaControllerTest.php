<?php

namespace Tests\Feature;

use App\Models\Boleto;
use App\Models\Rifa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuletaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_rifas_from_database(): void
    {
        Rifa::factory()->create(['numero' => 2]);

        $this->get(route('ruleta.index'))
            ->assertOk()
            ->assertSee('data-codigo="S2"', false);
    }

    public function test_ganador_returns_ticket_inside_date_range(): void
    {
        $rifa = Rifa::factory()->create();
        Boleto::factory()->for($rifa)->create(['numero' => '12345', 'fecha_reserva' => '2026-11-10 12:00:00']);
        Boleto::factory()->for($rifa)->create(['numero' => '99999', 'fecha_reserva' => '2026-12-10 12:00:00']);

        $this->getJson(route('ruleta.ganador', [
            'rifa' => $rifa->id,
            'inicio' => '2026-11-01 00:00',
            'fin' => '2026-11-30 23:59',
        ]))
            ->assertOk()
            ->assertJsonPath('ganador.boleto', '12345');
    }

    public function test_ganador_with_minimum_only_picks_paid_tickets_of_qualifying_clients(): void
    {
        $rifa = Rifa::factory()->create();
        Boleto::factory()->count(2)->pagado()->for($rifa)->create(['celular' => '3311111111', 'fecha_reserva' => '2026-11-10 12:00:00']);
        Boleto::factory()->pagado()->for($rifa)->create(['celular' => '3322222222', 'fecha_reserva' => '2026-11-10 12:00:00']);
        Boleto::factory()->for($rifa)->create(['celular' => '3311111111', 'fecha_reserva' => '2026-11-10 12:00:00']);

        $ids = collect(range(1, 15))->map(fn () => $this->getJson(route('ruleta.ganador', [
            'rifa' => $rifa->id,
            'inicio' => '2026-11-01 00:00',
            'fin' => '2026-11-30 23:59',
            'minimo' => 2,
        ]))->assertOk()->json('ganador'));

        $ids->each(fn (array $ganador) => $this->assertSame('PAGADO', $ganador['status']));
        $permitidos = Boleto::where('celular', '3311111111')->where('pagado', true)->pluck('numero')->all();
        $ids->each(fn (array $ganador) => $this->assertContains($ganador['boleto'], $permitidos));
    }

    public function test_ganador_returns_404_when_no_ticket_matches(): void
    {
        $rifa = Rifa::factory()->create();

        $this->getJson(route('ruleta.ganador', [
            'rifa' => $rifa->id,
            'inicio' => '2026-11-01 00:00',
            'fin' => '2026-11-30 23:59',
        ]))
            ->assertNotFound()
            ->assertJsonPath('message', 'No hay boletos que cumplan los filtros.');
    }

    public function test_ganador_validates_unknown_rifa(): void
    {
        $this->getJson(route('ruleta.ganador', ['rifa' => 999, 'inicio' => '2026-11-01', 'fin' => '2026-11-30']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rifa');
    }
}
