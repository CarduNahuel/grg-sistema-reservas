<?php

namespace App\Services;

class Validator
{
    private $errors = [];
    private $data = [];

    public function validate($data, $rules)
    {
        $this->errors = [];
        $this->data = $data;

        foreach ($rules as $field => $ruleSet) {
            $ruleArray = explode('|', $ruleSet);
            
            foreach ($ruleArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $rule)
    {
        $value = $this->data[$field] ?? null;
        
        // Parse rule and parameters
        if (strpos($rule, ':') !== false) {
            list($ruleName, $param) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field][] = "El campo {$field} es obligatorio.";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "El campo {$field} debe ser un email válido.";
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $param) {
                    $this->errors[$field][] = "El campo {$field} debe tener al menos {$param} caracteres.";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $param) {
                    $this->errors[$field][] = "El campo {$field} no debe exceder {$param} caracteres.";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = "El campo {$field} debe ser numérico.";
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = "El campo {$field} debe ser un número entero.";
                }
                break;

            case 'date':
                if (!empty($value) && !$this->isValidDate($value)) {
                    $this->errors[$field][] = "El campo {$field} debe ser una fecha válida.";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!empty($value) && (!isset($this->data[$confirmField]) || $value !== $this->data[$confirmField])) {
                    $this->errors[$field][] = "La confirmación de {$field} no coincide.";
                }
                break;

            case 'unique':
                // Format: unique:table,column
                if (!empty($value)) {
                    list($table, $column) = explode(',', $param);
                    if (!$this->isUnique($table, $column, $value)) {
                        $this->errors[$field][] = "El valor de {$field} ya existe.";
                    }
                }
                break;
        }
    }

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isUnique($table, $column, $value)
    {
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $db->fetchOne($sql, [$value]);
        return $result['count'] == 0;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError()
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }

        if ($data === null) {
            return '';
        }

        // Cast everything else to string and trim safely
        $stringValue = trim((string)$data);

        return htmlspecialchars(strip_tags($stringValue), ENT_QUOTES, 'UTF-8');
    }
}
