<?php
require_once __DIR__ . '/../core/Response.php';

class ValidationMiddleware {
    public static function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            
            foreach ($rulesArray as $rule) {
                $value = $data[$field] ?? null;
                
                if ($rule === 'required' && empty($value)) {
                    $errors[$field] = "The $field field is required";
                    break;
                }

                if ($rule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "The $field must be a valid email";
                }

                if (strpos($rule, 'min:') === 0) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = "The $field must be at least $min characters";
                    }
                }

                if (strpos($rule, 'max:') === 0) {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = "The $field must be at most $max characters";
                    }
                }

                if ($rule === 'numeric' && $value && !is_numeric($value)) {
                    $errors[$field] = "The $field must be a number";
                }

                if ($rule === 'positive' && $value && $value <= 0) {
                    $errors[$field] = "The $field must be a positive number";
                }
            }
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        return true;
    }
}