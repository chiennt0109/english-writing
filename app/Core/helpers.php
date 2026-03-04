<?php

use App\Core\Config;

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function base_path(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = rtrim(dirname($scriptName), '/');
    return $dir === '/' ? '' : $dir;
}

function url(string $path = ''): string
{
    $base = base_path();
    $path = '/' . ltrim($path, '/');
    return ($base === '' ? '' : $base) . ($path === '/' ? '' : $path);
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return hash_equals($_SESSION['_csrf'] ?? '', $token);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_role(string $role): bool
{
    return (current_user()['role'] ?? null) === $role;
}

function can_access(array $roles): bool
{
    return in_array(current_user()['role'] ?? '', $roles, true);
}

function app_name(): string
{
    return Config::get('app.name', 'App');
}
