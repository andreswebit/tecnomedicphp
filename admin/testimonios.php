<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_testimonios.php';

portal_require_role(['admin']);

$mensaje = '';
$error = '';

// ======================================================
// Utilidades de subida de multimedia para testimonios
// ======================================================
function tm_is_uploaded_file_ok(?array $file): bool {
    return $file
        && isset($file['error'])
        && (int)$file['error'] === UPLOAD_ERR_OK
        && !empty($file['tmp_name'])
        && is_file((string)$file['tmp_name']);
}

function tm_move_uploaded(array $file, string $destAbsDir, string $destRelPrefix, array $allowedExt, int $maxBytes): ?string {
    if (!tm_is_uploaded_file_ok($file)) return null;

    $origName = (string)($file['name'] ?? '');
    $tmpPath = (string)($file['tmp_name'] ?? '');
    $size = (int)($file['size'] ?? 0);

    if ($size <= 0) return null;
    if ($size > $maxBytes) return null;

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) return null;

    if (!is_dir($destAbsDir)) {
        if (!mkdir($destAbsDir, 0755, true)) return null;
    }

    $newName = 'testimonio_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destAbs = rtrim($destAbsDir, '/\\') . DIRECTORY_SEPARATOR . $newName;

    // OJO: en tu BD venís guardando paths tipo `static/...` (sin `/` inicial).
    // Acá devolvemos exactamente ese formato.
    $destRelPrefix = rtrim($destRelPrefix, '/');
    $destRel = $destRelPrefix . '/' . $newName;

    if (!move_uploaded_file($tmpPath, $destAbs)) return null;

    return $destRel;
}

$IMG_DEST_DIR = __DIR__ . '/../static/img/testimonios';
$IMG_DEST_PREFIX = 'static/img/testimonios';
$VID_DEST_DIR = __DIR__ . '/../static/video/testimonios';
$VID_DEST_PREFIX = 'static/video/testimonios';

$IMG_ALLOWED = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$VID_ALLOWED = ['mp4'];

