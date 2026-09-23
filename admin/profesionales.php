<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellido  = trim($_POST['apellido'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $dni       = trim($_POST['dni'] ?? '');
        $telefono  = '';
        if ($nombre && $apellido && $email && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st = db()->prepare("INSERT INTO tm_usuarios (nombre,apellido,email,password_hash,dni,telefono,rol,activo,fecha_aprobacion) VALUES (?,?,?,?,?,?,?,1,NOW())");
            $rol = 'profesional';
            $st->bind_param('sssssss', $nombre, $apellido, $email, $hash, $dni, $telefono, $rol);
            $st->execute();
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        }
    } elseif ($accion === 'editar') {
        $id       = (int)($_POST['id'] ?? 0);
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $dni      = trim($_POST['dni'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $activo   = isset($_POST['activo']) ? 1 : 0;
        $password = $_POST['password'] ?? '';
        if ($id && $nombre && $apellido && $email) {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $st = db()->prepare(
                    "UPDATE tm_usuarios SET nombre=?, apellido=?, email=?, dni=?, telefono=?, activo=?, password_hash=?
                     WHERE id=? AND rol='profesional'"
                );
                $st->bind_param('sssssiss', $nombre, $apellido, $email, $dni, $telefono, $activo, $hash, $id);
            } else {
                $st = db()->prepare(
                    "UPDATE tm_usuarios SET nombre=?, apellido=?, email=?, dni=?, telefono=?, activo=?
                     WHERE id=? AND rol='profesional'"
                );
                $st->bind_param('sssssii', $nombre, $apellido, $email, $dni, $telefono, $activo, $id);
            }
            $st->execute();
            // Sincronizar padrón
            persona_upsert($dni, $nombre, $apellido, $telefono, $email);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        }
    } elseif ($accion === 'asignar') {
        $paciente_id    = (int)($_POST['paciente_id'] ?? 0);
        $profesional_id = (int)($_POST['profesional_id'] ?? 0);
        $area           = trim($_POST['area'] ?? '');
        if ($paciente_id && $profesional_id && $area) {
            $st = db()->prepare("INSERT INTO tm_asignaciones (paciente_id, profesional_id, area, activa, fecha_asignacion) VALUES (?,?,?,1,NOW()) ON DUPLICATE KEY UPDATE activa=1");
            $st->bind_param('iis', $paciente_id, $profesional_id, $area);
            $st->execute();
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        }
    } elseif ($accion === 'desasignar') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("UPDATE tm_asignaciones SET activa = 0 WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$profesionales = [];
$asignaciones = [];
$pacientes = [];

try {
    $profesionales = db()->query(
        "SELECT * FROM tm_usuarios WHERE rol = 'profesional' ORDER BY apellido, nombre"
    )->fetch_all(MYSQLI_ASSOC);
    $asignaciones = db()->query(
        "SELECT a.id, a.area, a.fecha_asignacion, a.activa,
            p.id AS paciente_id, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
            pr.id AS profesional_id, pr.nombre AS profesional_nombre, pr.apellido AS profesional_apellido
         FROM tm_asignaciones a
         JOIN tm_usuarios p ON p.id = a.paciente_id
         JOIN tm_usuarios pr ON pr.id = a.profesional_id
         WHERE a.activa = 1
         ORDER BY a.fecha_asignacion DESC"
    )->fetch_all(MYSQLI_ASSOC);
    $pacientes = db()->query(
        "SELECT id, nombre, apellido, dni FROM tm_usuarios WHERE rol = 'paciente' AND activo = 1 ORDER BY apellido, nombre"
    )->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $profesionales = [];
    $asignaciones = [];
    $pacientes = [];
}

$asignaciones_json = json_encode($asignaciones, JSON_UNESCAPED_UNICODE);

$portal_titulo = 'Profesionales · Mi Portal';
$portal_activo = 'profesionales';
require __DIR__ . '/../includes/portal_header.php';
?>

<style>
    /* Reduce alto de filas en tablas para mejorar densidad visual */
    #tablaProfs tbody td,
    #tablaAsig tbody td {
        padding-top: 8px !important;
        padding-bottom: 8px !important;
        vertical-align: middle;
        line-height: 1.2;
    }

    #tablaProfs tbody tr,
    #tablaAsig tbody tr {
        min-height: 46px;
    }
</style>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Profesionales</div>
        <div class="stat-value" style="text-align: end"><?= count($profesionales) ?></div>
        <div class="stat-sub">registrados</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Asignaciones</div>
        <div class="stat-value" style="text-align: end"><?= count($asignaciones) ?></div>
        <div class="stat-sub">activas</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pacientes</div>
        <div class="stat-value" style="text-align: end"><?= count($pacientes) ?></div>
        <div class="stat-sub">disponibles</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Profesionales</div>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn-action btn-save btn-no-tooltip" style="padding:9px 18px;font-size:13px;"
                onclick="openModal('prof')">
                ➕ Nuevo profesional
            </button>
            <button class="btn-action btn-save btn-no-tooltip" style="padding:9px 18px;font-size:13px;"
                onclick="openModal('modalAsig')">
                🔗 Nueva asignación
            </button>
        </div>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">👨‍⚕️ <?= count($profesionales) ?>
            profesional<?= count($profesionales) !== 1 ? 'es' : '' ?></div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchProfs" placeholder="Buscar…" style="width:200px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaProfs">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Nombre<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Estado<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profesionales as $pr): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($pr['apellido'] . ', ' . $pr['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($pr['dni']) ?></td>
                    <td><?= htmlspecialchars($pr['email']) ?></td>
                    <td>
                        <span class="badge <?= $pr['activo'] == 1 ? 'aprobado' : 'pendiente' ?>">
                            <?= $pr['activo'] == 1 ? '✅ Activo' : '⏳ Inactivo' ?>
                        </span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-mod" data-tooltip="Editar"
                                onclick='openModal("editar", <?= json_encode($pr, JSON_UNESCAPED_UNICODE) ?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                                </svg>
                            </button>
                            <form method="post" style="flex:1;"
                                onsubmit="return tmConfirmSubmit(this,'¿Eliminar este profesional?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $pr['id'] ?>">
                                <button type="submit" class="btn-action btn-del" data-tooltip="Eliminar"
                                    >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        viewBox="0 0 16 16">
                                        <path
                                            d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                        <path
                                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$profesionales): ?>
        <div class="empty-state">
            <div class="empty-state-icon">👨‍⚕️</div>
            <div>No hay profesionales registrados.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">🔗 <?= count($asignaciones) ?> asignación<?= count($asignaciones) !== 1 ? 'es' : '' ?>
            activas</div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchAsig" placeholder="Buscar…" style="width:200px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaAsig">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Paciente<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">Profesional<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Área<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Fecha<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asignaciones as $a): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($a['paciente_apellido'] . ', ' . $a['paciente_nombre']) ?></strong>
                    </td>
                    <td><?= htmlspecialchars($a['profesional_apellido'] . ', ' . $a['profesional_nombre']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars(ucfirst($a['area'])) ?></span></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($a['fecha_asignacion']))) ?></td>
                    <td class="actions-col">
                        <form method="post" onsubmit="return tmConfirmSubmit(this,'¿Desasignar este paciente?');">
                            <input type="hidden" name="accion" value="desasignar">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn-actions btn-action btn-del" data-tooltip="Desasignar">
                                <svg fill="#fcf8f8" height="16px" width="16px" version="1.1" id="Capa_1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    viewBox="0 0 177.055 177.055" xml:space="preserve">
                                    <path d="M0.001,88.527c0,48.814,39.713,88.527,88.527,88.527c48.813,0,88.526-39.713,88.526-88.527S137.341,0,88.528,0
	C39.714,0,0.001,39.713,0.001,88.527z M88.528,24.304c35.413,0,64.224,28.811,64.224,64.224c0,13.324-4.081,25.712-11.055,35.983
	L52.544,35.359C62.816,28.385,75.204,24.304,88.528,24.304z M124.511,141.696c-10.272,6.974-22.659,11.055-35.983,11.055
	c-35.413,0-64.223-28.811-64.223-64.224c0-13.324,4.081-25.711,11.054-35.983L124.511,141.696z" />
                                </svg> </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$asignaciones): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔗</div>
            <div>No hay asignaciones activas.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal profesional (creación + edición)
