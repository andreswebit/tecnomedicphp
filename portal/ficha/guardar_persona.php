<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_portal.php';
portal_require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$pacienteId = (int)($_POST['paciente_id'] ?? 0);

// Solo admin o profesional pueden editar
$rol = portal_rol();
if (!in_array($rol, ['admin', 'profesional'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No tenés permiso para editar esta ficha.']);
    exit;
}

// Verificar acceso
if ($rol === 'profesional') {
    $st = db()->prepare(
        "SELECT 1 FROM tm_asignaciones WHERE profesional_id=? AND paciente_id=? AND activa=1"
    );
    $st->bind_param('ii', $_SESSION['portal_uid'], $pacienteId);
    $st->execute();
    if (!$st->get_result()->fetch_row()) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'No tenés asignación con este paciente.']);
        exit;
    }
}

$paciente = usuario_by_id($pacienteId);
if (!$paciente || $paciente['rol'] !== 'paciente') {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Paciente no encontrado.']);
    exit;
}

$dni       = preg_replace('/\D/', '', $_POST['dni'] ?? '');
$nombre    = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$email     = trim($_POST['email'] ?? '');
$fechaNac  = trim($_POST['fecha_nacimiento'] ?? '');
$obraSocialId = !empty($_POST['obra_social_id']) ? (int)$_POST['obra_social_id'] : null;

if (!$nombre || !$apellido || !$dni) {
    echo json_encode(['ok' => false, 'error' => 'Nombre, apellido y DNI son obligatorios.']);
    exit;
}

// Actualizar tm_usuarios (nombre, apellido, telefono, email)
$st = db()->prepare(
    "UPDATE tm_usuarios SET nombre=?, apellido=?, telefono=?, email=? WHERE id=?"
);
$st->bind_param('ssssi', $nombre, $apellido, $telefono, $email, $pacienteId);
$st->execute();

// Actualizar tm_perfiles_paciente (fecha_nacimiento, obra_social_id)
$st2 = db()->prepare(
    "UPDATE tm_perfiles_paciente SET fecha_nacimiento=?, obra_social_id=? WHERE usuario_id=?"
);
$fn = $fechaNac !== '' ? $fechaNac : null;
$st2->bind_param('sii', $fn, $obraSocialId, $pacienteId);
$st2->execute();

// Sincronizar tm_personas (padrón)
persona_upsert($dni, $nombre, $apellido, $telefono, $email, $obraSocialId);

echo json_encode(['ok' => true, 'message' => 'Datos actualizados correctamente.']);
