<?php
// FILE: /app/helpers/Validator.php

class Validator {
    private $errors = array();
    private $data = array();

    public function __construct($data) {
        $this->data = $data;
    }

    public function required($fields) {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
                $this->errors[$field] = ucfirst($field) . ' is required';
            }
        }

        return $this;
    }

    public function email($field) {
        if (isset($this->data[$field]) && !Security::validateEmail($this->data[$field])) {
            $this->errors[$field] = 'Invalid email format';
        }
        return $this;
    }

    public function minLength($field, $min) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = ucfirst($field) . " must be at least {$min} characters";
        }
        return $this;
    }

    public function maxLength($field, $max) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
        }
        return $this;
    }

    public function numeric($field) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = ucfirst($field) . ' must be numeric';
        }
        return $this;
    }

    public function date($field) {
        if (isset($this->data[$field]) && !Security::validateDate($this->data[$field])) {
            $this->errors[$field] = 'Invalid date format';
        }
        return $this;
    }

    public function enum($field, $allowed) {
        if (isset($this->data[$field]) && !Security::validateEnum($this->data[$field], $allowed)) {
            $this->errors[$field] = 'Invalid value for ' . $field;
        }
        return $this;
    }

    public function unique($field, $table, $tenantId = null, $excludeId = null) {
        if (isset($this->data[$field])) {
            $db = Database::getInstance();
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?";
            $params = array($this->data[$field]);

            if ($tenantId !== null) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tenantId;
            }

            if ($excludeId !== null) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }

            $result = $db->fetch($sql, $params);
            if ($result['count'] > 0) {
                $this->errors[$field] = ucfirst($field) . ' already exists';
            }
        }
        return $this;
    }

    public function fails() {
        return !empty($this->errors);
    }

    public function passes() {
        return empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }

    public function firstError() {
        return !empty($this->errors) ? reset($this->errors) : '';
    }
}
