<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_config.php';
portal_require_role(['admin']);

$mensaje = '';
$error = '';

// =========================
// 1) Guardar VIDEO HERO (MP4)
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hero_video_submit'])) {
    $file = $_FILES['hero_video_file'] ?? null;

    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'No se recibió ningún archivo (o hubo un error al subirlo).';
    } else {
        $origName = (string)($file['name'] ?? '');
        $tmpPath = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['mp4'];

        $maxBytes = 50 * 1024 * 1024; // 50MB

        if (!in_array($ext, $allowed, true)) {
            $error = 'Formato inválido. Solo se permite MP4.';
        } elseif ($size <= 0) {
            $error = 'El archivo está vacío o no se pudo leer.';
        } elseif ($size > $maxBytes) {
            $error = 'El archivo es demasiado grande. Máximo 50MB.';
        } else {
            $destDirAbs = __DIR__ . '/../static/video';
            if (!is_dir($destDirAbs)) {
                $error = 'El directorio de destino para videos no existe.';
            } else {
                $newName = 'hero_video_' . date('Ymd_His') . '.mp4';
                $destRel = '/static/video/' . $newName;
                $destAbs = __DIR__ . '/../' . ltrim($destRel, '/');

                if (!move_uploaded_file($tmpPath, $destAbs)) {
                    $error = 'No se pudo guardar el archivo en el servidor.';
                } else {
                    config_set('hero_video_path', $destRel);
                    $mensaje = 'Video del hero actualizado.';
                }
            }
        }
    }
}

// =========================
// 2) Guardar OTROS AJUSTES
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_config_submit'])) {
    // Logo
    $logoFile = $_FILES['site_logo_file'] ?? null;
    if ($logoFile && isset($logoFile['error']) && $logoFile['error'] === UPLOAD_ERR_OK) {
        $origName = (string)($logoFile['name'] ?? '');
        $tmpPath = (string)($logoFile['tmp_name'] ?? '');
        $size = (int)($logoFile['size'] ?? 0);

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $maxBytes = 10 * 1024 * 1024; // 10MB

        if (!in_array($ext, $allowedExt, true)) {
            $error = 'Formato de logo inválido. Usá JPG, PNG, GIF o WEBP.';
        } elseif ($size <= 0 || $size > $maxBytes) {
            $error = 'El archivo de logo es inválido o supera el tamaño máximo (10MB).';
        } else {
            $destDirAbs = __DIR__ . '/../static/img/logos';
            // Si no existe carpeta, caemos a static/img.
            if (!is_dir($destDirAbs)) {
                $destDirAbs = __DIR__ . '/../static/img';
            }

            $newName = 'site_logo_' . date('Ymd_His') . '.' . $ext;
            $destRel = '/static/img/logos/' . $newName;

            // Si se guardó en static/img (no /logos), ajustamos la ruta.
            $destAbs = __DIR__ . '/../' . ltrim($destRel, '/');
            if (!is_dir(__DIR__ . '/../static/img/logos')) {
                $destRel = '/static/img/' . $newName;
                $destAbs = __DIR__ . '/../' . ltrim($destRel, '/');
            }

            if (!move_uploaded_file($tmpPath, $destAbs)) {
                $error = 'No se pudo guardar el logo en el servidor.';
            } else {
                config_set('site_logo_path', $destRel);
            }
        }
    }

    // Textos/URLs
    $contactoTelefono = trim((string)($_POST['contact_telefono'] ?? ''));
    $contactoDireccion = trim((string)($_POST['contact_direccion'] ?? ''));
    $contactoHorarios = trim((string)($_POST['contact_horarios'] ?? ''));
    $waNumero = trim((string)($_POST['contact_whatsapp_number'] ?? ''));

    $socialInstagram = trim((string)($_POST['social_instagram_url'] ?? ''));
    $socialFacebook = trim((string)($_POST['social_facebook_url'] ?? ''));

    if ($contactoTelefono !== '') {
        config_set('contact_telefono', $contactoTelefono);
    }
    if ($contactoDireccion !== '') {
        config_set('contact_direccion', $contactoDireccion);
    }
    if ($contactoHorarios !== '') {
        config_set('contact_horarios', $contactoHorarios);
    }

    if ($waNumero !== '') {
        // Permitimos + y dígitos, pero guardamos solo dígitos para wa.me
        $waClean = preg_replace('/[^0-9]/', '', $waNumero);
        if (!$waClean) {
            $error = 'El número de WhatsApp no es válido.';
        } else {
            config_set('contact_whatsapp_number', $waClean);
        }
    }

    if ($socialInstagram !== '') {
        if (filter_var($socialInstagram, FILTER_VALIDATE_URL)) {
            config_set('social_instagram_url', $socialInstagram);
        } else {
            $error = 'La URL de Instagram no es válida.';
        }
    }

    if ($socialFacebook !== '') {
        if (filter_var($socialFacebook, FILTER_VALIDATE_URL)) {
            config_set('social_facebook_url', $socialFacebook);
        } else {
            $error = 'La URL de Facebook no es válida.';
        }
    }

    if (!$error) {
        $mensaje = 'Configuración del sitio actualizada.';
    }
}

