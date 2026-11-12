<?php

namespace App\Http\Requests\Panel;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateRifaRequest extends StoreRifaRequest
{
    protected function reglaNumeroUnico(): Unique
    {
        return Rule::unique('rifas', 'numero')->ignore($this->route('rifa'));
    }
}
