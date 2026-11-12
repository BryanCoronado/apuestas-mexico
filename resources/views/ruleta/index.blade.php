@extends('layouts.rifas')

@section('title', 'Ruletazo de la Suerte')

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('banner')
    <div class="page-banner">
        <h1 class="page-banner__title">RULETAZO DE LA SUERTE</h1>
    </div>
@endsection

@section('content')
    @php
        $sorteoDefault = $sorteos[0] ?? ['codigo' => 'S102', 'inicio' => now(), 'fin' => now()->addDays(2), 'premio_url' => null];
        $sorteoRibbon = implode(' ', mb_str_split($sorteoDefault['codigo']));
    @endphp

    <div class="ruleta-app" id="ruleta-app" data-ganador-url="{{ route('ruleta.ganador') }}">
        {{-- Paso 1: entrada --}}
        <section class="ruleta-entrada" id="step-entrada">
            <button type="button" class="btn btn--entrar" id="btn-entrar">ENTRAR</button>
        </section>

        {{-- Paso 2: formulario + ruleta / ganador --}}
        <section class="ruleta-juego" id="step-juego" hidden>
            <div class="ruleta-form">
                <label class="sr-only" for="sorteo-select">Sorteo</label>
                <select id="sorteo-select" class="ruleta-input">
                    <option value="" selected disabled>SELECCIONA RIFA</option>
                    @foreach ($sorteos as $sorteo)
                        <option value="{{ $sorteo['id'] }}"
                            data-codigo="{{ $sorteo['codigo'] }}"
                            data-inicio="{{ \Carbon\Carbon::parse($sorteo['inicio'])->format('Y-m-d H:i') }}"
                            data-fin="{{ \Carbon\Carbon::parse($sorteo['fin'])->format('Y-m-d H:i') }}"
                            data-premio="{{ $sorteo['premio_url'] }}">
                            {{ $sorteo['codigo'] }}
                        </option>
                    @endforeach
                </select>

                <label class="sr-only" for="fecha-inicio">Fecha de inicio</label>
                <input type="text" class="ruleta-input" id="fecha-inicio" placeholder="Fecha inicio"
                    value="{{ \Carbon\Carbon::parse($sorteoDefault['inicio'])->format('Y-m-d H:i') }}">

                <label class="sr-only" for="fecha-fin">Fecha de fin</label>
                <input type="text" class="ruleta-input" id="fecha-fin" placeholder="Fecha fin"
                    value="{{ \Carbon\Carbon::parse($sorteoDefault['fin'])->format('Y-m-d H:i') }}">

                <label class="sr-only" for="min-boletos">Mínimo de boletos pagados</label>
                <input type="number" min="0" class="ruleta-input" id="min-boletos" placeholder="Mín. boletos pagados">

                <button type="button" class="btn btn--iniciar" id="btn-iniciar" disabled>INICIAR</button>
            </div>

            <div class="ruleta-stage">
                <div class="ruleta-wheel-wrap" id="ruleta-wheel-wrap" hidden>
                    <img src="{{ asset('ruleta.gif') }}" alt="Ruleta girando" class="ruleta-wheel" id="ruleta-gif">
                </div>

                <article class="boleto" id="boleto-ganador" hidden aria-live="polite">
                    <div class="boleto__side"><span class="boleto__codigo" data-codigo>{{ $sorteoRibbon }}</span></div>

                    <div class="boleto__body">
                        <header class="boleto__header">
                            <img src="{{ asset('Logotipo-1.png') }}" alt="" class="boleto__logo">
                            <span class="boleto__marca">RFS<br>ECONOMIAS<br>GDL</span>
                        </header>

                        <div class="boleto__numero">
                            <span class="boleto__numero-label">Boleto:</span>
                            <span class="boleto__numero-valor" id="ganador-boleto"></span>
                        </div>

                        <dl class="boleto__datos">
                            <div><dt>NOMBRE:</dt><dd id="ganador-nombre"></dd></div>
                            <div><dt>APELLIDO:</dt><dd id="ganador-apellido"></dd></div>
                            <div><dt>ESTADO:</dt><dd id="ganador-estado"></dd></div>
                            <div><dt>STATUS:</dt><dd id="ganador-status"></dd></div>
                            <div><dt>COMPRA:</dt><dd id="ganador-compra"></dd></div>
                        </dl>

                        <div class="boleto__premio">
                            <img src="{{ $sorteoDefault['premio_url'] }}" alt="Premio del sorteo" id="premio-img"
                                @if (! $sorteoDefault['premio_url']) hidden @endif>
                            <div class="boleto__premio-vacio" id="premio-vacio"
                                @if ($sorteoDefault['premio_url']) hidden @endif>
                                <span>Sube la imagen del premio desde el Panel de Rifas</span>
                            </div>
                        </div>

                        <p class="boleto__felicidades">¡FELICIDADES!</p>
                    </div>

                    <div class="boleto__side"><span class="boleto__codigo" data-codigo>{{ $sorteoRibbon }}</span></div>
                </article>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="{{ asset('js/ruleta.js') }}" defer></script>
@endpush
