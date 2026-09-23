<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_novedades.php';
portal_require_role(['admin']);

$mensaje = '';
$error   = '';

// ── Acciones POST ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $d = [
            'titulo'    => trim($_POST['titulo'] ?? ''),
            'contenido' => trim($_POST['contenido'] ?? ''),
            'imagen'    => '',
            'video_url' => trim($_POST['video_url'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'tipo'      => $_POST['tipo'] ?? 'novedad',
            'orden'     => (int)($_POST['orden'] ?? 0),
            'activo'    => isset($_POST['activo']) ? 1 : 0,
        ];
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $d['imagen'] = novedad_subir_imagen($_FILES['imagen']);
        }
        if ($d['titulo'] && $d['contenido']) {
            novedad_crear($d);
            header('Location: ' . $_SERVER['REQUEST_URI'] . '?ok=1');
            exit;
        } else {
            $error = 'Título y contenido son obligatorios.';
        }
    } elseif ($accion === 'editar') {
        $id     = (int)($_POST['id'] ?? 0);
        $actual = novedad_get($id);
        $d = [
            'titulo'    => trim($_POST['titulo'] ?? ''),
            'contenido' => trim($_POST['contenido'] ?? ''),
            'imagen'    => $actual['imagen'] ?? '',
            'video_url' => trim($_POST['video_url'] ?? ''),
            'categoria' => trim($_POST['categoria'] ?? ''),
            'tipo'      => $_POST['tipo'] ?? 'novedad',
            'orden'     => (int)($_POST['orden'] ?? 0),
            'activo'    => isset($_POST['activo']) ? 1 : 0,
        ];
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $d['imagen'] = novedad_subir_imagen($_FILES['imagen']);
        }
        novedad_editar($id, $d);
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } elseif ($accion === 'eliminar') {
        $id     = (int)($_POST['id'] ?? 0);
        $actual = novedad_get($id);
        novedad_eliminar($id);
        if ($actual && $actual['imagen']) {
            $ruta = __DIR__ . '/../' . $actual['imagen'];
            if (is_file($ruta)) unlink($ruta);
        }
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$todas     = novedades_listar();
$total     = count($todas);
$activas   = count(array_filter($todas, fn($n) => $n['activo']));
$inactivas = $total - $activas;

$portal_titulo = 'Novedades · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<!-- ── STATS ── -->
<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-sub">novedades</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Activas</div>
        <div class="stat-value"><?= $activas ?></div>
        <div class="stat-sub">visibles en sitio</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Inactivas</div>
        <div class="stat-value"><?= $inactivas ?></div>
        <div class="stat-sub">ocultas</div>
    </div>
</div>

<!-- ── TOPBAR ── -->
<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Novedades</div>
            <div class="chip">🎬 Multimedia</div>
        </div>
        <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('crear')">
            ➕ Nueva novedad
        </button>
    </div>
</div>

<!-- ── TABLA ── -->
<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> novedad<?= $total !== 1 ? 'es' : '' ?></div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Buscar…" style="width:220px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="mainTable">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Título<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">Tipo<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Categoría<span class="sort-icon"></span></th>
                    <th>Imagen</th>
                    <th class="sortable" data-col="4">Orden<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="5">Activo<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todas as $n): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($n['titulo']) ?></strong></td>
                    <td>
                        <span class="badge <?= $n['tipo'] === 'noticia' ? 'aprobado' : 'pendiente' ?>">
                            <?= htmlspecialchars(ucfirst($n['tipo'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($n['categoria'] ?: '-') ?></td>
                    <td>
                        <?php if ($n['imagen']): ?>
                            <img src="<?= b('/' . $n['imagen']) ?>" style="max-height:40px;border-radius:4px;" alt="">
                        <?php else: ?>
                            <span style="color:#94a3b8;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int)$n['orden'] ?></td>
                    <td>
                        <span class="badge <?= $n['activo'] ? 'aprobado' : 'pendiente' ?>">
                            <?= $n['activo'] ? 'Sí' : 'No' ?>
                        </span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-mod" data-tooltip="Editar"
                                onclick='openModal("editar", <?= json_encode($n, JSON_UNESCAPED_UNICODE) ?>)'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg>
                            </button>
                            <form method="post" style="flex:1;" onsubmit="return tmConfirmSubmit(this,'¿Eliminar esta novedad?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $n['id'] ?>">
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
        <?php if (!$todas): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <div>No hay novedades cargadas.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── MODAL CREAR / EDITAR ── -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="modalTitle">➕ Nueva novedad</div>
            <button class="edit-modal-close" onclick="closeModal()">✕</button>
        </div>
        <form action="<?= b('/admin/novedades.php') ?>" method="post" enctype="multipart/form-data" id="modalForm">
            <input type="hidden" name="accion" id="modalAccion" value="crear">
            <input type="hidden" name="id" id="modalId" value="">

            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Título *</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="titulo" id="f-titulo" required>
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Contenido *</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="contenido" id="f-contenido" rows="4" required></textarea>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Categoría</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="categoria" id="f-categoria" placeholder="Ej: Audiología">
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Tipo</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="tipo" id="f-tipo">
                            <option value="novedad">Novedad</option>
                            <option value="noticia">Noticia</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Orden</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="number" name="orden" id="f-orden" value="0" min="0">
                    </div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">URL video</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="video_url" id="f-video_url" placeholder="https://www.youtube.com/embed/…">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Imagen</div>
                    <div class="edit-input-wrap" id="imgPreviewWrap" style="display:none;margin-bottom:8px;">
                        <img id="imgPreview" src="" style="max-height:80px;border-radius:6px;">
                    </div>
                    <input class="edit-input" type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp">
                    <small style="color:#94a3b8;font-size:11px;">JPG, PNG, WEBP — máx. 5MB. Dejá vacío para no cambiar la imagen.</small>
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

<!-- ── TOAST ── -->
<div id="toast-ok">✅ Guardado exitosamente</div>

<script>
(function() {
    // Toast
    if (new URLSearchParams(location.search).get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        t.classList.add('show');
        setTimeout(function() { t.classList.remove('show'); history.replaceState({}, '', location.pathname); }, 3000);
    }

    // Búsqueda
    document.getElementById('searchInput').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#mainTable tbody tr').forEach(function(r) {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // Ordenamiento
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
            if (!isNaN(aVal) && !isNaN(bVal)) { aVal = +aVal; bVal = +bVal; }
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

    // Modal
    window.openModal = function(mode, data) {
        document.getElementById('modalAccion').value = mode === 'editar' ? 'editar' : 'crear';
        document.getElementById('modalId').value = data && data.id ? data.id : '';
        document.getElementById('modalTitle').textContent = mode === 'editar' ? '✏️ Editar novedad' : '➕ Nueva novedad';
        document.getElementById('modalSubmitBtn').textContent = mode === 'editar' ? '💾 Guardar cambios' : '💾 Crear';

        // Limpiar / resetear
        document.getElementById('f-titulo').value = '';
        document.getElementById('f-contenido').value = '';
        document.getElementById('f-categoria').value = '';
        document.getElementById('f-tipo').value = 'novedad';
        document.getElementById('f-orden').value = '0';
        document.getElementById('f-video_url').value = '';
        document.getElementById('f-activo').checked = true;
        document.getElementById('imgPreviewWrap').style.display = 'none';
        document.getElementById('imgPreview').src = '';

        if (mode === 'editar' && data) {
            document.getElementById('f-titulo').value = data.titulo || '';
            document.getElementById('f-contenido').value = data.contenido || '';
            document.getElementById('f-categoria').value = data.categoria || '';
            document.getElementById('f-tipo').value = data.tipo || 'novedad';
            document.getElementById('f-orden').value = data.orden || 0;
            document.getElementById('f-video_url').value = data.video_url || '';
            document.getElementById('f-activo').checked = data.activo == '1' || data.activo === 1;
            if (data.imagen) {
                document.getElementById('imgPreview').src = '<?= b('/') ?>' + data.imagen;
                document.getElementById('imgPreviewWrap').style.display = 'block';
            }
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

    // Animar filas
    document.querySelectorAll('#mainTable tbody tr').forEach(function(r, i) {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-10px)';
        r.style.transition = 'opacity .35s ease, transform .35s ease';
        setTimeout(function() { r.style.opacity = '1'; r.style.transform = 'translateX(0)'; }, 80 + i * 40);
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
