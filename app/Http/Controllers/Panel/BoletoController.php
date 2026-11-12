<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\Rifa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BoletoController extends Controller
{
    /**
     * @var list<int>
     */
    public const POR_PAGINA = [50, 100, 500, 1000, 10000];

    /**
     * @var list<string>
     */
    private const COLUMNAS_ORDENABLES = ['numero', 'fecha_pago', 'fecha_reserva'];

    public function index(Request $request): View
    {
        $rifas = Rifa::query()->orderByDesc('numero')->get();

        $rifa = $rifas->firstWhere('id', $request->integer('rifa'))
            ?? $rifas->firstWhere('abierta', true)
            ?? $rifas->first();

        $filtro = in_array($request->query('filtro'), ['pagados', 'no_pagados'], true) ? $request->query('filtro') : 'todos';
        $busqueda = trim((string) $request->query('q'));
        $orden = in_array($request->query('orden'), self::COLUMNAS_ORDENABLES, true) ? $request->query('orden') : 'fecha_reserva';
        $direccion = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        $porPagina = in_array($request->integer('por_pagina'), self::POR_PAGINA, true) ? $request->integer('por_pagina') : 100;

        $boletos = null;
        $resumen = null;

        if ($rifa) {
            $boletos = $rifa->boletos()
                ->buscar($busqueda)
                ->when($filtro === 'pagados', fn ($query) => $query->where('pagado', true))
                ->when($filtro === 'no_pagados', fn ($query) => $query->where('pagado', false))
                ->orderBy($orden, $direccion)
                ->orderBy('id', $direccion)
                ->paginate($porPagina)
                ->withQueryString();

            $resumen = $rifa->resumenBoletos();
        }

        return view('panel.boletos.index', [
            'rifas' => $rifas,
            'rifa' => $rifa,
            'boletos' => $boletos,
            'resumen' => $resumen,
            'filtro' => $filtro,
            'busqueda' => $busqueda,
            'orden' => $orden,
            'direccion' => $direccion,
            'porPagina' => $porPagina,
            'opcionesPorPagina' => self::POR_PAGINA,
        ]);
    }

    public function exportar(Rifa $rifa): StreamedResponse
    {
        return response()->streamDownload(function () use ($rifa): void {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Numero', 'Pagado', 'Nombre', 'Apellido', 'Celular', 'Estado', 'Fecha Pago', 'Fecha Reserva', 'Mod']);

            $rifa->boletos()->orderBy('numero')->lazyById(2000)->each(function (Boleto $boleto) use ($salida): void {
                fputcsv($salida, [
                    $boleto->numero,
                    $boleto->pagado ? 'SI' : 'NO',
                    $boleto->nombre,
                    $boleto->apellido,
                    $boleto->celular,
                    $boleto->estado,
                    $boleto->fecha_pago?->format('Y-m-d H:i:s'),
                    $boleto->fecha_reserva?->format('Y-m-d H:i:s'),
                    $boleto->modificado_por,
                ]);
            });

            fclose($salida);
        }, 'boletos-'.strtolower($rifa->codigo).'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pagar(Request $request): RedirectResponse
    {
        $ids = $this->idsSeleccionados($request);

        $actualizados = Boleto::query()
            ->whereIn('id', $ids)
            ->where('pagado', false)
            ->update([
                'pagado' => true,
                'fecha_pago' => now(),
                'modificado_por' => $request->user()->name,
                'updated_at' => now(),
            ]);

        return back()->with('status', "{$actualizados} boleto(s) marcados como pagados.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $ids = $this->idsSeleccionados($request);

        $eliminados = Boleto::query()->whereIn('id', $ids)->delete();

        return back()->with('status', "{$eliminados} apartado(s) eliminados.");
    }

    public function senalar(Request $request, Boleto $boleto): RedirectResponse
    {
        $boleto->update([
            'senalado' => ! $boleto->senalado,
            'modificado_por' => $request->user()->name,
        ]);

        return back();
    }

    /**
     * @return list<int>
     */
    private function idsSeleccionados(Request $request): array
    {
        $validados = $request->validate([
            'boletos' => ['required', 'array', 'max:10000'],
            'boletos.*' => ['integer'],
        ], [
            'boletos.required' => 'Selecciona al menos un boleto.',
        ]);

        return array_map('intval', $validados['boletos']);
    }
}