<div class="edit-modal-overlay" id="modalProf">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="modalProfTitle">➕ Nuevo profesional</div>
            <button class="edit-modal-close" onclick="closeModal('modalProf')">✕</button>
        </div>
        <form action="<?= b('/admin/profesionales.php') ?>" method="post" id="modalProfForm">
            <input type="hidden" name="accion" id="modalProfAccion" value="crear">
            <input type="hidden" name="id" id="modalProfId" value="">

            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Nombre *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="nombre" id="f-nombre" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Apellido *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="apellido" id="f-apellido" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">DNI</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="dni" id="f-dni"></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Teléfono</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="telefono" id="f-telefono"></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Email *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="email" name="email" id="f-email" required></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Contraseña <span id="pwdHint">*</span></div>
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password" id="f-password" minlength="6" placeholder="Mínimo 6 caracteres"></div>
                </div>
                <div class="edit-group full">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="activo" id="f-activo" value="1" checked style="width:auto;">
                        <span style="color:#ccc;">Activo</span>
                    </label>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalProf')">Cancelar</button>
                <button type="submit" class="btn-edit-save" id="modalProfSubmitBtn">💾 Crear profesional</button>
            </div>
        </form>
    </div>
</div> -->
<!-- Modal crear / editar -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="modalTitle">Agregar Profesional</div>
            <button class="edit-modal-close"
                style="background:none;border:none;font-size:24px;cursor:pointer;color:#888;padding:4px;"
                onclick="closeModal()">
                <svg height="26" width="26" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512">
                    <circle style="fill:#FF6643;" cx="256" cy="256" r="256" />
                    <path style="fill:#FF6643;" d="M256,0v512c141.385,0,256-114.615,256-256S397.385,0,256,0z" />
                    <polygon style="fill:#ffffff;"
                        points="365.904,184.885 327.115,146.096 256,217.211 184.885,146.096 146.096,184.885 217.211,256
                            146.096,327.115 184.885,365.904 256,294.789 327.115,365.904 365.904,327.115 294.789,256 " />
                </svg>
            </button>
            </button>
        </div>
        <form action="<?= b('/admin/staff.php') ?>" method="post" enctype="multipart/form-data" id="modalForm">
            <input type="hidden" name="accion" id="modalAccion" value="crear">
            <input type="hidden" name="id" id="modalId" value="">

            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Nombre *</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="nombre" id="f-nombre" required>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Apellido *</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="apellido" id="f-apellido" required>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Título profesional</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="titulo" id="f-titulo"
                            placeholder="Ej: Dra., Lic., Dr.">
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Especialidad</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="especialidad" id="f-especialidad"
                            placeholder="Ej: Audiometría, Nutrición…">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Descripción</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="descripcion" id="f-descripcion" rows="3"
                            placeholder="Breve descripción del profesional…"></textarea>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Instagram</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="instagram" id="f-instagram" placeholder="@usuario">
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Orden</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="number" name="orden" id="f-orden" value="0" min="0">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Foto</div>
                    <div class="edit-input-wrap" id="imgPreviewWrap" style="display:none;margin-bottom:8px;">
                        <img id="imgPreview" src="" style="max-height:80px;border-radius:50%;object-fit:cover;">
                    </div>
                    <input class="edit-input" type="file" name="foto" accept=".jpg,.jpeg,.png,.webp">
                    <small style="color:#94a3b8;font-size:11px;">JPG, PNG — máx. 3MB. Dejá vacío para no
                        cambiar.</small>
                </div>
                <div class="edit-group full">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="activo" id="f-activo" value="1" checked style="width:auto;">
                        <span style="color:#ccc;">Visible en el sitio</span>
                    </label>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-edit-save" id="modalSubmitBtn">💾 Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-ok">✅ Guardado exitosamente</div>

