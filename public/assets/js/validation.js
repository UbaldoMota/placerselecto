/**
 * validation.js
 * Validación frontend en tiempo real para todos los formularios.
 * Complementa (NO reemplaza) la validación del backend.
 * ES6+ — sin dependencias externas.
 */

'use strict';

// ---------------------------------------------------------
// REGLAS DE VALIDACIÓN
// ---------------------------------------------------------
const RULES = {
    required: (val) => val.trim() !== '',
    email:    (val) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i.test(val),
    minLen:   (val, n) => val.length >= parseInt(n),
    maxLen:   (val, n) => val.length <= parseInt(n),
    minLength:(val, n) => val.length >= parseInt(n),
    maxLength:(val, n) => val.length <= parseInt(n),
    phone:    (val) => /^[\d\+\-\s\(\)]{7,20}$/.test(val),
    /**
     * Contraseña fuerte: 8+ chars, 1 mayús, 1 minús, 1 dígito
     */
    strongPassword: (val) => (
        val.length >= 8 &&
        /[A-Z]/.test(val) &&
        /[a-z]/.test(val) &&
        /[0-9]/.test(val)
    ),
    noScript: (val) => !/<script|javascript:|on\w+=/i.test(val),
};

const MESSAGES = {
    required:       () => 'Este campo es requerido.',
    email:          () => 'Ingresa un email válido.',
    minLen:         (n) => `Mínimo ${n} caracteres.`,
    maxLen:         (n) => `Máximo ${n} caracteres.`,
    minLength:      (n) => `Mínimo ${n} caracteres.`,
    maxLength:      (n) => `Máximo ${n} caracteres.`,
    phone:          () => 'Teléfono inválido (solo dígitos, +, -, espacios).',
    strongPassword: () => 'Mínimo 8 caracteres, con mayúscula, minúscula y número.',
    noScript:       () => 'El campo contiene caracteres no permitidos.',
};

// ---------------------------------------------------------
// CLASE PRINCIPAL
// ---------------------------------------------------------
class FormValidator {
    /**
     * @param {HTMLFormElement} form
     */
    constructor(form) {
        this.form        = form;
        this.fields      = form.querySelectorAll('[data-validate]');
        this.submitBtn   = form.querySelector('button[type="submit"], input[type="submit"]');
        this.touched     = new WeakSet();        // campos que el usuario ya tocó
        this._bind();
        this._updateSubmit();                    // estado inicial del botón
    }

    _bind() {
        this.fields.forEach(field => {
            // Validar al perder foco — primera vez muestra error completo
            field.addEventListener('blur', () => {
                this.touched.add(field);
                this._validateField(field, true);
                this._updateSubmit();
            });

            // Validar mientras escribe — actualiza UI y estado del botón
            const evt = (field.tagName === 'SELECT' || field.type === 'checkbox') ? 'change' : 'input';
            field.addEventListener(evt, () => {
                if (this.touched.has(field)) {
                    this._validateField(field, true);
                } else {
                    this._validateField(field, false); // silencioso: no marca error aún
                }
                this._updateSubmit();
            });
        });

        // Submit: validar todo y bloquear si falta algo
        this.form.addEventListener('submit', (e) => {
            // Marcar todos los campos como touched al hacer submit
            this.fields.forEach(f => this.touched.add(f));
            if (!this._validateAll()) {
                e.preventDefault();
                const firstError = this.form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus({ preventScroll: true });
                }
                this._updateSubmit();
            }
        });
    }

    _validateAll() {
        let valid = true;
        this.fields.forEach(field => {
            if (!this._validateField(field, true)) valid = false;
        });
        return valid;
    }

    /**
     * Devuelve true si TODOS los campos requeridos pasan validación.
     * Modo silencioso: no toca clases ni mensajes.
     */
    _isFormValid() {
        for (const field of this.fields) {
            if (!this._checkField(field)) return false;
        }
        return true;
    }

    /**
     * Habilita/deshabilita el botón de submit según validez del form.
     */
    _updateSubmit() {
        if (!this.submitBtn) return;
        const ok = this._isFormValid();
        this.submitBtn.disabled = !ok;
        this.submitBtn.classList.toggle('disabled', !ok);
        // Tooltip explicativo accesible
        if (!ok) {
            this.submitBtn.setAttribute('aria-disabled', 'true');
            this.submitBtn.title = 'Completa los campos requeridos para enviar.';
        } else {
            this.submitBtn.removeAttribute('aria-disabled');
            this.submitBtn.removeAttribute('title');
        }
    }

    /**
     * Verifica un campo SIN tocar el DOM. Devuelve true si pasa.
     * Útil para saber si el form está completo para habilitar el botón.
     */
    _checkField(field) {
        const rulesStr = field.dataset.validate || '';
        const rules    = rulesStr.split('|').filter(Boolean);
        const val      = field.type === 'checkbox' ? (field.checked ? '1' : '') : (field.value ?? '');

        if (field.dataset.match) {
            const target = document.getElementById(field.dataset.match);
            if (target && val !== target.value) return false;
        }

        for (const rule of rules) {
            const [name, arg] = rule.split(':');
            const fn = RULES[name];
            if (!fn) continue;
            if (name !== 'required' && val.toString().trim() === '') continue;
            const ok = arg !== undefined ? fn(val.toString(), arg) : fn(val.toString());
            if (!ok) return false;
        }
        return true;
    }

    /**
     * Valida un campo y refleja resultado en UI.
     * @param {Element} field
     * @param {boolean} showErrors  Si false, solo marca verde cuando OK pero no rojo cuando falla
     */
    _validateField(field, showErrors = true) {
        const rulesStr = field.dataset.validate || '';
        const rules    = rulesStr.split('|').filter(Boolean);
        const val      = field.type === 'checkbox' ? (field.checked ? '1' : '') : (field.value ?? '');

        // Caso especial: match (confirmar contraseña)
        if (field.dataset.match) {
            const target = document.getElementById(field.dataset.match);
            if (target && val !== target.value) {
                if (showErrors) this._setError(field, 'Las contraseñas no coinciden.');
                return false;
            }
        }

        for (const rule of rules) {
            const [ruleName, ruleArg] = rule.split(':');
            const fn = RULES[ruleName];
            if (!fn) continue;
            if (ruleName !== 'required' && val.toString().trim() === '') continue;

            const ok = ruleArg !== undefined ? fn(val.toString(), ruleArg) : fn(val.toString());
            if (!ok) {
                if (showErrors) {
                    const msgFn = MESSAGES[ruleName];
                    const msg   = msgFn ? msgFn(ruleArg) : `Validación [${ruleName}] fallida.`;
                    this._setError(field, msg);
                } else {
                    this._clearError(field);
                }
                return false;
            }
        }

        this._setValid(field);
        return true;
    }

    /**
     * Inserta el mensaje de error directamente debajo del input/select/textarea.
     * Si el campo está dentro de un wrapper position-relative (como password con toggle),
     * insertamos el feedback DESPUÉS del wrapper para no romper el layout.
     */
    _getOrCreateFeedback(field) {
        // Buscar feedback hermano existente
        let sibling = field.nextElementSibling;
        while (sibling) {
            if (sibling.classList && sibling.classList.contains('invalid-feedback')) {
                return sibling;
            }
            // Si encontramos otro hermano que no es help-text, paramos
            if (sibling.classList && (sibling.classList.contains('form-text') || sibling.tagName === 'BUTTON')) {
                sibling = sibling.nextElementSibling;
                continue;
            }
            break;
        }

        // Crear nuevo
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.style.fontSize = '.78rem';
        feedback.style.marginTop = '.25rem';

        // Insertar después de form-text si existe, o después del campo
        const formText = field.parentElement.querySelector('.form-text');
        if (formText) {
            formText.insertAdjacentElement('afterend', feedback);
        } else {
            field.insertAdjacentElement('afterend', feedback);
        }
        return feedback;
    }

    _setError(field, message) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        const feedback = this._getOrCreateFeedback(field);
        feedback.textContent = message;
        feedback.style.display = '';
    }

    _setValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        // Buscar feedback existente y ocultarlo
        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) feedback.textContent = '';
    }

    _clearError(field) {
        field.classList.remove('is-invalid', 'is-valid');
        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) feedback.textContent = '';
    }
}

