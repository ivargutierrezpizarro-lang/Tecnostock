/*
 * Validaciones del lado del cliente (JavaScript).
 * IMPORTANTE: las mismas reglas se repiten en PHP (includes/funciones.php y registrar.php),
 * porque la validación del navegador puede saltarse.
 *
 * Uso: agregar  class="needs-validation" novalidate  al <form>.
 */
(function () {
    'use strict';

    // Revisa un campo y define un mensaje personalizado si no cumple la regla.
    function revisarCampo(campo) {
        campo.setCustomValidity('');
        const valor = (campo.value || '').trim();
        const nombre = campo.name;

        if (campo.required && valor === '' && campo.type !== 'file') {
            campo.setCustomValidity('Este campo es obligatorio.');
            return;
        }
        if (valor === '') return;

        if (nombre === 'codigo' && !/^[A-Za-z0-9._-]{1,50}$/.test(valor)) {
            campo.setCustomValidity('Use solo letras, números, punto, guion o guion bajo (máx. 50).');
        }
        if ((nombre === 'nombre') && valor.length > 100) {
            campo.setCustomValidity('Máximo 100 caracteres.');
        }
        if (nombre === 'precio') {
            const n = Number(valor);
            if (isNaN(n) || n < 0) campo.setCustomValidity('El precio no puede ser negativo.');
        }
        if (['stock_actual', 'stock_minimo', 'cantidad'].includes(nombre)) {
            const n = Number(valor);
            const min = nombre === 'cantidad' ? 1 : 0;
            if (!Number.isInteger(n)) campo.setCustomValidity('Ingrese un número entero.');
            else if (n < min) campo.setCustomValidity(min === 1 ? 'La cantidad debe ser mayor a 0.' : 'No puede ser negativo.');
        }
        if (nombre === 'correo' && campo.type === 'email' && !campo.checkValidity()) {
            campo.setCustomValidity('Ingrese un correo válido.');
        }
    }

    // Regla específica de movimientos: una salida no puede superar el stock disponible.
    function revisarMovimiento(form) {
        const sel = form.querySelector('select[name="id_producto"]');
        const tipo = form.querySelector('select[name="tipo"]');
        const cant = form.querySelector('input[name="cantidad"]');
        if (!sel || !tipo || !cant) return;

        const opt = sel.options[sel.selectedIndex];
        const stock = opt && opt.dataset.stock !== undefined ? parseInt(opt.dataset.stock, 10) : null;

        if (tipo.value === 'SALIDA' && stock !== null) {
            cant.max = stock;
        } else {
            cant.removeAttribute('max');
        }
        const n = parseInt(cant.value, 10);
        if (cant.value !== '' && tipo.value === 'SALIDA' && stock !== null && n > stock) {
            cant.setCustomValidity('Stock insuficiente: solo hay ' + stock + ' unidades disponibles.');
        }
        const ayuda = form.querySelector('#ayuda-stock');
        if (ayuda) {
            ayuda.textContent = (stock !== null && sel.value !== '')
                ? 'Stock disponible del producto: ' + stock : '';
        }
    }

    // Muestra el mensaje del navegador/JS bajo cada campo.
    function pintarMensajes(form) {
        form.querySelectorAll('input, select, textarea').forEach(function (campo) {
            let fb = campo.parentElement.querySelector('.invalid-feedback');
            if (!fb) {
                fb = document.createElement('div');
                fb.className = 'invalid-feedback';
                campo.insertAdjacentElement('afterend', fb);
            }
            fb.textContent = campo.validationMessage;
        });
    }

    function validarFormulario(form) {
        form.querySelectorAll('input, select, textarea').forEach(revisarCampo);
        revisarMovimiento(form);
        pintarMensajes(form);
        return form.checkValidity();
    }

    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            if (!validarFormulario(form)) {
                ev.preventDefault();
                ev.stopPropagation();
            }
            form.classList.add('was-validated');
        });
        // Revalidar en vivo cuando el usuario ya intentó enviar
        form.addEventListener('input', function () {
            if (form.classList.contains('was-validated')) validarFormulario(form);
            else revisarMovimiento(form);
        });
        form.addEventListener('change', function () {
            revisarMovimiento(form);
            if (form.classList.contains('was-validated')) validarFormulario(form);
        });
        revisarMovimiento(form);
    });
})();
