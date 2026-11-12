<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Models\Rifa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RuletaController extends Controller
{
    public function index(): View
    {
        $sorteos = Rifa::query()
            ->orderByDesc('fecha')
            ->get()
            ->map(fn (Rifa $rifa) => [
                'id' => $rifa->id,
                'codigo' => $rifa->codigo,
                'inicio' => $rifa->created_at,
                'fin' => $rifa->fecha,
                'premio_url' => $rifa->imagen_premio_url,
            ])
            ->all();

        return view('ruleta.index', compact('sorteos'));
    }

    public function ganador(Request $request): JsonResponse
    {
        $validados = $request->validate([
            'rifa' => ['required', 'integer', 'exists:rifas,id'],
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after_or_equal:inicio'],
            'minimo' => ['nullable', 'integer', 'min:0'],
        ]);

        $rifa = Rifa::findOrFail($validados['rifa']);

        $elegibles = Boleto::query()->elegiblesParaRuleta(
            $rifa,
            Carbon::parse($validados['inicio']),
            Carbon::parse($validados['fin'])->endOfMinute(),
            (int) ($validados['minimo'] ?? 0),
        );

        $total = (clone $elegibles)->count();

        if ($total === 0) {
            return response()->json(['message' => 'No hay boletos que cumplan los filtros.'], 404);
        }

        $boleto = $elegibles->orderBy('id')->skip(random_int(0, $total - 1))->first();

        return response()->json([
            'ganador' => [
                'boleto' => $boleto->numero,
                'nombre' => $boleto->nombre,
                'apellido' => $boleto->apellido,
                'estado' => $boleto->estado,
                'status' => $boleto->pagado ? 'PAGADO' : 'NO PAGADO',
                'compra' => $boleto->fecha_reserva ? strtoupper($boleto->fecha_reserva->format('M d Y H:i:s')) : '-',
            ],
        ]);
    }
}
