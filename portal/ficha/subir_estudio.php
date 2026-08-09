<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    exit('No tenés permiso.');
}

if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit('No se pudo subir el archivo.');
}

$permitidas = ['pdf' => 'PDF', 'jpg' => 'Imagen', 'jpeg' => 'Imagen', 'png' => 'Imagen'];
$nombreOriginal = $_FILES['archivo']['name'];
$ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

if (!isset($permitidas[$ext])) {
    http_response_code(400);
    exit('Tipo de archivo no permitido. Solo PDF, JPG o PNG.');
}

if ($_FILES['archivo']['size'] > 10 * 1024 * 1024) {
    http_response_code(400);
    exit('El archivo supera los 10MB.');
}

$carpeta = __DIR__ . '/../../storage/estudios/' . $pacienteId;
if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

$nombreArchivo = uniqid('estudio_') . '.' . $ext;
$rutaCompleta  = $carpeta . '/' . $nombreArchivo;
$rutaRelativa  = 'storage/estudios/' . $pacienteId . '/' . $nombreArchivo;

if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaCompleta)) {
    http_response_code(500);
    exit('Error al guardar el archivo en el servidor.');
}

$fechaRaw = trim($_POST['fecha_estudio'] ?? '');
$fechaDMY = '';
if ($fechaRaw !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
    if ($dt) $fechaDMY = $dt->format('d/m/Y');
}

estudio_crear(
    $pacienteId,
    (int)($_SESSION['portal_uid'] ?? 0),
    $nombreOriginal,
    $rutaRelativa,
    $permitidas[$ext],
    trim($_POST['notas'] ?? ''),
    $fechaDMY
);

echo 'ok';
