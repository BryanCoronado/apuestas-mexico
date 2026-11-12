(function () {
    'use strict';

    const app = document.getElementById('boletos-app');
    const formAcciones = document.getElementById('form-acciones');
    if (!app || !formAcciones) {
        return;
    }

    const checkTodos = document.getElementById('check-todos');
    const contador = document.getElementById('contador-seleccion');
    const metodo = document.getElementById('accion-metodo');
    const checks = () => Array.from(document.querySelectorAll('.check-boleto'));
    const seleccionados = () => checks().filter((check) => check.checked);

    let mensajes = {};
    try {
        mensajes = JSON.parse(app.dataset.mensajes || '{}');
    } catch (e) {
        mensajes = {};
    }

    function actualizarContador() {
        const total = seleccionados().length;
        contador.textContent = total;
        if (checkTodos) {
            checkTodos.checked = total > 0 && total === checks().length;
            checkTodos.indeterminate = total > 0 && total < checks().length;
        }
    }

    checkTodos?.addEventListener('change', () => {
        checks().forEach((check) => {
            check.checked = checkTodos.checked;
        });
        actualizarContador();
    });

    document.addEventListener('change', (evento) => {
        if (evento.target.classList?.contains('check-boleto')) {
            actualizarContador();
        }
    });

    document.addEventListener('click', (evento) => {
        const ver = evento.target.closest('.celular__ver');
        if (!ver) {
            return;
        }
        const celular = ver.previousElementSibling;
        const oculto = celular.textContent === celular.dataset.oculto;
        celular.textContent = oculto ? celular.dataset.completo : celular.dataset.oculto;
    });

    formAcciones.addEventListener('submit', (evento) => {
        const boton = evento.submitter;
        if (!boton?.dataset.metodo) {
            return;
        }

        if (seleccionados().length === 0) {
            evento.preventDefault();
            window.alert('Selecciona al menos un boleto.');
            return;
        }

        if (boton.dataset.confirmar && !window.confirm(boton.dataset.confirmar)) {
            evento.preventDefault();
            return;
        }

        metodo.value = boton.dataset.metodo;
    });

    function telefonoWhats(celular) {
        const digitos = String(celular || '').replace(/\D/g, '');
        if (digitos.length === 10) {
            return `52${digitos}`;
        }
        return digitos;
    }

    formAcciones.querySelectorAll('[data-whats]').forEach((boton) => {
        boton.addEventListener('click', () => {
            const filas = seleccionados()
                .map((check) => check.closest('tr'))
                .filter((fila) => fila?.dataset.celular);

            if (filas.length === 0) {
                window.alert('Selecciona al menos un boleto con celular.');
                return;
            }

            const porCelular = new Map();
            filas.forEach((fila) => {
                const grupo = porCelular.get(fila.dataset.celular) || { nombre: fila.dataset.nombre, numeros: [] };
                grupo.numeros.push(fila.dataset.numero);
                porCelular.set(fila.dataset.celular, grupo);
            });

            const plantilla = mensajes[boton.dataset.whats] || '';

            porCelular.forEach((grupo, celular) => {
                const texto = plantilla
                    .replaceAll(':nombre', grupo.nombre || '')
                    .replaceAll(':boletos', grupo.numeros.join(', '))
                    .replaceAll(':rifa', app.dataset.rifa || '');

                window.open(`https://wa.me/${telefonoWhats(celular)}?text=${encodeURIComponent(texto)}`, '_blank', 'noopener');
            });
        });
    });

    actualizarContador();
})();