// =========================
// 1) Guardar Testimonios (crear / editar / eliminar)
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear' || $accion === 'editar') {
        $id = (int)($_POST['id'] ?? 0);
        $actual = $accion === 'editar' ? testimonio_get($id) : null;

        if ($accion === 'editar' && !$actual) {
            $error = 'No se encontró el testimonio a editar.';
        } else {
            $media_tipo = trim((string)($_POST['media_tipo'] ?? 'ninguno'));
            if ($media_tipo === '') $media_tipo = 'ninguno';

            // Rutas actuales (hidden) para cuando editás sin re-subir.
            $existing_media_url = trim((string)($_POST['media_url'] ?? ''));
            $existing_video_thumb = trim((string)($_POST['video_thumb'] ?? ''));

            if ($accion === 'editar') {
                // fallback desde BD
                $existing_media_url = $existing_media_url !== '' ? $existing_media_url : (string)($actual['media_url'] ?? '');
                $existing_video_thumb = $existing_video_thumb !== '' ? $existing_video_thumb : (string)($actual['video_thumb'] ?? '');
            }

            $media_url = $existing_media_url;
            $video_thumb = $existing_video_thumb;

            if ($media_tipo === 'imagen') {
                $uploaded = null;
                if (!empty($_FILES['media_image_file']['tmp_name'])) {
                    $uploaded = tm_move_uploaded(
                        $_FILES['media_image_file'],
                        $IMG_DEST_DIR,
                        $IMG_DEST_PREFIX,
                        $IMG_ALLOWED,
                        10 * 1024 * 1024
                    );
                }

                if ($uploaded) {
                    $media_url = $uploaded;
                    $video_thumb = '';
                }

                if ($media_url === '') {
                    $error = ($accion === 'crear')
                        ? 'Subí una imagen para el testimonio.'
                        : 'No existe imagen configurada para este testimonio.';
                }
            } elseif ($media_tipo === 'video') {
                $uploaded_video = null;
                $uploaded_thumb = null;

                if (!empty($_FILES['media_video_file']['tmp_name'])) {
                    $uploaded_video = tm_move_uploaded(
                        $_FILES['media_video_file'],
                        $VID_DEST_DIR,
                        $VID_DEST_PREFIX,
                        $VID_ALLOWED,
                        50 * 1024 * 1024
                    );
                }

                if (!empty($_FILES['video_thumb_file']['tmp_name'])) {
                    $uploaded_thumb = tm_move_uploaded(
                        $_FILES['video_thumb_file'],
                        $VID_DEST_DIR,
                        $VID_DEST_PREFIX,
                        $IMG_ALLOWED,
                        10 * 1024 * 1024
                    );
                }

                if ($uploaded_video) $media_url = $uploaded_video;
                if ($uploaded_thumb) $video_thumb = $uploaded_thumb;

                if ($media_url === '') {
                    $error = ($accion === 'crear')
                        ? 'Subí el video (MP4) para el testimonio.'
                        : 'No existe video configurado para este testimonio.';
                } elseif ($video_thumb === '') {
                    $error = ($accion === 'crear')
                        ? 'Subí la miniatura (thumb) de la imagen para el video.'
                        : 'No existe thumb configurado para este testimonio.';
                }
            } else {
                // sin medio
                $media_url = '';
                $video_thumb = '';
            }

            if ($error === '') {
                $d = [
                    'nombre'      => trim((string)($_POST['nombre'] ?? '')),
                    'rol'         => trim((string)($_POST['rol'] ?? '')),
                    'texto'       => trim((string)($_POST['texto'] ?? '')),
                    'resultado'   => trim((string)($_POST['resultado'] ?? '')),
                    'media_tipo'  => $media_tipo,
                    'media_url'   => $media_url,
                    'video_thumb' => $video_thumb,
                    'orden'       => (int)($_POST['orden'] ?? 0),
                    'activo'      => isset($_POST['activo']) ? 1 : 0,
                ];

                if ($d['nombre'] && $d['texto']) {
                    if ($accion === 'crear') {
                        testimonio_crear($d);
                    } else {
                        testimonio_editar($id, $d);
                    }

                    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
                    exit;
                } else {
                    $error = 'Nombre y texto son obligatorios.';
                }
            }
        }
    } elseif ($accion === 'eliminar') {
        testimonio_eliminar((int)($_POST['id'] ?? 0));
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$todos = testimonios_listar();
$total = count($todos);
$activos = count(array_filter($todos, fn($t) => (int)($t['activo'] ?? 0) === 1));

$portal_titulo = 'Testimonios · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<div style="margin:20px 28px 0;" id="tmErrorWrap">
    <?php if ($error): ?>
        <div style="background:#c94f4f;color:#fff;padding:12px 18px;border-radius:10px;font-weight:600;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
</div>

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
                        <span class="badge <?= ($t['media_tipo'] ?? 'ninguno') === 'video' ? 'aprobado' : (($t['media_tipo'] ?? 'ninguno') === 'imagen' ? 'pendiente' : '') ?>">
                            <?= htmlspecialchars(ucfirst((string)($t['media_tipo'] ?? 'ninguno'))) ?>
                        </span>
                    </td>
                    <td><?= (int)($t['orden'] ?? 0) ?></td>
                    <td>
                        <span class="badge <?= (int)($t['activo'] ?? 0) ? 'aprobado' : 'pendiente' ?>">
                            <?= (int)($t['activo'] ?? 0) ? 'Sí' : 'No' ?>
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
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
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

        <form action="<?= b('/admin/testimonios.php') ?>" method="post" enctype="multipart/form-data" id="modalForm">
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

                <!-- Rutas actuales (hidden) para mantener si no se re-suben -->
                <input type="hidden" name="media_url" id="f-media_url" value="">
                <input type="hidden" name="video_thumb" id="f-video_thumb" value="">

                <!-- Imagen -->
                <div class="edit-group full" id="mediaImageBlock" style="display:none;">
                    <div class="edit-label">Imagen</div>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;">
                        <div style="position:relative;width:220px;max-width:100%;height:auto;border-radius:10px;overflow:hidden;background:#000;">
                            <img id="previewImage" src="" alt="Vista previa" style="width:100%;display:none;">
                            <div id="previewImageEmpty" style="padding:14px;color:#94a3b8;display:block;font-size:13px;">
                                Sin imagen cargada
                            </div>
                        </div>
                        <div style="flex:1;min-width:220px;">
                            <input class="edit-input" type="file" name="media_image_file" id="f-media_image_file" accept="image/*">
                            <p style="color:#64748b;font-size:12px;margin:6px 0 0;">Se guarda en static/img/testimonios (máx. 10MB).</p>
                        </div>
                    </div>
                </div>

                <!-- Video + thumb -->
                <div class="edit-group full" id="mediaVideoBlock" style="display:none;">
                    <div class="edit-label">Video (MP4) + miniatura</div>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;">
                        <div style="position:relative;width:320px;max-width:100%;height:auto;border-radius:10px;overflow:hidden;background:#000;">
                            <video id="previewVideo" width="320" height="180" style="display:none;width:100%;height:auto;" muted loop autoplay playsinline controls></video>
                            <div id="previewVideoEmpty" style="padding:14px;color:#94a3b8;display:block;font-size:13px;">
                                Sin video cargado
                            </div>
                        </div>
                        <div style="flex:1;min-width:220px;">
                            <div style="margin-bottom:10px;">
                                <input class="edit-input" type="file" name="media_video_file" id="f-media_video_file" accept="video/mp4">
                                <p style="color:#64748b;font-size:12px;margin:6px 0 0;">Se guarda en static/video/testimonios (máx. 50MB).</p>
                            </div>

                            <div style="margin-top:12px;">
                                <input class="edit-input" type="file" name="video_thumb_file" id="f-video_thumb_file" accept="image/*">
                                <p style="color:#64748b;font-size:12px;margin:6px 0 0;">Miniatura (imagen) para el video (máx. 10MB).</p>
                            </div>

                            <div style="margin-top:12px;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:8px;">Miniatura actual:</div>
                                <img id="previewThumb" src="" alt="Thumb" style="max-width:100%;height:auto;border-radius:10px;display:none;">
                                <div id="previewThumbEmpty" style="color:#94a3b8;font-size:12px;display:block;">Sin thumb cargada</div>
                            </div>
                        </div>
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
    const TM_BASE = <?= json_encode($base) ?>;

    function pathToUrl(p) {
        if (!p) return '';
        if (typeof p !== 'string') p = String(p);
        if (p.startsWith('http://') || p.startsWith('https://')) return p;

        var base = TM_BASE;
        if (base && base.endsWith('/')) base = base.slice(0, -1);

        // tu BD guarda `static/...` (sin `/` inicial). También podría venir con `/static/...`
        if (p.startsWith('/')) return base + p;
        return base + '/' + p;
    }

    function setPreviewByTipo(tipo, data) {
        const imageBlock = document.getElementById('mediaImageBlock');
        const videoBlock = document.getElementById('mediaVideoBlock');

        const previewImage = document.getElementById('previewImage');
        const previewImageEmpty = document.getElementById('previewImageEmpty');

        const previewVideo = document.getElementById('previewVideo');
        const previewVideoEmpty = document.getElementById('previewVideoEmpty');

        const previewThumb = document.getElementById('previewThumb');
        const previewThumbEmpty = document.getElementById('previewThumbEmpty');

        imageBlock.style.display = 'none';
        videoBlock.style.display = 'none';

        if (previewImage) {
            previewImage.src = '';
            previewImage.style.display = 'none';
        }
        if (previewImageEmpty) previewImageEmpty.style.display = 'block';

        if (previewVideo) {
            try { previewVideo.pause && previewVideo.pause(); } catch (e) {}
            previewVideo.src = '';
            previewVideo.style.display = 'none';
        }
        if (previewVideoEmpty) previewVideoEmpty.style.display = 'block';

        if (previewThumb) {
            previewThumb.src = '';
            previewThumb.style.display = 'none';
        }
        if (previewThumbEmpty) previewThumbEmpty.style.display = 'block';

        if (tipo === 'imagen') {
            imageBlock.style.display = 'block';

            const mediaUrl = (data && data.media_url) ? data.media_url : document.getElementById('f-media_url').value;
            if (mediaUrl) {
                if (previewImage) {
                    previewImage.src = pathToUrl(mediaUrl);
                    previewImage.style.display = 'block';
                }
                if (previewImageEmpty) previewImageEmpty.style.display = 'none';
            }
        } else if (tipo === 'video') {
            videoBlock.style.display = 'block';

            const mediaUrl = (data && data.media_url) ? data.media_url : document.getElementById('f-media_url').value;
            const thumb = (data && data.video_thumb) ? data.video_thumb : document.getElementById('f-video_thumb').value;

            if (previewVideo && mediaUrl) {
                previewVideo.src = pathToUrl(mediaUrl);
                previewVideo.style.display = 'block';
                if (previewVideoEmpty) previewVideoEmpty.style.display = 'none';
            }

            if (previewThumb && thumb) {
                previewThumb.src = pathToUrl(thumb);
                previewThumb.style.display = 'block';
                if (previewThumbEmpty) previewThumbEmpty.style.display = 'none';
            }

            // mensajes vacíos
            if (previewVideoEmpty) previewVideoEmpty.style.display = mediaUrl ? 'none' : 'block';
            if (previewThumbEmpty) previewThumbEmpty.style.display = thumb ? 'none' : 'block';
        }
    }

    // Toast
    if (new URLSearchParams(location.search).get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        if (t) {
            t.classList.add('show');
            setTimeout(function() {
                t.classList.remove('show');
                history.replaceState({}, '', location.pathname);
            }, 3000);
        }
    }

    // Búsqueda
    var search = document.getElementById('searchInput');
    if (search) {
        search.addEventListener('input', function() {
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

    // Modal
    window.openModal = function(mode, data) {
        document.getElementById('modalAccion').value = mode === 'editar' ? 'editar' : 'crear';
        document.getElementById('modalId').value = data && data.id ? data.id : '';
        document.getElementById('modalTitle').textContent = mode === 'editar' ? '✏️ Editar testimonio' : '➕ Nuevo testimonio';
        document.getElementById('modalSubmitBtn').textContent = mode === 'editar' ? '💾 Guardar cambios' : '💾 Crear';

        ['nombre','rol','texto','resultado'].forEach(function(f) {
            var el = document.getElementById('f-' + f);
            if (el) el.value = data && data[f] ? data[f] : '';
        });

        // rutas actuales hidden
        ['media_url','video_thumb'].forEach(function(f) {
            var el = document.getElementById('f-' + f);
            if (!el) return;
            var v = data && data[f] ? data[f] : '';
            el.value = (v === null || typeof v === 'undefined') ? '' : String(v);
        });

        document.getElementById('f-media_tipo').value = data && data.media_tipo ? data.media_tipo : 'ninguno';
        document.getElementById('f-orden').value = data && data.orden ? data.orden : 0;
        document.getElementById('f-activo').checked = !data || (data.activo == '1' || data.activo === 1 || data.activo === true);

        // reset inputs de archivo (no se pueden setear por seguridad)
        var imgFile = document.getElementById('f-media_image_file');
        var vidFile = document.getElementById('f-media_video_file');
        var thumbFile = document.getElementById('f-video_thumb_file');
        if (imgFile) imgFile.value = '';
        if (vidFile) vidFile.value = '';
        if (thumbFile) thumbFile.value = '';

        setPreviewByTipo(document.getElementById('f-media_tipo').value, data);

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

    // Alternar bloques según media_tipo
    var mediaTipoSelect = document.getElementById('f-media_tipo');
    if (mediaTipoSelect) {
        mediaTipoSelect.addEventListener('change', function() {
            setPreviewByTipo(this.value, null);
        });
    }

    // Previews al seleccionar archivos
    var mediaImgInput = document.getElementById('f-media_image_file');
    var previewImage = document.getElementById('previewImage');
    var previewImageEmpty = document.getElementById('previewImageEmpty');
    if (mediaImgInput && previewImage) {
        mediaImgInput.addEventListener('change', function() {
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;
            var url = URL.createObjectURL(file);
            previewImage.src = url;
            previewImage.style.display = 'block';
            if (previewImageEmpty) previewImageEmpty.style.display = 'none';
        });
    }

    var mediaVidInput = document.getElementById('f-media_video_file');
    var previewVideo = document.getElementById('previewVideo');
    var previewVideoEmpty = document.getElementById('previewVideoEmpty');
    if (mediaVidInput && previewVideo) {
        mediaVidInput.addEventListener('change', function() {
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;
            var url = URL.createObjectURL(file);
            previewVideo.src = url;
            previewVideo.style.display = 'block';
            if (previewVideoEmpty) previewVideoEmpty.style.display = 'none';
        });
    }

    var thumbInput = document.getElementById('f-video_thumb_file');
    var previewThumb = document.getElementById('previewThumb');
    var previewThumbEmpty = document.getElementById('previewThumbEmpty');
    if (thumbInput && previewThumb) {
        thumbInput.addEventListener('change', function() {
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;
            var url = URL.createObjectURL(file);
            previewThumb.src = url;
            previewThumb.style.display = 'block';
            if (previewThumbEmpty) previewThumbEmpty.style.display = 'none';
        });
    }

    // Animar filas
    document.querySelectorAll('#mainTable tbody tr').forEach(function(r, i) {
        r.style.opacity = '0';
        r.style.transform = 'translateX(-10px)';
        r.style.transition = 'opacity .35s ease, transform .35s ease';
        setTimeout(function() {
            r.style.opacity = '1';
            r.style.transform = 'translateX(0)';
        }, 80 + i * 40);
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