$hero_video_path = @config_get('hero_video_path') ?: '';
$hero_video_ok = false;
if ($hero_video_path) {
    $local = __DIR__ . '/../' . ltrim($hero_video_path, '/');
    $hero_video_ok = is_file($local) && is_readable($local);
}

$site_logo_path = @config_get('site_logo_path') ?: '';
$site_logo_ok = false;
if ($site_logo_path) {
    $local = __DIR__ . '/../' . ltrim($site_logo_path, '/');
    $site_logo_ok = is_file($local) && is_readable($local);
}

$contact_telefono = @config_get('contact_telefono') ?: '(3794) 34-9278';
$contact_direccion = @config_get('contact_direccion') ?: 'C. Pellegrini 799, Corrientes';
$contact_horarios = @config_get('contact_horarios') ?: 'Lun–Vie 8:00–18:00 · Sáb 9:00–13:00';
$contact_whatsapp_number = @config_get('contact_whatsapp_number') ?: '5493794775341';

$social_instagram_url = @config_get('social_instagram_url') ?: 'https://www.instagram.com/tecnomedicsalud_/';
$social_facebook_url = @config_get('social_facebook_url') ?: 'https://www.facebook.com/';

$portal_titulo = 'Configuración · Mi Portal';
$portal_activo = 'multimedia';
require __DIR__ . '/../includes/portal_header.php';
?>

<!-- <div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Video</div>
        <div class="stat-value" style="font-size:20px;word-break:break-all;">
            <?= $hero_video_ok ? '<span style="color:#10b981;">✔</span>' : '<span style="color:#f59e0b;">—</span>' ?>
        </div>
        <div class="stat-sub"><?= $hero_video_ok ? 'Configurado' : 'Sin configurar' ?></div>
    </div>
