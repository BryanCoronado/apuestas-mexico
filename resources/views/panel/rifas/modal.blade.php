<dialog class="panel-modal" id="modal-rifa" aria-labelledby="modal-rifa-titulo">
    <form method="POST" action="{{ route('panel.rifas.store') }}" enctype="multipart/form-data" id="form-rifa" novalidate>
        @csrf
        <input type="hidden" name="_method" value="POST" id="rifa-metodo">
        <input type="hidden" name="_rifa_id" value="" id="rifa-id">

        <header class="panel-modal__header">
            <h2 id="modal-rifa-titulo">Agregar Rifa</h2>
            <button type="button" class="panel-modal__cerrar" data-cerrar-modal aria-label="Cerrar">
                <x-panel.icono nombre="cerrar" />
            </button>
        </header>

        <div class="panel-modal__body">
            @if ($errors->any())
                <ul class="panel-errores">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <label class="panel-label" for="rifa-numero">Número de Rifa (SÓLO NÚMEROS)</label>
            <input type="number" min="1" id="rifa-numero" name="numero" class="panel-input" required>

            <label class="panel-check-label">
                <span>¿Es Rifa Zodiaco?</span>
                <input type="checkbox" name="es_zodiaco" value="1" id="rifa-zodiaco" class="panel-check">
            </label>

            <label class="panel-check-label">
                <span>¿Es Rifa Gratuita?</span>
                <input type="checkbox" name="es_gratuita" value="1" id="rifa-gratuita" class="panel-check">
            </label>

            <label class="panel-label" for="rifa-cifras">¿De Cuántas Cifras serán los Boletos?</label>
            <select id="rifa-cifras" name="cifras" class="panel-input" required>
                <option value="">Selecciona Cifras</option>
                @foreach (range(2, 7) as $cifras)
                    <option value="{{ $cifras }}">{{ $cifras }} cifras</option>
                @endforeach
            </select>

            <label class="panel-label" for="rifa-emision">Emisión de Boletos</label>
            <input type="number" min="1" id="rifa-emision" name="emision" class="panel-input" required>

            <label class="panel-label" for="rifa-titulo">Título de la Rifa</label>
            <input type="text" id="rifa-titulo" name="titulo" maxlength="255" class="panel-input" required>

            <label class="panel-label" for="rifa-titulo-corto">Título Corto de la Rifa</label>
            <input type="text" id="rifa-titulo-corto" name="titulo_corto" maxlength="120" class="panel-input" required>

            <label class="panel-label" for="rifa-fecha">Fecha de la Rifa</label>
            <input type="datetime-local" id="rifa-fecha" name="fecha" class="panel-input panel-input--corto" required>

            <label class="panel-label" for="rifa-imagenes">Imágenes de Premios (para lista de números)</label>
            <div class="panel-archivo">
                <label class="panel-btn panel-btn--primario panel-btn--icono" for="rifa-imagenes" title="Agregar imágenes">
                    <x-panel.icono nombre="mas" />
                </label>
                <input type="file" id="rifa-imagenes" name="imagenes_premios[]" accept="image/jpeg,image/png,image/webp" multiple hidden>
                <span class="panel-archivo__nombre" id="rifa-imagenes-nombre"></span>
            </div>

            <span class="panel-label">Cantidades y Precios de Boletos (para maquinita)</span>
            <div id="rifa-paquetes" class="rifa-paquetes"></div>
            <button type="button" class="panel-btn panel-btn--primario panel-btn--icono" id="btn-agregar-paquete" title="Agregar paquete">
                <x-panel.icono nombre="mas" />
            </button>

            <template id="tpl-paquete">
                <div class="rifa-paquete">
                    <input type="number" min="1" class="panel-input" data-campo="cantidad" placeholder="Cantidad" aria-label="Cantidad">
                    <input type="number" min="0" step="0.01" class="panel-input" data-campo="precio" placeholder="Precio" aria-label="Precio">
                    <button type="button" class="panel-btn panel-btn--rojo panel-btn--icono" data-quitar-paquete title="Quitar">
                        <x-panel.icono nombre="basura" />
                    </button>
                </div>
            </template>

            <label class="panel-label" for="rifa-texto-precios">Texto de Precios (para lista de números)</label>
            <input type="text" id="rifa-texto-precios" name="texto_precios" maxlength="2000" class="panel-input">

            <label class="panel-label" for="rifa-premios">Premios Secundarios, Preventas y Bonos</label>
            <textarea id="rifa-premios" name="premios_secundarios" rows="4" maxlength="5000" class="panel-input panel-textarea"></textarea>

            <label class="panel-label" for="rifa-imagen-premio">Imagen del Premio (para boleto digital)</label>
            <input type="file" id="rifa-imagen-premio" name="imagen_premio" accept="image/jpeg,image/png,image/webp" class="panel-input panel-input--archivo">
            <a href="#" target="_blank" rel="noopener noreferrer" class="panel-archivo__actual" id="rifa-imagen-actual" hidden>Ver imagen actual</a>

            <label class="panel-check-label">
                <span>¿Rifa Abierta?</span>
                <input type="checkbox" name="abierta" value="1" id="rifa-abierta" class="panel-check">
            </label>
        </div>

        <footer class="panel-modal__footer">
            <button type="submit" class="panel-btn panel-btn--morado" id="rifa-submit">Agregar Rifa</button>
        </footer>
    </form>
</dialog>
