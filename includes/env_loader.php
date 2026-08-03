<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — Cargador de variables de entorno (.env)
// No depende de Composer/vlucas-dotenv para que funcione igual
// en XAMPP local y en Ferozo shared hosting.
// ══════════════════════════════════════════════════════════════

function cargar_env(string $path): void {
    if (!file_exists($path)) {
        return; // si no hay .env, seguimos: env() devolverá los defaults
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (!str_contains($line, '=')) continue;

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Quitar comillas simples/dobles si las tiene
        if (strlen($value) >= 2) {
            $primero = $value[0];
            $ultimo = $value[strlen($value) - 1];
            if (($primero === '"' && $ultimo === '"') || ($primero === "'" && $ultimo === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Atajo para leer variables con default: env('DB_HOST', 'localhost')
function env(string $key, $default = null) {
    $valor = getenv($key);
    return $valor !== false ? $valor : $default;
}
