@extends('layouts.panel')

@section('title', 'Panel de Boletos')

@php
    $porcentaje = fn (int $cantidad): int => $rifa && $rifa->emision > 0 ? (int) round($cantidad / $rifa->emision * 100) : 0;
    $urlOrden = fn (string $columna): string => request()->fullUrlWithQuery([
        'orden' => $columna,
        'dir' => $orden === $columna && $direccion === 'asc' ? 'desc' : 'asc',
        'page' => null,
    ]);
@endphp

@section('content')
    <div class="boletos-panel" id="boletos-app"
        data-rifa="{{ $rifa?->codigo }}"
        data-mensajes='@json(config('ruleta.mensajes'))'>

        <form method="GET" action="{{ route('panel.boletos.index') }}" class="rifa-select">
            <label for="rifa-select" class="rifa-select__label">Rifa</label>
            <select id="rifa-select" name="rifa" class="rifa-select__input" data-autosubmit>
                @forelse ($rifas as $opcion)
                    <option value="{{ $opcion->id }}" @selected($rifa?->is($opcion))>{{ $opcion->codigo }}</option>
                @empty
                    <option value="">Sin rifas</option>
                @endforelse
            </select>
            <x-panel.icono nombre="chevron" class="icono rifa-select__chevron" />
        </form>

        @if (! $rifa)
            <p class="panel-vacio">
                Aún no hay rifas. <a href="{{ route('panel.rifas.index') }}">Crea la primera en el Panel de Rifas.</a>
            </p>
        @else
            <div class="boletos-resumen">
                <span class="boletos-resumen__titulo">{{ $rifa->titulo_corto }}</span>
                <a href="{{ route('panel.rifas.index', ['editar' => $rifa->id]) }}" class="panel-icon-btn panel-icon-btn--morado" title="Configurar rifa">
                    <x-panel.icono nombre="config" />
                </a>
                <a href="{{ route('panel.boletos.exportar', $rifa) }}" class="panel-icon-btn panel-icon-btn--azul" title="Descargar boletos (CSV)">
                    <x-panel.icono nombre="descargar" />
                </a>
                <span class="boletos-resumen__stats">
                    reservados: {{ $resumen['reservados'] }} ({{ $porcentaje($resumen['reservados']) }}%)
                    | pagados: {{ $resumen['pagados'] }} ({{ $porcentaje($resumen['pagados']) }}%)
                    | libres: {{ $resumen['libres'] }} ({{ $porcentaje($resumen['libres']) }}%)
                </span>
            </div>

            <form method="POST" id="form-acciones" class="boletos-acciones">
                @csrf
                <input type="hidden" name="_method" value="PATCH" id="accion-metodo">

                <button type="button" class="panel-btn panel-btn--outline-azul" data-whats="whats">Enviar Whats</button>
                <span class="boletos-acciones__grupo">
                    <button type="button" class="panel-btn panel-btn--outline-naranja" data-whats="rec1">Rec 1</button>
                    <button type="button" class="panel-btn panel-btn--outline-naranja" data-whats="rec2">Rec 2</button>
                </span>
                <button type="button" class="panel-btn panel-btn--outline-verde" data-whats="boleto">Enviar Boleto</button>
                <button type="submit" class="panel-btn panel-btn--outline-verde"
                    formaction="{{ route('panel.boletos.pagar') }}" data-metodo="PATCH"
                    data-confirmar="¿Marcar como pagados los boletos seleccionados?">Pagado</button>
                <button type="submit" class="panel-btn panel-btn--outline-rojo"
                    formaction="{{ route('panel.boletos.destroy') }}" data-metodo="DELETE"
                    data-confirmar="¿Eliminar los apartados seleccionados? Los números quedarán libres.">Eliminar Apartado</button>
            </form>

            <form method="POST" id="form-senalar" hidden>
                @csrf
                <input type="hidden" name="_method" value="PATCH">
            </form>

            <form method="GET" action="{{ route('panel.boletos.index') }}" class="boletos-filtros" id="form-filtros">
                <input type="hidden" name="rifa" value="{{ $rifa->id }}">
                <input type="hidden" name="orden" value="{{ $orden }}">
                <input type="hidden" name="dir" value="{{ $direccion }}">

                <div class="boletos-filtros__buscar">
                    <label for="buscar" class="sr-only">Buscar boleto, nombre o celular</label>
                    <input type="search" id="buscar" name="q" value="{{ $busqueda }}" class="panel-input" placeholder="">
                    <button type="submit" class="panel-btn panel-btn--primario">Buscar</button>
                </div>

                <div class="boletos-filtros__radios">
                    @foreach (['todos' => 'sin filtro', 'pagados' => 'pagados', 'no_pagados' => 'no pagados'] as $valor => $etiqueta)
                        <label class="panel-radio">
                            <input type="radio" name="filtro" value="{{ $valor }}" @checked($filtro === $valor) data-autosubmit>
                            {{ $etiqueta }}
                        </label>
                    @endforeach
                </div>

                <div class="boletos-filtros__paginacion">
                    {{ $boletos->onEachSide(22)->links('panel.partials.paginacion') }}
                </div>

                <div class="boletos-filtros__pie">
                    <span><strong id="contador-seleccion">0</strong> Boletos seleccionados</span>
                    <label class="sr-only" for="por-pagina">Boletos por página</label>
                    <select id="por-pagina" name="por_pagina" class="panel-select-mini" data-autosubmit>
                        @foreach ($opcionesPorPagina as $opcion)
                            <option value="{{ $opcion }}" @selected($porPagina === $opcion)>{{ number_format($opcion) }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="panel-tabla-wrap panel-tabla-wrap--scroll">
                <table class="panel-tabla boletos-tabla">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="panel-check" id="check-todos" aria-label="Seleccionar todos"></th>
                            <th>Pagado</th>
                            <th><a href="{{ $urlOrden('numero') }}">Número ⇅</a></th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Celular</th>
                            <th>Estado</th>
                            <th><a href="{{ $urlOrden('fecha_pago') }}">Fecha Pago ⇅</a></th>
                            <th><a href="{{ $urlOrden('fecha_reserva') }}">Fecha Reserva ⇅</a></th>
                            <th>Mod</th>
                            <th>Señ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($boletos as $boleto)
                            <tr @class(['senalado' => $boleto->senalado])
                                data-celular="{{ $boleto->celular }}"
                                data-numero="{{ $boleto->numero }}"
                                data-nombre="{{ $boleto->nombre }}">
                                <td>
                                    <input type="checkbox" class="panel-check check-boleto" name="boletos[]" value="{{ $boleto->id }}"
                                        form="form-acciones" aria-label="Seleccionar boleto {{ $boleto->numero }}">
                                </td>
                                <td @class(['texto-verde' => $boleto->pagado])>{{ $boleto->pagado ? 'SI' : 'NO' }}</td>
                                <td>{{ $boleto->numero }}</td>
                                <td class="texto-azul">{{ $boleto->nombre }}</td>
                                <td class="texto-azul">{{ $boleto->apellido }}</td>
                                <td class="texto-azul">
                                    @if ($boleto->celular)
                                        <span class="celular" data-oculto="{{ $boleto->celularOculto() }}" data-completo="{{ $boleto->celular }}">{{ $boleto->celularOculto() }}</span>
                                        <button type="button" class="celular__ver" aria-label="Ver celular"><x-panel.icono nombre="ojo" /></button>
                                    @endif
                                </td>
                                <td class="texto-azul">{{ $boleto->estado }}</td>
                                <td>{{ $boleto->fecha_pago?->format('d M y H:i:s') }}</td>
                                <td>{{ $boleto->fecha_reserva?->format('d M y H:i:s') }}</td>
                                <td>{{ $boleto->modificado_por }}</td>
                                <td>
                                    <button type="submit" form="form-senalar" formaction="{{ route('panel.boletos.senalar', $boleto) }}"
                                        @class(['panel-btn panel-btn--mini', 'activo' => $boleto->senalado])>
                                        {{ $boleto->senalado ? 'Señalado' : 'Señalar' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="panel-vacio">No hay boletos con esos filtros.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/panel-boletos.js') }}" defer></script>
@endpush
