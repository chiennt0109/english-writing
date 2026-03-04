<?php
namespace App\Core;

class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $map = [
            'app.name' => Env::get('APP_NAME', 'English Writing Coach'),
            'app.url' => Env::get('APP_URL', 'http://localhost:8000'),
            'db.driver' => Env::get('DB_DRIVER', 'sqlite'),
            'db.database' => Env::get('DB_DATABASE', 'database/app.db'),
            'db.host' => Env::get('DB_HOST', '127.0.0.1'),
            'db.port' => Env::get('DB_PORT', '3306'),
            'db.name' => Env::get('DB_NAME', 'english_writing'),
            'db.user' => Env::get('DB_USER', 'root'),
            'db.pass' => Env::get('DB_PASS', ''),
        ];

        return $map[$key] ?? $default;
    }
}
