<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db_ficha.php';
require_once __DIR__ . '/../../includes/db_nutricion.php';
portal_require_login();

$pacienteId = (int)($_POST['paciente_id'] ?? 0);
$tipo = $_POST['tipo'] ?? '';
$controles = [
    'antropometria' => ['tabla' => 'tm_antropometrias_nutricionales', 'fecha' => 'fecha_medicion', 'campos' => ['peso_actual','altura','imc','cintura','cadera','brazo_relajado','brazo_contraido','muslo_medial']],
    'composicion' => ['tabla' => 'tm_composiciones_nutricionales', 'fecha' => 'fecha_composicion', 'campos' => ['pliegue_tricipital','pliegue_bicipital','pliegue_subescapular','pliegue_suprailíaco','pliegue_abdominal','pliegue_muslo_anterior','pliegue_pierna_medial','porcentaje_grasa','masa_grasa','porcentaje_muscular','masa_muscular','porcentaje_agua','grasa_visceral','imc_composicion']],
    'laboratorio' => ['tabla' => 'tm_laboratorios_nutricionales', 'fecha' => 'fecha_analisis', 'campos' => ['glucosa','hba1c','insulina','colesterol_total','colesterol_ldl','colesterol_hdl','trigliceridos','got','gpt','creatinina','urea','acido_urico','hemoglobina','hematocrito','ferritina','hierro','tsh','t3_libre','t4_libre','vitamina_d','vitamina_b12','acido_folico']],
    'habitos' => ['tabla' => 'tm_habitos_nutricionales', 'fecha' => 'fecha_habitos', 'campos' => ['presion_sistolica','presion_diastolica','horas_sueno','calidad_sueno','bristol','frecuencia_evacuacion','estres','actividad_fisica','agua']],
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$pacienteId || !isset($controles[$tipo]) || !ficha_puede_editar($pacienteId)) {
    http_response_code(403);
    die('No tenés permiso.');
}

$config = $controles[$tipo];
$fecha = trim($_POST[$config['fecha']] ?? '');
$fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
    http_response_code(400);
    die('La fecha indicada no es válida.');
}

$datos = $_POST;
if ($tipo === 'antropometria') {
    $datos['peso'] = $datos['peso_actual'] ?? '';
}
if ($tipo === 'composicion') {
    $datos['imc'] = $datos['imc_composicion'] ?? '';
}

$campos = $config['campos'];
if ($tipo === 'antropometria') $campos[0] = 'peso';
if ($tipo === 'composicion') $campos[count($campos) - 1] = 'imc';
nutricion_crear_registro($config['tabla'], $pacienteId, (int)$_SESSION['portal_uid'], $campos, $datos, $fecha);

header('Location: ' . b('/portal/nutricion/registro.php?paciente_id=' . $pacienteId . '&seccion=' . $tipo . '&ok=' . $tipo));
exit;
