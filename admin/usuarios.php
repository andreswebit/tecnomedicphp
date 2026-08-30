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
        if ($nombre && $apellido && $email && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st = db()->prepare("INSERT INTO tm_usuarios (nombre,apellido,email,password_hash,dni,telefono,rol,activo,fecha_aprobacion) VALUES (?,?,?,?,?,?,?,1,NOW())");
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
        // Validar email único (excluyendo al usuario actual)
        $stCheck = db()->prepare("SELECT id FROM tm_usuarios WHERE email=? AND id!=?");
        $stCheck->bind_param('si', $email, $id);
        $stCheck->execute();
        $resCheck = $stCheck->get_result();
        if ($resCheck->num_rows > 0) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?error=email_duplicado');
            exit;
        }
        $st = db()->prepare("UPDATE tm_usuarios SET nombre=?, apellido=?, email=?, dni=?, telefono=? WHERE id=?");
        $st->bind_param('sssssi', $nombre, $apellido, $email, $dni, $telefono, $id);
        $st->execute();
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
        "SELECT * FROM tm_usuarios WHERE rol != 'admin' ORDER BY activo ASC, apellido, nombre"
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

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Usuarios</div>
        </div>
        <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('crear')">
            ➕ Nuevo usuario
        </button>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> usuario<?= $total !== 1 ? 's' : '' ?></div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Buscar…" style="width:220px;">
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
                            <?php if ($u['activo'] == 0): ?>
                            <form method="post" style="flex:1;">
                                <input type="hidden" name="accion" value="aprobar">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-action btn-save" data-tooltip="Aprobar" style="width:100%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-circle" viewBox="0 0 16 16">
                                        <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/>
                                        <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/>
                                    </svg>
                                </button>
                            </form>
                            <?php endif; ?>

                            <!-- Editar -->
                            <button class="btn-action btn-mod" data-tooltip="Editar"
                                onclick="<?= $esAdmin ? "openEdit(" . json_encode($u, JSON_UNESCAPED_UNICODE) . ")" : 'mostrarSinPermiso()' ?>"
                                style="padding:7px 10px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-fill" viewBox="0 0 16 16">
                                    <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
                                </svg>
                            </button>

                            <!-- Resetear contraseña (admin siempre) -->
                            <button class="btn-action btn-print-turn" data-tooltip="Resetear contraseña" onclick='openPass(<?= $u['id'] ?>)' style="padding:7px 10px;display:flex;align-items:center;justify-content:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16">
                                    <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2M2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg>
                            </button>

                            <!-- Eliminar/Desactivar (solo admin) -->
                            <form method="post" style="flex:1;" onsubmit="return <?= $esAdmin ? "confirm('¿Cambiar el estado de este usuario?')" : "mostrarSinPermisoSinConfirm() && false" ?>">
                                <input type="hidden" name="accion" value="<?= $u['activo'] == 1 ? 'desactivar' : 'aprobar' ?>">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn-action btn-del" data-tooltip="<?= $u['activo'] == 1 ? 'Desactivar' : 'Activar' ?>" style="width:100%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                        <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                    </svg>
                                </button>
                            </form>
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
                        <select class="edit-input" name="rol" required>
                            <option value="paciente">Paciente</option>
                            <option value="profesional">Profesional</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Contraseña *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password" required minlength="6"></div>
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
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEditar')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Guardar cambios</button>
            </div>
        </form>
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
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres"></div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalPass')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-ok">✅ Acción realizada</div>
<div id="toast-error" style="display:none;position:fixed;bottom:24px;right:24px;background:#c94f4f;color:#fff;padding:14px 22px;border-radius:10px;font-weight:600;z-index:9999;box-shadow:0 4px 16px rgba(0,0,0,.2);"></div>

<script>
(function() {
    var params = new URLSearchParams(location.search);
    if (params.get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        t.classList.add('show');
        setTimeout(function() { t.classList.remove('show'); history.replaceState({}, '', location.pathname); }, 3000);
    }
    if (params.get('error') === 'email_duplicado') {
        var t = document.getElementById('toast-error');
        t.textContent = '⚠️ El email ya está en uso por otro usuario.';
        t.style.display = 'block';
        setTimeout(function() { t.style.display = 'none'; history.replaceState({}, '', location.pathname); }, 4000);
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#mainTable tbody tr').forEach(function(r) {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    var sortState = { col: -1, dir: 'asc' };
    document.querySelectorAll('th.sortable').forEach(function(th) {
        th.addEventListener('click', function() {
            var col = parseInt(th.dataset.col);
            var dir = (sortState.col === col && sortState.dir === 'asc') ? 'desc' : 'asc';
            sortState = { col: col, dir: dir };
            document.querySelectorAll('th.sortable').forEach(function(h) { h.classList.remove('asc','desc'); });
            th.classList.add(dir);
            sortTable(col, dir);
        });
    });

    function sortTable(col, dir) {
        var tbody = document.querySelector('#mainTable tbody');
        var rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort(function(a, b) {
            var aVal = (a.querySelectorAll('td')[col] || {textContent:''}).textContent.trim().toLowerCase();
            var bVal = (b.querySelectorAll('td')[col] || {textContent:''}).textContent.trim().toLowerCase();
            if (aVal < bVal) return dir === 'asc' ? -1 : 1;
            if (aVal > bVal) return dir === 'asc' ? 1 : -1;
            return 0;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
    }

    window.openModal = function() { document.getElementById('modalCrear').classList.add('open'); };
    window.closeModal = function(id) { document.getElementById(id).classList.remove('open'); };

    window.openEdit = function(d) {
        document.getElementById('f-id').value = d.id;
        document.getElementById('f-nombre').value = d.nombre || '';
        document.getElementById('f-apellido').value = d.apellido || '';
        document.getElementById('f-dni').value = d.dni || '';
        document.getElementById('f-telefono').value = d.telefono || '';
        document.getElementById('f-email').value = d.email || '';
        document.getElementById('modalEditar').classList.add('open');
    };

    window.openPass = function(id) {
        document.getElementById('f-pass-id').value = id;
        document.getElementById('modalPass').classList.add('open');
    };

    window.mostrarSinPermiso = function() {
        var t = document.getElementById('toast-error');
        t.textContent = '⚠️ No tiene permiso para esta acción.';
        t.style.display = 'block';
        setTimeout(function() { t.style.display = 'none'; }, 4000);
        return false;
    };
    window.mostrarSinPermisoSinConfirm = function() {
        var t = document.getElementById('toast-error');
        t.textContent = '⚠️ No tiene permiso para esta acción.';
        t.style.display = 'block';
        setTimeout(function() { t.style.display = 'none'; }, 4000);
        return true;
    };

    document.getElementById('modalCrear').addEventListener('click', function(e) { if (e.target === this) closeModal('modalCrear'); });
    document.getElementById('modalEditar').addEventListener('click', function(e) { if (e.target === this) closeModal('modalEditar'); });
    document.getElementById('modalPass').addEventListener('click', function(e) { if (e.target === this) closeModal('modalPass'); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeModal('modalCrear'); closeModal('modalEditar'); closeModal('modalPass'); } });

    document.querySelectorAll('#mainTable tbody tr').forEach(function(r, i) {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-10px)';
        r.style.transition = 'opacity .35s ease, transform .35s ease';
        setTimeout(function() { r.style.opacity = '1'; r.style.transform = 'translateX(0); }, 80 + i * 40);
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
