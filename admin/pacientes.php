<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'aprobar') {
        usuario_aprobar((int)($_POST['id'] ?? 0));
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    } elseif ($accion === 'rechazar') {
        usuario_rechazar((int)($_POST['id'] ?? 0));
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    } elseif ($accion === 'crear') {
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $dni      = trim($_POST['dni'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
        $obra_social_id   = !empty($_POST['obra_social_id']) ? (int)$_POST['obra_social_id'] : null;

        // Validar campos obligatorios
        if (!$nombre || !$apellido || !$email || !$password) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=faltan_campos');
            exit;
        }

        // Validar email único
        $stCheck = db()->prepare("SELECT id FROM tm_usuarios WHERE email=? OR dni=?");
        $stCheck->bind_param('ss', $email, $dni);
        $stCheck->execute();
        if ($stCheck->get_result()->num_rows > 0) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=email_duplicado');
            exit;
        }

        // Crear el paciente
        try {
            $id = usuario_crear_paciente([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'dni' => $dni,
                'telefono' => $telefono,
                'password' => $password,
                'obra_social_id' => $obra_social_id,
            ]);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        } catch (Exception $e) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=' . urlencode($e->getMessage()));
        }
        exit;
    } elseif ($accion === 'editar') {
        $id       = (int)($_POST['id'] ?? 0);
        $nombre   = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $dni      = trim($_POST['dni'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
        $obra_social_id   = !empty($_POST['obra_social_id']) ? (int)$_POST['obra_social_id'] : null;

        // Validar email único (excluyendo al usuario actual)
        $stCheck = db()->prepare("SELECT id FROM tm_usuarios WHERE email=? AND id!=?");
        $stCheck->bind_param('si', $email, $id);
        $stCheck->execute();
        if ($stCheck->get_result()->num_rows > 0) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=email_duplicado');
            exit;
        }

        // Actualizar datos básicos del usuario
        usuario_editar($id, [
            'nombre'   => $nombre,
            'apellido' => $apellido,
            'email'    => $email,
            'dni'      => $dni,
            'telefono' => $telefono,
        ]);

        // Actualizar perfil del paciente
        $stP = db()->prepare(
            "INSERT INTO tm_perfiles_paciente (usuario_id, fecha_nacimiento, obra_social_id)
             VALUES (?,?,?) ON DUPLICATE KEY UPDATE
             fecha_nacimiento=VALUES(fecha_nacimiento), obra_social_id=VALUES(obra_social_id)"
        );
        $stP->bind_param('isi', $id, $fecha_nacimiento, $obra_social_id);
        $stP->execute();

        // Sincronizar tm_personas
        if (function_exists('persona_upsert')) {
            persona_upsert($dni, $nombre, $apellido, $telefono, $email, $obra_social_id);
        }

        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    } elseif ($accion === 'eliminar') {
        usuario_eliminar((int)($_POST['id'] ?? 0));
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$pendientes = usuarios_pendientes();
$pacientes  = pacientes_todos_activos();
$obras = obras_sociales_todas();

$portal_titulo = 'Pacientes · Mi Portal';
$portal_activo = 'pacientes';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value" style="text-align: end;"><?= count($pacientes) ?></div>
        <div class="stat-sub">pacientes activos</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value" style="text-align: end;"><?= count($pendientes) ?></div>
        <div class="stat-sub">por aprobar</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Pacientes</div>
        </div>
    </div>
</div>

<?php if (count($pendientes)): ?>
<div class="table-card" style="margin:0 28px 20px;">
    <div class="table-header">
        <div class="table-title">⏳ Pendientes de aprobación (<?= count($pendientes) ?>)</div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchPendientes" placeholder="Buscar…" style="width:200px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaPendientes">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Nombre<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Fecha<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendientes as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($p['dni']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['fecha_alta']) ?></td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <form method="post" style="flex:1;">
                                <input type="hidden" name="accion" value="aprobar">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-action btn-save" data-tooltip="Aprobar"
                                    style="width:100%;">✅ Aprobar</button>
                            </form>
                            <form method="post" style="flex:1;"
                                onsubmit="return confirm('¿Rechazar y eliminar esta solicitud?');">
                                <input type="hidden" name="accion" value="rechazar">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-action btn-del" data-tooltip="Rechazar"
                                    style="width:100%;">❌ Rechazar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">Pacientes activos (<?= count($pacientes) ?>)</div>
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchActivos" placeholder="Buscar…" style="width:220px ;">
            </div>
            <button class="btn-action btn-save btn-no-tooltip" style="padding:7px 14px;text-decoration:none;" onclick="openModal()">
                Nuevo paciente
            </button>
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaActivos">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Nombre<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th>Teléfono</th>
                    <th>Obra social</th>
                    <th>Fecha nac.</th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($p['dni']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($p['obra_social_nombre'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($p['fecha_nacimiento'] ?: '-') ?></td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <!-- Ficha médica -->
                            <button class="btn-action btn-save" data-tooltip="Ver ficha médica"
                                onclick="abrirFicha(<?= $p['id'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path id="Path_184" data-name="Path 184"
                                        d="M57.5,41a.5.5,0,0,0-.5.5V43H47V31h2v.5a.5.5,0,0,0,.5.5h5a.5.5,0,0,0,.5-.5V31h2v.5a.5.5,0,0,0,1,0v-1a.5.5,0,0,0-.5-.5H55v-.5A1.5,1.5,0,0,0,53.5,28h-3A1.5,1.5,0,0,0,49,29.5V30H46.5a.5.5,0,0,0-.5.5v13a.5.5,0,0,0,.5.5h11a.5.5,0,0,0,.5-.5v-2A.5.5,0,0,0,57.5,41ZM50,29.5a.5.5,0,0,1,.5-.5h3a.5.5,0,0,1,.5.5V31H50Zm11.854,4.646-2-2a.5.5,0,0,0-.708,0l-6,6A.5.5,0,0,0,53,38.5v2a.5.5,0,0,0,.5.5h2a.5.5,0,0,0,.354-.146l6-6A.5.5,0,0,0,61.854,34.146ZM54,40V38.707l5.5-5.5L60.793,34.5l-5.5,5.5Zm-2,.5a.5.5,0,0,1-.5.5h-2a.5.5,0,0,1,0-1h2A.5.5,0,0,1,52,40.5Zm0-3a.5.5,0,0,1-.5.5h-2a.5.5,0,0,1,0-1h2A.5.5,0,0,1,52,37.5ZM54.5,35h-5a.5.5,0,0,1,0-1h5a.5.5,0,0,1,0,1Z"
                                        transform="translate(-46 -28)" />
                                </svg>
                            </button>

                            <!-- Nutrición -->
                            <a href="<?= b('/portal/nutricion/registro.php?paciente_id=' . $p['id']) ?>"
                                class="btn-action btn-print-turn" data-tooltip="Registro nutricional"
                                style=" justify-content:center; content-justify:center; text-decoration:none;">
                                <svg height="16" width="16" version="1.1" id="_x32_" fill="currentColor"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                    viewBox="0 0 512 512" xml:space="preserve">
                                    <g>
                                        <path class="st0" d="M199.259,2.124c-10.736,0-17.97,8.514-17.97,19.002v90.51c0,10.292-5.288,15.216-13.403,15.216
                                            c-7.412,0-11.785-6.115-11.785-13.376l-0.408-94.021C155.693,7.59,145.348,0,135.731,0c-9.607,0-19.961,7.59-19.961,19.455v94.021
                                            c0,7.261-4.782,13.376-12.194,13.376c-8.105,0-13.402-4.924-13.402-15.216v-90.51c0-10.488-7.226-19.002-17.953-19.002
                                            c-10.744,0.018-16.46,9.261-17.633,18.326c-1.155,9.074-7.119,73.873-7.768,115.288c-0.666,41.425,28.414,65.652,44.98,73.349
                                            c11.332,5.261,19.526,16.575,19.526,30.182v34.288h48.81v-34.288c0-13.607,8.194-24.921,19.526-30.182
                                            c16.575-7.696,45.655-31.924,44.997-73.349c-0.657-41.415-6.612-106.214-7.785-115.288C215.701,11.385,209.995,2.142,199.259,2.124
                                            z" />
                                        <path class="st0" d="M332.341,209.087c13.384,11.838,19.526,16.575,19.526,30.182v34.288h48.818v-34.288
                                            c0-13.607,6.132-18.344,19.518-30.182c13.687-12.096,44.988-45.726,44.988-87.151c0-72.317-45.771-118.354-88.919-118.354
                                            c-43.149,0-88.92,46.037-88.92,118.354C287.352,163.361,318.653,196.991,332.341,209.087z" />
                                        <path class="st0" d="M177.831,287.44H93.649c-10.682,0-19.339,8.657-19.339,19.339s8.657,19.339,19.339,19.339h84.182
                                            c10.683,0,19.339-8.657,19.339-19.339S188.514,287.44,177.831,287.44z" />
                                        <path class="st0" d="M94.786,471.046c0,22.618,18.336,40.954,40.954,40.954c22.619,0,40.954-18.335,40.954-40.954V339.769H94.786
                                            V471.046z" />
                                        <path class="st0" d="M418.363,287.44H334.18c-10.683,0-19.339,8.657-19.339,19.339s8.656,19.339,19.339,19.339h84.182
                                            c10.682,0,19.339-8.657,19.339-19.339S429.045,287.44,418.363,287.44z" />
                                        <path class="st0" d="M335.318,471.046c0,22.618,18.335,40.954,40.954,40.954c22.619,0,40.954-18.335,40.954-40.954V339.769h-81.907
                                            V471.046z" />
                                    </g>
                                </svg>
                            </a>

                            <!-- Editar -->
                            <button class="btn-action btn-mod" data-tooltip="Editar" style="padding:7px 1px;"
                                onclick='openEdit(<?= htmlspecialchars(json_encode($p, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z" />
                                </svg>
                            </button>

                            <!-- Eliminar -->
                            <form method="post" style="flex:1;"
                                onsubmit="return confirm('¿Eliminar este paciente?\nEsta acción no se puede deshacer.');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-action btn-del" data-tooltip="Eliminar"
                                    style="width:100%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-trash" viewBox="0 0 16 16">
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
        <?php if (!$pacientes): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🧑‍⚕️</div>
            <div>No hay pacientes activos.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nuevo Paciente -->
<div class="edit-modal-overlay" id="modalCrear">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">➕ Nuevo paciente</div>
            <button class="edit-modal-close"
                style="background:none;border:none;font-size:24px;cursor:pointer;color:#888;padding:4px;"
                onclick="closeModal('modalCrear')">
                <svg height="26" width="26" version="1.1" id="Layer_1"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512">
                    <circle style="fill:#FF6643;" cx="256" cy="256" r="256" />
                    <path style="fill:#FF6643;"
                        d="M256,0v512c141.385,0,256-114.615,256-256S397.385,0,256,0z" />
                    <polygon style="fill:#ffffff;"
                        points="365.904,184.885 327.115,146.096 256,217.211 184.885,146.096 146.096,184.885 217.211,256
                            146.096,327.115 184.885,365.904 256,294.789 327.115,365.904 365.904,327.115 294.789,256 " />
                </svg>
            </button>
        </div>
        <form action="<?= b('/admin/pacientes.php') ?>" method="post">
            <input type="hidden" name="accion" value="crear">
            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Nombre *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="nombre" id="f-nombre-nuevo" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Apellido *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="apellido" id="f-apellido-nuevo" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">DNI</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="dni" id="f-dni-nuevo"></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Teléfono</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="telefono" id="f-telefono-nuevo"></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Email *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="email" name="email" id="f-email-nuevo" required></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Contraseña * <span style="font-size:0.8em;color:#aaa;">(mín. 6 caracteres)</span></div>
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password" id="f-password-nuevo" minlength="6" required></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Rol</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="rol" id="f-rol-nuevo" disabled>
                            <option value="paciente" selected>Paciente</option>
                        </select>
                        <input type="hidden" name="rol" value="paciente">
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Fecha de nacimiento</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="date" name="fecha_nacimiento" id="f-fecha-nacimiento-nuevo"></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Obra Social</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="obra_social_id" id="f-obra-social-nuevo">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($obras as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalCrear')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Crear paciente</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Paciente -->
<div class="edit-modal-overlay" id="modalEditar">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">Editar paciente</div>
            <button class="edit-modal-close"
                style="background:none;border:none;font-size:24px;cursor:pointer;color:#888;padding:4px;"
                onclick="closeModal('modalEditar')">
                <svg height="26" width="26" version="1.1" id="Layer_1"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512">
                    <circle style="fill:#FF6643;" cx="256" cy="256" r="256" />
                    <path style="fill:#FF6643;"
                        d="M256,0v512c141.385,0,256-114.615,256-256S397.385,0,256,0z" />
                    <polygon style="fill:#ffffff;"
                        points="365.904,184.885 327.115,146.096 256,217.211 184.885,146.096 146.096,184.885 217.211,256
                            146.096,327.115 184.885,365.904 256,294.789 327.115,365.904 365.904,327.115 294.789,256 " />
                </svg>
            </button>
        </div>
        <form action="<?= b('/admin/pacientes.php') ?>" method="post">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" id="f-id">
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
                <div class="edit-group">
                    <div class="edit-label">Fecha de nacimiento</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="date" name="fecha_nacimiento" id="f-fecha-nacimiento"></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Obra Social</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="obra_social_id" id="f-obra-social">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($obras as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-ok">✅ Guardado con Exito</div>
<div id="toast-error" style="display:none;position:fixed;top:20px;left:50%;transform:translateX(-50%);background:#f44336;color:#fff;padding:12px 24px;border-radius:8px;z-index:2000;"></div>

<script>
(function() {
    if (new URLSearchParams(location.search).get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        t.classList.add('show');
        setTimeout(function() {
            t.classList.remove('show');
            history.replaceState({}, '', location.pathname);
        }, 3000);
    }

    var err = new URLSearchParams(location.search).get('error');
    if (err) {
        var t = document.getElementById('toast-error');
        if (t) {
            t.textContent = '⚠️ Error: ' + decodeURIComponent(err.replace(/_/g, ' '));
            t.style.display = 'block';
            setTimeout(function() {
                t.style.display = 'none';
                history.replaceState({}, '', location.pathname);
            }, 5000);
        }
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

    setupTable('tablaPendientes', 'searchPendientes');
    setupTable('tablaActivos', 'searchActivos');
})();

function openModal() {
    document.getElementById('modalCrear').classList.add('open');
}

function closeModal(id) {
    var modal = document.getElementById(id);
    if (modal) modal.classList.remove('open');
}

function openEdit(d) {
    var modal = document.getElementById('modalEditar');
    if (!modal) { alert('Modal no encontrado'); return; }
    document.getElementById('f-id').value = d.id;
    document.getElementById('f-nombre').value = d.nombre || '';
    document.getElementById('f-apellido').value = d.apellido || '';
    document.getElementById('f-dni').value = d.dni || '';
    document.getElementById('f-telefono').value = d.telefono || '';
    document.getElementById('f-email').value = d.email || '';
    document.getElementById('f-fecha-nacimiento').value = d.fecha_nacimiento || '';

    var obraSocialId = d.obra_social_id || '';
    var obraSocialSelect = document.getElementById('f-obra-social');
    if (obraSocialSelect) {
        obraSocialSelect.value = obraSocialId;
        Array.prototype.forEach.call(obraSocialSelect.options, function(option) {
            option.selected = option.value === obraSocialId;
        });
    }
    modal.classList.add('open');
}
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>