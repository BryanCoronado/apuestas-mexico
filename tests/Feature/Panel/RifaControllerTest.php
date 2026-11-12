<?php

namespace Tests\Feature\Panel;

use App\Models\Boleto;
use App\Models\Rifa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RifaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function datosValidos(array $cambios = []): array
    {
        return [
            'numero' => 7,
            'cifras' => 5,
            'emision' => 100000,
            'titulo' => 'GRAN RIFA SUZUKI',
            'titulo_corto' => 'SUZUKI R750',
            'fecha' => '2026-12-20 20:30',
            'paquetes' => [
                ['cantidad' => '1', 'precio' => '50'],
                ['cantidad' => '', 'precio' => ''],
            ],
            'abierta' => '1',
            ...$cambios,
        ];
    }

    public function test_lists_rifas_with_code_and_public_url(): void
    {
        Rifa::factory()->create(['numero' => 2, 'titulo_corto' => 'SUZUKI R750 2026']);

        $this->actingAs($this->admin)
            ->get(route('panel.rifas.index'))
            ->assertOk()
            ->assertSee('S2')
            ->assertSee('SUZUKI R750 2026')
            ->assertSee(config('ruleta.sitio').'/s2-lista');
    }

    public function test_filters_rifas_by_short_title(): void
    {
        Rifa::factory()->create(['titulo_corto' => 'DUCATI V4']);
        Rifa::factory()->create(['titulo_corto' => 'IPHONE Y BONO']);

        $this->actingAs($this->admin)
            ->get(route('panel.rifas.index', ['q' => 'ducati']))
            ->assertSee('DUCATI V4')
            ->assertDontSee('IPHONE Y BONO');
    }

    public function test_store_creates_rifa_with_images_and_drops_empty_packages(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('panel.rifas.store'), $this->datosValidos([
                'imagenes_premios' => [UploadedFile::fake()->image('moto.jpg')],
                'imagen_premio' => UploadedFile::fake()->image('premio.png'),
            ]))
            ->assertRedirect(route('panel.rifas.index'))
            ->assertSessionHasNoErrors();

        $rifa = Rifa::sole();
        $this->assertSame(7, $rifa->numero);
        $this->assertTrue($rifa->abierta);
        $this->assertFalse($rifa->es_zodiaco);
        $this->assertSame([['cantidad' => 1, 'precio' => 50]], $rifa->paquetes);
        $this->assertCount(1, $rifa->imagenes_premios);
        Storage::disk('public')->assertExists([$rifa->imagenes_premios[0], $rifa->imagen_premio]);
    }

    public function test_store_rejects_duplicated_number(): void
    {
        Rifa::factory()->create(['numero' => 7]);

        $this->actingAs($this->admin)
            ->post(route('panel.rifas.store'), $this->datosValidos())
            ->assertSessionHasErrors(['numero' => 'Ya existe una rifa con ese número.']);

        $this->assertSame(1, Rifa::count());
    }

    public function test_store_rejects_emission_above_digit_capacity(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.rifas.store'), $this->datosValidos(['cifras' => 2, 'emision' => 101]))
            ->assertSessionHasErrors(['emision' => 'La emisión no puede superar 100 boletos para esas cifras.']);
    }

    public function test_zodiac_rifa_allows_twelve_times_the_emission(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.rifas.store'), $this->datosValidos(['cifras' => 2, 'emision' => 1200, 'es_zodiaco' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertSame(1200, Rifa::sole()->emision);
    }

    public function test_store_requires_main_fields(): void
    {
        $this->actingAs($this->admin)
            ->post(route('panel.rifas.store'), [])
            ->assertSessionHasErrors(['numero', 'cifras', 'emision', 'titulo', 'titulo_corto', 'fecha']);
    }

    public function test_update_keeps_its_own_number_and_saves_changes(): void
    {
        $rifa = Rifa::factory()->create(['numero' => 7]);

        $this->actingAs($this->admin)
            ->put(route('panel.rifas.update', $rifa), $this->datosValidos(['titulo_corto' => 'NUEVO TITULO']))
            ->assertRedirect(route('panel.rifas.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('NUEVO TITULO', $rifa->fresh()->titulo_corto);
    }

    public function test_destroy_deletes_rifa_with_its_boletos(): void
    {
        $boleto = Boleto::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('panel.rifas.destroy', $boleto->rifa))
            ->assertRedirect(route('panel.rifas.index'));

        $this->assertModelMissing($boleto->rifa);
        $this->assertModelMissing($boleto);
    }

    public function test_duplicar_creates_closed_copy_with_next_number(): void
    {
        $rifa = Rifa::factory()->abierta()->create(['numero' => 10, 'titulo_corto' => 'DUCATI']);

        $this->actingAs($this->admin)
            ->post(route('panel.rifas.duplicar', $rifa))
            ->assertRedirect(route('panel.rifas.index'));

        $copia = Rifa::where('numero', 11)->sole();
        $this->assertSame('DUCATI (COPIA)', $copia->titulo_corto);
        $this->assertFalse($copia->abierta);
    }

    public function test_alternar_abierta_toggles_state(): void
    {
        $rifa = Rifa::factory()->create(['abierta' => false]);

        $this->actingAs($this->admin)
            ->patch(route('panel.rifas.abierta', $rifa))
            ->assertRedirect();

        $this->assertTrue($rifa->fresh()->abierta);
    }
}
