<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
portal_require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
if (!$pacienteId || !ficha_puede_editar($pacienteId) || portal_rol() === 'paciente') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No tenés permiso para subir estudios a este paciente.']);
    exit;
}

if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No se seleccionó ningún archivo o ocurrió un error en la subida.']);
    exit;
}

$permitidas = ['pdf' => 'PDF', 'jpg' => 'Imagen', 'jpeg' => 'Imagen', 'png' => 'Imagen'];
$nombreOriginal = $_FILES['archivo']['name'];
$ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

if (!isset($permitidas[$ext])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Tipo de archivo no permitido. Solo se aceptan archivos PDF, JPG o PNG.']);
    exit;
}

if ($_FILES['archivo']['size'] > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El archivo supera el límite de 10 MB.']);
    exit;
}

$carpeta = __DIR__ . '/../../storage/estudios/' . $pacienteId;
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$nombreArchivo = uniqid('estudio_') . '.' . $ext;
$rutaCompleta  = $carpeta . '/' . $nombreArchivo;
$rutaRelativa  = 'storage/estudios/' . $pacienteId . '/' . $nombreArchivo;

if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaCompleta)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar el archivo en el servidor.']);
    exit;
}

$fechaRaw = trim($_POST['fecha_estudio'] ?? '');
$fechaDMY = '';
if ($fechaRaw !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
    if (!$dt) {
        $dt = DateTime::createFromFormat('d/m/Y', $fechaRaw);
    }
    if ($dt) {
        $fechaDMY = $dt->format('d/m/Y');
    }
}

try {
    $tipo = trim($_POST['tipo'] ?? '') ?: $permitidas[$ext];
    $notas = trim($_POST['notas'] ?? '');
    $subidoPor = (int)($_SESSION['portal_uid'] ?? 0);

    $id = estudio_crear(
        $pacienteId,
        $subidoPor,
        $nombreOriginal,
        $rutaRelativa,
        $tipo,
        $notas,
        $fechaDMY
    );

    echo json_encode([
        'ok' => true,
        'message' => 'Estudio subido correctamente.',
        'estudio_id' => $id
    ]);
} catch (Throwable $e) {
    // Si falla la BD, intentamos borrar el archivo huérfano
    if (is_file($rutaCompleta)) {
        unlink($rutaCompleta);
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al registrar el estudio en la base de datos: ' . $e->getMessage()]);
}
