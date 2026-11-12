<?php

namespace Tests\Feature\Panel;

use App\Models\Boleto;
use App\Models\Rifa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoletoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Brayan']);
    }

    public function test_shows_summary_of_reserved_paid_and_free_tickets(): void
    {
        $rifa = Rifa::factory()->create(['emision' => 100]);
        Boleto::factory()->count(3)->for($rifa)->create();
        Boleto::factory()->pagado()->for($rifa)->create();

        $this->actingAs($this->admin)
            ->get(route('panel.boletos.index', ['rifa' => $rifa->id]))
            ->assertOk()
            ->assertSee('reservados: 3 (3%)')
            ->assertSee('pagados: 1 (1%)')
            ->assertSee('libres: 96 (96%)');
    }

    public function test_defaults_to_open_rifa(): void
    {
        Rifa::factory()->create(['numero' => 50, 'titulo_corto' => 'CERRADA']);
        Rifa::factory()->abierta()->create(['numero' => 2, 'titulo_corto' => 'ABIERTA']);

        $this->actingAs($this->admin)
            ->get(route('panel.boletos.index'))
            ->assertViewHas('rifa', fn (Rifa $rifa) => $rifa->titulo_corto === 'ABIERTA');
    }

    public function test_paid_filter_and_search_narrow_results(): void
    {
        $rifa = Rifa::factory()->create();
        Boleto::factory()->for($rifa)->create(['numero' => '11111', 'nombre' => 'ARMANDO']);
        Boleto::factory()->pagado()->for($rifa)->create(['numero' => '22222', 'nombre' => 'ARMANDO']);
        Boleto::factory()->pagado()->for($rifa)->create(['numero' => '33333', 'nombre' => 'LUCIA']);

        $this->actingAs($this->admin)
            ->get(route('panel.boletos.index', ['rifa' => $rifa->id, 'filtro' => 'pagados', 'q' => 'arm']))
            ->assertViewHas('boletos', fn ($boletos) => $boletos->pluck('numero')->all() === ['22222']);
    }

    public function test_masks_phone_number_in_table(): void
    {
        $boleto = Boleto::factory()->create(['celular' => '4421234567']);

        $this->actingAs($this->admin)
            ->get(route('panel.boletos.index', ['rifa' => $boleto->rifa_id]))
            ->assertSee('442*******');
    }

    public function test_pagar_marks_selected_tickets_as_paid_by_current_user(): void
    {
        $seleccionado = Boleto::factory()->create();
        $otro = Boleto::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('panel.boletos.pagar'), ['boletos' => [$seleccionado->id]])
            ->assertRedirect()
            ->assertSessionHas('status', '1 boleto(s) marcados como pagados.');

        $seleccionado->refresh();
        $this->assertTrue($seleccionado->pagado);
        $this->assertNotNull($seleccionado->fecha_pago);
        $this->assertSame('Brayan', $seleccionado->modificado_por);
        $this->assertFalse($otro->fresh()->pagado);
    }

    public function test_pagar_requires_a_selection(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('panel.boletos.pagar'), [])
            ->assertSessionHasErrors(['boletos' => 'Selecciona al menos un boleto.']);
    }

    public function test_destroy_releases_selected_reservations(): void
    {
        $boleto = Boleto::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('panel.boletos.destroy'), ['boletos' => [$boleto->id]])
            ->assertRedirect();

        $this->assertModelMissing($boleto);
    }

    public function test_senalar_toggles_flag(): void
    {
        $boleto = Boleto::factory()->create(['senalado' => false]);

        $this->actingAs($this->admin)->patch(route('panel.boletos.senalar', $boleto));

        $this->assertTrue($boleto->fresh()->senalado);
    }

    public function test_exportar_downloads_csv_with_rifa_tickets(): void
    {
        $boleto = Boleto::factory()->create(['numero' => '88868', 'nombre' => 'ARMANDO']);

        $response = $this->actingAs($this->admin)
            ->get(route('panel.boletos.exportar', $boleto->rifa))
            ->assertOk()
            ->assertDownload('boletos-'.strtolower($boleto->rifa->codigo).'.csv');

        $this->assertStringContainsString('88868,NO,ARMANDO', $response->streamedContent());
    }
}
