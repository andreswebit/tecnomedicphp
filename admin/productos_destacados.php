<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/tienda.php';
portal_require_role(['admin']);

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $st = db()->prepare("UPDATE tm_productos SET destacado = ? WHERE id = ?");
    $st->bind_param('ii', $destacado, $id);
    $st->execute();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
    exit;
}

$productos = db()->query(
    "SELECT p.*, f.nombre AS familia_nombre, c.nombre AS categoria_nombre, c.slug AS categoria_slug
     FROM tm_productos p
     JOIN tm_familias f ON f.id = p.familia_id
     JOIN tm_categorias c ON c.id = f.categoria_id
     ORDER BY p.destacado DESC, p.creado_en DESC"
)->fetch_all(MYSQLI_ASSOC);

$total = count($productos);
$destacados = count(array_filter($productos, fn($p) => $p['destacado']));

$portal_titulo = 'Destacados · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-sub">productos</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Destacados</div>
        <div class="stat-value"><?= $destacados ?></div>
        <div class="stat-sub">visibles en home</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">No destacados</div>
        <div class="stat-value"><?= $total - $destacados ?></div>
        <div class="stat-sub">ocultos en home</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Productos Destacados</div>
            <div class="chip">🎬 Multimedia</div>
        </div>
    </div>
    <p style="color:#94a3b8;font-size:13px;margin:0 0 20px;">
        Los productos marcados con ★ aparecen en la sección "Tienda Virtual" del homepage.
    </p>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> producto<?= $total !== 1 ? 's' : '' ?> · <?= $destacados ?> destacado<?= $destacados !== 1 ? 's' : '' ?></div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" placeholder="Buscar…" style="width:220px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="mainTable">
            <thead>
                <tr>
                    <th>★</th>
                    <th class="sortable" data-col="1">Producto<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Categoría<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Familia<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="4">Modalidad<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="5">Activo<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                <tr>
                    <td style="text-align:center;font-size:18px;">
                        <?= $p['destacado'] ? '★' : '<span style="color:#94a3b8;">☆</span>' ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                        <?php if (!empty($p['imagen'])): ?>
                            <br><img src="<?= $base ?>/static/img/productos/<?= htmlspecialchars($p['imagen']) ?>" style="max-height:30px;border-radius:4px;margin-top:4px;" alt="">
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge pendiente"><?= htmlspecialchars($p['categoria_nombre'] ?? '-') ?></span>
                    </td>
                    <td><?= htmlspecialchars($p['familia_nombre'] ?? '-') ?></td>
                    <td><?= htmlspecialchars(modalidad_label($p['modalidad'])) ?></td>
                    <td>
                        <span class="badge <?= $p['activo'] ? 'aprobado' : 'pendiente' ?>">
                            <?= $p['activo'] ? 'Sí' : 'No' ?>
                        </span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <?php if ($p['destacado']): ?>
                            <form method="post" style="flex:1;" onsubmit="return tmConfirmSubmit(this,'¿Quitar este producto de destacados?');">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="destacado" value="0">
                                <button type="submit" class="btn-action btn-del" data-tooltip="Quitar destacado" style="width:100%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/></svg>
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="post" style="flex:1;">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="destacado" value="1">
                                <button type="submit" class="btn-action btn-save" data-tooltip="Marcar como destacado" style="width:100%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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

    document.querySelectorAll('#mainTable tbody tr').forEach(function(r, i) {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-10px)';
        r.style.transition = 'opacity .35s ease, transform .35s ease';
        setTimeout(function() { r.style.opacity = '1'; r.style.transform = 'translateX(0)'; }, 80 + i * 40);
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