</div> -->

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Config del sitio</div>
            <div class="chip"><img class="tm-thumb" src="<?= $base ?>/static/img/icons/multimedia.ico" alt="" /> Multimedia</div>
        </div>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title"><img class="tm-thumb" src="<?= $base ?>/static/img/icons/multimedia.ico" alt="" /> Video del Hero</div>
    </div>
    <div style="padding:20px 24px;">

        <?php if ($mensaje): ?>
        <div
            id="site_config_toast"
            style="position:fixed;right:20px;bottom:20px;z-index:9999;background:linear-gradient(135deg,#059669,#10b981);color:#fff;padding:12px 18px;border-radius:12px;font-weight:700;display:flex;align-items:center;gap:10px;box-shadow:0 12px 30px rgba(0,0,0,0.22);opacity:1;transform:translateY(0);transition:opacity 0.3s ease, transform 0.3s ease;">
            ✅ <?= htmlspecialchars($mensaje) ?>
        </div>
        <script>
        (function () {
            var el = document.getElementById('site_config_toast');
            if (!el) return;

            // Ocultar luego de ~5s
            setTimeout(function () {
                el.style.opacity = '0';
                el.style.transform = 'translateY(10px)';
            }, 4800);

            setTimeout(function () {
                try { el.remove(); } catch (e) {}
            }, 5200);
        })();
        </script>
        <?php endif; ?>

        <?php if ($error): ?>
        <div style="background:#c94f4f;color:#fff;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-weight:600;">
            ⚠️ <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <p style="color:#94a3b8;margin:0 0 20px;font-size:13px;">
            Subí un video en formato <strong>MP4</strong> que se mostrará como fondo en la sección hero de la página principal.
        </p>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="hero_video_submit" value="1" />

            <div style="display:grid;gap:16px;max-width:640px;">

                <div>
                    <label style="display:block;color:#ccc;font-weight:600;margin-bottom:8px;font-size:13px;">Video (MP4)</label>
                    <input
                        type="file"
                        name="hero_video_file"
                        accept="video/mp4"
                        required
                        style="width:100%;background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;font-family:'Montserrat',sans-serif;font-size:14px;color:#fff;outline:none;box-sizing:border-box;">
                    <p style="color:#64748b;font-size:12px;margin:6px 0 0;">
                        Máximo 50MB. Se guarda en <code style="color:#7aad2e;">static/video/</code>.
                    </p>
                </div>

                <?php if ($hero_video_ok): ?>
                <div>
                    <p style="color:#ccc;font-weight:600;margin-bottom:8px;font-size:13px;">Vista previa:</p>
                    <div style="position:relative;width:320px;max-width:100%;height:180px;overflow:hidden;border-radius:10px;background:#000;">
                        <video
                            width="320"
                            height="180"
                            src="<?= htmlspecialchars($base . $hero_video_path) ?>"
                            style="position:absolute;top:0;left:0;opacity:0.65;"
                            autoplay
                            muted
                            loop
                            playsinline
                            preload="metadata"
                            controls="controls"></video>
                    </div>
                </div>
                <?php endif; ?>

                <div>
                    <button type="submit" class="btn-edit-save" style="padding:12px 32px;">💾 Guardar video</button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- Otros ajustes -->
