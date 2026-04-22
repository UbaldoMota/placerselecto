<?php
/**
 * Validator.php
 * Validador de formularios con reglas encadenables.
 *
 * Uso:
 *   $v = new Validator($_POST);
 *   $v->required('email', 'Email')
 *     ->email('email')
 *     ->required('password', 'Contraseña')
 *     ->minLength('password', 8);
 *
 *   if ($v->fails()) {
 *       $errors = $v->errors();
 *   }
 */

class Validator
{
    /** @var array<string, mixed> Datos a validar */
    private array $data;

    /** @var array<string, array<string>> Errores por campo */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data Array de datos (ej. $_POST)
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ---------------------------------------------------------
    // REGLAS
    // ---------------------------------------------------------

    /**
     * Campo requerido (no vacío).
     */
    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->getValue($field);

        if ($value === null || trim((string) $value) === '') {
            $this->addError($field, "El campo {$label} es requerido.");
        }
        return $this;
    }

    /**
     * Formato de email válido.
     */
    public function email(string $field, string $label = 'Email'): static
    {
        $value = trim((string) $this->getValue($field));
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El {$label} no tiene un formato válido.");
        }
        return $this;
    }

    /**
     * Longitud mínima de caracteres.
     */
    public function minLength(string $field, int $min, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = (string) $this->getValue($field);

        if ($value !== '' && mb_strlen($value, 'UTF-8') < $min) {
            $this->addError($field, "El campo {$label} debe tener al menos {$min} caracteres.");
        }
        return $this;
    }

    /**
     * Longitud máxima de caracteres.
     */
    public function maxLength(string $field, int $max, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = (string) $this->getValue($field);

        if ($value !== '' && mb_strlen($value, 'UTF-8') > $max) {
            $this->addError($field, "El campo {$label} no puede superar {$max} caracteres.");
        }
        return $this;
    }

    /**
     * El campo debe coincidir con otro campo (ej. confirmar contraseña).
     */
    public function matches(string $field, string $otherField, string $label = ''): static
    {
        $label = $label ?: "'{$field}' y '{$otherField}'";
        $val1  = $this->getValue($field);
        $val2  = $this->getValue($otherField);

        if ($val1 !== $val2) {
            $this->addError($field, "Los campos {$label} no coinciden.");
        }
        return $this;
    }

    /**
     * El valor debe estar en un conjunto de opciones permitidas.
     *
     * @param array<mixed> $allowed
     */
    public function in(string $field, array $allowed, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->getValue($field);

        if ($value !== null && !in_array($value, $allowed, true)) {
            $opciones = implode(', ', $allowed);
            $this->addError($field, "El campo {$label} debe ser uno de: {$opciones}.");
        }
        return $this;
    }

    /**
     * Valor entero positivo.
     */
    public function positiveInt(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->getValue($field);

        if ($value !== null && trim((string) $value) !== '') {
            if (!filter_var($value, FILTER_VALIDATE_INT) || (int) $value <= 0) {
                $this->addError($field, "El campo {$label} debe ser un número entero positivo.");
            }
        }
        return $this;
    }

    /**
     * Validación de contraseña segura.
     * Mínimo 8 chars, 1 mayúscula, 1 minúscula, 1 número.
     */
    public function strongPassword(string $field, string $label = 'Contraseña'): static
    {
        $value  = (string) $this->getValue($field);
        $errors = Security::validatePassword($value);

        foreach ($errors as $err) {
            $this->addError($field, $err);
        }
        return $this;
    }

    /**
     * Número de teléfono (entre 7 y 20 dígitos).
     */
    public function phone(string $field, string $label = 'Teléfono'): static
    {
        $value = (string) $this->getValue($field);
        if ($value !== '' && !Security::isValidPhone($value)) {
            $this->addError($field, "El {$label} no tiene un formato válido (7-20 dígitos).");
        }
        return $this;
    }

    /**
     * No permite caracteres HTML/script (campo de solo texto plano).
     */
    public function noHtml(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = (string) $this->getValue($field);

        if ($value !== strip_tags($value)) {
            $this->addError($field, "El campo {$label} no puede contener HTML.");
        }
        return $this;
    }

    /**
     * Valida que un campo sea una URL válida.
     */
    public function url(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = (string) $this->getValue($field);

        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "El campo {$label} debe ser una URL válida.");
        }
        return $this;
    }

    /**
     * Valida que un campo sea numérico y esté en rango [min, max].
     */
    public function range(string $field, float $min, float $max, string $label = ''): static
    {
        $label = $label ?: $field;
        $value = $this->getValue($field);

        if ($value !== null && trim((string) $value) !== '') {
            $num = (float) $value;
            if ($num < $min || $num > $max) {
                $this->addError($field, "El campo {$label} debe estar entre {$min} y {$max}.");
            }
        }
        return $this;
    }

    /**
     * Añade un error personalizado condicionalmente.
     *
     * @param bool   $condition Si es true, se agrega el error
     * @param string $field
     * @param string $message
     */
    public function custom(bool $condition, string $field, string $message): static
    {
        if ($condition) {
            $this->addError($field, $message);
        }
        return $this;
    }

    // ---------------------------------------------------------
    // RESULTADO
    // ---------------------------------------------------------

    /**
     * Retorna true si hay errores de validación.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Retorna true si no hay errores.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Retorna todos los errores.
     *
     * @return array<string, array<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Retorna el primer error de un campo específico.
     */
    public function firstError(string $field): string
    {
        return $this->errors[$field][0] ?? '';
    }

    /**
     * Retorna todos los errores como array plano (para flash messages).
     *
     * @return array<string>
     */
    public function allErrors(): array
    {
        $flat = [];
        foreach ($this->errors as $fieldErrors) {
            foreach ($fieldErrors as $err) {
                $flat[] = $err;
            }
        }
        return $flat;
    }

    /**
     * Retorna el primer error global (el primero de todos los campos).
     */
    public function firstGlobalError(): string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return '';
    }

    // ---------------------------------------------------------
    // HELPERS INTERNOS
    // ---------------------------------------------------------

    private function getValue(string $field): mixed
    {
        // Soporte para notación dot: 'user.name' → $data['user']['name']
        if (str_contains($field, '.')) {
            $parts = explode('.', $field);
            $value = $this->data;
            foreach ($parts as $part) {
                if (!is_array($value) || !array_key_exists($part, $value)) {
                    return null;
                }
                $value = $value[$part];
            }
            return $value;
        }

        return $this->data[$field] ?? null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}
