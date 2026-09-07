<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $dni      = trim($_POST['dni'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rol      = $_POST['rol'] ?? 'paciente';
        $titulo        = trim($_POST['titulo'] ?? '');
        $especialidad  = trim($_POST['especialidad'] ?? '');
        $descripcion   = trim($_POST['descripcion'] ?? '');
        $instagram     = trim($_POST['instagram'] ?? '');
        $orden         = !empty($_POST['orden']) ? (int)$_POST['orden'] : 0;
        $area          = trim($_POST['area'] ?? '');
        $matricula     = trim($_POST['matricula'] ?? '');
        $obra_social_id = !empty($_POST['obra_social_id']) ? (int)$_POST['obra_social_id'] : null;

        if ($nombre && $apellido && $email && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($rol === 'profesional') {
                $st = db()->prepare(
                    "INSERT INTO tm_usuarios (nombre,apellido,email,password_hash,dni,telefono,rol,titulo,especialidad,descripcion,instagram,orden,activo,fecha_aprobacion)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,NOW())"
                );
                $st->bind_param('sssssssssssi', $nombre, $apellido, $email, $hash, $dni, $telefono, $rol, $titulo, $especialidad, $descripcion, $instagram, $orden);
            } else {
                $st = db()->prepare(
                    "INSERT INTO tm_usuarios (nombre,apellido,email,password_hash,dni,telefono,rol,activo,fecha_aprobacion)
                     VALUES (?,?,?,?,?,?,?,1,NOW())"
                );
                $st->bind_param('sssssss', $nombre, $apellido, $email, $hash, $dni, $telefono, $rol);
            }
            $st->execute();
            $id = db()->insert_id;

            // Crear perfil según rol
            if ($rol === 'paciente') {
                $stP = db()->prepare("INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id) VALUES (?,?)");
                $stP->bind_param('ii', $id, $obra_social_id);
                $stP->execute();
            } elseif ($rol === 'profesional') {
                $stP = db()->prepare("INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula) VALUES (?,?,?)");
                $stP->bind_param('iss', $id, $area, $matricula);
                $stP->execute();
            }

            // Sincronizar tm_personas
            if (function_exists('persona_upsert')) {
                persona_upsert($dni, $nombre, $apellido, $telefono, $email, $obra_social_id);
            }

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
        $rol      = $_POST['rol'] ?? 'paciente';
        $password = trim($_POST['password'] ?? '');

        // Campos específicos paciente
        $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
        $obra_social_id   = !empty($_POST['obra_social_id']) ? (int)$_POST['obra_social_id'] : null;

        // Campos específicos profesional
        $area       = trim($_POST['area'] ?? '');
        $matricula  = trim($_POST['matricula'] ?? '');
        $titulo     = trim($_POST['titulo'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');
        $descripcion  = trim($_POST['descripcion'] ?? '');
        $foto         = trim($_POST['foto'] ?? '');
        $instagram    = trim($_POST['instagram'] ?? '');
        $orden        = !empty($_POST['orden']) ? (int)$_POST['orden'] : 0;

        // Validar email único (excluyendo al usuario actual)
        $stCheck = db()->prepare("SELECT id FROM tm_usuarios WHERE email=? AND id!=?");
        $stCheck->bind_param('si', $email, $id);
        $stCheck->execute();
        $resCheck = $stCheck->get_result();
        if ($resCheck->num_rows > 0) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=email_duplicado');
            exit;
        }

        // Construir query de actualización dinámicamente para tm_usuarios
        $sets = ["nombre=?", "apellido=?", "email=?", "dni=?", "telefono=?", "rol=?"];
        $params = [$nombre, $apellido, $email, $dni, $telefono, $rol];

        // Campos de staff (solo si es profesional)
        if ($rol === 'profesional') {
            $sets[] = "titulo=?";        $params[] = $titulo;
            $sets[] = "especialidad=?";  $params[] = $especialidad;
            $sets[] = "descripcion=?";   $params[] = $descripcion;
            $sets[] = "foto=?";          $params[] = $foto;
            $sets[] = "instagram=?";     $params[] = $instagram;
            $sets[] = "orden=?";         $params[] = $orden;
        }

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sets[] = "password_hash=?";
            $params[] = $hash;
        }

        $query = "UPDATE tm_usuarios SET " . implode(", ", $sets) . " WHERE id=?";
        $params[] = $id;

        $st = db()->prepare($query);
        $types = '';
        foreach ($params as $i => $p) {
            if (is_int($p)) $types .= 'i';
            else if (is_float($p)) $types .= 'd';
            else $types .= 's';
        }
        $st->bind_param($types, ...$params);
        $st->execute();

        // Actualizar perfiles específicos según rol
        if ($rol === 'paciente') {
            // tm_perfiles_paciente
            $stP = db()->prepare("INSERT INTO tm_perfiles_paciente (usuario_id, fecha_nacimiento, obra_social_id) VALUES (?,?,?) ON DUPLICATE KEY UPDATE fecha_nacimiento=VALUES(fecha_nacimiento), obra_social_id=VALUES(obra_social_id)");
            $stP->bind_param('isi', $id, $fecha_nacimiento, $obra_social_id);
            $stP->execute();
        } elseif ($rol === 'profesional') {
            // tm_perfiles_profesional
            $stP = db()->prepare("INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula) VALUES (?,?,?) ON DUPLICATE KEY UPDATE area=VALUES(area), matricula=VALUES(matricula)");
            $stP->bind_param('iss', $id, $area, $matricula);
            $stP->execute();
        }

        // Sincronizar tm_personas
        if (function_exists('persona_upsert')) {
            persona_upsert($dni, $nombre, $apellido, $telefono, $email, $obra_social_id);
        }

        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    } elseif ($accion === 'password') {
        $id = (int)($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        if ($id && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st = db()->prepare("UPDATE tm_usuarios SET password_hash=? WHERE id=?");
            $st->bind_param('si', $hash, $id);
            $st->execute();
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        }
    } elseif ($accion === 'aprobar') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("UPDATE tm_usuarios SET activo=1, fecha_aprobacion=NOW() WHERE id=?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    } elseif ($accion === 'desactivar') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("UPDATE tm_usuarios SET activo=0 WHERE id=?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$usuarios = [];
try {
    $usuarios = db()->query(
        "SELECT u.*, pp.fecha_nacimiento, pp.obra_social_id, pf.area, pf.matricula
         FROM tm_usuarios u
         LEFT JOIN tm_perfiles_paciente pp ON pp.usuario_id = u.id
         LEFT JOIN tm_perfiles_profesional pf ON pf.usuario_id = u.id
         WHERE u.rol != 'admin'
         ORDER BY u.activo ASC, u.apellido, u.nombre"
    )->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $usuarios = [];
}

$total = count($usuarios);
$aprobados = count(array_filter($usuarios, fn($u) => $u['activo'] == 1));
$pendientes = count(array_filter($usuarios, fn($u) => $u['activo'] == 0));

$esAdmin = (portal_rol() === 'admin');

$portal_titulo = 'Usuarios · Mi Portal';
$portal_activo = 'usuarios';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-sub">usuarios</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Aprobados</div>
        <div class="stat-value"><?= $aprobados ?></div>
        <div class="stat-sub">activos</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value"><?= $pendientes ?></div>
        <div class="stat-sub">por aprobar</div>
    </div>
</div>

<div style="padding:24px 28px 0; margin-bottom: 16px;">
    <div class="page-title">Usuarios</div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> usuario<?= $total !== 1 ? 's' : '' ?></div>
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Buscar…" style="width:220px;">
            </div>
            <button class="btn-action btn-save" style="padding:7px 14px;text-decoration:none;" onclick="openModal()">
                ➕ Nuevo usuario
            </button>
        </div>
    </div>
    <div class="table-wrap">
        <table id="mainTable">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Nombre<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Teléfono<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="4">Rol<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="5">Estado<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <?php
                $badge = $u['activo'] == 1 ? 'aprobado' : 'pendiente';
                $labelEstado = $u['activo'] == 1 ? 'Activo' : 'Inactivo';
                $rolBadge = $u['rol'] === 'profesional' ? 'aprobado' : ($u['rol'] === 'paciente' ? 'pendiente' : '');
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($u['dni']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['telefono'] ?: '-') ?></td>
                    <td><span class="badge <?= $rolBadge ?>"><?= ucfirst($u['rol']) ?></span></td>
                    <td><span class="badge <?= $badge ?>"><?= $labelEstado ?></span></td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <!-- Activar / Desactivar (botón único que cambia de color) -->
                            <form method="post" style="flex:1;" id="form-toggle-<?= $u['id'] ?>">
                                <input type="hidden" name="accion"
                                    value="<?= $u['activo'] == 1 ? 'desactivar' : 'aprobar' ?>">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="button"
                                    class="btn-action <?= $u['activo'] == 1 ? 'btn-del' : 'btn-save' ?>"
                                    data-tooltip="<?= $u['activo'] == 1 ? 'Desactivar' : 'Activar' ?>"
                                    style="width:100%;"
                                    onclick="<?= $esAdmin ? "mostrarConfirmacion('form-toggle-" . $u['id'] . "', '" . ($u['activo'] == 1 ? 'Desactivar' : 'Activar') . " este usuario')" : "mostrarSinPermiso(); false" ?>">
                                    <?php if ($u['activo'] == 1): ?>
                                    <svg fill="#f5f1f1" width="16px" height="16px" viewBox="-2 0 19 19" xmlns="http://www.w3.org/2000/svg" class="cf-icon-svg"><path d="M7.498 17.1a7.128 7.128 0 0 1-.98-.068 7.455 7.455 0 0 1-1.795-.483 7.26 7.26 0 0 1-3.028-2.332A7.188 7.188 0 0 1 .73 12.52a7.304 7.304 0 0 1 .972-7.128 7.221 7.221 0 0 1 1.387-1.385 1.03 1.03 0 0 1 1.247 1.638 5.176 5.176 0 0 0-.993.989 5.313 5.313 0 0 0-.678 1.181 5.23 5.23 0 0 0-.348 1.292 5.22 5.22 0 0 0 .326 2.653 5.139 5.139 0 0 0 .69 1.212 5.205 5.205 0 0 0 .992.996 5.257 5.257 0 0 0 1.178.677 5.37 5.37 0 0 0 1.297.35 5.075 5.075 0 0 0 1.332.008 5.406 5.406 0 0 0 1.32-.343 5.289 5.289 0 0 0 2.211-1.682 5.18 5.18 0 0 0 1.02-2.465 5.2 5.2 0 0 0 .01-1.336 5.315 5.315 0 0 0-.343-1.318 5.195 5.195 0 0 0-.695-1.222 5.134 5.134 0 0 0-.987-.989 1.03 1.03 0 1 1 1.24-1.643 7.186 7.186 0 0 1 1.384 1.386 7.259 7.259 0 0 1 .97 1.706 7.413 7.413 0 0 1 .473 1.827 7.296 7.296 0 0 1-4.522 7.65 7.476 7.476 0 0 1-1.825.471 7.203 7.203 0 0 1-.89.056zM7.5 9.613a1.03 1.03 0 0 1-1.03-1.029V2.522a1.03 1.03 0 0 1 2.06 0v6.062a1.03 1.03 0 0 1-1.03 1.03z"/></svg>
                                    <?php else: ?>
                                     <svg fill="#f5f1f1" width="16px" height="16px" viewBox="-2 0 19 19" xmlns="http://www.w3.org/2000/svg" class="cf-icon-svg"><path d="M7.498 17.1a7.128 7.128 0 0 1-.98-.068 7.455 7.455 0 0 1-1.795-.483 7.26 7.26 0 0 1-3.028-2.332A7.188 7.188 0 0 1 .73 12.52a7.304 7.304 0 0 1 .972-7.128 7.221 7.221 0 0 1 1.387-1.385 1.03 1.03 0 0 1 1.247 1.638 5.176 5.176 0 0 0-.993.989 5.313 5.313 0 0 0-.678 1.181 5.23 5.23 0 0 0-.348 1.292 5.22 5.22 0 0 0 .326 2.653 5.139 5.139 0 0 0 .69 1.212 5.205 5.205 0 0 0 .992.996 5.257 5.257 0 0 0 1.178.677 5.37 5.37 0 0 0 1.297.35 5.075 5.075 0 0 0 1.332.008 5.406 5.406 0 0 0 1.32-.343 5.289 5.289 0 0 0 2.211-1.682 5.18 5.18 0 0 0 1.02-2.465 5.2 5.2 0 0 0 .01-1.336 5.315 5.315 0 0 0-.343-1.318 5.195 5.195 0 0 0-.695-1.222 5.134 5.134 0 0 0-.987-.989 1.03 1.03 0 1 1 1.24-1.643 7.186 7.186 0 0 1 1.384 1.386 7.259 7.259 0 0 1 .97 1.706 7.413 7.413 0 0 1 .473 1.827 7.296 7.296 0 0 1-4.522 7.65 7.476 7.476 0 0 1-1.825.471 7.203 7.203 0 0 1-.89.056zM7.5 9.613a1.03 1.03 0 0 1-1.03-1.029V2.522a1.03 1.03 0 0 1 2.06 0v6.062a1.03 1.03 0 0 1-1.03 1.03z"/></svg>
                                    <?php endif; ?>
                                </button>
                            </form>

                            <!-- Editar -->
                            <button class="btn-action btn-mod" data-tooltip="Editar" style="padding:7px 1px;"
                                onclick='<?= $esAdmin ? "openEdit(" . json_encode($u, JSON_UNESCAPED_UNICODE) . ")" : 'mostrarSinPermiso()' ?>'
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                                </svg>
                            </button>

                            <!-- Resetear contraseña (admin siempre) -->
                            <button class="btn-action btn-print-turn" data-tooltip="Resetear contraseña" style="padding:7px 1px;"
                                onclick="openPass(<?= $u['id'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-key-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$usuarios): ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <div>No hay usuarios.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal crear -->
<div class="edit-modal-overlay" id="modalCrear">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">➕ Nuevo usuario</div>
            <button class="edit-modal-close" onclick="closeModal('modalCrear')">✕</button>
        </div>
        <form action="<?= b('/admin/usuarios.php') ?>" method="post">
            <input type="hidden" name="accion" value="crear">
            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Nombre *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="nombre" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Apellido *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="apellido" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">DNI</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="dni"></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Teléfono</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="telefono"></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Email *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="email" name="email" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Rol *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="rol" required onchange="toggleRolFieldsCrear()">
                            <option value="paciente">Paciente</option>
                            <option value="profesional">Profesional</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Contraseña *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password" required
                            minlength="6"></div>
                </div>

                <!-- Campos específicos Paciente -->
                <div class="edit-group-separator" id="sep-paciente-c" style="display:none;">
                    <div class="edit-label-section">Datos de Paciente</div>
                </div>
                <div class="edit-group" id="grp-fecha-nacimiento-c" style="display:none;">
                    <div class="edit-label">Fecha de nacimiento</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="date" name="fecha_nacimiento" id="f-fecha-nacimiento-c"></div>
                </div>
                <div class="edit-group full" id="grp-obra-social-c" style="display:none;">
                    <div class="edit-label">Obra Social</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="obra_social_id" id="f-obra-social-c">
                            <option value="">-- Seleccionar --</option>
                            <?php
                            $obras = obras_sociales_todas();
                            foreach ($obras as $o):
                            ?>
                                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Campos específicos Profesional -->
                <div class="edit-group-separator" id="sep-profesional-c" style="display:none;">
                    <div class="edit-label-section">Datos de Profesional</div>
                </div>
                <div class="edit-group" id="grp-area-c" style="display:none;">
                    <div class="edit-label">Área</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="area" id="f-area-c">
                            <option value="">-- Seleccionar --</option>
                            <option value="audiologia">Audiología</option>
                            <option value="hiperbarica">Medicina Hiperbárica</option>
                            <option value="nutricion">Nutrición</option>
                            <option value="ortopedia">Ortopedia y Rehabilitación</option>
                            <option value="equipamiento">Equipamiento Médico</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group" id="grp-matricula-c" style="display:none;">
                    <div class="edit-label">Matrícula</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="matricula" id="f-matricula-c"></div>
                </div>
                <div class="edit-group" id="grp-titulo-c" style="display:none;">
                    <div class="edit-label">Título</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="titulo" id="f-titulo-c"></div>
                </div>
                <div class="edit-group full" id="grp-especialidad-c" style="display:none;">
                    <div class="edit-label">Especialidad</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="especialidad" id="f-especialidad-c"></div>
                </div>
                <div class="edit-group full" id="grp-descripcion-c" style="display:none;">
                    <div class="edit-label">Descripción</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="descripcion" id="f-descripcion-c"></div>
                </div>
                <div class="edit-group" id="grp-instagram-c" style="display:none;">
                    <div class="edit-label">Instagram</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="instagram" id="f-instagram-c"></div>
                </div>
                <div class="edit-group" id="grp-foto-c" style="display:none;">
                    <div class="edit-label">Foto</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="foto" id="f-foto-c"></div>
                </div>
                <div class="edit-group" id="grp-orden-c" style="display:none;">
                    <div class="edit-label">Orden</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="number" name="orden" id="f-orden-c" min="0"></div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalCrear')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Crear</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal editar -->
<div class="edit-modal-overlay" id="modalEditar">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">✏️ Editar usuario</div>
            <button class="edit-modal-close" onclick="closeModal('modalEditar')">✕</button>
        </div>
        <form action="<?= b('/admin/usuarios.php') ?>" method="post">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="f-id">
            <div class="edit-grid">
                <!-- Campos universales -->
                <div class="edit-group">
                    <div class="edit-label">Nombre *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="nombre" id="f-nombre"
                            required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Apellido *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="apellido" id="f-apellido"
                            required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">DNI</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="dni" id="f-dni"></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Teléfono</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="telefono" id="f-telefono">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Email *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="email" name="email" id="f-email"
                            required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Rol *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="rol" id="f-rol" required onchange="toggleRolFields()">
                            <option value="paciente">Paciente</option>
                            <option value="profesional">Profesional</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Contraseña (dejar vacío para no cambiar)</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password"
                            id="f-password" placeholder="Dejar vacío para mantener la actual"></div>
                </div>

                <!-- Campos específicos Paciente -->
                <div class="edit-group-separator" id="sep-paciente" style="display:none;">
                    <div class="edit-label-section">Datos de Paciente</div>
                </div>
                <div class="edit-group" id="grp-fecha-nacimiento" style="display:none;">
                    <div class="edit-label">Fecha de nacimiento</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="date" name="fecha_nacimiento" id="f-fecha-nacimiento"></div>
                </div>
                <div class="edit-group full" id="grp-obra-social" style="display:none;">
                    <div class="edit-label">Obra Social</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="obra_social_id" id="f-obra-social">
                            <option value="">-- Seleccionar --</option>
                            <?php
                            $obras = obras_sociales_todas();
                            foreach ($obras as $o):
                            ?>
                                <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Campos específicos Profesional -->
                <div class="edit-group-separator" id="sep-profesional" style="display:none;">
                    <div class="edit-label-section">Datos de Profesional</div>
                </div>
                <div class="edit-group" id="grp-area" style="display:none;">
                    <div class="edit-label">Área</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="area" id="f-area">
                            <option value="">-- Seleccionar --</option>
                            <option value="audiologia">Audiología</option>
                            <option value="hiperbarica">Medicina Hiperbárica</option>
                            <option value="nutricion">Nutrición</option>
                            <option value="ortopedia">Ortopedia y Rehabilitación</option>
                            <option value="equipamiento">Equipamiento Médico</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group" id="grp-matricula" style="display:none;">
                    <div class="edit-label">Matrícula</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="matricula" id="f-matricula"></div>
                </div>
                <div class="edit-group" id="grp-titulo" style="display:none;">
                    <div class="edit-label">Título</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="titulo" id="f-titulo"></div>
                </div>
                <div class="edit-group full" id="grp-especialidad" style="display:none;">
                    <div class="edit-label">Especialidad</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="especialidad" id="f-especialidad"></div>
                </div>
                <div class="edit-group full" id="grp-descripcion" style="display:none;">
                    <div class="edit-label">Descripción</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="descripcion" id="f-descripcion"></div>
                </div>
                <div class="edit-group" id="grp-instagram" style="display:none;">
                    <div class="edit-label">Instagram</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="instagram" id="f-instagram"></div>
                </div>
                <div class="edit-group" id="grp-foto" style="display:none;">
                    <div class="edit-label">Foto</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="foto" id="f-foto"></div>
                </div>
                <div class="edit-group" id="grp-orden" style="display:none;">
                    <div class="edit-label">Orden</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="number" name="orden" id="f-orden" min="0"></div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal confirmación personalizado con colores de marca -->
<div class="confirm-overlay" id="confirmOverlay" style="display:none;">
    <div class="confirm-box">
        <div class="confirm-icon" id="confirmIcon"></div>
        <div class="confirm-title" id="confirmTitle">¿Estás seguro?</div>
        <div class="confirm-message" id="confirmMessage"></div>
        <div class="confirm-actions">
            <button class="btn btn-outline" id="confirmCancel">Cancelar</button>
            <button class="btn-confirm" id="confirmBtn">Confirmar</button>
        </div>
    </div>
</div>

<!-- Modal password -->
<div class="edit-modal-overlay" id="modalPass">
    <div class="edit-modal" style="max-width:420px;">
        <div class="edit-modal-header">
            <div class="edit-modal-title">🔑 Nueva contraseña</div>
            <button class="edit-modal-close" onclick="closeModal('modalPass')">✕</button>
        </div>
        <form action="<?= b('/admin/usuarios.php') ?>" method="post">
            <input type="hidden" name="accion" value="password">
            <input type="hidden" name="id" id="f-pass-id">
            <div class="edit-grid">
                <div class="edit-group full">
                    <div class="edit-label">Nueva contraseña *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password" required
                            minlength="6" placeholder="Mínimo 6 caracteres"></div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalPass')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-ok">✅ Guardado Exitosamente</div>
<div id="toast-error"
    style="display:none;position:fixed;bottom:24px;right:24px;background:#c94f4f;color:#fff;padding:14px 22px;border-radius:10px;font-weight:600;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.2);">
</div>

<script>
var formToSubmitConfirm = null;
var formIdActual = null;

function openModal() {
    document.getElementById('modalCrear').classList.add('open');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}
function openEdit(d) {
    document.getElementById('f-id').value = d.id;
    document.getElementById('f-nombre').value = d.nombre || '';
    document.getElementById('f-apellido').value = d.apellido || '';
    document.getElementById('f-dni').value = d.dni || '';
    document.getElementById('f-telefono').value = d.telefono || '';
    document.getElementById('f-email').value = d.email || '';
    var rolSelect = document.getElementById('f-rol');
    if (rolSelect) {
        var validRoles = ['paciente', 'profesional', 'admin'];
        if (validRoles.includes(d.rol)) {
            rolSelect.value = d.rol;
        } else {
            rolSelect.value = 'paciente';
        }
    }
    // Limpiar campos específicos
    document.getElementById('f-fecha-nacimiento').value = d.fecha_nacimiento || '';
    document.getElementById('f-obra-social').value = d.obra_social_id || '';
    document.getElementById('f-area').value = d.area || '';
    document.getElementById('f-matricula').value = d.matricula || '';
    document.getElementById('f-titulo').value = d.titulo || '';
    document.getElementById('f-especialidad').value = d.especialidad || '';
    document.getElementById('f-descripcion').value = d.descripcion || '';
    document.getElementById('f-instagram').value = d.instagram || '';
    document.getElementById('f-foto').value = d.foto || '';
    document.getElementById('f-orden').value = d.orden || 0;
    document.getElementById('f-password').value = '';
    // Mostrar/ocultar secciones según rol
    toggleRolFields();
    document.getElementById('modalEditar').classList.add('open');
}

function toggleRolFields() {
    var rol = document.getElementById('f-rol').value;
    toggleSecciones(rol, '');
}
function toggleRolFieldsCrear() {
    var rol = document.getElementById('f-rol').value;
    toggleSecciones(rol, '-c');
}
function toggleSecciones(rol, sufijo) {
    var pacienteSections = ['sep-paciente', 'grp-fecha-nacimiento', 'grp-obra-social'];
    var profesionalSections = ['sep-profesional', 'grp-area', 'grp-matricula', 'grp-titulo', 'grp-especialidad', 'grp-descripcion', 'grp-instagram', 'grp-foto', 'grp-orden'];
    pacienteSections.forEach(function(id) {
        var el = document.getElementById(id + sufijo);
        if (el) el.style.display = 'none';
    });
    profesionalSections.forEach(function(id) {
        var el = document.getElementById(id + sufijo);
        if (el) el.style.display = 'none';
    });
    if (rol === 'paciente') {
        pacienteSections.forEach(function(id) {
            var el = document.getElementById(id + sufijo);
            if (el) el.style.display = '';
        });
    } else if (rol === 'profesional') {
        profesionalSections.forEach(function(id) {
            var el = document.getElementById(id + sufijo);
            if (el) el.style.display = '';
        });
    }
}
function openPass(id) {
    document.getElementById('f-pass-id').value = id;
    document.getElementById('modalPass').classList.add('open');
}
function mostrarConfirmacion(formId, mensaje) {
    formIdActual = formId;
    var overlay = document.getElementById('confirmOverlay');
    var msgEl = document.getElementById('confirmMessage');
    var titleEl = document.getElementById('confirmTitle');
    var iconEl = document.getElementById('confirmIcon');
    var btnEl = document.getElementById('confirmBtn');

    if (!overlay || !msgEl || !titleEl || !iconEl || !btnEl) return;

    msgEl.textContent = mensaje;

    if (mensaje.indexOf('Desactivar') === 0) {
        titleEl.textContent = 'Desactivar usuario';
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/></svg>';
        iconEl.className = 'confirm-icon warn';
        btnEl.textContent = 'Desactivar';
        btnEl.className = 'btn-confirm danger';
    } else {
        titleEl.textContent = 'Activar usuario';
        iconEl.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/><path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/></svg>';
        iconEl.className = 'confirm-icon success';
        btnEl.textContent = 'Activar';
        btnEl.className = 'btn-confirm success';
    }
    overlay.style.display = 'flex';
}
function cerrarConfirmacion() {
    var overlay = document.getElementById('confirmOverlay');
    if (overlay) overlay.style.display = 'none';
    formIdActual = null;
}
function ejecutarConfirmacion() {
    if (formIdActual) {
        var form = document.getElementById(formIdActual);
        if (form) form.submit();
    }
}
function mostrarSinPermiso() {
    var t = document.getElementById('toast-error');
    if (t) { t.textContent = '⚠️ No tiene permiso para esta acción.'; t.style.display = 'block'; setTimeout(function() { t.style.display = 'none'; }, 4000); }
    return false;
}
function mostrarSinPermisoSinConfirm() {
    var t = document.getElementById('toast-error');
    if (t) { t.textContent = '⚠️ No tiene permiso para esta acción.'; t.style.display = 'block'; setTimeout(function() { t.style.display = 'none'; }, 4000); }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    // Toasts
    var params = new URLSearchParams(location.search);
    if (params.get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        if (t) { t.classList.add('show'); setTimeout(function() { t.classList.remove('show'); history.replaceState({}, '', location.pathname); }, 3000); }
    }
    if (params.get('error') === 'email_duplicado') {
        var t = document.getElementById('toast-error');
        if (t) { t.textContent = '⚠️ El email ya está en uso.'; t.style.display = 'block'; setTimeout(function() { t.style.display = 'none'; history.replaceState({}, '', location.pathname); }, 4000); }
    }

    // Buscador
    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#mainTable tbody tr').forEach(function(r) {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // Ordenamiento
    var sortState = { col: -1, dir: 'asc' };
    document.querySelectorAll('th.sortable').forEach(function(th) {
        th.addEventListener('click', function() {
            var col = parseInt(th.dataset.col);
            var dir = (sortState.col === col && sortState.dir === 'asc') ? 'desc' : 'asc';
            sortState = { col: col, dir: dir };
            document.querySelectorAll('th.sortable').forEach(function(h) { h.classList.remove('asc', 'desc'); });
            th.classList.add(dir);
            var tbody = document.querySelector('#mainTable tbody');
            var rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort(function(a, b) {
                var aVal = (a.querySelectorAll('td')[col] || { textContent: '' }).textContent.trim().toLowerCase();
                var bVal = (b.querySelectorAll('td')[col] || { textContent: '' }).textContent.trim().toLowerCase();
                return (aVal < bVal ? -1 : aVal > bVal ? 1 : 0) * (dir === 'asc' ? 1 : -1);
            });
            rows.forEach(function(r) { tbody.appendChild(r); });
        });
    });

    // Botones del modal de confirmación
    var confirmBtn = document.getElementById('confirmBtn');
    if (confirmBtn) confirmBtn.addEventListener('click', ejecutarConfirmacion);
    var confirmCancel = document.getElementById('confirmCancel');
    if (confirmCancel) confirmCancel.addEventListener('click', cerrarConfirmacion);
    var confirmOverlay = document.getElementById('confirmOverlay');
    if (confirmOverlay) {
        confirmOverlay.addEventListener('click', function(e) {
            if (e.target === this) cerrarConfirmacion();
        });
    }

    // Cerrar modales al hacer clic fuera
    ['modalCrear', 'modalEditar', 'modalPass'].forEach(function(id) {
        var m = document.getElementById(id);
        if (m) m.addEventListener('click', function(e) { if (e.target === this) closeModal(id); });
    });

    // Escape para cerrar
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('modalCrear');
            closeModal('modalEditar');
            closeModal('modalPass');
            cerrarConfirmacion();
        }
    });

    // Animación de filas
    document.querySelectorAll('#mainTable tbody tr').forEach(function(r, i) {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-10px)';
        r.style.transition = 'opacity .35s ease, transform .35s ease';
        setTimeout(function() { r.style.opacity = '1'; r.style.transform = 'translateX(0)'; }, 80 + i * 40);
    });
});
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>