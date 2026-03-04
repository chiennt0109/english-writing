<?php
namespace App\Core;

class Logger
{
    public static function error(string $message): void
    {
        $dir = __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $line = sprintf("[%s] ERROR: %s\n", date('c'), $message);
        file_put_contents($dir . '/app.log', $line, FILE_APPEND);
    }
}
