<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("DELETE FROM tm_presupuestos WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$presupuestos = [];
try {
    $presupuestos = db()->query(
        "SELECT * FROM tm_presupuestos ORDER BY FIELD(estado,'Pendiente','Elaborado','Enviado'), creado_en DESC"
    )->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $presupuestos = [];
}

$total = count($presupuestos);
$pendientes = count(array_filter($presupuestos, fn($p) => strtolower($p['estado'] ?? '') === 'pendiente'));
$elaborados = count(array_filter($presupuestos, fn($p) => strtolower($p['estado'] ?? '') === 'elaborado'));
$enviados = count(array_filter($presupuestos, fn($p) => strtolower($p['estado'] ?? '') === 'enviado'));

$portal_titulo = 'Presupuestos · Mi Portal';
$portal_activo = 'presupuestos';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"style="text-align: end"><?= $total ?></div> 
        <div class="stat-sub">presupuestos</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value"style="text-align: end"><?= $pendientes ?></div>
        <div class="stat-sub">por elaborar</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Elaborados</div>
        <div class="stat-value"style="text-align: end"><?= $elaborados ?></div>
        <div class="stat-sub">listos para enviar</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Enviados</div>
        <div class="stat-value"style="text-align: end"><?= $enviados ?></div>
        <div class="stat-sub">confirmados</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Presupuestos</div>
        </div>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;">
            <div>
                <div class="table-title"><?= $total ?> presupuesto<?= $total !== 1 ? 's' : '' ?></div>
            </div>
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Buscar…" style="width:220px;">
            </div>
        </div>
    </div>

    <div id="whatsapp" style="height:1px;"></div>
    <div id="email" style="height:1px;"></div>
    <div class="table-wrap">
        <table id="mainTable">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Paciente<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI / CUIT<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Área<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="4">Fecha<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="5">Estado<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presupuestos as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars(($p['apellido'] ?? '') . ', ' . ($p['nombre'] ?? '-')) ?></strong></td>
                    <td><?= htmlspecialchars($p['dni_cuit'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['email'] ?? '-') ?></td>
                    <td><span class="badge"><?= htmlspecialchars(ucfirst($p['area'] ?? '-')) ?></span></td>
                    <td><?= htmlspecialchars($p['creado_en'] ? date('d/m/Y', strtotime($p['creado_en'])) : '-') ?></td>
                    <td>
                        <?php
                        $estMap = ['pendiente'=>'pendiente','elaborado'=>'aprobado','enviado'=>'aprobado'];
                        $labMap = ['pendiente'=>'⏳ Pendiente','elaborado'=>'📋 Elaborado','enviado'=>'📤 Enviado'];
                        $e = strtolower($p['estado'] ?? '');
                        $badge = $estMap[$e] ?? 'pendiente';
                        $label = $labMap[$e] ?? ucfirst($p['estado'] ?? '-');
                        ?>
                        <span class="badge <?= $badge ?>"><?= $label ?></span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-save" style="padding:7px 12px;" onclick='verDetalle(<?= json_encode($p, JSON_UNESCAPED_UNICODE) ?>)'>
                                👁 Ver
                            </button>
                            <form method="post" onsubmit="return tmConfirmSubmit(this,'¿Eliminar este presupuesto?');" style="flex:1;">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
        <?php if (!$presupuestos): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <div>No hay presupuestos cargados.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal ver detalle -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal" style="max-width:600px;">
        <div class="edit-modal-header">
            <div class="edit-modal-title">📋 Detalle del presupuesto</div>
            <button class="edit-modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="edit-grid" id="modalContent"></div>
        <div class="edit-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal()">Cerrar</button>
        </div>
    </div>
</div>

<div id="toast-ok">✅ Acción realizada</div>

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
            var aVal = (a.querySelectorAll('td')[col] || {textContent:''}).textContent.trim().toLowerCase();
            var bVal = (b.querySelectorAll('td')[col] || {textContent:''}).textContent.trim().toLowerCase();
            if (aVal < bVal) return dir === 'asc' ? -1 : 1;
            if (aVal > bVal) return dir === 'asc' ? 1 : -1;
            return 0;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
    }

    window.verDetalle = function(d) {
        var html = '';
        html += '<div class="edit-group"><div class="edit-label">Paciente</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + ((d.apellido||'') + ', ' + (d.nombre||'-')).trim() + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">DNI / CUIT</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.dni_cuit||'-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Email</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.email||'-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Teléfono</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.telefono||'-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Área</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.area||'-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Fecha</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.creado_en ? d.creado_en.substring(0,10).split('-').reverse().join('/') : '-') + '</div></div>';
        if (d.descripcion) {
            html += '<div class="edit-group full"><div class="edit-label">Descripción / Pedido</div><div class="edit-input-wrap"><textarea class="edit-input" readonly rows="5" style="resize:none;">' + d.descripcion + '</textarea></div></div>';
        }
        document.getElementById('modalContent').innerHTML = html;
        document.getElementById('editModal').classList.add('open');
    };

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
