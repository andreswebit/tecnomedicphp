<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_testimonios.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear' || $accion === 'editar') {
        $d = [
            'nombre'      => trim($_POST['nombre'] ?? ''),
            'rol'         => trim($_POST['rol'] ?? ''),
            'texto'       => trim($_POST['texto'] ?? ''),
            'resultado'   => trim($_POST['resultado'] ?? ''),
            'media_tipo'  => $_POST['media_tipo'] ?? 'ninguno',
            'media_url'   => trim($_POST['media_url'] ?? ''),
            'video_thumb' => trim($_POST['video_thumb'] ?? ''),
            'orden'       => (int)($_POST['orden'] ?? 0),
            'activo'      => isset($_POST['activo']) ? 1 : 0,
        ];
        if ($accion === 'crear') {
            if ($d['nombre'] && $d['texto']) {
                testimonio_crear($d);
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
                exit;
            }
        } else {
            $id = (int)($_POST['id'] ?? 0);
            testimonio_editar($id, $d);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
    } elseif ($accion === 'eliminar') {
        testimonio_eliminar((int)($_POST['id'] ?? 0));
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$todos = testimonios_listar();
$total = count($todos);
$activos = count(array_filter($todos, fn($t) => $t['activo']));

$portal_titulo = 'Testimonios · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-sub">testimonios</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Activos</div>
        <div class="stat-value"><?= $activos ?></div>
        <div class="stat-sub">visibles</div>
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
            <div class="page-title">Testimonios</div>
            <div class="chip">🎬 Multimedia</div>
        </div>
        <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('crear')">
            ➕ Nuevo testimonio
        </button>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> testimonio<?= $total !== 1 ? 's' : '' ?></div>
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
                    <th class="sortable" data-col="1">Rol<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Texto<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Tipo medio<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="4">Orden<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="5">Activo<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($t['rol'] ?: '-') ?></td>
                    <td style="max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($t['texto']) ?>">
                        <?= htmlspecialchars(mb_substr($t['texto'], 0, 60)) ?>…
                    </td>
                    <td>
                        <span class="badge <?= $t['media_tipo'] === 'video' ? 'aprobado' : ($t['media_tipo'] === 'imagen' ? 'pendiente' : '') ?>">
                            <?= htmlspecialchars(ucfirst($t['media_tipo'])) ?>
                        </span>
                    </td>
                    <td><?= (int)$t['orden'] ?></td>
                    <td>
                        <span class="badge <?= $t['activo'] ? 'aprobado' : 'pendiente' ?>">
                            <?= $t['activo'] ? 'Sí' : 'No' ?>
                        </span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-mod" data-tooltip="Editar"
                                onclick='openModal("editar", <?= json_encode($t, JSON_UNESCAPED_UNICODE) ?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg>
                            </button>
                            <form method="post" style="flex:1;" onsubmit="return tmConfirmSubmit(this,'¿Eliminar este testimonio?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
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
            <div class="empty-state-icon">⭐</div>
            <div>No hay testimonios cargados.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="modalTitle">➕ Nuevo testimonio</div>
            <button class="edit-modal-close" onclick="closeModal()">✕</button>
        </div>
        <form action="<?= b('/admin/testimonios.php') ?>" method="post" id="modalForm">
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
                    <div class="edit-label">Rol</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="rol" id="f-rol" placeholder="Ej: Paciente de heridas complejas">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Texto *</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="texto" id="f-texto" rows="3" required></textarea>
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Resultado</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="resultado" id="f-resultado" placeholder="Ej: Con el tratamiento, sentí cambios en la segunda sesión.">
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Tipo de medio</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="media_tipo" id="f-media_tipo">
                            <option value="ninguno">Sin medio</option>
                            <option value="imagen">Imagen</option>
                            <option value="video">Video</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Orden</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="number" name="orden" id="f-orden" value="0" min="0">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">URL de imagen o video</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="media_url" id="f-media_url" placeholder="https://… o storage/testimonios/imagen.jpg">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Thumbnail del video (solo videos)</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="video_thumb" id="f-video_thumb" placeholder="storage/testimonios/thumb.jpg">
                    </div>
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
        document.getElementById('modalTitle').textContent = mode === 'editar' ? '✏️ Editar testimonio' : '➕ Nuevo testimonio';
        document.getElementById('modalSubmitBtn').textContent = mode === 'editar' ? '💾 Guardar cambios' : '💾 Crear';

        ['nombre','rol','texto','resultado','media_url','video_thumb'].forEach(function(f) {
            document.getElementById('f-' + f).value = data && data[f] ? data[f] : '';
        });
        document.getElementById('f-media_tipo').value = data && data.media_tipo ? data.media_tipo : 'ninguno';
        document.getElementById('f-orden').value = data && data.orden ? data.orden : 0;
        document.getElementById('f-activo').checked = !data || data.activo == '1' || data.activo === 1;

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
