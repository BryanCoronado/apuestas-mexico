@extends('layouts.panel')

@section('title', 'Panel de Rifas')

@php
    $datosRifa = fn (\App\Models\Rifa $rifa): array => [
        'id' => $rifa->id,
        'codigo' => $rifa->codigo,
        'accion' => route('panel.rifas.update', $rifa),
        'numero' => $rifa->numero,
        'es_zodiaco' => $rifa->es_zodiaco,
        'es_gratuita' => $rifa->es_gratuita,
        'abierta' => $rifa->abierta,
        'cifras' => $rifa->cifras,
        'emision' => $rifa->emision,
        'titulo' => $rifa->titulo,
        'titulo_corto' => $rifa->titulo_corto,
        'fecha' => $rifa->fecha->format('Y-m-d\TH:i'),
        'paquetes' => $rifa->paquetes ?? [],
        'texto_precios' => $rifa->texto_precios,
        'premios_secundarios' => $rifa->premios_secundarios,
        'total_imagenes' => count($rifa->imagenes_premios ?? []),
        'imagen_premio_url' => $rifa->imagen_premio_url,
    ];

    $estadoInicial = match (true) {
        $errors->any() => ['modo' => old('_rifa_id') ? 'editar' : 'crear', 'datos' => [
            ...\Illuminate\Support\Arr::except(old(), ['_token', '_method']),
            'id' => old('_rifa_id'),
            'accion' => old('_rifa_id') ? route('panel.rifas.update', old('_rifa_id')) : route('panel.rifas.store'),
        ]],
        $rifaEditar !== null => ['modo' => 'editar', 'datos' => $datosRifa($rifaEditar)],
        default => null,
    };
@endphp

@section('content')
    <div class="rifas-panel" id="rifas-app" data-estado-inicial='@json($estadoInicial)' data-accion-crear="{{ route('panel.rifas.store') }}">
        <div class="rifas-barra">
            <button type="button" class="panel-btn panel-btn--primario rifas-barra__agregar" id="btn-agregar-rifa">
                <x-panel.icono nombre="mas" /> Agregar Rifa
            </button>

            <form method="GET" action="{{ route('panel.rifas.index') }}" class="rifas-barra__buscar">
                <x-panel.icono nombre="buscar" />
                <label for="buscar-rifa" class="sr-only">Buscar por título corto</label>
                <input type="search" id="buscar-rifa" name="q" value="{{ $busqueda }}" placeholder="Buscar por Título Corto">
            </form>
        </div>

        <div class="panel-tabla-wrap">
            <table class="panel-tabla rifas-tabla">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['orden' => $orden === 'desc' ? 'asc' : 'desc', 'page' => null]) }}">
                                # de Rifa {{ $orden === 'desc' ? '▲' : '▼' }}
                            </a>
                        </th>
                        <th>Título Corto</th>
                        <th>URL</th>
                        <th>Fecha de la Rifa</th>
                        <th>Emisión de Boletos</th>
                        <th>Abierta</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rifas as $rifa)
                        <tr data-rifa='@json($datosRifa($rifa))'>
                            <td>{{ $rifa->codigo }}</td>
                            <td>{{ $rifa->titulo_corto }}</td>
                            <td><a href="https://{{ $rifa->url_lista }}" target="_blank" rel="noopener noreferrer" class="texto-azul">{{ $rifa->url_lista }}</a></td>
                            <td>{{ $rifa->fecha->format('d M Y, h:i A') }}.</td>
                            <td>{{ number_format($rifa->emision) }}</td>
                            <td>
                                <form method="POST" action="{{ route('panel.rifas.abierta', $rifa) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class(['panel-badge', 'panel-badge--si' => $rifa->abierta]) title="Cambiar estado">
                                        {{ $rifa->abierta ? 'Si' : 'No' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="rifas-acciones">
                                    <a href="https://{{ $rifa->url_lista }}" target="_blank" rel="noopener noreferrer" class="panel-accion" title="Ver lista pública">
                                        <x-panel.icono nombre="ojo" />
                                    </a>
                                    <button type="button" class="panel-accion" data-editar title="Editar">
                                        <x-panel.icono nombre="lapiz" />
                                    </button>
                                    <form method="POST" action="{{ route('panel.rifas.duplicar', $rifa) }}" data-confirmar="¿Duplicar la rifa {{ $rifa->codigo }}?">
                                        @csrf
                                        <button type="submit" class="panel-accion" title="Duplicar"><x-panel.icono nombre="copiar" /></button>
                                    </form>
                                    <form method="POST" action="{{ route('panel.rifas.destroy', $rifa) }}" data-confirmar="¿Eliminar la rifa {{ $rifa->codigo }} y TODOS sus boletos? Esta acción no se puede deshacer.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="panel-accion" title="Eliminar"><x-panel.icono nombre="basura" /></button>
                                    </form>
                                    <a href="{{ route('panel.boletos.index', ['rifa' => $rifa->id]) }}" class="panel-accion" title="Ver boletos">
                                        <x-panel.icono nombre="boleto" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="panel-vacio">No hay rifas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="rifas-pie">
                <span>Mostrando {{ $rifas->currentPage() }} de {{ $rifas->lastPage() }} Páginas</span>
                <span class="rifas-pie__nav">
                    @if ($rifas->onFirstPage())
                        <span class="paginacion__item deshabilitado">◀</span>
                    @else
                        <a class="paginacion__item" href="{{ $rifas->previousPageUrl() }}" aria-label="Anterior">◀</a>
                    @endif
                    <span class="paginacion__item activo">{{ $rifas->currentPage() }}</span>
                    @if ($rifas->hasMorePages())
                        <a class="paginacion__item" href="{{ $rifas->nextPageUrl() }}" aria-label="Siguiente">▶</a>
                    @else
                        <span class="paginacion__item deshabilitado">▶</span>
                    @endif
                </span>
            </div>
        </div>

        @include('panel.rifas.modal')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/panel-rifas.js') }}" defer></script>
@endpush
