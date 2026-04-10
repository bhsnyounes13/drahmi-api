<?php

class Validator {
    public static function isEmail($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isNumeric($value) {
        return is_numeric($value);
    }

    public static function isPositive($value) {
        return is_numeric($value) && $value > 0;
    }

    public static function isInteger($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function isString($value) {
        return is_string($value);
    }

    public static function isArray($value) {
        return is_array($value);
    }

    public static function isDate($value, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }

    public static function minLength($value, $min) {
        return strlen($value) >= $min;
    }

    public static function maxLength($value, $max) {
        return strlen($value) <= $max;
    }

    public static function between($value, $min, $max) {
        return $value >= $min && $value <= $max;
    }

    public static function in($value, $array) {
        return in_array($value, $array);
    }

    public static function required($value) {
        return !empty($value) || $value === '0' || $value === 0;
    }

    public static function sanitize($value) {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
}