<?php
// ══════════════════════════════════════════════════════════════
// TECNOMEDIC — includes/auth.php
// Contiene DOS sistemas de sesión independientes, que conviven
// en el mismo archivo sin pisarse:
//
//  1) Panel de turnos (el original, ya existente):
//     $_SESSION['tm_logged'] — funciones: esta_logueado(),
//     requiere_login(), iniciar_sesion_php()
//
//  2) Portal paciente/profesional/admin (Fase A+B):
//     $_SESSION['portal_*'] — funciones: portal_login(),
//     portal_logout(), portal_logueado(), portal_current_user(),
//     portal_require_login(), portal_require_role()
// ══════════════════════════════════════════════════════════════

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/db_portal.php';

// ── 1) Panel de turnos (original) ───────────────────────────────

function iniciar_sesion_php(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

function esta_logueado(): bool {
    iniciar_sesion_php();
    return !empty($_SESSION['tm_logged']);
}

function requiere_login(): void {
    if (!esta_logueado()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

// ── 2) Portal paciente/profesional/admin (Fase A+B) ─────────────

iniciar_sesion_php(); // asegura que la sesión esté iniciada para ambos sistemas

function portal_login(string $identificador, string $password): array {
    $user = usuario_buscar_login(trim($identificador));
    if (!$user) {
        return ['ok' => false, 'motivo' => 'no_existe'];
    }
    if (!password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'motivo' => 'password_incorrecta'];
    }
    if ((int)$user['activo'] !== 1) {
        return ['ok' => false, 'motivo' => 'pendiente'];
    }
    $_SESSION['portal_uid']    = (int)$user['id'];
    $_SESSION['portal_rol']    = $user['rol'];
    $_SESSION['portal_nombre'] = $user['nombre'] . ' ' . $user['apellido'];
    return ['ok' => true, 'user' => $user];
}

function portal_logout(): void {
    unset($_SESSION['portal_uid'], $_SESSION['portal_rol'], $_SESSION['portal_nombre']);
}

function portal_logueado(): bool {
    return isset($_SESSION['portal_uid']);
}

function portal_rol(): ?string {
    return $_SESSION['portal_rol'] ?? null;
}

function portal_current_user(): ?array {
    if (!portal_logueado()) return null;
    return usuario_by_id((int)$_SESSION['portal_uid']);
}

function portal_require_login(): void {
    if (!portal_logueado()) {
        header('Location: ' . b('/login.php'));
        exit;
    }
}

function portal_require_role(array $rolesPermitidos): void {
    portal_require_login();
    if (!in_array(portal_rol(), $rolesPermitidos, true)) {
        http_response_code(403);
        die('No tenés permiso para ver esta página.');
    }
}