// ---------------------------------------------------------
// INDICADOR DE FORTALEZA DE CONTRASEÑA
// ---------------------------------------------------------
class PasswordStrength {
    /**
     * @param {HTMLInputElement} input    Campo de contraseña
     * @param {HTMLElement}      container Contenedor donde insertar el indicador
     */
    constructor(input, container) {
        this.input = input;
        this.container = container;
        this._render();
        this.input.addEventListener('input', () => this._update());
    }

    _render() {
        this.container.innerHTML = `
            <div class="password-strength mt-1">
                <div class="progress" style="height:4px">
                    <div class="progress-bar" role="progressbar" style="width:0%"></div>
                </div>
                <small class="strength-label text-muted"></small>
            </div>
        `;
        this.bar   = this.container.querySelector('.progress-bar');
        this.label = this.container.querySelector('.strength-label');
    }

    _update() {
        const val    = this.input.value;
        const score  = this._score(val);
        const levels = [
            { pct: 0,   cls: '',          label: '' },
            { pct: 25,  cls: 'bg-danger', label: 'Muy débil' },
            { pct: 50,  cls: 'bg-warning',label: 'Débil' },
            { pct: 75,  cls: 'bg-info',   label: 'Aceptable' },
            { pct: 100, cls: 'bg-success',label: 'Fuerte' },
        ];
        const level = levels[score];

        this.bar.style.width = level.pct + '%';
        this.bar.className   = 'progress-bar ' + level.cls;
        this.label.textContent = level.label;
    }

    _score(val) {
        if (!val) return 0;
        let score = 0;
        if (val.length >= 8)          score++;
        if (/[A-Z]/.test(val))        score++;
        if (/[a-z]/.test(val))        score++;
        if (/[0-9]/.test(val))        score++;
        // Bonus: carácter especial
        if (/[^A-Za-z0-9]/.test(val)) score = Math.min(score + 1, 4);
        return Math.min(score, 4);
    }
}

// ---------------------------------------------------------
// INICIALIZACIÓN AUTOMÁTICA
// ---------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar validador en todos los formularios marcados
    document.querySelectorAll('form[data-validate-form]').forEach(form => {
        new FormValidator(form);
    });

    // Inicializar indicador de fortaleza
    document.querySelectorAll('[data-password-strength]').forEach(input => {
        const targetId  = input.dataset.passwordStrength;
        const container = document.getElementById(targetId);
        if (container) {
            new PasswordStrength(input, container);
        }
    });

    // Contador de caracteres para textareas con data-maxlength
    document.querySelectorAll('textarea[data-maxlength]').forEach(ta => {
        const max     = parseInt(ta.dataset.maxlength);
        const counter = document.createElement('small');
        counter.className = 'text-muted character-counter';
        counter.textContent = `0 / ${max}`;
        ta.insertAdjacentElement('afterend', counter);

        ta.addEventListener('input', () => {
            const len = ta.value.length;
            counter.textContent = `${len} / ${max}`;
            counter.className = len > max
                ? 'text-danger character-counter'
                : 'text-muted character-counter';
        });
    });
});

// Exportar para uso en módulos
if (typeof module !== 'undefined') {
    module.exports = { FormValidator, PasswordStrength };
}
