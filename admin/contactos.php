<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'atender') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("UPDATE tm_contactos SET atendido = 1 WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    } elseif ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("DELETE FROM tm_contactos WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$contactos = [];
try {
    $contactos = db()->query(
        "SELECT * FROM tm_contactos ORDER BY atendido ASC, creado_en DESC"
    )->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $contactos = [];
}

$total = count($contactos);
$pendientes = count(array_filter($contactos, fn($c) => $c['atendido'] == 0));
$atendidos = $total - $pendientes;

$portal_titulo = 'Contactos · Mi Portal';
$portal_activo = 'contactos';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value" style="text-align: end"><?= $total ?></div>
        <div class="stat-sub">mensajes</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value" style="text-align: end"><?= $pendientes ?></div>
        <div class="stat-sub">por atender</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Atendidos</div>
        <div class="stat-value" style="text-align: end"><?= $atendidos ?></div>
        <div class="stat-sub">ya resueltos</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Contactos</div>
        </div>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> mensaje<?= $total !== 1 ? 's' : '' ?></div>
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
                    <th class="sortable" data-col="1">Email<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Teléfono<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Motivo<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="4">Fecha<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="5">Estado<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contactos as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <td><?= htmlspecialchars($c['telefono'] ?: '-') ?></td>
                    <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($c['motivo'] ?? '') ?>">
                        <?= htmlspecialchars(ucfirst($c['motivo'] ?? '-')) ?>
                    </td>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['creado_en']))) ?></td>
                    <td>
                        <span class="badge <?= $c['atendido'] == 0 ? 'pendiente' : 'aprobado' ?>">
                            <?= $c['atendido'] == 0 ? '⏳ Pendiente' : '✅ Atendido' ?>
                        </span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-save" style="padding:7px 12px;" onclick='verMensaje(<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>)'>
                                👁 Ver
                            </button>
                            <?php if ($c['atendido'] == 0): ?>
                            <form method="post" style="flex:1;">
                                <input type="hidden" name="accion" value="atender">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-action btn-save" data-tooltip="Marcar atendido" style="width:100%;">✔ Atender</button>
                            </form>
                            <?php endif; ?>
                            <form method="post" style="flex:1;" onsubmit="return confirm('¿Eliminar este mensaje?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
        <?php if (!$contactos): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📧</div>
            <div>No hay mensajes de contacto.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal ver mensaje -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">👁 Detalle del mensaje</div>
            <button class="edit-modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="edit-grid" id="modalContent">
        </div>
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

    window.verMensaje = function(d) {
        var html = '<div class="edit-group"><div class="edit-label">Nombre</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.nombre || '-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Email</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.email || '-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Teléfono</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.telefono || '-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Fecha</div><div class="edit-input-wrap" style="background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;">' + (d.creado_en ? d.creado_en.replace(' ','T').substring(0,16).replace('T',' ') : '-') + '</div></div>';
        html += '<div class="edit-group full"><div class="edit-label">Mensaje</div><div class="edit-input-wrap"><textarea class="edit-input" readonly rows="6" style="resize:none;">' + (d.mensaje || d.comentario || '-') + '</textarea></div></div>';
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
