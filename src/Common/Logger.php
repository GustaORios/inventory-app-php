<?php

namespace Src\Common;

class Logger
{
    private static string $logFile = __DIR__ . '/../../logs/app.log';

    public static function info(string $message): void
    {
        self::write('INFO', $message);
    }

    public static function error(string $message): void
    {
        self::write('ERROR', $message);
    }

    private static function write(string $level, string $message): void
    {
        $logDir = dirname(self::$logFile);

        // Asegurar que la carpeta exista
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $date = date('Y-m-d H:i:s');
        $line = "[$date][$level] $message" . PHP_EOL;

        // Crea el archivo si no existe / agrega si existe
        file_put_contents(self::$logFile, $line, FILE_APPEND);
    }
}
