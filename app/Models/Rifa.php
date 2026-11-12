<?php

namespace App\Models;

use Database\Factories\RifaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'numero',
    'titulo',
    'titulo_corto',
    'fecha',
    'cifras',
    'emision',
    'es_zodiaco',
    'es_gratuita',
    'abierta',
    'paquetes',
    'texto_precios',
    'premios_secundarios',
    'imagenes_premios',
    'imagen_premio',
])]
class Rifa extends Model
{
    /** @use HasFactory<RifaFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'es_zodiaco' => 'boolean',
            'es_gratuita' => 'boolean',
            'abierta' => 'boolean',
            'paquetes' => 'array',
            'imagenes_premios' => 'array',
        ];
    }

    /**
     * @return HasMany<Boleto, $this>
     */
    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class);
    }

    /**
     * Código público de la rifa, p. ej. "S2".
     *
     * @return Attribute<string, never>
     */
    protected function codigo(): Attribute
    {
        return Attribute::get(fn (): string => 'S'.$this->numero);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function urlLista(): Attribute
    {
        return Attribute::get(fn (): string => config('ruleta.sitio').'/'.strtolower($this->codigo).'-lista');
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imagenPremioUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->imagen_premio
            ? Storage::disk('public')->url($this->imagen_premio)
            : null);
    }

    /**
     * Cantidad máxima de boletos que admite la rifa según sus cifras.
     */
    public static function emisionPara(int $cifras, bool $esZodiaco): int
    {
        return (10 ** $cifras) * ($esZodiaco ? 12 : 1);
    }

    /**
     * @return array{total: int, reservados: int, pagados: int, libres: int}
     */
    public function resumenBoletos(): array
    {
        $conteo = $this->boletos()
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(CASE WHEN pagado = 1 THEN 1 ELSE 0 END), 0) as pagados')
            ->toBase()
            ->first();

        $total = (int) $conteo->total;
        $pagados = (int) $conteo->pagados;

        return [
            'total' => $total,
            'reservados' => $total - $pagados,
            'pagados' => $pagados,
            'libres' => max($this->emision - $total, 0),
        ];
    }
}
