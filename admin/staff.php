<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_staff.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $d = [
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'apellido'     => trim($_POST['apellido'] ?? ''),
            'titulo'       => trim($_POST['titulo'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'instagram'    => trim($_POST['instagram'] ?? ''),
            'foto'         => '',
            'orden'        => (int)($_POST['orden'] ?? 0),
            'activo'       => isset($_POST['activo']) ? 1 : 0,
        ];
        if (!empty($_FILES['foto']['tmp_name'])) {
            $d['foto'] = staff_subir_foto($_FILES['foto']);
        }
        if ($d['nombre'] && $d['apellido']) {
            staff_crear($d);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        }
    } elseif ($accion === 'editar') {
        $id     = (int)($_POST['id'] ?? 0);
        $actual = staff_get($id);
        $d = [
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'apellido'     => trim($_POST['apellido'] ?? ''),
            'titulo'       => trim($_POST['titulo'] ?? ''),
            'especialidad' => trim($_POST['especialidad'] ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'instagram'    => trim($_POST['instagram'] ?? ''),
            'foto'         => $actual['foto'] ?? '',
            'orden'        => (int)($_POST['orden'] ?? 0),
            'activo'       => isset($_POST['activo']) ? 1 : 0,
        ];
        if (!empty($_FILES['foto']['tmp_name'])) {
            $d['foto'] = staff_subir_foto($_FILES['foto']);
        }
        staff_editar($id, $d);
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } elseif ($accion === 'eliminar') {
        $id     = (int)($_POST['id'] ?? 0);
        $actual = staff_get($id);
        if ($actual && $actual['foto']) {
            $ruta = __DIR__ . '/../' . $actual['foto'];
            if (is_file($ruta)) unlink($ruta);
        }
        staff_eliminar($id);
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$todos = staff_listar();
$total = count($todos);
$activos = count(array_filter($todos, fn($s) => $s['activo']));

$portal_titulo = 'Staff Médico · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-sub">miembros</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Activos</div>
        <div class="stat-value"><?= $activos ?></div>
        <div class="stat-sub">visibles en sitio</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Inactivos</div>
        <div class="stat-value"><?= $total - $activos ?></div>
        <div class="stat-sub">ocultos</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Staff Médico</div>
            <div class="chip">🎬 Multimedia</div>
        </div>
        <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('crear')">
            ➕ Agregar miembro
        </button>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> miembro<?= $total !== 1 ? 's' : '' ?></div>
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
                    <th class="sortable" data-col="1">Título<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Especialidad<span class="sort-icon"></span></th>
                    <th>Foto</th>
                    <th>Instagram</th>
                    <th class="sortable" data-col="5">Orden<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="6">Activo<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars(($s['titulo'] ?? '') . ' ' . $s['apellido'] . ', ' . $s['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($s['titulo'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($s['especialidad'] ?: '-') ?></td>
                    <td>
                        <?php if ($s['foto']): ?>
                            <img src="<?= b('/' . $s['foto']) ?>" style="max-height:40px;border-radius:50%;object-fit:cover;" alt="">
                        <?php else: ?>
                            <span style="color:#94a3b8;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($s['instagram']): ?>
                            <a href="https://instagram.com/<?= htmlspecialchars(ltrim($s['instagram'], '@')) ?>" target="_blank" style="color:#e1306c;">
                                <i class="fa-brands fa-instagram"></i> @<?= htmlspecialchars(ltrim($s['instagram'], '@')) ?>
                            </a>
                        <?php else: ?>
                            <span style="color:#94a3b8;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int)$s['orden'] ?></td>
                    <td>
                        <span class="badge <?= $s['activo'] ? 'aprobado' : 'pendiente' ?>">
                            <?= $s['activo'] ? 'Sí' : 'No' ?>
                        </span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-mod" data-tooltip="Editar"
                                onclick='openModal("editar", <?= json_encode($s, JSON_UNESCAPED_UNICODE) ?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg>
                            </button>
                            <form method="post" style="flex:1;" onsubmit="return confirm('¿Eliminar este miembro del staff?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn-action btn-del" data-tooltip="Eliminar" style="width:100%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$todos): ?>
        <div class="empty-state">
            <div class="empty-state-icon">👨‍⚕️</div>
            <div>No hay miembros del staff cargados.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal crear / editar -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="modalTitle">➕ Agregar miembro</div>
            <button class="edit-modal-close" onclick="closeModal()">✕</button>
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
                        <input class="edit-input" type="text" name="titulo" id="f-titulo" placeholder="Ej: Dra., Lic., Dr.">
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Especialidad</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="especialidad" id="f-especialidad" placeholder="Ej: Audiometría, Nutrición…">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Descripción</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="descripcion" id="f-descripcion" rows="3" placeholder="Breve descripción del profesional…"></textarea>
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
                    <small style="color:#94a3b8;font-size:11px;">JPG, PNG — máx. 3MB. Dejá vacío para no cambiar.</small>
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

<script>
(function() {
    if (new URLSearchParams(location.search).get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        t.classList.add('show');
        setTimeout(function() { t.classList.remove('show'); history.replaceState({}, '', location.pathname); }, 3000);
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
            var aVal = getCellText(a, col);
            var bVal = getCellText(b, col);
            if (aVal < bVal) return dir === 'asc' ? -1 : 1;
            if (aVal > bVal) return dir === 'asc' ? 1 : -1;
            return 0;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
    }

    function getCellText(row, col) {
        var cells = row.querySelectorAll('td');
        if (!cells[col]) return '';
        return cells[col].textContent.trim().toLowerCase();
    }

    window.openModal = function(mode, data) {
        document.getElementById('modalAccion').value = mode === 'editar' ? 'editar' : 'crear';
        document.getElementById('modalId').value = data && data.id ? data.id : '';
        document.getElementById('modalTitle').textContent = mode === 'editar' ? '✏️ Editar miembro' : '➕ Agregar miembro';
        document.getElementById('modalSubmitBtn').textContent = mode === 'editar' ? '💾 Guardar cambios' : '💾 Crear';

        ['nombre','apellido','titulo','especialidad','descripcion','instagram','orden'].forEach(function(f) {
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

    window.closeModal = function() {
        document.getElementById('editModal').classList.remove('open');
    };

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    document.querySelectorAll('#mainTable tbody tr').forEach(function(r, i) {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-10px)';
        r.style.transition = 'opacity .35s ease, transform .35s ease';
        setTimeout(function() { r.style.opacity = '1'; r.style.transform = 'translateX(0)'; }, 80 + i * 40);
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
