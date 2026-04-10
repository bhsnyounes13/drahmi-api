<?php

class Logger {
    private static $logFile = 'logs/app.log';
    private static $enabled = true;

    public static function setLogFile($file) {
        self::$logFile = $file;
    }

    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }

    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }

    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }

    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }

    private static function log($level, $message, $context) {
        if (!self::$enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr\n";

        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        error_log($logEntry, 3, self::$logFile);
    }

    public static function disable() {
        self::$enabled = false;
    }

    public static function enable() {
        self::$enabled = true;
    }
}