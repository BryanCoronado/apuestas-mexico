<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\BoletoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rifa_id',
    'numero',
    'nombre',
    'apellido',
    'celular',
    'estado',
    'pagado',
    'fecha_pago',
    'fecha_reserva',
    'modificado_por',
    'senalado',
])]
class Boleto extends Model
{
    /** @use HasFactory<BoletoFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pagado' => 'boolean',
            'senalado' => 'boolean',
            'fecha_pago' => 'datetime',
            'fecha_reserva' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Rifa, $this>
     */
    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    /**
     * Busca por número exacto, nombre, apellido o celular.
     *
     * @param  Builder<Boleto>  $query
     */
    public function scopeBuscar(Builder $query, ?string $termino): void
    {
        $termino = trim((string) $termino);

        if ($termino === '') {
            return;
        }

        $query->where(function (Builder $query) use ($termino): void {
            $query->where('numero', $termino)
                ->orWhere('nombre', 'like', $termino.'%')
                ->orWhere('apellido', 'like', $termino.'%')
                ->orWhere('celular', 'like', '%'.$termino.'%');
        });
    }

    /**
     * Boletos que pueden salir en la ruleta: reservados dentro del rango y,
     * si se pide un mínimo, solo pagados de clientes con al menos ese número de boletos pagados.
     *
     * @param  Builder<Boleto>  $query
     */
    public function scopeElegiblesParaRuleta(
        Builder $query,
        Rifa $rifa,
        CarbonInterface $inicio,
        CarbonInterface $fin,
        int $minimoPagados,
    ): void {
        $query->whereBelongsTo($rifa)
            ->whereBetween('fecha_reserva', [$inicio, $fin]);

        if ($minimoPagados > 0) {
            $query->where('pagado', true)
                ->whereIn('celular', Boleto::query()
                    ->select('celular')
                    ->whereBelongsTo($rifa)
                    ->where('pagado', true)
                    ->whereNotNull('celular')
                    ->groupBy('celular')
                    ->havingRaw('COUNT(*) >= ?', [$minimoPagados]));
        }
    }

    /**
     * Celular con los dígitos finales ocultos, p. ej. "442*******".
     */
    public function celularOculto(): string
    {
        $celular = (string) $this->celular;

        if ($celular === '') {
            return '';
        }

        return substr($celular, 0, 3).str_repeat('*', max(strlen($celular) - 3, 0));
    }
}
