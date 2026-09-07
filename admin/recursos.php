<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_recursos.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?err=1');
            exit;
        }
        $permitidas = ['pdf'=>'PDF','jpg'=>'Imagen','jpeg'=>'Imagen','png'=>'Imagen','doc'=>'Word','docx'=>'Word'];
        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if (!isset($permitidas[$ext]) || $_FILES['archivo']['size'] > 15 * 1024 * 1024) {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?err=1');
            exit;
        }
        $carpeta = __DIR__ . '/../storage/recursos';
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
        $nombreArchivo = uniqid('recurso_') . '.' . $ext;
        $rutaRelativa = 'storage/recursos/' . $nombreArchivo;
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $carpeta . '/' . $nombreArchivo)) {
            recurso_crear([
                'titulo' => trim($_POST['titulo']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'area' => $_POST['area'] ?: null,
                'archivo_nombre' => $_FILES['archivo']['name'],
                'archivo_ruta' => $rutaRelativa,
                'tipo' => $permitidas[$ext],
                'publico' => isset($_POST['publico']) ? 1 : 0,
                'subido_por' => (int)($_SESSION['portal_uid'] ?? 0),
            ]);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        } else {
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?err=1');
            exit;
        }
    } elseif ($accion === 'eliminar') {
        $borrado = recurso_eliminar((int)($_POST['id'] ?? 0));
        if ($borrado && $borrado['archivo_ruta']) {
            $ruta = __DIR__ . '/../' . $borrado['archivo_ruta'];
            if (is_file($ruta)) unlink($ruta);
        }
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$recursos = recursos_listar_todos();
$total = count($recursos);
$publicos = count(array_filter($recursos, fn($r) => $r['publico']));

$portal_titulo = 'Recursos · Mi Portal';
$portal_activo = 'recursos';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"style="text-align: end;"><?= $total ?></div>
        <div class="stat-sub">recursos</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Públicos</div>
        <div class="stat-value" style="text-align: end;"><?= $publicos ?></div>
        <div class="stat-sub">visibles sin login</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Solo Portal</div>
        <div class="stat-value" style="text-align: end;"><?= $total - $publicos ?></div>
        <div class="stat-sub">requieren login</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Recursos</div>
        </div>
        <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('crear')">
            ➕ Subir recurso
        </button>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> recurso<?= $total !== 1 ? 's' : '' ?></div>
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
                    <th class="sortable" data-col="1">Área<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Tipo<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Visibilidad<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="4">Subido por<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recursos as $r): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($r['titulo']) ?></strong>
                        <?php if ($r['descripcion']): ?>
                            <br><small style="color:#64748b;"><?= htmlspecialchars($r['descripcion']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['area'] ?: '<span style="color:#94a3b8;">General</span>') ?></td>
                    <td><?= htmlspecialchars($r['tipo']) ?></td>
                    <td>
                        <span class="badge <?= $r['publico'] ? 'aprobado' : 'pendiente' ?>">
                            <?= $r['publico'] ? '🌐 Público' : '🔒 Solo Portal' ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($r['subido_por_apellido'] . ', ' . $r['subido_por_nombre']) ?></td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <a href="<?= b('/recursos_descargar.php?id=' . $r['id']) ?>" target="_blank" class="btn-action btn-print-turn" data-tooltip="Descargar" style="padding:7px 12px;display:flex;align-items:center;">
                                ⬇ Descargar
                            </a>
                            <form method="post" style="flex:1;" onsubmit="return confirm('¿Eliminar este recurso?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
        <?php if (!$recursos): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📚</div>
            <div>No hay recursos cargados.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal crear -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">➕ Subir recurso</div>
            <button class="edit-modal-close" onclick="closeModal()">✕</button>
        </div>
        <form action="<?= b('/admin/recursos.php') ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="crear">
            <div class="edit-grid">
                <div class="edit-group full">
                    <div class="edit-label">Título *</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="titulo" required placeholder="Ej: Ficha de admisión">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Descripción</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="descripcion" placeholder="Opcional">
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Área</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="area">
                            <option value="">— General —</option>
                            <option value="audiologia">Audiología</option>
                            <option value="hiperbarica">Medicina hiperbárica</option>
                            <option value="nutricion">Nutrición</option>
                            <option value="ortopedia">Ortopedia y rehabilitación</option>
                            <option value="equipamiento">Equipamiento médico y quirúrgico</option>
                        </select>
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Archivo * (PDF, JPG, PNG, DOC/DOCX — máx. 15MB)</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="file" name="archivo" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    </div>
                </div>
                <div class="edit-group full">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="publico" value="1" style="width:auto;">
                        <span style="color:#ccc;">Visible públicamente (sin login)</span>
                    </label>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Subir recurso</button>
            </div>
        </form>
    </div>
</div>

<div id="toast-ok">✅ Recurso subido</div>
<div id="toast-err" style="display:none;position:fixed;bottom:24px;right:24px;background:#c94f4f;color:#fff;padding:14px 22px;border-radius:10px;font-weight:600;z-index:9999;"></div>

<script>
(function() {
    var params = new URLSearchParams(location.search);
    if (params.get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        t.classList.add('show');
        setTimeout(function() { t.classList.remove('show'); history.replaceState({}, '', location.pathname); }, 3000);
    }
    if (params.get('err') === '1') {
        var te = document.getElementById('toast-err');
        te.textContent = '⚠️ No se pudo subir el archivo. Verificá el tipo y tamaño.';
        te.style.display = 'block';
        setTimeout(function() { te.style.display = 'none'; history.replaceState({}, '', location.pathname); }, 4000);
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

    window.openModal = function() { document.getElementById('editModal').classList.add('open'); };
    window.closeModal = function() { document.getElementById('editModal').classList.remove('open'); };
    document.getElementById('editModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });

    document.querySelectorAll('#mainTable tbody tr').forEach(function(r, i) {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-10px)';
        r.style.transition = 'opacity .35s ease, transform .35s ease';
        setTimeout(function() { r.style.opacity = '1'; r.style.transform = 'translateX(0)'; }, 80 + i * 40);
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
