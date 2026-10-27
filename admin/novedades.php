<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_novedades.php';
require_once __DIR__ . '/../includes/media_vertical.php';
portal_require_role(['admin']);

$mensaje = '';
$error = '';
$reabrir = null; // datos para reabrir el modal si falla una validación

function tm_seccion_de_tipo(?string $tipo): string {
    return novedad_normalizar_tipo($tipo);
}

// ── Acciones POST ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $seccion = tm_seccion_de_tipo((string)($_POST['modal_seccion'] ?? 'tarjeta'));
    $etiqueta = $seccion === 'noticia' ? 'la noticia' : 'la novedad';

    if ($accion === 'crear' || $accion === 'editar') {
        $id = (int)($_POST['id'] ?? 0);
        $actual = ($accion === 'editar') ? novedad_get($id) : null;

        if ($accion === 'editar' && !$actual) {
            $error = 'No se encontró la publicación a editar.';
        } else {
            $d = [
                'ncar_tag'    => trim((string)($_POST['ncar_tag'] ?? '')),
                'ncar_title'  => trim((string)($_POST['ncar_title'] ?? '')),
                'ncar_expert' => trim((string)($_POST['ncar_expert'] ?? '')),
                'ncar_list'   => $seccion === 'tarjeta' ? trim((string)($_POST['ncar_list'] ?? '')) : '',
                'imagen'      => (string)($actual['imagen'] ?? ''),
                'video_url'   => (string)($actual['video_url'] ?? ''),
                'tipo'        => $seccion,
                'orden'       => (int)($_POST['orden'] ?? 0),
                'activo'      => isset($_POST['activo']) ? 1 : 0,
            ];

            $imgNueva = !empty($_FILES['imagen']['tmp_name']);
            $vidNuevo = !empty($_FILES['video_file']['tmp_name']);

            // 1) Validar texto
            if ($d['ncar_title'] === '') {
                $error = 'El título es obligatorio.';
            } elseif ($d['ncar_expert'] === '') {
                $error = 'El extracto es obligatorio.';
            } elseif ($seccion === 'tarjeta' && novedad_lista_a_array($d['ncar_list']) === []) {
                $error = 'La lista es obligatoria (una viñeta por línea).';
            }

            // 2) Validar archivos antes de moverlos
            if ($error === '' && $imgNueva) {
                $errSubida = novedad_upload_error($_FILES['imagen']);
                if ($errSubida !== '') {
                    $error = 'Imagen: ' . $errSubida;
                } elseif (tm_imagen_dimensiones_visibles((string)$_FILES['imagen']['tmp_name']) === null) {
                    $error = 'No se pudo leer la imagen. Usá JPG, PNG o WEBP.';
                }
            }
            if ($error === '' && $vidNuevo) {
                $errSubida = novedad_upload_error($_FILES['video_file']);
                if ($errSubida !== '') {
                    $error = 'Video: ' . $errSubida;
                }
            }

            // 3) Debe quedar al menos un medio
            if ($error === '' && !$imgNueva && !$vidNuevo && $d['imagen'] === '' && $d['video_url'] === '') {
                $error = 'Subí una imagen 9:16 o un video MP4 para ' . $etiqueta . '.';
            }

            // 4) Subir
            $viejaImagen = $d['imagen'];
            $viejoVideo  = $d['video_url'];
            if ($error === '' && $imgNueva) {
                $nuevo = novedad_subir_imagen($_FILES['imagen']);
                if ($nuevo === '') {
                    $error = tm_media_error() ?: 'No se pudo subir la imagen (JPG, PNG o WEBP, hasta 5 MB).';
                } else {
                    $d['imagen'] = $nuevo;
                }
            }
            if ($error === '' && $vidNuevo) {
                $nuevo = novedad_subir_video($_FILES['video_file']);
                if ($nuevo === '') {
                    // Si la imagen recién subida quedó sin usar, se borra (si otra publicación la usa, se conserva).
                    if ($imgNueva) novedad_liberar_archivo($d['imagen']);
                    $error = tm_media_error() ?: 'No se pudo subir el video (MP4, hasta 80 MB).';
                } else {
                    $d['video_url'] = $nuevo;
                }
            }

            // 5) Guardar
            if ($error === '') {
                if ($accion === 'crear') {
                    novedad_crear($d);
                } else {
                    novedad_editar($id, $d);
                    if ($imgNueva) novedad_liberar_archivo($viejaImagen);
                    if ($vidNuevo) novedad_liberar_archivo($viejoVideo);
                }
                $conv = tm_media_convertido();
                $reu = novedad_reutilizado();
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1'
                    . ($conv ? '&conv=' . implode(',', $conv) : '')
                    . ($reu ? '&reuse=' . implode(',', $reu) : ''));
                exit;
            }

            // Falló: se reabre el modal con lo que escribió el usuario
            $reabrir = [
                'mode'    => $accion,
                'seccion' => $seccion,
                'data'    => [
                    'id'        => $id,
                    'tipo'      => $seccion,
                    'titulo'    => $d['ncar_title'],
                    'categoria' => $d['ncar_tag'],
                    'contenido' => novedad_payload_encode($d),
                    'imagen'    => (string)($actual['imagen'] ?? ''),
                    'video_url' => (string)($actual['video_url'] ?? ''),
                    'orden'     => $d['orden'],
                    'activo'    => $d['activo'],
                ],
            ];
        }
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        $actual = novedad_get($id);
        novedad_eliminar($id);

        if ($actual) {
            novedad_liberar_archivo($actual['imagen'] ?? '');
            novedad_liberar_archivo($actual['video_url'] ?? '');
        }

        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$todas = novedades_listar();

