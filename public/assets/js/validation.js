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
        this.form    = form;
        this.fields  = form.querySelectorAll('[data-validate]');
        this.touched = new WeakSet();   // campos que el usuario ya tocó
        this._bind();
    }

    _bind() {
        this.fields.forEach(field => {
            // Validar al perder foco — primera vez muestra error completo
            field.addEventListener('blur', () => {
                this.touched.add(field);
                this._validateField(field, true);
            });

            // Mientras escribe: solo refresca el feedback si el campo ya fue tocado
            // (evita mostrar rojo en campos que aún no han sido completados)
            const evt = (field.tagName === 'SELECT' || field.type === 'checkbox') ? 'change' : 'input';
            field.addEventListener(evt, () => {
                if (this.touched.has(field)) {
                    this._validateField(field, true);
                }
            });
        });

        // Submit: validar todo. Si hay errores, prevenir envío y mostrarlos
        // debajo de cada campo. El botón sigue clickable en todo momento.
        this.form.addEventListener('submit', (e) => {
            this.fields.forEach(f => this.touched.add(f));
            if (!this._validateAll()) {
                e.preventDefault();
                const firstError = this.form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus({ preventScroll: true });
                }
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
     * Inserta el mensaje de error debajo del control. El "anchor" donde se inserta
     * depende de la estructura:
     * - Si el campo está dentro de un .input-group (input + botón al lado), el
     *   feedback va DESPUÉS del .input-group para no romper el flex.
     * - Si está dentro de un wrapper .position-relative (password con toggle
     *   absolute), también va después del wrapper.
     * - Si hay un .form-text (helper), va después del helper.
     * - Por defecto, va inmediatamente después del campo.
     */
    _getAnchor(field) {
        // 1) input-group → usar el grupo completo
        const inputGroup = field.closest('.input-group');
        if (inputGroup) return inputGroup;

        // 2) wrapper position-relative (password con toggle absolute)
        const posRel = field.closest('.position-relative');
        if (posRel && posRel !== field) return posRel;

        // 3) form-text si está después del campo en el mismo padre
        const parent = field.parentElement;
        if (parent) {
            const formText = parent.querySelector(':scope > .form-text');
            if (formText && field.compareDocumentPosition(formText) & Node.DOCUMENT_POSITION_FOLLOWING) {
                return formText;
            }
        }

        // 4) default: el propio campo
        return field;
    }

    _getOrCreateFeedback(field) {
        const anchor = this._getAnchor(field);

        // Si ya existe un feedback inmediatamente después del anchor, reutilizarlo
        const next = anchor.nextElementSibling;
        if (next && next.classList && next.classList.contains('invalid-feedback')) {
            return next;
        }

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block';
        feedback.style.fontSize = '.78rem';
        feedback.style.marginTop = '.25rem';
        anchor.insertAdjacentElement('afterend', feedback);
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
        const anchor   = this._getAnchor(field);
        const feedback = anchor.nextElementSibling;
        if (feedback && feedback.classList && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    }

    _clearError(field) {
        field.classList.remove('is-invalid', 'is-valid');
        const anchor   = this._getAnchor(field);
        const feedback = anchor.nextElementSibling;
        if (feedback && feedback.classList && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
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
