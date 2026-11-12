<?php

namespace App\Http\Requests\Panel;

use App\Models\Rifa;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreRifaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Descarta las filas de paquetes que se dejaron vacías.
     */
    protected function prepareForValidation(): void
    {
        $paquetes = collect($this->input('paquetes', []))
            ->filter(fn ($paquete) => is_array($paquete)
                && (filled($paquete['cantidad'] ?? null) || filled($paquete['precio'] ?? null)))
            ->values()
            ->all();

        $this->merge([
            'paquetes' => $paquetes,
            'es_zodiaco' => $this->boolean('es_zodiaco'),
            'es_gratuita' => $this->boolean('es_gratuita'),
            'abierta' => $this->boolean('abierta'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $cifras = (int) $this->input('cifras');
        $emisionMaxima = $cifras >= 1 && $cifras <= 7
            ? Rifa::emisionPara($cifras, $this->boolean('es_zodiaco'))
            : 1;

        return [
            'numero' => ['required', 'integer', 'min:1', 'max:99999', $this->reglaNumeroUnico()],
            'es_zodiaco' => ['boolean'],
            'es_gratuita' => ['boolean'],
            'abierta' => ['boolean'],
            'cifras' => ['required', 'integer', 'between:2,7'],
            'emision' => ['required', 'integer', 'min:1', 'max:'.$emisionMaxima],
            'titulo' => ['required', 'string', 'max:255'],
            'titulo_corto' => ['required', 'string', 'max:120'],
            'fecha' => ['required', 'date'],
            'imagenes_premios' => ['nullable', 'array', 'max:10'],
            'imagenes_premios.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'paquetes' => ['array', 'max:30'],
            'paquetes.*.cantidad' => ['required', 'integer', 'min:1'],
            'paquetes.*.precio' => ['required', 'numeric', 'min:0'],
            'texto_precios' => ['nullable', 'string', 'max:2000'],
            'premios_secundarios' => ['nullable', 'string', 'max:5000'],
            'imagen_premio' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numero.unique' => 'Ya existe una rifa con ese número.',
            'emision.max' => 'La emisión no puede superar :max boletos para esas cifras.',
            'paquetes.*.cantidad.required' => 'Cada paquete necesita una cantidad.',
            'paquetes.*.precio.required' => 'Cada paquete necesita un precio.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'numero' => 'número de rifa',
            'cifras' => 'cifras',
            'emision' => 'emisión de boletos',
            'titulo' => 'título de la rifa',
            'titulo_corto' => 'título corto',
            'fecha' => 'fecha de la rifa',
            'imagenes_premios.*' => 'imagen de premio',
            'imagen_premio' => 'imagen del premio',
        ];
    }

    protected function reglaNumeroUnico(): Unique
    {
        return Rule::unique('rifas', 'numero');
    }
}
