<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\StoreRifaRequest;
use App\Http\Requests\Panel\UpdateRifaRequest;
use App\Models\Rifa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RifaController extends Controller
{
    public function index(Request $request): View
    {
        $orden = $request->query('orden') === 'asc' ? 'asc' : 'desc';
        $busqueda = trim((string) $request->query('q'));

        $rifas = Rifa::query()
            ->when($busqueda !== '', fn ($query) => $query->where('titulo_corto', 'like', '%'.$busqueda.'%'))
            ->orderBy('numero', $orden)
            ->paginate(15)
            ->withQueryString();

        $rifaEditar = $request->filled('editar') ? Rifa::find($request->integer('editar')) : null;

        return view('panel.rifas.index', compact('rifas', 'orden', 'busqueda', 'rifaEditar'));
    }

    public function store(StoreRifaRequest $request): RedirectResponse
    {
        $rifa = Rifa::create($this->datosDesde($request));

        return redirect()->route('panel.rifas.index')->with('status', "Rifa {$rifa->codigo} creada.");
    }

    public function update(UpdateRifaRequest $request, Rifa $rifa): RedirectResponse
    {
        $rifa->update($this->datosDesde($request, $rifa));

        return redirect()->route('panel.rifas.index')->with('status', "Rifa {$rifa->codigo} actualizada.");
    }

    public function destroy(Rifa $rifa): RedirectResponse
    {
        $this->borrarArchivos([...($rifa->imagenes_premios ?? []), $rifa->imagen_premio]);
        $rifa->delete();

        return redirect()->route('panel.rifas.index')->with('status', "Rifa {$rifa->codigo} eliminada.");
    }

    public function duplicar(Rifa $rifa): RedirectResponse
    {
        $copia = $rifa->replicate();
        $copia->numero = (int) Rifa::max('numero') + 1;
        $copia->titulo_corto = Str::limit($rifa->titulo_corto.' (COPIA)', 120, '');
        $copia->abierta = false;
        $copia->imagenes_premios = collect($rifa->imagenes_premios ?? [])
            ->map(fn (string $ruta) => $this->copiarArchivo($ruta))
            ->filter()
            ->values()
            ->all();
        $copia->imagen_premio = $rifa->imagen_premio ? $this->copiarArchivo($rifa->imagen_premio) : null;
        $copia->save();

        return redirect()->route('panel.rifas.index')->with('status', "Rifa {$rifa->codigo} duplicada como {$copia->codigo}.");
    }

    public function alternarAbierta(Rifa $rifa): RedirectResponse
    {
        $rifa->update(['abierta' => ! $rifa->abierta]);

        return back()->with('status', "Rifa {$rifa->codigo} ".($rifa->abierta ? 'abierta.' : 'cerrada.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function datosDesde(StoreRifaRequest $request, ?Rifa $rifa = null): array
    {
        $datos = $request->safe()->except(['imagenes_premios', 'imagen_premio']);

        $datos['paquetes'] = collect($datos['paquetes'] ?? [])
            ->map(fn (array $paquete) => [
                'cantidad' => (int) $paquete['cantidad'],
                'precio' => (float) $paquete['precio'],
            ])
            ->all();

        if ($request->hasFile('imagenes_premios')) {
            $this->borrarArchivos($rifa?->imagenes_premios ?? []);
            $datos['imagenes_premios'] = collect($request->file('imagenes_premios'))
                ->map(fn ($archivo) => $archivo->store('rifas', 'public'))
                ->all();
        }

        if ($request->hasFile('imagen_premio')) {
            $this->borrarArchivos([$rifa?->imagen_premio]);
            $datos['imagen_premio'] = $request->file('imagen_premio')->store('rifas', 'public');
        }

        return $datos;
    }

    /**
     * @param  array<int, string|null>  $rutas
     */
    private function borrarArchivos(array $rutas): void
    {
        $rutas = array_filter($rutas);

        if ($rutas !== []) {
            Storage::disk('public')->delete($rutas);
        }
    }

    private function copiarArchivo(string $ruta): ?string
    {
        $disco = Storage::disk('public');

        if (! $disco->exists($ruta)) {
            return null;
        }

        $destino = 'rifas/'.Str::random(40).'.'.pathinfo($ruta, PATHINFO_EXTENSION);
        $disco->copy($ruta, $destino);

        return $destino;
    }
}
