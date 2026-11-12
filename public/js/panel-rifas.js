(function () {
    'use strict';

    const app = document.getElementById('rifas-app');
    const modal = document.getElementById('modal-rifa');
    const form = document.getElementById('form-rifa');
    if (!app || !modal || !form) {
        return;
    }

    const campo = (id) => document.getElementById(id);
    const paquetes = campo('rifa-paquetes');
    const plantillaPaquete = campo('tpl-paquete');
    const imagenActual = campo('rifa-imagen-actual');
    const nombreImagenes = campo('rifa-imagenes-nombre');

    function reindexarPaquetes() {
        paquetes.querySelectorAll('.rifa-paquete').forEach((fila, indice) => {
            fila.querySelectorAll('[data-campo]').forEach((input) => {
                input.name = `paquetes[${indice}][${input.dataset.campo}]`;
            });
        });
    }

    function agregarPaquete(paquete = {}) {
        const fila = plantillaPaquete.content.firstElementChild.cloneNode(true);
        fila.querySelector('[data-campo="cantidad"]').value = paquete.cantidad ?? '';
        fila.querySelector('[data-campo="precio"]').value = paquete.precio ?? '';
        paquetes.appendChild(fila);
        reindexarPaquetes();
    }

    function emisionSugerida() {
        const cifras = parseInt(campo('rifa-cifras').value, 10);
        if (Number.isNaN(cifras)) {
            return '';
        }
        return 10 ** cifras * (campo('rifa-zodiaco').checked ? 12 : 1);
    }

    function abrir(modo, datos = {}) {
        form.reset();
        paquetes.innerHTML = '';

        const editando = modo === 'editar';
        form.action = datos.accion || app.dataset.accionCrear;
        campo('rifa-metodo').value = editando ? 'PUT' : 'POST';
        campo('rifa-id').value = editando ? (datos.id ?? '') : '';
        campo('modal-rifa-titulo').textContent = editando ? `Editar Rifa ${datos.codigo ?? ''}`.trim() : 'Agregar Rifa';
        campo('rifa-submit').textContent = editando ? 'Guardar Cambios' : 'Agregar Rifa';

        campo('rifa-numero').value = datos.numero ?? '';
        campo('rifa-zodiaco').checked = Boolean(Number(datos.es_zodiaco ?? 0));
        campo('rifa-gratuita').checked = Boolean(Number(datos.es_gratuita ?? 0));
        campo('rifa-abierta').checked = Boolean(Number(datos.abierta ?? 0));
        campo('rifa-cifras').value = datos.cifras ?? '';
        campo('rifa-emision').value = datos.emision ?? '';
        campo('rifa-titulo').value = datos.titulo ?? '';
        campo('rifa-titulo-corto').value = datos.titulo_corto ?? '';
        campo('rifa-fecha').value = datos.fecha ?? '';
        campo('rifa-texto-precios').value = datos.texto_precios ?? '';
        campo('rifa-premios').value = datos.premios_secundarios ?? '';

        nombreImagenes.textContent = datos.total_imagenes ? `${datos.total_imagenes} imagen(es) guardada(s)` : '';
        imagenActual.hidden = !datos.imagen_premio_url;
        imagenActual.href = datos.imagen_premio_url || '#';

        const listaPaquetes = Array.isArray(datos.paquetes) ? datos.paquetes : Object.values(datos.paquetes || {});
        if (listaPaquetes.length === 0) {
            agregarPaquete();
        } else {
            listaPaquetes.forEach((paquete) => agregarPaquete(paquete));
        }

        modal.showModal();
    }

    campo('btn-agregar-rifa')?.addEventListener('click', () => {
        modal.querySelector('.panel-errores')?.remove();
        abrir('crear');
    });

    app.querySelectorAll('[data-editar]').forEach((boton) => {
        boton.addEventListener('click', () => {
            modal.querySelector('.panel-errores')?.remove();
            abrir('editar', JSON.parse(boton.closest('tr').dataset.rifa));
        });
    });

    modal.querySelector('[data-cerrar-modal]')?.addEventListener('click', () => modal.close());

    campo('btn-agregar-paquete').addEventListener('click', () => agregarPaquete());

    paquetes.addEventListener('click', (evento) => {
        const quitar = evento.target.closest('[data-quitar-paquete]');
        if (quitar) {
            quitar.closest('.rifa-paquete').remove();
            reindexarPaquetes();
        }
    });

    ['rifa-cifras', 'rifa-zodiaco'].forEach((id) => {
        campo(id).addEventListener('change', () => {
            campo('rifa-emision').value = emisionSugerida();
        });
    });

    campo('rifa-imagenes').addEventListener('change', (evento) => {
        const total = evento.target.files.length;
        nombreImagenes.textContent = total ? `${total} imagen(es) nueva(s) seleccionada(s)` : '';
    });

    let estadoInicial = null;
    try {
        estadoInicial = JSON.parse(app.dataset.estadoInicial || 'null');
    } catch (e) {
        estadoInicial = null;
    }

    if (estadoInicial) {
        const errores = modal.querySelector('.panel-errores');
        abrir(estadoInicial.modo, estadoInicial.datos);
        if (errores) {
            modal.querySelector('.panel-modal__body').prepend(errores);
        }
    }
})();