<!-- Modal asignar -->
<div class="edit-modal-overlay" id="modalAsig">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title"> Nueva asignación</div>
            <button class="edit-modal-close"
                style="background:none;border:none;font-size:24px;cursor:pointer;color:#888;padding:4px;"
                onclick="closeModal('modalAsig')">

                <svg height="26" width="26" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512">
                    <circle style="fill:#FF6643;" cx="256" cy="256" r="256" />
                    <path style="fill:#FF6643;" d="M256,0v512c141.385,0,256-114.615,256-256S397.385,0,256,0z" />
                    <polygon style="fill:#ffffff;"
                        points="365.904,184.885 327.115,146.096 256,217.211 184.885,146.096 146.096,184.885 217.211,256
                            146.096,327.115 184.885,365.904 256,294.789 327.115,365.904 365.904,327.115 294.789,256 " />
                </svg></button>
        </div>
        <form action="<?= b('/admin/profesionales.php') ?>" method="post">
            <input type="hidden" name="accion" value="asignar">
            <div class="edit-grid">
                <div class="edit-group full">
                    <div class="edit-label">Paciente *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="paciente_id" required>
                            <option value="">— Seleccionar paciente —</option>
                            <?php foreach ($pacientes as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre'] . ' (' . $p['dni'] . ')') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Profesional *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="profesional_id" required>
                            <option value="">— Seleccionar profesional —</option>
                            <?php foreach ($profesionales as $pr): ?>
                            <option value="<?= $pr['id'] ?>">
                                <?= htmlspecialchars($pr['apellido'] . ', ' . $pr['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Área *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="area" required>
                            <option value="">— Seleccionar área —</option>
                            <option value="audiologia">Audiología</option>
                            <option value="hiperbarica">Medicina hiperbárica</option>
                            <option value="nutricion">Nutrición</option>
                            <option value="ortopedia">Ortopedia y rehabilitación</option>
                            <option value="equipamiento">Equipamiento médico y quirúrgico</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAsig')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Asignar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal ver profesional y pacientes por área -->
<div class="edit-modal-overlay" id="modalVerProf" onclick="if(event.target===this) closeModalVerProf()">
    <div class="edit-modal" style="max-width:500px;max-height:80vh;overflow-y:auto;">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="verProfTitle"></div>
            <button class="edit-modal-close btn-cerrar-rojo" onclick="closeModalVerProf()">❌</button>
        </div>
        <div class="edit-modal-body" id="verProfBody">Cargando…</div>
    </div>
</div>

<div id="toast-ok">✅ Acción realizada</div>

<script>
(function() {
    // Datos de asignaciones para ver pacientes por profesional
    var ASIGNACIONES = <?= $asignaciones_json ?>;

    if (new URLSearchParams(location.search).get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        t.classList.add('show');
        setTimeout(function() {
            t.classList.remove('show');
            history.replaceState({}, '', location.pathname);
        }, 3000);
    }

    function setupTable(tableId, searchId) {
        var search = document.getElementById(searchId);
        if (!search) return;
        search.addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#' + tableId + ' tbody tr').forEach(function(r) {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        var sortState = {
            col: -1,
            dir: 'asc'
        };
        document.querySelectorAll('#' + tableId + ' th.sortable').forEach(function(th) {
            th.addEventListener('click', function() {
                var col = parseInt(th.dataset.col);
                var dir = (sortState.col === col && sortState.dir === 'asc') ? 'desc' : 'asc';
                sortState = {
                    col: col,
                    dir: dir
                };
                document.querySelectorAll('#' + tableId + ' th.sortable').forEach(function(h) {
                    h.classList.remove('asc', 'desc');
                });
                th.classList.add(dir);
                sortTable(tableId, col, dir);
            });
        });
    }

    function sortTable(tableId, col, dir) {
        var tbody = document.querySelector('#' + tableId + ' tbody');
        var rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort(function(a, b) {
            var aVal = (a.querySelectorAll('td')[col] || {
                textContent: ''
            }).textContent.trim().toLowerCase();
            var bVal = (b.querySelectorAll('td')[col] || {
                textContent: ''
            }).textContent.trim().toLowerCase();
            if (aVal < bVal) return dir === 'asc' ? -1 : 1;
            if (aVal > bVal) return dir === 'asc' ? 1 : -1;
            return 0;
        });
        rows.forEach(function(r) {
            tbody.appendChild(r);
        });
    }

    setupTable('tablaProfs', 'searchProfs');
    setupTable('tablaAsig', 'searchAsig');

    // window.verProfs = function(d) {
    //     var areas = {};
    //     ASIGNACIONES.forEach(function(a) {
    //         if (a.profesional_id == d.id) {
    //             if (!areas[a.area]) areas[a.area] = [];
    //             areas[a.area].push(a.paciente_apellido + ', ' + a.paciente_nombre);
    //         }
    //     });
    //     var html = '<div style="margin-bottom:8px;font-weight:600;">Email: ' + (d.email||'-') + '</div>';
    //     html += '<div style="margin-bottom:8px;font-weight:600;">DNI: ' + (d.dni||'-') + '</div>';
    //     html += '<div style="margin-bottom:12px;font-weight:600;">Pacientes por área:</div>';
    //     var areasNombres = {
    //         'audiologia': 'Audiología',
    //         'hiperbarica': 'Medicina Hiperbárica',
    //         'nutricion': 'Nutrición',
    //         'ortopedia': 'Ortopedia y Rehabilitación',
    //         'equipamiento': 'Equipamiento Médico y Quirúrgico'
    //     };
    //     Object.keys(areas).forEach(function(area) {
    //         html += '<div style="margin-bottom:10px;border-left:3px solid var(--tm-teal);padding-left:10px;">';
    //         html += '<div style="font-weight:600;margin-bottom:4px;">' + (areasNombres[area] || area) + '</div>';
    //         areas[area].forEach(function(p) {
    //             html += '<div style="padding:2px 0;">• ' + p + '</div>';
    //         });
    //         html += '</div>';
    //     });
    //     if (Object.keys(areas).length === 0) {
    //         html += '<div style="color:var(--muted);">Este profesional no tiene asignaciones activas.</div>';
    //     }
    //     document.getElementById('verProfTitle').textContent = d.apellido + ', ' + d.nombre;
    //     document.getElementById('verProfBody').innerHTML = html;
    //     document.getElementById('modalVerProf').classList.add('open');
    // };

    // window.closeModalVerProf = function() {
    //     document.getElementById('modalVerProf').classList.remove('open');
    // };

    // window.openModal = function(mode, data) {
    //     var accion = mode === 'editar' ? 'editar' : 'crear';
    //     document.getElementById('modalProfAccion').value = accion;
    //     document.getElementById('modalProfId').value = data && data.id ? data.id : '';
    //     document.getElementById('modalProfTitle').textContent = mode === 'editar' ? '✏️ Editar profesional' : '➕ Nuevo profesional';
    //     document.getElementById('modalProfSubmitBtn').textContent = mode === 'editar' ? '💾 Guardar cambios' : '💾 Crear profesional';

    //     var isEdit = mode === 'editar';
    //     document.getElementById('pwdHint').textContent = isEdit ? '(opcional)' : '*';
    //     document.getElementById('f-password').required = !isEdit;
    //     document.getElementById('f-password').placeholder = isEdit ? 'Dejar vacío para no cambiar' : 'Mínimo 6 caracteres';

    //     var formFields = ['nombre', 'apellido', 'dni', 'telefono', 'email', 'password', 'activo'];
    //     formFields.forEach(function(f) {
    //         var el = document.getElementById('f-' + f);
    //         if (!el) return;
    //         if (f === 'activo') {
    //             el.checked = !data || data.activo == '1' || data.activo === 1;
    //         } else {
    //             el.value = data && data[f] ? data[f] : '';
    //         }
    //     });
    //     document.getElementById('modalProf').classList.add('open');
    // };
    // window.closeModal = function(id) { document.getElementById(id).classList.remove('open'); };

    // document.getElementById('modalProf').addEventListener('click', function(e) { if (e.target === this) closeModal('modalProf'); });
    // document.getElementById('modalAsig').addEventListener('click', function(e) { if (e.target === this) closeModal('modalAsig'); });
    // document.getElementById('modalVerProf').addEventListener('click', function(e) { if (e.target === this) closeModalVerProf(); });
    // document.addEventListener('keydown', function(e) {
    //     if (e.key === 'Escape') {
    //         closeModal('modalProf');
    //         closeModal('modalAsig');
    //         closeModalVerProf();
    //     }
    // });

    window.openModal = function(mode, data) {
        if (mode === 'modalAsig') {
            document.getElementById('modalAsig').classList.add('open');
            return;
        }

        document.getElementById('modalAccion').value = mode === 'editar' ? 'editar' : 'crear';
        document.getElementById('modalId').value = data && data.id ? data.id : '';
        document.getElementById('modalTitle').textContent = mode === 'editar' ? '✏️ Editar miembro' :
            'Agregar Profesional';
        document.getElementById('modalSubmitBtn').textContent = mode === 'editar' ? '💾 Guardar cambios' :
            '💾 Crear';

        ['nombre', 'apellido', 'titulo', 'especialidad', 'descripcion', 'instagram', 'orden'].forEach(function(
            f) {
            document.getElementById('f-' + f).value = data && data[f] ? data[f] : '';
        });
        document.getElementById('f-orden').value = data && data.orden ? data.orden : 0;
        document.getElementById('f-activo').checked = !data || data.activo == '1' || data.activo === 1;
        document.getElementById('imgPreviewWrap').style.display = 'none';
        document.getElementById('imgPreview').src = '';
        if (data && data.foto) {
            document.getElementById('imgPreview').src = '<?= b('/') ?>' + data.foto;
            document.getElementById('imgPreviewWrap').style.display = 'block';
        }

        document.getElementById('editModal').classList.add('open');
    };

    window.closeModal = function(id) {
        document.getElementById(id || 'editModal').classList.remove('open');
    };

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    document.getElementById('modalAsig').addEventListener('click', function(e) {
        if (e.target === this) closeModal('modalAsig');
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeModal('modalAsig');
        }
    });

})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>