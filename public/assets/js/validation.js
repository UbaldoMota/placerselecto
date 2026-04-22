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
        this.form = form;
        this.fields = form.querySelectorAll('[data-validate]');
        this._bind();
    }

    _bind() {
        // Validar campo al perder foco
        this.fields.forEach(field => {
            field.addEventListener('blur', () => this._validateField(field));
            field.addEventListener('input', () => this._clearError(field));
        });

        // Validar todo al submit
        this.form.addEventListener('submit', (e) => {
            if (!this._validateAll()) {
                e.preventDefault();
                // Scroll al primer error
                const firstError = this.form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    }

    _validateAll() {
        let valid = true;
        this.fields.forEach(field => {
            if (!this._validateField(field)) valid = false;
        });
        return valid;
    }

    /**
     * Valida un campo según sus atributos data-validate.
     * Formato: data-validate="required|email|minLen:8"
     */
    _validateField(field) {
        const rulesStr = field.dataset.validate || '';
        const rules    = rulesStr.split('|').filter(Boolean);
        const val      = field.value;

        // Caso especial: confirmar contraseña
        if (field.dataset.match) {
            const target = document.getElementById(field.dataset.match);
            if (target && val !== target.value) {
                this._setError(field, 'Las contraseñas no coinciden.');
                return false;
            }
        }

        for (const rule of rules) {
            const [ruleName, ruleArg] = rule.split(':');
            const fn = RULES[ruleName];
            if (!fn) continue;

            // Si el campo no es requerido y está vacío, omitir otras reglas
            if (ruleName !== 'required' && val.trim() === '') continue;

            const ok = ruleArg !== undefined ? fn(val, ruleArg) : fn(val);
            if (!ok) {
                const msgFn = MESSAGES[ruleName];
                const msg   = msgFn ? msgFn(ruleArg) : `Validación [${ruleName}] fallida.`;
                this._setError(field, msg);
                return false;
            }
        }

        this._setValid(field);
        return true;
    }

    _setError(field, message) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');

        let feedback = field.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.classList.add('invalid-feedback');
            field.insertAdjacentElement('afterend', feedback);
        }
        feedback.textContent = message;
    }

    _setValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        const feedback = field.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    }

    _clearError(field) {
        field.classList.remove('is-invalid', 'is-valid');
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
