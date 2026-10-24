<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_novedades.php';
portal_require_role(['admin']);

$mensaje = '';
$error = '';

// Valida que la imagen esté en formato 9:16 (aprox), para el carrusel/noticias.
function tm_is_ratio_9_16(array $file, float $tol = 0.03): bool {
    if (empty($file['tmp_name']) || !is_file((string)$file['tmp_name'])) return false;

    $info = @getimagesize((string)$file['tmp_name']);
    if (!$info || empty($info[0]) || empty($info[1])) return false;

    $w = (float)$info[0];
    $h = (float)$info[1];
    if ($h <= 0.0) return false;

    $ratio = $w / $h;
    $target = 9.0 / 16.0;

    return abs($ratio - $target) <= ($target * $tol);
}

function tm_seccion_de_tipo(?string $tipo): string {
    $tipo = (string)($tipo ?? '');
    if ($tipo === 'noticia') return 'noticia';
    // Compatibilidad: el esquema viejo usaba 'novedad' para tarjetas.
    return 'tarjeta';
}

function tm_tipo_de_seccion(string $seccion): string {
    return $seccion === 'noticia' ? 'noticia' : 'tarjeta';
}

// ── Acciones POST ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $modalSeccion = tm_seccion_de_tipo((string)($_POST['modal_seccion'] ?? 'tarjeta'));
    $seccion = $modalSeccion;
    $tipo = tm_tipo_de_seccion($seccion);

    if ($accion === 'crear') {
        $d = [
            'ncar_tag' => trim((string)($_POST['ncar_tag'] ?? '')),
            'ncar_title' => trim((string)($_POST['ncar_title'] ?? '')),
            'ncar_expert' => trim((string)($_POST['ncar_expert'] ?? '')),
            'descripcion' => trim((string)($_POST['descripcion'] ?? '')),
            'ncar_list' => trim((string)($_POST['ncar_list'] ?? '')),

            'imagen' => '',
            'video_url' => '',

            'categoria' => '',
            'tipo' => $tipo,
            'orden' => (int)($_POST['orden'] ?? 0),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];

        // Subidas
        if ($seccion === 'tarjeta') {
            if (!empty($_FILES['imagen']['tmp_name'])) {
                if (!tm_is_ratio_9_16($_FILES['imagen'])) {
                    $error = 'La imagen de Tarjetas debe ser 9:16 (vertical).';
                } else {
                    $d['imagen'] = novedad_subir_imagen($_FILES['imagen']);
                    if ($d['imagen'] === '') $error = 'No se pudo subir la imagen.';
                }
            }
            if (!empty($_FILES['video_file']['tmp_name'])) {
                $d['video_url'] = novedad_subir_video($_FILES['video_file']);
                if ($d['video_url'] === '') $error = 'No se pudo subir el video (MP4).';
            }
        } else {
            // Noticias
            if (!empty($_FILES['imagen_noticia']['tmp_name'])) {
                if (!tm_is_ratio_9_16($_FILES['imagen_noticia'])) {
                    $error = 'La imagen de Noticias debe ser 9:16 (vertical).';
                } else {
                    $d['imagen'] = novedad_subir_imagen($_FILES['imagen_noticia']);
                    if ($d['imagen'] === '') $error = 'No se pudo subir la imagen.';
                }
            }
            if (!empty($_FILES['video_file_noticia']['tmp_name'])) {
                $d['video_url'] = novedad_subir_video($_FILES['video_file_noticia']);
                if ($d['video_url'] === '') $error = 'No se pudo subir el video (MP4).';
            }
        }

        // Validaciones
        if ($d['ncar_title'] === '') {
            $error = 'El campo ncar-title es obligatorio.';
        } else if ($seccion === 'tarjeta') {
            if ($d['descripcion'] === '') {
                $error = 'El campo descripcion es obligatorio para Tarjetas.';
            } else if ($d['ncar_expert'] === '') {
                $error = 'El campo ncar-expert es obligatorio para Tarjetas.';
            } else if (trim((string)$d['ncar_list']) === '') {
                $error = 'El campo ncar-list es obligatorio (viñetas) para Tarjetas.';
            }
        } else {
            // Noticias: descripcion visual se arma con ncar_expert (alineado al pedido)
            if ($d['descripcion'] === '' && $d['ncar_expert'] !== '') {
                $d['descripcion'] = $d['ncar_expert'];
            }

            if ($d['ncar_expert'] === '') {
                $error = 'El campo ncar-expert (descripcion) es obligatorio para Noticias.';
            } else if (trim((string)$d['ncar_list']) === '') {
                $error = 'El campo ncar-list es obligatorio (viñetas) para Noticias.';
            }
        }

        $tieneImagen = !empty($d['imagen']);
        $tieneVideo = !empty($d['video_url']);
        if ($error === '') {
            if (!$tieneImagen && !$tieneVideo) {
                $error = ($seccion === 'tarjeta')
                    ? 'Subí una imagen 9:16 o un video (MP4) para la tarjeta.'
                    : 'Subí una imagen 9:16 o un video (MP4) para la noticia.';
            }
        }

        if ($error === '') {
            $nuevoId = novedad_crear($d);
            header('Location: ' . $_SERVER['REQUEST_URI'] . '?ok=1');
            exit;
        }
    }

    if ($accion === 'editar') {
        $id = (int)($_POST['id'] ?? 0);
        $actual = novedad_get($id);

        if (!$actual) {
            $error = 'No se encontró la novedad/tarjeta a editar.';
        } else {
            $payload = novedad_payload_decode((string)($actual['contenido'] ?? ''));

            $d = [
                'ncar_tag' => trim((string)($_POST['ncar_tag'] ?? ($actual['categoria'] ?? ''))),
                'ncar_title' => trim((string)($_POST['ncar_title'] ?? ($actual['titulo'] ?? ''))),
                'ncar_expert' => trim((string)($_POST['ncar_expert'] ?? ($payload['ncar_expert'] ?? ''))),
                'descripcion' => trim((string)($_POST['descripcion'] ?? ($payload['descripcion'] ?? ''))),
                'ncar_list' => trim((string)($_POST['ncar_list'] ?? (is_array($payload['ncar_list'] ?? null) ? implode("\n", $payload['ncar_list']) : ($payload['ncar_list'] ?? '')))),

                'imagen' => (string)($actual['imagen'] ?? ''),
                'video_url' => (string)($actual['video_url'] ?? ''),

                'tipo' => $tipo,
                'orden' => (int)($_POST['orden'] ?? ($actual['orden'] ?? 0)),
                'activo' => isset($_POST['activo']) ? 1 : 0,
            ];

            // Subida (solo del seccion correspondiente)
            if ($seccion === 'tarjeta') {
                if (!empty($_FILES['imagen']['tmp_name'])) {
                    if (!tm_is_ratio_9_16($_FILES['imagen'])) {
                        $error = 'La imagen de Tarjetas debe ser 9:16 (vertical).';
                    } else {
                        $nuevo = novedad_subir_imagen($_FILES['imagen']);
                        if ($nuevo !== '') $d['imagen'] = $nuevo;
                        else $error = 'No se pudo subir la imagen.';
                    }
                }
                if (!empty($_FILES['video_file']['tmp_name'])) {
                    $nuevo = novedad_subir_video($_FILES['video_file']);
                    if ($nuevo !== '') $d['video_url'] = $nuevo;
                    else $error = 'No se pudo subir el video (MP4).';
                }
            } else {
                if (!empty($_FILES['imagen_noticia']['tmp_name'])) {
                    if (!tm_is_ratio_9_16($_FILES['imagen_noticia'])) {
                        $error = 'La imagen de Noticias debe ser 9:16 (vertical).';
                    } else {
                        $nuevo = novedad_subir_imagen($_FILES['imagen_noticia']);
                        if ($nuevo !== '') $d['imagen'] = $nuevo;
                        else $error = 'No se pudo subir la imagen.';
                    }
                }
                if (!empty($_FILES['video_file_noticia']['tmp_name'])) {
                    $nuevo = novedad_subir_video($_FILES['video_file_noticia']);
                    if ($nuevo !== '') $d['video_url'] = $nuevo;
                    else $error = 'No se pudo subir el video (MP4).';
                }
            }

            // Validaciones
            if ($d['ncar_title'] === '') {
                $error = 'El campo ncar-title es obligatorio.';
            } else if ($seccion === 'tarjeta') {
                if ($d['descripcion'] === '') {
                    $error = 'El campo descripcion es obligatorio para Tarjetas.';
                } else if ($d['ncar_expert'] === '') {
                    $error = 'El campo ncar-expert es obligatorio para Tarjetas.';
                } else if (trim((string)$d['ncar_list']) === '') {
                    $error = 'El campo ncar-list es obligatorio (viñetas) para Tarjetas.';
                }
            } else {
                if ($d['descripcion'] === '' && $d['ncar_expert'] !== '') {
                    $d['descripcion'] = $d['ncar_expert'];
                }

                if ($d['ncar_expert'] === '') {
                    $error = 'El campo ncar-expert (descripcion) es obligatorio para Noticias.';
                } else if (trim((string)$d['ncar_list']) === '') {
                    $error = 'El campo ncar-list es obligatorio (viñetas) para Noticias.';
                }
            }

            $tieneImagen = !empty($d['imagen']);
            $tieneVideo = !empty($d['video_url']);
            if ($error === '') {
                if (!$tieneImagen && !$tieneVideo) {
                    $error = 'Debe existir una imagen 9:16 o un video (MP4).';
                }
            }

            if ($error === '') {
                novedad_editar($id, $d);
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
                exit;
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $actual = novedad_get($id);
        novedad_eliminar($id);

        if ($actual) {
            if (!empty($actual['imagen'])) {
                $ruta = __DIR__ . '/../' . $actual['imagen'];
                if (is_file($ruta)) unlink($ruta);
            }
            if (!empty($actual['video_url'])) {
                $ruta = __DIR__ . '/../' . $actual['video_url'];
                if (is_file($ruta)) unlink($ruta);
            }
        }

        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$todas = novedades_listar();
$tarjetas = array_values(array_filter($todas, fn($n) => tm_seccion_de_tipo((string)($n['tipo'] ?? '')) === 'tarjeta'));
$noticias = array_values(array_filter($todas, fn($n) => tm_seccion_de_tipo((string)($n['tipo'] ?? '')) === 'noticia'));

$total = count($todas);
$activas = count(array_filter($todas, fn($n) => (int)($n['activo'] ?? 0) === 1));
$inactivas = $total - $activas;

$portal_titulo = 'Novedades · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<?php if (!empty($error)): ?>
<div style="background:#c94f4f;color:#fff;padding:12px 18px;border-radius:10px;margin:18px 28px 0;font-weight:600;">
    ⚠️ <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= $total ?></div>
        <div class="stat-sub">publicaciones</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Activas</div>
        <div class="stat-value"><?= $activas ?></div>
        <div class="stat-sub">visibles</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Inactivas</div>
        <div class="stat-value"><?= $inactivas ?></div>
        <div class="stat-sub">ocultas</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div class="page-title">Multimedia / Tarjetas y Noticias</div>
            <div class="chip">🎬 Carrusel + Artículos</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('crear', null, 'tarjeta')" data-tooltip="Crear tarjeta para el carrusel">
                + Tarjeta
            </button>
            <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('crear', null, 'noticia')" data-tooltip="Crear noticia para la sección artículos">
                + Noticia
            </button>
        </div>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><?= $total ?> publicación<?= $total !== 1 ? 'es' : '' ?></div>
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
                    <th class="sortable" data-col="1">Sección<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Tag<span class="sort-icon"></span></th>
                    <th>Media (9:16)</th>
                    <th class="sortable" data-col="4">Orden<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="5">Activo<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($todas as $n): ?>
                    <?php
                        $titulo = $n['ncar_title'] ?? $n['titulo'] ?? '';
                        $tag = $n['ncar_tag'] ?? $n['categoria'] ?? '';
                        $tipoRow = tm_seccion_de_tipo((string)($n['tipo'] ?? ''));
                        $hasImg = !empty($n['imagen']);
                        $hasVid = !empty($n['video_url']);
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars((string)$titulo) ?></strong></td>
                        <td>
                            <span class="badge <?= $tipoRow === 'tarjeta' ? 'aprobado' : 'pendiente' ?>">
                                <?= $tipoRow === 'tarjeta' ? 'Tarjeta' : 'Noticia' ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars((string)$tag !== '' ? $tag : '-') ?></td>
                        <td class="actions-col">
                            <?php if ($hasImg): ?>
                                <img src="<?= b('/' . $n['imagen']) ?>" style="max-height:40px;border-radius:4px;" alt="">
                            <?php elseif ($hasVid): ?>
                                <span style="color:#94a3b8;">🎥 Video</span>
                            <?php else: ?>
                                <span style="color:#94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int)($n['orden'] ?? 0) ?></td>
                        <td>
                            <span class="badge <?= (int)($n['activo'] ?? 0) ? 'aprobado' : 'pendiente' ?>">
                                <?= (int)($n['activo'] ?? 0) ? 'Sí' : 'No' ?>
                            </span>
                        </td>
                        <td class="actions-col">
                            <div class="btn-actions">
                                <button class="btn-action btn-mod" data-tooltip="Editar"
                                    onclick='openModal("editar", <?= json_encode($n, JSON_UNESCAPED_UNICODE) ?>, "<?= htmlspecialchars($tipoRow) ?>")'>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/></svg>
                                </button>

                                <form method="post" style="flex:1;" onsubmit="return tmConfirmSubmit(this,'¿Eliminar esta publicación?');">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                                    <button type="submit" class="btn-action btn-del" data-tooltip="Eliminar" style="width:100%;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1 1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>
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
                <div>No hay tarjetas/noticias cargadas.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── MODAL ── -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="modalTitle">➕ Nueva tarjeta</div>
            <button class="edit-modal-close" onclick="closeModal()">✕</button>
        </div>

        <form action="<?= b('/admin/novedades.php') ?>" method="post" enctype="multipart/form-data" id="modalForm">
            <input type="hidden" name="accion" id="modalAccion" value="crear">
            <input type="hidden" name="id" id="modalId" value="">
            <input type="hidden" name="modal_seccion" id="modalSeccion" value="tarjeta">

            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">ncar-tag</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="ncar_tag" id="f-ncar_tag" placeholder="Ej: Tratamiento">
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">ncar-title *</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="ncar_title" id="f-ncar_title" >
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">ncar-expert (descripcion expert)</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="ncar_expert" id="f-ncar_expert" placeholder="Ej: Cámara hiperbárica">
                    </div>
                </div>

                <div class="edit-group full">
                    <div class="edit-label">descripcion</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="descripcion" id="f-descripcion" rows="3"></textarea>
                    </div>
                </div>

                <div class="edit-group full">
                    <div class="edit-label">ncar-list (viñetas) — 1 por línea</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="ncar_list" id="f-ncar_list" rows="3" placeholder="Ej:\n• ...\n• ..."></textarea>
                    </div>
                </div>

                <!-- Bloque TARJETAS -->
                <div class="edit-group full" id="blockTarjetasWrap">
                    <div class="edit-label">Tarjetas (se muestran en el carrusel)</div>

                    <div class="edit-input-wrap" style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Imagen 9:16</div>
                                <input class="edit-input" type="file" name="imagen" accept="image/*">
                            </div>
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Video (MP4)</div>
                                <input class="edit-input" type="file" name="video_file" accept="video/mp4">
                            </div>
                        </div>
                        <small style="color:#94a3b8;font-size:11px;">Para Tarjetas: imagen debe ser 9:16. El video debe ser vertical (9:16) idealmente.</small>
                    </div>
                </div>

                <!-- Bloque NOTICIAS -->
                <div class="edit-group full" id="blockNoticiasWrap">
                    <div class="edit-label">Noticias (se muestran en la sección artículos)</div>

                    <div class="edit-input-wrap" style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Imagen 9:16</div>
                                <input class="edit-input" type="file" name="imagen_noticia" accept="image/*">
                            </div>
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Video 9:16 (MP4)</div>
                                <input class="edit-input" type="file" name="video_file_noticia" accept="video/mp4">
                            </div>
                        </div>
                        <small style="color:#94a3b8;font-size:11px;">Para Noticias: imagen debe ser 9:16. El video debe ser 9:16 (idealmente vertical).</small>
                    </div>
                </div>

                <!-- Preview media (se llena con lo que haya en la BD al editar) -->
                <div class="edit-group full">
                    <div class="edit-label">Previsualización</div>

                    <div class="edit-input-wrap">
                        <div id="imgPreviewWrap" style="display:none;margin-bottom:10px;">
                            <img id="imgPreview" src="" style="max-height:120px;border-radius:8px;">
                        </div>
                        <div id="videoPreviewWrap" style="display:none;margin-bottom:10px;">
                            <video id="videoPreview" src="" style="max-height:120px;border-radius:8px;" controls muted></video>
                        </div>
                    </div>
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
<div id="toast-ok" style="position:fixed;right:20px;bottom:20px;z-index:9999;background:linear-gradient(135deg,#059669,#10b981);color:#fff;padding:12px 18px;border-radius:12px;font-weight:700;display:flex;align-items:center;gap:10px;box-shadow:0 12px 30px rgba(0,0,0,0.22);opacity:1;transform:translateY(0);transition:opacity 0.3s ease, transform 0.3s ease;">
    ✅ Guardado exitosamente
</div>

<script>
(function() {
    // Toast
    if (new URLSearchParams(location.search).get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        if (t) {
            setTimeout(function () {
                t.style.opacity = '0';
                t.style.transform = 'translateY(10px)';
            }, 4800);

            setTimeout(function () {
                try { t.remove(); } catch (e) {}
            }, 5200);

            setTimeout(function () {
                history.replaceState({}, '', location.pathname);
            }, 5200);
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

    function setBlocks(seccion) {
        var tar = document.getElementById('blockTarjetasWrap');
        var not = document.getElementById('blockNoticiasWrap');
        if (tar) tar.style.display = (seccion === 'tarjeta') ? '' : 'none';
        if (not) not.style.display = (seccion === 'noticia') ? '' : 'none';
    }

    // Modal
    window.openModal = function(mode, data, seccion) {
        var modalSeccion = seccion || (data && data.tipo === 'noticia' ? 'noticia' : 'tarjeta');

        document.getElementById('modalAccion').value = mode === 'editar' ? 'editar' : 'crear';
        document.getElementById('modalId').value = data && data.id ? data.id : '';
        document.getElementById('modalSeccion').value = modalSeccion;

        var modalTitle = document.getElementById('modalTitle');
        var submitBtn = document.getElementById('modalSubmitBtn');

        if (modalSeccion === 'noticia') {
            modalTitle.textContent = mode === 'editar' ? '📰 Editar noticia' : '📰 Nueva noticia';
        } else {
            modalTitle.textContent = mode === 'editar' ? '✏️ Editar tarjeta' : '➕ Nueva tarjeta';
        }

        submitBtn.textContent = mode === 'editar'
            ? '💾 Guardar cambios'
            : (modalSeccion === 'noticia' ? '💾 Crear noticia' : '💾 Crear tarjeta');

        // Reset texto
        document.getElementById('f-ncar_tag').value = '';
        document.getElementById('f-ncar_title').value = '';
        document.getElementById('f-ncar_expert').value = '';
        document.getElementById('f-descripcion').value = '';
        document.getElementById('f-ncar_list').value = '';

        // Reset media previews
        var imgWrap = document.getElementById('imgPreviewWrap');
        var img = document.getElementById('imgPreview');
        if (imgWrap) imgWrap.style.display = 'none';
        if (img) img.src = '';

        var videoWrap = document.getElementById('videoPreviewWrap');
        var video = document.getElementById('videoPreview');
        if (videoWrap) videoWrap.style.display = 'none';
        if (video) video.src = '';

        // Reset checkbox
        // (el input activo vive en el viejo HTML; acá no la incluimos, así que dejamos activo siempre en backend)
        // Para evitar romper el layout si algo quedó, no tocamos.

        // Set bloques según seccion
        setBlocks(modalSeccion);

        if (mode === 'editar' && data) {
            document.getElementById('f-ncar_tag').value = data.categoria || '';
            document.getElementById('f-ncar_title').value = data.titulo || '';

            document.getElementById('f-ncar_list').value = '';

            var payloadOk = false;
            var payload = {};
            if (typeof data.contenido === 'string') {
                try {
                    payload = JSON.parse(data.contenido);
                    payloadOk = payload && typeof payload === 'object';
                } catch (e) {
                    payloadOk = false;
                }
            }

            if (payloadOk) {
                document.getElementById('f-ncar_expert').value = payload.ncar_expert || '';
                document.getElementById('f-descripcion').value = payload.descripcion || '';
                if (Array.isArray(payload.ncar_list)) {
                    document.getElementById('f-ncar_list').value = payload.ncar_list.join('\n');
                }
            } else {
                document.getElementById('f-descripcion').value = data.contenido || '';
            }

            if (data.imagen) {
                if (imgWrap) imgWrap.style.display = 'block';
                if (img) img.src = '<?= b('/') ?>' + data.imagen;
            }

            if (data.video_url) {
                if (videoWrap) videoWrap.style.display = 'block';
                if (video) video.src = '<?= b('/') ?>' + data.video_url;
            }
        }

        document.getElementById('editModal').classList.add('open');
        document.getElementById('modalForm').setAttribute('data-opened', '1');
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
