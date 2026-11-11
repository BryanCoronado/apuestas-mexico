<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RuletaController extends Controller
{
    public function index(): View
    {
        $sorteos = collect(config('ruleta.sorteos', []))
            ->map(function (array $sorteo) {
                $imagen = $sorteo['premio_imagen'] ?? null;
                $sorteo['premio_url'] = $imagen && is_file(public_path($imagen)) ? asset($imagen) : null;

                return $sorteo;
            })
            ->values()
            ->all();

        $boletos = config('ruleta.boletos', []);

        return view('ruleta.index', compact('sorteos', 'boletos'));
    }
}