<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">⚙️ Otros ajustes</div>
    </div>
    <div style="padding:20px 24px;">

        <p style="color:#64748b;font-size:13px;margin:0 0 16px;">Configuraciones básicas del sitio principal.</p>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="site_config_submit" value="1" />

            <div style="display:grid;gap:16px;max-width:720px;">

                <div>
                    <label style="display:block;color:#ccc;font-weight:600;margin-bottom:8px;font-size:13px;">Logo del sitio</label>
                    
                    <div
                        id="site_logo_preview_wrap"
                        style="margin-top:12px;position:relative;width:320px;max-width:100%;height:120px;overflow:hidden;border-radius:10px;background:#000; margin-bottom:12px; display:<?= $site_logo_ok ? 'block' : 'none' ?>;"
                        data-prev-src="<?= htmlspecialchars($site_logo_ok ? ($base . $site_logo_path) : '') ?>">
                        <img
                            id="site_logo_preview_img"
                            src="<?= htmlspecialchars($site_logo_ok ? ($base . $site_logo_path) : '') ?>"
                            alt="Logo del sitio"
                            style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);max-width:100%;max-height:100%;opacity:0.9;">
                    </div>
                    <input
                        type="file"
                        name="site_logo_file"
                        accept="image/*"
                        preload="metadata"
                        autoload="true"
                        style="width:100%;background:var(--green-dk);border:1px solid var(--border);border-radius:10px;padding:11px 12px;font-family:'Montserrat',sans-serif;font-size:14px;color:#fff;outline:none;box-sizing:border-box; margin-top:12px; margin-bottom:6px;">

                </div>

                <div class="tf-grid" style="grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="tf-field">
                        <label style="font-size:11px;font-weight:700;color:var(--g600);letter-spacing:0.5px;text-transform:uppercase;">Teléfono</label>
                        <input
                            type="text"
                            name="contact_telefono"
                            value="<?= htmlspecialchars($contact_telefono) ?>"
                            style="padding:12px 15px;border:1.5px solid var(--g100);border-radius:10px;font-family:'Montserrat',sans-serif;font-size:14px;color:var(--g800);background:var(--g50);outline:none;">
                    </div>

                    <div class="tf-field">
                        <label style="font-size:11px;font-weight:700;color:var(--g600);letter-spacing:0.5px;text-transform:uppercase;">WhatsApp (número)</label>
                        <input
                            type="text"
                            name="contact_whatsapp_number"
                            value="<?= htmlspecialchars($contact_whatsapp_number) ?>"
                            style="padding:12px 15px;border:1.5px solid var(--g100);border-radius:10px;font-family:'Montserrat',sans-serif;font-size:14px;color:var(--g800);background:var(--g50);outline:none;">
                        <p style="color:#64748b;font-size:12px;margin:6px 0 0;">Ej: 5493794775341 (sin https://wa.me/)</p>
                    </div>
                </div>

                <div class="tf-field" style="max-width:720px;">
                    <label style="font-size:11px;font-weight:700;color:var(--g600);letter-spacing:0.5px;text-transform:uppercase;">Dirección</label>
                    <input
                        type="text"
                        name="contact_direccion"
                        value="<?= htmlspecialchars($contact_direccion) ?>"
                        style="padding:12px 15px;border:1.5px solid var(--g100);border-radius:10px;font-family:'Montserrat',sans-serif;font-size:14px;color:var(--g800);background:var(--g50);outline:none;">
                </div>

                <div class="tf-field" style="max-width:720px;">
                    <label style="font-size:11px;font-weight:700;color:var(--g600);letter-spacing:0.5px;text-transform:uppercase;">Horarios de atención</label>
                    <input
                        type="text"
                        name="contact_horarios"
                        value="<?= htmlspecialchars($contact_horarios) ?>"
                        style="padding:12px 15px;border:1.5px solid var(--g100);border-radius:10px;font-family:'Montserrat',sans-serif;font-size:14px;color:var(--g800);background:var(--g50);outline:none;">
                </div>

                <div class="tf-grid" style="grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="tf-field">
                        <label style="font-size:11px;font-weight:700;color:var(--g600);letter-spacing:0.5px;text-transform:uppercase;">Instagram URL</label>
                        <input
                            type="url"
                            name="social_instagram_url"
                            value="<?= htmlspecialchars($social_instagram_url) ?>"
                            style="padding:12px 15px;border:1.5px solid var(--g100);border-radius:10px;font-family:'Montserrat',sans-serif;font-size:14px;color:var(--g800);background:var(--g50);outline:none;">
                    </div>
                    <div class="tf-field">
                        <label style="font-size:11px;font-weight:700;color:var(--g600);letter-spacing:0.5px;text-transform:uppercase;">Facebook URL</label>
                        <input
                            type="url"
                            name="social_facebook_url"
                            value="<?= htmlspecialchars($social_facebook_url) ?>"
                            style="padding:12px 15px;border:1.5px solid var(--g100);border-radius:10px;font-family:'Montserrat',sans-serif;font-size:14px;color:var(--g800);background:var(--g50);outline:none;">
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn-edit-save" style="padding:12px 32px;">💾 Guardar configuración</button>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
(function () {
    const input = document.querySelector('input[name="site_logo_file"]');
    const wrap = document.getElementById('site_logo_preview_wrap');
    const img = document.getElementById('site_logo_preview_img');
    if (!input || !wrap || !img) return;

    let currentObjectUrl = null;

    input.addEventListener('change', () => {
        const file = input.files && input.files[0] ? input.files[0] : null;
        const prevSrc = wrap.getAttribute('data-prev-src') || '';

        if (!file) {
            if (currentObjectUrl) {
                URL.revokeObjectURL(currentObjectUrl);
                currentObjectUrl = null;
            }
            img.src = prevSrc;
            wrap.style.display = prevSrc ? 'block' : 'none';
            return;
        }

        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
        }

        currentObjectUrl = URL.createObjectURL(file);
        img.src = currentObjectUrl;
        wrap.style.display = 'block';
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