$total = count($todas);
$activas = count(array_filter($todas, fn($n) => (int)($n['activo'] ?? 0) === 1));
$inactivas = $total - $activas;
$nTarjetas = count(array_filter($todas, fn($n) => tm_seccion_de_tipo((string)($n['tipo'] ?? '')) === 'tarjeta'));
$nNoticias = $total - $nTarjetas;

$videoConvierte = tm_video_conversion_disponible();
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
        <div class="stat-sub"><?= $nTarjetas ?> novedades · <?= $nNoticias ?> noticias</div>
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
            <div class="page-title"> Novedades y Noticias</div>
            <div class="chip"> pagina principal</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;"
                onclick="openModal('crear', null, 'tarjeta')"
                data-tooltip="Crear una novedad para el carrusel del inicio (#novedades)">
                + Publicar novedades
            </button>
            <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px; background:var(--navy);"
                onclick="openModal('crear', null, 'noticia')"
                data-tooltip="Crear una noticia para la sección artículos (#articulos)">
                + Publicar noticias
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
                        $titulo = (string)($n['titulo'] ?? '');
                        $tag = (string)($n['categoria'] ?? '');
                        $tipoRow = tm_seccion_de_tipo((string)($n['tipo'] ?? ''));
                        $hasImg = !empty($n['imagen']);
                        $hasVid = !empty($n['video_url']);
                        $rowJson = json_encode($n, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($titulo) ?></strong></td>
                        <td>
                            <span class="badge <?= $tipoRow === 'tarjeta' ? 'aprobado' : 'pendiente' ?>">
                                <?= $tipoRow === 'tarjeta' ? 'Novedad' : 'Noticia' ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($tag !== '' ? $tag : '-') ?></td>
                        <td>
                            <?php if ($hasImg): ?>
                                <img src="<?= b('/' . $n['imagen']) ?>" style="max-height:40px;border-radius:4px;" alt="">
                            <?php endif; ?>
                            <?php if ($hasVid): ?>
                                <span style="color:#94a3b8;">🎥 Video</span>
                            <?php endif; ?>
                            <?php if (!$hasImg && !$hasVid): ?>
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
                                    data-row="<?= htmlspecialchars((string)$rowJson, ENT_QUOTES) ?>"
                                    data-seccion="<?= htmlspecialchars($tipoRow) ?>"
                                    onclick="openModalFromRow(this)">
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
                <div>No hay novedades ni noticias cargadas.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── MODAL (un formulario por botón: Novedades o Noticias) ── -->
<div class="edit-modal-overlay" id="editModal">
    <div class="edit-modal" style="max-width:680px;">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="modalTitle"> Nueva publicación</div>
            <!-- <button class="edit-modal-close" onclick="closeModal()">✕xxx</button> -->
            <button class="edit-modal-close"
                style="background:none;border:none;font-size:24px;cursor:pointer;color:#888;padding:4px;"
                onclick="closeModal('')">
                <svg height="26" width="26" version="1.1" id="Layer_1"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512">
                    <circle style="fill:#FF6643;" cx="256" cy="256" r="256" />
                    <path style="fill:#FF6643;"
                        d="M256,0v512c141.385,0,256-114.615,256-256S397.385,0,256,0z" />
                    <polygon style="fill:#ffffff;"
                        points="365.904,184.885 327.115,146.096 256,217.211 184.885,146.096 146.096,184.885 217.211,256
                            146.096,327.115 184.885,365.904 256,294.789 327.115,365.904 365.904,327.115 294.789,256 " />
                </svg>
            </button>
        </div>
        <div id="modalHint" style="color:#cbd5e1;font-size:12px;margin:10px 0 0;">
            <?= $videoConvierte
                ? 'Las imágenes y los videos que no sean 9:16 se convierten solos.'
                : 'Las imágenes que no sean 9:16 se convierten solas. Este servidor no convierte videos: subí el video ya en 9:16.' ?>
        </div>

        <!-- ═════════ PARTE 1: NOVEDADES (carrusel, section id="novedades") ═════════ -->
        <form action="<?= b('/admin/novedades.php') ?>" method="post" enctype="multipart/form-data"
            id="formTarjeta" class="nv-form" data-seccion="tarjeta">
            <input type="hidden" name="accion" value="crear" data-role="accion">
            <input type="hidden" name="id" value="" data-role="id">
            <input type="hidden" name="modal_seccion" value="tarjeta">

            <div class="edit-section-title" style="margin:18px 0 4px;font-weight:900;color:var(--g100);">
                Novedades · carrusel del inicio
            </div>
            <div style="color:#94a3b8;font-size:11px;margin-bottom:6px;">
                Se muestran en la sección #novedades de la página principal.
            </div>

            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Encabezado</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="ncar_tag" maxlength="50" placeholder="Ej: Tratamiento">
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">Titulo</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="ncar_title" maxlength="200" required>
                    </div>
                </div>

                <div class="edit-group full">
                    <div class="edit-label">Extracto</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="ncar_expert" rows="3" required></textarea>
                    </div>
                </div>

                <div class="edit-group full">
                    <div class="edit-label"> Lista ( genera una viñeta por línea)</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="ncar_list" rows="4" required
                            placeholder="Acelera la cicatrización&#10;Mejora la circulación"></textarea>
                    </div>
                </div>

                <div class="edit-group full">
                    <div class="edit-label">Imagen y video 9:16</div>
                    <div class="edit-input-wrap" style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Imagen max 5 MB. Si no es 9:16 se convierte.</div>
                                <input class="edit-input" type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-check="image">
                            </div>
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Video MP4 (80 MB). <?= $videoConvierte ? 'Si no es 9:16 se convierte.' : 'Debe ser 9:16.' ?></div>
                                <input class="edit-input" type="file" name="video_file" accept="video/mp4" data-check="video">
                            </div>
                        </div>
                        <div class="nv-file-error" style="display:none;font-size:12px;font-weight:600;"></div>
                    </div>
                </div>

                <div class="edit-group full nv-preview" style="display:none;">
                    <div class="edit-label">Archivo actual</div>
                    <div class="edit-input-wrap" style="display:flex;gap:12px;flex-wrap:wrap;">
                        <img class="nv-img" src="" alt="" style="display:none;max-height:140px;border-radius:8px;">
                        <video class="nv-video" src="" style="display:none;max-height:140px;border-radius:8px;" controls muted></video>
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">Orden</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="number" name="orden" value="0" step="1">
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">Visible en el sitio</div>
                    <div class="edit-input-wrap">
                        <label style="display:flex;align-items:center;gap:8px;color:var(--g100);font-size:14px;">
                            <input type="checkbox" name="activo" value="1" checked> Activo
                        </label>
                    </div>
                </div>
            </div>

            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-edit-save" data-role="submit">💾 Publicar novedad</button>
            </div>
        </form>

        <div id="modalDivider" style="border-top:1.5px dashed rgba(255,255,255,0.25);margin:26px 0 4px;"></div>

        <!-- ═════════ PARTE 2: NOTICIAS (section id="articulos") ═════════ -->
        <form action="<?= b('/admin/novedades.php') ?>" method="post" enctype="multipart/form-data"
            id="formNoticia" class="nv-form" data-seccion="noticia">
            <input type="hidden" name="accion" value="crear" data-role="accion">
            <input type="hidden" name="id" value="" data-role="id">
            <input type="hidden" name="modal_seccion" value="noticia">

            <div class="edit-section-title" style="margin:18px 0 4px;font-weight:900;color:var(--g100);">
                Noticias
            </div>
            <div style="color:#94a3b8;font-size:11px;margin-bottom:6px;">
                Se muestran en la sección #articulos de la página principal. Imagen o video en formato 9:16.
            </div>

            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Categoría</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="ncar_tag" maxlength="50" placeholder="Ej: Heridas">
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">Título *</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="text" name="ncar_title" maxlength="200" required>
                    </div>
                </div>

                <div class="edit-group full">
                    <div class="edit-label">Extracto *</div>
                    <div class="edit-input-wrap">
                        <textarea class="edit-input" name="ncar_expert" rows="3" required></textarea>
                    </div>
                </div>

                <div class="edit-group full">
                    <div class="edit-label">Imagen y video 9:16</div>
                    <div class="edit-input-wrap" style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Imagen (JPG, PNG, WEBP · 5 MB). Si no es 9:16 se convierte.</div>
                                <input class="edit-input" type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-check="image">
                            </div>
                            <div style="min-width:220px;flex:1;">
                                <div style="color:#94a3b8;font-size:12px;margin-bottom:6px;">Video MP4 (80 MB). <?= $videoConvierte ? 'Si no es 9:16 se convierte.' : 'Debe ser 9:16.' ?></div>
                                <input class="edit-input" type="file" name="video_file" accept="video/mp4" data-check="video">
                            </div>
                        </div>
                        <div class="nv-file-error" style="display:none;font-size:12px;font-weight:600;"></div>
                    </div>
                </div>

                <div class="edit-group full nv-preview" style="display:none;">
                    <div class="edit-label">Archivo actual</div>
                    <div class="edit-input-wrap" style="display:flex;gap:12px;flex-wrap:wrap;">
                        <img class="nv-img" src="" alt="" style="display:none;max-height:140px;border-radius:8px;">
                        <video class="nv-video" src="" style="display:none;max-height:140px;border-radius:8px;" controls muted></video>
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">Orden</div>
                    <div class="edit-input-wrap">
                        <input class="edit-input" type="number" name="orden" value="0" step="1">
                    </div>
                </div>

                <div class="edit-group">
                    <div class="edit-label">Visible en el sitio</div>
                    <div class="edit-input-wrap">
                        <label style="display:flex;align-items:center;gap:8px;color:var(--g100);font-size:14px;">
                            <input type="checkbox" name="activo" value="1" checked> Activo
                        </label>
                    </div>
                </div>
            </div>

            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn-edit-save" data-role="submit">💾 Publicar noticia</button>
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
    var BASE = '<?= b('/') ?>';
    var RATIO = 9 / 16;
    var TOL = 0.03;
    var VIDEO_CONVIERTE = <?= $videoConvierte ? 'true' : 'false' ?>;
    var MODO_TXT = <?= TM_MEDIA_MODO === 'recorte' ? "'recortando los bordes al centro'" : "'sobre un fondo desenfocado'" ?>;

    // Toast (solo si volvió de guardar)
    var toast = document.getElementById('toast-ok');
    if (new URLSearchParams(location.search).get('ok') === '1') {
        if (toast) {
            var qs = new URLSearchParams(location.search);
            var conv = (qs.get('conv') || '').split(',').filter(Boolean);
            var reuse = (qs.get('reuse') || '').split(',').filter(Boolean);
            if (conv.length || reuse.length) {
                var nom = function(t) { return t === 'video' ? 'El video' : 'La imagen'; };
                var partes = [];
                conv.forEach(function(t) { partes.push(nom(t) + ' se convirtió a 9:16.'); });
                reuse.forEach(function(t) { partes.push(nom(t) + ' ya estaba en el servidor y se reutilizó (no se grabó otra copia).'); });
                toast.textContent = '✅ Guardado. ' + partes.join(' ');
            }
            setTimeout(function () {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
            }, 4800);
            setTimeout(function () { try { toast.remove(); } catch (e) {} }, 5200);
            setTimeout(function () { history.replaceState({}, '', location.pathname); }, 5200);
        }
    } else if (toast) {
        toast.remove();
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

    // ── Aviso 9:16 en el navegador (imagen y video) ──
    // 'error' = rojo, 'info' = verde. Solo se rechaza lo que el servidor no puede procesar.
    function mostrarErrorArchivo(form, msg, tipo) {
        var box = form.querySelector('.nv-file-error');
        if (!box) return;
        box.textContent = msg || '';
        box.style.color = tipo === 'info' ? '#86efac' : '#fca5a5';
        box.style.display = msg ? 'block' : 'none';
    }

    function ratioOk(w, h) {
        if (!w || !h) return false;
        return Math.abs((w / h) - RATIO) <= RATIO * TOL;
    }

    document.querySelectorAll('input[type=file][data-check]').forEach(function(inp) {
        inp.addEventListener('change', function() {
            var form = inp.closest('form');
            mostrarErrorArchivo(form, '');
            var f = inp.files && inp.files[0];
            if (!f) return;

            var url = URL.createObjectURL(f);
            var rechazar = function(msg) {
                inp.value = '';
                mostrarErrorArchivo(form, msg, 'error');
                URL.revokeObjectURL(url);
            };

            if (inp.dataset.check === 'image') {
                var im = new Image();
                im.onload = function() {
                    if (!ratioOk(im.naturalWidth, im.naturalHeight)) {
                        mostrarErrorArchivo(form, 'La imagen mide ' + im.naturalWidth + '×' + im.naturalHeight +
                            '. Se convertirá a 9:16 ' + MODO_TXT + '.', 'info');
                    }
                    URL.revokeObjectURL(url);
                };
                im.onerror = function() { rechazar('No se pudo leer la imagen.'); };
                im.src = url;
            } else {
                var v = document.createElement('video');
                v.preload = 'metadata';
                v.onloadedmetadata = function() {
                    if (!ratioOk(v.videoWidth, v.videoHeight)) {
                        var medida = 'El video mide ' + v.videoWidth + '×' + v.videoHeight + '. ';
                        if (VIDEO_CONVIERTE) {
                            mostrarErrorArchivo(form, medida + 'Se convertirá a 9:16 ' + MODO_TXT +
                                '. Puede tardar unos minutos al guardar.', 'info');
                            URL.revokeObjectURL(url);
                        } else {
                            rechazar(medida + 'Este servidor no convierte videos. Subilo ya en 9:16 (vertical).');
                        }
                    } else { URL.revokeObjectURL(url); }
                };
                v.onerror = function() { rechazar('No se pudo leer el video.'); };
                v.src = url;
            }
        });
    });

    // ── Modal ──
    function formDe(seccion) {
        return document.getElementById(seccion === 'noticia' ? 'formNoticia' : 'formTarjeta');
    }

    function limpiarForm(form) {
        form.reset();
        form.querySelector('[data-role=accion]').value = 'crear';
        form.querySelector('[data-role=id]').value = '';
        form.querySelector('[name=orden]').value = '0';
        form.querySelector('[name=activo]').checked = true;
        mostrarErrorArchivo(form, '');

        var prev = form.querySelector('.nv-preview');
        var img = form.querySelector('.nv-img');
        var vid = form.querySelector('.nv-video');
        if (prev) prev.style.display = 'none';
        if (img) { img.style.display = 'none'; img.removeAttribute('src'); }
        if (vid) { vid.style.display = 'none'; vid.removeAttribute('src'); }
    }

    function rellenarForm(form, data) {
        form.querySelector('[data-role=accion]').value = 'editar';
        form.querySelector('[data-role=id]').value = data.id || '';
        form.querySelector('[name=ncar_tag]').value = data.categoria || '';
        form.querySelector('[name=ncar_title]').value = data.titulo || '';
        form.querySelector('[name=orden]').value = parseInt(data.orden || 0, 10);
        form.querySelector('[name=activo]').checked = String(data.activo) !== '0';

        var payload = {};
        if (typeof data.contenido === 'string') {
            try { payload = JSON.parse(data.contenido) || {}; }
            catch (e) { payload = { descripcion: data.contenido }; }
        }
        if (typeof payload !== 'object' || Array.isArray(payload)) payload = {};

        form.querySelector('[name=ncar_expert]').value = payload.ncar_expert || payload.descripcion || '';
        var lista = form.querySelector('[name=ncar_list]');
        if (lista) lista.value = Array.isArray(payload.ncar_list) ? payload.ncar_list.join('\n') : '';

        var prev = form.querySelector('.nv-preview');
        var img = form.querySelector('.nv-img');
        var vid = form.querySelector('.nv-video');
        var hay = false;
        if (data.imagen && img) { img.src = BASE + data.imagen; img.style.display = 'block'; hay = true; }
        if (data.video_url && vid) { vid.src = BASE + data.video_url; vid.style.display = 'block'; hay = true; }
        if (prev) prev.style.display = hay ? '' : 'none';
    }

    window.openModal = function(mode, data, seccion) {
        var editar = mode === 'editar' && data;
        var sec = seccion || (data && data.tipo === 'noticia' ? 'noticia' : 'tarjeta');

        var fT = formDe('tarjeta');
        var fN = formDe('noticia');
        limpiarForm(fT);
        limpiarForm(fN);

        var title = document.getElementById('modalTitle');
        var hint = document.getElementById('modalHint');
        var divider = document.getElementById('modalDivider');

        // Con sección definida (editar, o uno de los dos botones de publicar) se ve solo ese formulario.
        // Sin sección se muestran los dos, uno debajo del otro.
        var unica = editar || !!seccion;

        if (unica) {
            fT.style.display = (sec === 'tarjeta') ? '' : 'none';
            fN.style.display = (sec === 'noticia') ? '' : 'none';
            divider.style.display = 'none';

            var f = formDe(sec);
            if (editar) {
                title.textContent = sec === 'noticia' ? ' Editar noticia' : ' Editar novedad';
                rellenarForm(f, data);
                f.querySelector('[data-role=submit]').textContent = '💾 Guardar cambios';
            } else {
                title.textContent = sec === 'noticia' ? ' Nueva noticia' : ' Nueva novedad';
                f.querySelector('[data-role=submit]').textContent = sec === 'noticia' ? ' Publicar noticia' : ' Publicar novedad';
            }
        } else {
            fT.style.display = '';
            fN.style.display = '';
            divider.style.display = '';
            title.textContent = ' Nueva publicación';
            fT.querySelector('[data-role=submit]').textContent = ' Publicar novedad';
            fN.querySelector('[data-role=submit]').textContent = ' Publicar noticia';
        }
        hint.style.display = editar ? 'none' : '';

        document.getElementById('editModal').classList.add('open');
    };

    window.openModalFromRow = function(btn) {
        var data = {};
        try { data = JSON.parse(btn.getAttribute('data-row') || '{}'); } catch (e) {}
        openModal('editar', data, btn.getAttribute('data-seccion'));
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

<?php if ($reabrir): ?>
    // Falló una validación: se reabre el modal con lo escrito
    (function() {
        var re = <?= json_encode($reabrir, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        if (re.mode === 'editar') {
            openModal('editar', re.data, re.seccion);
        } else {
            openModal('crear', null, re.seccion);
            var f = formDe(re.seccion);
            rellenarForm(f, re.data);
            f.querySelector('[data-role=accion]').value = 'crear';
            f.querySelector('[data-role=id]').value = '';
            f.querySelector('[data-role=submit]').textContent = re.seccion === 'noticia' ? '💾 Publicar noticia' : '💾 Publicar novedad';
            var prev = f.querySelector('.nv-preview');
            if (prev) prev.style.display = 'none';
            f.scrollIntoView({ block: 'start' });
        }
    })();
<?php endif; ?>

})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
