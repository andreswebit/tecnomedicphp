<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
portal_require_role(['admin']);

// Manejo de acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'atender_contacto') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("UPDATE tm_contactos SET atendido = 1 WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=web&ok=1');
        exit;
    } elseif ($accion === 'desatender_contacto') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("UPDATE tm_contactos SET atendido = 0 WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=web&ok=1');
        exit;
    } elseif ($accion === 'eliminar_contacto') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("DELETE FROM tm_contactos WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?tab=web&ok=1');
        exit;
    }
}

// 1) Mensajes Web (tm_contactos)
$contactos = [];
try {
    $contactos = db()->query(
        "SELECT * FROM tm_contactos ORDER BY atendido ASC, creado_en DESC"
    )?->fetch_all(MYSQLI_ASSOC) ?: [];
} catch (Throwable $e) {
    $contactos = [];
}

$contactos_total = count($contactos);
$contactos_pendientes = count(array_filter($contactos, fn($c) => (int)($c['atendido'] ?? 0) === 0));
$contactos_atendidos = $contactos_total - $contactos_pendientes;

// 2) Emails (cache local tm_emails)
// Cuenta inicial para la vista Webmail (evita warning de variable no definida)
$cuenta = trim($_GET['cuenta'] ?? 'contacto');
if (!in_array($cuenta, ['contacto', 'noreply'], true)) {
    $cuenta = 'contacto';
}
$emails = [];
$emails_sin_leer = 0;
try {
    $res = db()->query("SELECT * FROM tm_emails WHERE cuenta = 'contacto' AND folder = 'INBOX' ORDER BY creado_en DESC LIMIT 50");
    if ($res) {
        $emails = $res->fetch_all(MYSQLI_ASSOC);
        $emails_sin_leer = count(array_filter($emails, fn($m) => (int)($m['leido'] ?? 0) === 0));
    }
} catch (Throwable $e) {
    $emails = [];
}

// 3) WhatsApp chats
$wa_chats = [];
$wa_sin_leer = 0;
try {
    $res = db()->query("SELECT * FROM tm_wa_chats ORDER BY ultimo_mensaje_at DESC");
    if ($res) {
        $wa_chats = $res->fetch_all(MYSQLI_ASSOC);
        foreach ($wa_chats as $c) {
            $wa_sin_leer += (int)($c['mensajes_sin_leer'] ?? 0);
        }
    }
} catch (Throwable $e) {
    $wa_chats = [];
}

$portal_titulo = 'Hub de Mensajes · TecnoMedic';
$portal_activo = 'mensajes';
require __DIR__ . '/../includes/portal_header.php';
?>

<link rel="stylesheet" href="<?= $base ?>/portal/css/mensajes.css">
<link rel="stylesheet" href="<?= $base ?>/portal/css/mensajes-webmail.css">
<script>window.WM_API = { get: '<?= b('/api/get_emails.php') ?>', send: '<?= b('/api/send_email.php') ?>', del: '<?= b('/api/delete_email.php') ?>' };</script>
<script src="<?= $base ?>/portal/js/mensajes-webmail.js"></script>

<div style="padding: 16px 28px 0;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-family:'Poppins',sans-serif; font-size:20px; font-weight:700; color:#0d1b2a; margin:0;">
                📬 Centro de Comunicaciones
            </h1>
            <p style="font-size:12.5px; color:#64748b; margin:2px 0 0;">
                
            </p>
        </div>
    </div>
</div>

<div class="msg-hub-container">
    <!-- Tab Bar -->
    <div class="msg-tab-bar">
        <button class="msg-tab-btn active" data-tab="web">
            <span>🌐 Mensajes Web</span>
            <span class="msg-tab-badge web" id="badge-web"><?= (int)$contactos_pendientes ?> pendientes</span>
        </button>
        <button class="msg-tab-btn" data-tab="email">
            <span>✉️ Email Corporativo</span>
            <span class="msg-tab-badge email" id="badge-email"><?= (int)$emails_sin_leer ?> sin leer</span>
        </button>
        <button class="msg-tab-btn" data-tab="whatsapp">
            <span>💬 WhatsApp Web</span>
            <span class="msg-tab-badge wa" id="badge-wa"><?= (int)$wa_sin_leer ?> chats</span>
        </button>
    </div>

    <div class="msg-panels-area">

        <!-- PANEL 1: MENSAJES WEB -->
        <div class="msg-panel msg-panel-web active" id="panel-web">
            <div class="stats" style="margin: 0 0 16px; display:flex; gap:12px; flex-wrap:wrap;">
                <div class="stat-card blue" style="flex:1; min-width:200px; background:#e8f6f5; border:1px solid rgba(26,165,165,0.18); border-radius:12px; padding:14px;">
                    <div class="stat-label" style="font-size:12px; color:#0d1b2a; opacity:.75;">Total Mensajes</div>
                    <div class="stat-value" style="text-align:end; font-size:22px; font-weight:800; color:#0d1b2a;"><?= (int)$contactos_total ?></div>
                    <div class="stat-sub" style="font-size:12px; color:#64748b;">consultas recibidas</div>
                </div>
                <div class="stat-card amber" style="flex:1; min-width:200px; background:#fff6e6; border:1px solid rgba(245,158,11,0.25); border-radius:12px; padding:14px;">
                    <div class="stat-label" style="font-size:12px; color:#0d1b2a; opacity:.75;">Pendientes</div>
                    <div class="stat-value" style="text-align:end; font-size:22px; font-weight:800; color:#0d1b2a;"><?= (int)$contactos_pendientes ?></div>
                    <div class="stat-sub" style="font-size:12px; color:#64748b;">por responder / atender</div>
                </div>
                <div class="stat-card green" style="flex:1; min-width:200px; background:#f0fdf4; border:1px solid rgba(34,197,94,0.25); border-radius:12px; padding:14px;">
                    <div class="stat-label" style="font-size:12px; color:#0d1b2a; opacity:.75;">Atendidos</div>
                    <div class="stat-value" style="text-align:end; font-size:22px; font-weight:800; color:#16a34a;"><?= (int)$contactos_atendidos ?></div>
                    <div class="stat-sub" style="font-size:12px; color:#64748b;">consultas resueltas</div>
                </div>
            </div>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:12px;">
                <div style="flex:1; min-width:240px;">
                    <input id="webSearchInput" type="text" class="edit-input" style="background:#ffffff;" placeholder="Buscar por nombre, email, teléfono, motivo o mensaje…" />
                </div>
            </div>

            <div style="overflow:auto; border:1px solid #e2e8f0; border-radius:12px; background:#ffffff;">
                <table id="webTable" style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="padding:12px 14px; font-size:12px; color:#334155; text-align:left;">Contacto / Remitente</th>
                            <th style="padding:12px 14px; font-size:12px; color:#334155; text-align:left;">Email</th>
                            <th style="padding:12px 14px; font-size:12px; color:#334155; text-align:left;">Teléfono</th>
                            <th style="padding:12px 14px; font-size:12px; color:#334155; text-align:left;">Motivo</th>
                            <th style="padding:12px 14px; font-size:12px; color:#334155; text-align:left;">Fecha</th>
                            <th style="padding:12px 14px; font-size:12px; color:#334155; text-align:left;">Estado</th>
                            <th style="padding:12px 14px; font-size:12px; color:#334155; text-align:left;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($contactos): ?>
                            <?php foreach ($contactos as $c):
                                $es_atendido = (int)($c['atendido'] ?? 0) === 1;
                            ?>
                                <tr style="border-top:1px solid #f1f5f9;">
                                    <td style="padding:12px 14px; font-size:13px; font-weight:600; color:#0d1b2a;">
                                        <?= htmlspecialchars($c['nombre'] ?? '') ?>
                                    </td>
                                    <td style="padding:12px 14px; font-size:13px; color:#334155;">
                                        <?= htmlspecialchars($c['email'] ?? '-') ?>
                                    </td>
                                    <td style="padding:12px 14px; font-size:13px; color:#334155;">
                                        <?= htmlspecialchars($c['telefono'] ?? '-') ?>
                                    </td>
                                    <td style="padding:12px 14px; font-size:13px; color:#334155;">
                                        <span style="display:inline-block; max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($c['motivo'] ?? '') ?>">
                                            <?= htmlspecialchars(ucfirst($c['motivo'] ?? '-')) ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px 14px; font-size:12.5px; color:#64748b; white-space:nowrap;">
                                        <?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['creado_en'] ?? 'now'))) ?>
                                    </td>
                                    <td style="padding:12px 14px; font-size:13px; white-space:nowrap;">
                                        <?php if ($es_atendido): ?>
                                            <span style="display:inline-block; padding:4px 10px; border-radius:999px; background:#dcfce7; color:#15803d; font-weight:700; font-size:11.5px;">✅ Atendido</span>
                                        <?php else: ?>
                                            <span style="display:inline-block; padding:4px 10px; border-radius:999px; background:#fef3c7; color:#b45309; font-weight:700; font-size:11.5px;">⏳ Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding:12px 14px; font-size:13px; color:#334155; white-space:nowrap;">
                                        <button type="button" class="btn btn-outline" style="padding:5px 10px; font-size:12px;" onclick="verDetalleContacto(<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>)">👁 Ver</button>

                                        <form method="post" style="display:inline; margin-left:6px;">
                                            <input type="hidden" name="accion" value="<?= $es_atendido ? 'desatender_contacto' : 'atender_contacto' ?>">
                                            <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
                                            <button type="submit" class="btn <?= $es_atendido ? 'btn-outline' : 'btn-primary' ?>" style="padding:5px 10px; font-size:12px;" title="<?= $es_atendido ? 'Marcar como pendiente' : 'Marcar como atendido' ?>">
                                                <?= $es_atendido ? '↩ Pendiente' : '✔ Atender' ?>
                                            </button>
                                        </form>

                                        <form method="post" style="display:inline; margin-left:6px;">
                                            <input type="hidden" name="accion" value="eliminar_contacto">
                                            <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
                                            <button type="submit" class="btn btn-outpanel" style="padding:5px 9px; font-size:12px;" onclick="return confirm('¿Eliminar este mensaje de contacto?');" title="Eliminar">🗑</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="padding:28px; text-align:center; color:#94a3b8;">🌐 Sin mensajes de contacto web recibidos.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PANEL 2: EMAIL (estilo Webmail Ferozo) -->
        <div class="msg-panel msg-panel-email" id="panel-email">

            <!-- Vista bandeja -->
            <div class="wm-view active" id="wmViewMail">
                <div class="wm-toolbar">
                    <button type="button" class="wm-tb-btn" id="wmBtnRefresh"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg><span>Actualizar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmBtnCompose"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg><span>Redactar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmBtnReply" disabled><svg viewBox="0 0 24 24"><path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 6 6v3"/></svg><span>Responder</span></button>
                    <button type="button" class="wm-tb-btn" id="wmBtnReplyAll" disabled><svg viewBox="0 0 24 24"><path d="m7 14-5-5 5-5"/><path d="M12 14 7 9l5-5"/><path d="M7 9h9a5 5 0 0 1 5 5v4"/></svg><span>Responder a todos</span></button>
                    <button type="button" class="wm-tb-btn" id="wmBtnFwd" disabled><svg viewBox="0 0 24 24"><path d="m15 14 5-5-5-5"/><path d="M20 9H10a6 6 0 0 0-6 6v3"/></svg><span>Reenviar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmBtnDel" disabled><svg viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m6 6 1 14h10l1-14"/></svg><span>Eliminar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmBtnMark" disabled><svg viewBox="0 0 24 24"><path d="M20 12 12 20 3 11V3h8Z"/><circle cx="7.5" cy="7.5" r="1"/></svg><span>Marcar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmBtnMore"><svg viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/></svg><span>Más</span></button>
                    <div class="wm-tb-sp"></div>
                    <select class="wm-tb-select" id="wmFilter"><option value="all">Todos</option><option value="unread">No leídos</option><option value="read">Leídos</option></select>
                    <div class="wm-tb-search">🔍<input type="text" id="wmSearch" placeholder="Buscar..."><button type="button" id="wmSearchClear">✕</button></div>
                </div>

                <div class="wm-mail">
                    <div class="wm-folders">
                        <div class="wm-account-box">
                            <select id="wmAccountSelect" class="wm-account-select" title="Cuenta de correo activa" style="background: #8cb5c9;; border:1px solid #abee7e; border-radius:6px; padding:6px 10px; font-size:13px; font-weight:600; color: #494747; hover:border-color :: before:#8cb5c9: #8cb5c9;">
                                <option value="contacto" <?= ($cuenta === 'contacto' ? 'selected' : '') ?>>Contacto</option>
                                <option value="noreply" <?= ($cuenta === 'noreply' ? 'selected' : '') ?>>No Reply</option>
                            </select>
                        </div>
                        <div id="wmFolderList"></div>
                        <div class="wm-folders-foot">
                            <input type="text" id="wmNewFolder" placeholder="Nueva carpeta">
                            <button type="button" id="wmNewFolderBtn">Crear</button>
                        </div>
                    </div>

                    <div class="wm-list-col">
                        <div class="wm-list-head">
                            <span id="wmCount">Mensajes 0 a 0 de 0</span>
                            <div class="wm-pager">
                                <button type="button" id="wmFirst">⏮</button><button type="button" id="wmPrev">◀</button>
                                <input type="text" id="wmPage" value="1">
                                <button type="button" id="wmNext">▶</button><button type="button" id="wmLast">⏭</button>
                            </div>
                        </div>
                        <div class="wm-list" id="wmList">
                            <?php foreach ($emails as $m): ?>
                                <div class="wm-row <?= (int)($m['leido'] ?? 0) === 0 ? 'unread' : '' ?>" data-id="<?= (int)$m['id'] ?>">
                                    <div class="wm-av"><?= htmlspecialchars(strtoupper(mb_substr($m['remitente_nombre'] ?: $m['remitente_email'], 0, 1)) ?: '?') ?></div>
                                    <div class="wm-from"><?= htmlspecialchars($m['remitente_nombre'] ?: $m['remitente_email']) ?></div>
                                    <div class="wm-date"><?= htmlspecialchars(date('d/m H:i', strtotime($m['creado_en'] ?? 'now'))) ?></div>
                                    <div class="wm-subj"><?= htmlspecialchars($m['asunto'] ?: '(Sin Asunto)') ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="wm-prev" id="emailDetailView">
                        <div class="wm-prev-head">
                            <div class="wm-prev-av" id="wmPrevAv"></div>
                            <div class="wm-prev-info">
                                <h2 class="wm-prev-subj" id="wmPrevSubj"></h2>
                                <div class="wm-prev-meta"><span>Remitente <b id="wmPrevFrom"></b></span><span>Fecha <b id="wmPrevDate"></b></span></div>
                            </div>
                            <div class="wm-prev-acts">
                                <button type="button" class="wm-ic" id="wmIcReply" title="Responder">↩</button>
                                <button type="button" class="wm-ic" id="wmIcReplyAll" title="Responder a todos">↩↩</button>
                                <button type="button" class="wm-ic" id="wmIcFwd" title="Reenviar">➡</button>
                                <button type="button" class="wm-ic" id="wmIcPop" title="Abrir en ventana">⧉</button>
                            </div>
                        </div>
                        <div class="wm-prev-body" id="wmPrevBody"></div>
                    </div>
                </div>
            </div>

            <!-- Vista redactar -->
            <div class="wm-view" id="wmViewCompose">
                <div class="wm-toolbar">
                    <button type="button" class="wm-tb-btn" id="wmCCancel"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m12 8-4 4 4 4"/><path d="M8 12h8"/></svg><span>Cancelar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmCSend"><svg viewBox="0 0 24 24"><path d="m22 2-11 11"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg><span>Enviar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmCSave"><svg viewBox="0 0 24 24"><path d="M5 3h12l4 4v14H3V3Z"/><path d="M7 3v6h8V3"/><rect x="7" y="13" width="10" height="8"/></svg><span>Guardar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmCSpell"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8 12 3 3 5-6"/></svg><span>Ortografía</span></button>
                    <button type="button" class="wm-tb-btn" id="wmAttBtn"><svg viewBox="0 0 24 24"><path d="m21 11-9 9a5 5 0 0 1-7-7l9-9a3.5 3.5 0 0 1 5 5l-9 9a2 2 0 0 1-3-3l8-8"/></svg><span>Adjuntar</span></button>
                    <button type="button" class="wm-tb-btn" id="wmCSign"><svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M14 4l6 6L9 21H3v-6Z"/></svg><span>Firma</span></button>
                    <button type="button" class="wm-tb-btn" id="wmCResp"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-6-4-6 4Z"/></svg><span>Respuestas</span></button>
                    <input type="file" id="wmFile" multiple style="display:none">
                </div>

                <div class="wm-compose">
                    <div class="wm-comp-side">
                        <div class="wm-comp-side-h">Contactos <span>⏮ ◀ ▶ ⏭</span></div>
                        <div class="wm-comp-side-s"><input type="text" id="wmContactSearch" placeholder="Buscar..."></div>
                        <div class="wm-contacts" id="wmContacts">
                            <?php
                            $wm_contactos = [];
                            foreach ($contactos as $c) { if (!empty($c['email'])) $wm_contactos[strtolower($c['email'])] = trim(($c['nombre'] ?? '') . ' <' . $c['email'] . '>'); }
                            foreach ($wm_contactos as $mail => $label): ?>
                                <div data-m="<?= htmlspecialchars($label) ?>"><?= htmlspecialchars($label) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="wm-comp-side-f"><button type="button" data-t="wmTo">To+</button><button type="button" data-t="wmCc">Cc+</button><button type="button" data-t="wmBcc">Bcc+</button></div>
                    </div>

                    <div class="wm-comp-main">
                        <div class="wm-form">
                            <label>Remitente</label>
                            <div><select id="wmFrom" style="max-width:320px"><option value="contacto@tecnomedic.com.ar">contacto@tecnomedic.com.ar</option><option value="noreply@tecnomedic.com.ar">noreply@tecnomedic.com.ar</option></select></div>
                            <label>Destinatario</label>
                            <input type="text" id="wmTo" autocomplete="off">
                            <div class="wm-sub"><a data-show="wmCc">Agregar Cc</a><a data-show="wmBcc">Agregar Cco</a><a data-show="wmReplyTo">Agregar respuesta a</a></div>
                            <label class="wm-hidden" data-for="wmCc">Cc</label><input type="text" id="wmCc" class="wm-hidden" style="grid-column:2">
                            <label class="wm-hidden" data-for="wmBcc">Cco</label><input type="text" id="wmBcc" class="wm-hidden" style="grid-column:2">
                            <label class="wm-hidden" data-for="wmReplyTo">Responder a</label><input type="text" id="wmReplyTo" class="wm-hidden" style="grid-column:2">
                            <label>Asunto</label>
                            <input type="text" id="wmSubject">
                        </div>

                        <div class="wm-opts">
                            <span>Tipo de editor <select id="wmEdType"><option value="html">HTML</option><option value="text">Texto sin formato</option></select></span>
                            <span>Prioridad <select id="wmPrio"><option value="3">Normal</option><option value="1">Más alta</option><option value="2">Alta</option><option value="4">Baja</option><option value="5">Más baja</option></select></span>
                            <label class="wm-sw"><input type="checkbox" id="wmReceipt"><i></i>Acuse de recibo</label>
                            <span>Guardar mensaje enviado en <select id="wmSaveIn"><option value="SENT">Enviados</option><option value="">No guardar</option></select></span>
                        </div>

                        <div class="wm-edwrap">
                            <div class="wm-ed-col">
                                <div class="wm-edtb" id="wmEdTb">
                                    <button data-cmd="bold" title="Negrita"><b>B</b></button>
                                    <button data-cmd="italic" title="Cursiva"><i>I</i></button>
                                    <button data-cmd="underline" title="Subrayado"><u>U</u></button>
                                    <span class="wm-sep"></span>
                                    <button data-cmd="justifyLeft" title="Izquierda">≡</button>
                                    <button data-cmd="justifyCenter" title="Centro">☰</button>
                                    <button data-cmd="justifyRight" title="Derecha">≣</button>
                                    <button data-cmd="justifyFull" title="Justificado">▤</button>
                                    <span class="wm-sep"></span>
                                    <select id="wmFont"><option>Verdana</option><option>Arial</option><option>Georgia</option><option>Times New Roman</option><option>Courier New</option><option>Tahoma</option><option>Trebuchet MS</option></select>
                                    <select id="wmSize"><option>8pt</option><option selected>10pt</option><option>12pt</option><option>14pt</option><option>18pt</option><option>24pt</option><option>36pt</option></select>
                                    <span class="wm-sep"></span>
                                    <span class="wm-cbtn" title="Color de texto">A<i class="wm-bar" id="wmColorBar" style="color:#000"></i><input type="color" id="wmColor" value="#000000"></span>
                                    <span class="wm-cbtn" title="Resaltado">🖍<i class="wm-bar" id="wmHiliteBar" style="color:#ffeb3b"></i><input type="color" id="wmHilite" value="#ffeb3b"></span>
                                    <span class="wm-sep"></span>
                                    <button data-cmd="insertUnorderedList" title="Viñetas">☷</button>
                                    <button data-cmd="insertOrderedList" title="Numeración">①</button>
                                    <button data-cmd="outdent" title="Reducir sangría">⇤</button>
                                    <button data-cmd="indent" title="Aumentar sangría">⇥</button>
                                    <button type="button" id="wmLtr" title="Izquierda a derecha">¶</button>
                                    <button type="button" id="wmRtl" title="Derecha a izquierda">⁋</button>
                                    <button type="button" id="wmQuote" title="Cita">❞</button>
                                    <span class="wm-sep"></span>
                                    <button type="button" id="wmLink" title="Enlace">🔗</button>
                                    <button type="button" id="wmUnlink" title="Quitar enlace">⛓</button>
                                    <button type="button" id="wmTable" title="Tabla">▦</button>
                                    <button type="button" id="wmMoreEd" title="Más">•••</button>
                                </div>
                                <div class="wm-edtb" id="wmEdTb2" style="display:none">
                                    <button data-cmd="strikeThrough" title="Tachado"><s>S</s></button>
                                    <button data-cmd="subscript" title="Subíndice">x₂</button>
                                    <button data-cmd="superscript" title="Superíndice">x²</button>
                                    <button data-cmd="insertHorizontalRule" title="Línea horizontal">―</button>
                                    <button data-cmd="removeFormat" title="Quitar formato">Tx</button>
                                    <button data-cmd="undo" title="Deshacer">↶</button>
                                    <button data-cmd="redo" title="Rehacer">↷</button>
                                </div>
                                <div class="wm-edit" id="wmEdit" contenteditable="true" spellcheck="true"></div>
                                <textarea class="wm-plain" id="wmPlain"></textarea>
                            </div>
                            <div class="wm-att" id="wmAtt">
                                <div>La suma de los archivos adjuntos no podrá superar los 50 MB</div>
                                <button type="button" onclick="document.getElementById('wmFile').click()">Añadir un archivo</button>
                                <ul class="wm-att-list" id="wmAttList"></ul>
                                <div class="wm-drop">⬇</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Nuevo chat de WhatsApp (estilos propios, independientes de otros CSS) */
            #panel-whatsapp .wa-sidebar-head{ position:relative; }
            #panel-whatsapp .wan-bar{ display:flex; gap:8px; width:100%; align-items:stretch; }
            #panel-whatsapp .wan-bar > button{ flex:1; white-space:nowrap; margin:0; }
            #panel-whatsapp .wan-new{ border:1px solid #1fa855; background:#25d366; color:#fff; border-radius:8px; padding:8px 12px; font-size:12px; font-weight:700; cursor:pointer; transition:background .15s; }
            #panel-whatsapp .wan-new:hover{ background:#1fa855; }
            #panel-whatsapp .wan-pop{ display:none; position:absolute; left:10px; right:10px; top:calc(100% - 4px); background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 14px 36px rgba(13,27,42,.25); padding:12px; z-index:80; }
            #panel-whatsapp .wan-pop.open{ display:block; }
            #panel-whatsapp .wan-pop-head{ display:flex; align-items:center; justify-content:space-between; font-size:13px; font-weight:700; color:#0d1b2a; margin-bottom:8px; }
            #panel-whatsapp .wan-x{ border:none; background:none; color:#94a3b8; font-size:15px; cursor:pointer; }
            #panel-whatsapp .wan-x:hover{ color:#0d1b2a; }
            #panel-whatsapp .wan-in{ width:100%; box-sizing:border-box; border:1px solid #cbd5e1; border-radius:8px; padding:9px 12px; font-size:13px; outline:none; font-family:inherit; }
            #panel-whatsapp .wan-in:focus{ border-color:#25d366; box-shadow:0 0 0 3px rgba(37,211,102,.15); }
            #panel-whatsapp .wan-dd{ display:none; margin-top:8px; border:1px solid #e2e8f0; border-radius:8px; max-height:280px; overflow-y:auto; background:#fff; }
            #panel-whatsapp .wan-dd.show{ display:block; }
            #panel-whatsapp .wan-row{ display:flex; align-items:center; gap:10px; padding:9px 12px; border-bottom:1px solid #f1f5f9; cursor:pointer; }
            #panel-whatsapp .wan-row:last-child{ border-bottom:none; }
            #panel-whatsapp .wan-row:hover, #panel-whatsapp .wan-row.sel{ background:#f0fdf4; }
            #panel-whatsapp .wan-info{ flex:1; min-width:0; }
            #panel-whatsapp .wan-name{ font-size:13px; font-weight:600; color:#0d1b2a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            #panel-whatsapp .wan-tel{ font-size:11.5px; color:#64748b; }
            #panel-whatsapp .wan-tag{ font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px; background:#e2e8f0; color:#475569; white-space:nowrap; }
            #panel-whatsapp .wan-empty{ padding:16px; text-align:center; color:#94a3b8; font-size:12.5px; }
            #panel-whatsapp .wan-hint{ margin-top:10px; font-size:10.5px; color:#94a3b8; line-height:1.45; }
        </style>

        <!-- PANEL 3: WHATSAPP -->
        <div class="msg-panel msg-panel-wa" id="panel-whatsapp">

            <div class="wa-sidebar">
                <div class="wa-sidebar-head">
                    <div class="email-search-wrap" style="border:none; background:#f8fafc;">
                        <span>🔍</span>
                        <input type="text" id="waSearchInput" placeholder="Buscar chat por nombre o cel…" />
                    </div>
                    <div class="wan-bar">
                        <button type="button" class="wan-new" id="waNewChatBtn" onclick="toggleNuevoChatWA(event)" title="Iniciar una conversación nueva">➕ Nuevo chat</button>
                        <button class="wa-refresh-btn" id="waRefreshBtn" onclick="refrescarWAChats()" title="Recargar listas desde el backend">🔄 Actualizar</button>
                    </div>
                    <div class="wan-pop" id="waNewPop">
                        <div class="wan-pop-head"><span>💬 Nuevo chat de WhatsApp</span><button type="button" class="wan-x" onclick="cerrarNuevoChatWA()" title="Cerrar">✕</button></div>
                        <input type="text" id="waNcSearch" class="wan-in" placeholder="Buscar contacto o escribir un número…" autocomplete="off">
                        <div class="wan-dd" id="waNcResults"></div>
                        <div class="wan-hint">Buscá por nombre, teléfono, DNI o email. Para un número nuevo, escribilo con código de área (ej: 3794123456). WhatsApp solo entrega mensajes libres si el contacto te escribió en las últimas 24 horas.</div>
                    </div>
                </div>

                <div class="wa-list" id="waList">
                    <?php if ($wa_chats): ?>
                        <?php foreach ($wa_chats as $idx => $c): ?>
                            <div class="wa-item <?= $idx === 0 ? 'active' : '' ?>" data-chat-id="<?= (int)$c['id'] ?>" data-name="<?= htmlspecialchars($c['nombre_contacto'] ?? '') ?>" data-phone="<?= htmlspecialchars($c['telefono'] ?? '') ?>" onclick="cargarChatWA(<?= (int)$c['id'] ?>, this)">
                                <div class="wa-avatar"><?= htmlspecialchars(strtoupper(substr($c['nombre_contacto'] ?: ($c['telefono'] ?? ''), 0, 2))) ?></div>
                                <div class="ei-content">
                                    <div class="ei-top">
                                        <span class="ei-from"><?= htmlspecialchars($c['nombre_contacto'] ?: $c['telefono']) ?></span>
                                        <span class="ei-time"><?= htmlspecialchars(date('H:i', strtotime($c['ultimo_mensaje_at'] ?? 'now'))) ?></span>
                                    </div>
                                    <div class="ei-preview"><?= htmlspecialchars(substr($c['ultimo_mensaje'] ?? '', 0, 45)) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:24px; text-align:center; color:#94a3b8; font-size:12.5px;">
                            💬 Sin conversaciones activas.<br>
                            <small style="color:#cbd5e1;">Los mensajes entrantes de Twilio aparecerán aquí automáticamente.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="wa-chat" id="waChatView">
                <?php $c0 = $wa_chats[0] ?? null; ?>
                <?php if ($c0): ?>
                    <script>window.__WA_INIT_CHAT_ID = <?= (int)$c0['id'] ?>;</script>
                <?php endif; ?>

                <div class="wa-chat-head" id="waChatHead" <?= $c0 ? '' : 'style="visibility:hidden"' ?>>
                    <div class="wa-avatar" id="waHeadAvatar"><?= $c0 ? htmlspecialchars(strtoupper(substr($c0['nombre_contacto'] ?: ($c0['telefono'] ?? ''), 0, 2))) : '' ?></div>
                    <div>
                        <div style="font-weight:600; font-size:14px; color:#0d1b2a;" id="waHeadName"><?= $c0 ? htmlspecialchars($c0['nombre_contacto'] ?: $c0['telefono']) : '' ?></div>
                        <div style="font-size:11px; color:#1aa5a5;" id="waHeadPhone"><?= $c0 ? htmlspecialchars($c0['telefono']) . ' · WhatsApp Business' : '' ?></div>
                    </div>
                </div>

                <div class="wa-messages" id="waMessagesArea">
                    <?php if ($c0): ?>
                        <div style="text-align:center; font-size:11px; color:#94a3b8; margin:10px 0;">Conversación iniciada vía Twilio</div>
                    <?php else: ?>
                        <div style="display:flex; flex:1; align-items:center; justify-content:center; color:#94a3b8; flex-direction:column; gap:8px; margin:auto;">
                            <div style="font-size:40px;">💬</div>
                            <div>Seleccioná un chat o iniciá uno con "Nuevo chat"</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="wa-input-bar">
                    <input type="text" class="wa-input" id="waInputMsg" placeholder="Escribir mensaje de WhatsApp…" onkeypress="if(event.key==='Enter') enviarMensajeWA()">
                    <button class="btn-wa-send" onclick="enviarMensajeWA()" title="Enviar">➔</button>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Modal Detalle Mensaje Web -->
<div class="edit-modal-overlay" id="modalContacto">
    <div class="edit-modal" style="max-width:620px;">
        <div class="edit-modal-header">
            <div class="edit-modal-title">🌐 Detalle de Consulta Web</div>
            <button class="edit-modal-close" onclick="closeModalContacto()">✕</button>
        </div>
        <div class="edit-grid" id="modalContactoContent"></div>
        <div class="edit-footer" style="display:flex; justify-content:space-between; align-items:center; width:100%; margin-top:16px;">
            <div id="modalContactoActions" style="display:flex; gap:8px;"></div>
            <button type="button" class="btn btn-outline" onclick="closeModalContacto()">Cerrar</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── NAVEGACIÓN ENTRE PESTAÑAS ──
    const tabBtns = document.querySelectorAll('.msg-tab-btn');
    const panels  = document.querySelectorAll('.msg-panel');

    const urlTab = (new URLSearchParams(location.search).get('tab') || '').toLowerCase();
    if (urlTab) {
        const btn = document.querySelector('.msg-tab-btn[data-tab="' + urlTab + '"]');
        if (btn) btn.click();
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.dataset.tab;
            tabBtns.forEach(b => b.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            const targetPanel = document.getElementById('panel-' + targetTab);
            if (targetPanel) targetPanel.classList.add('active');

            // Update URL (sin recargar)
            const u = new URL(location.href);
            u.searchParams.set('tab', targetTab);
            history.replaceState({}, '', u.toString());
        });
    });

    // ── Filtro / búsqueda mensajes web ──
    const webSearch = document.getElementById('webSearchInput');
    if (webSearch) {
        webSearch.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#webTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Modal Detalle Mensaje Web ──
    window.verDetalleContacto = function(d) {
        let html = '';
        html += '<div class="edit-group"><div class="edit-label">Nombre y Apellido</div><div class="edit-input-wrap" style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;font-weight:600;color:#0d1b2a;">' + escapeHtml(d.nombre || '-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Email</div><div class="edit-input-wrap" style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;"><a href="mailto:' + encodeURIComponent(d.email||'') + '" style="color:#1aa5a5;text-decoration:none;font-weight:500;">' + escapeHtml(d.email || '-') + '</a></div></div>';
        html += '<div class="edit-group"><div class="edit-label">Teléfono</div><div class="edit-input-wrap" style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;">' + (d.telefono ? '<a href="tel:' + encodeURIComponent(d.telefono) + '" style="color:#1aa5a5;text-decoration:none;font-weight:500;">' + escapeHtml(d.telefono) + '</a>' : '<span style="color:#94a3b8;">No informado</span>') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Motivo / Especialidad</div><div class="edit-input-wrap" style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;"><span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:600;">' + escapeHtml(d.motivo || 'General') + '</span></div></div>';
        html += '<div class="edit-group"><div class="edit-label">Fecha de Envío</div><div class="edit-input-wrap" style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;">' + (d.creado_en ? escapeHtml(d.creado_en) : '-') + '</div></div>';
        html += '<div class="edit-group"><div class="edit-label">Estado</div><div class="edit-input-wrap" style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:10px 12px;">' + ((Number(d.atendido) === 1) ? '<span class="badge badge-success">Atendido</span>' : '<span class="badge badge-warning">Pendiente</span>') + '</div></div>';

        if (d.mensaje) {
            html += '<div class="edit-group full"><div class="edit-label">Mensaje de la consulta</div><div class="edit-input-wrap"><div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:14px;white-space:pre-wrap;line-height:1.6;color:#334155;font-size:13.5px;max-height:200px;overflow-y:auto;">' + escapeHtml(d.mensaje) + '</div></div></div>';
        }
        document.getElementById('modalContactoContent').innerHTML = html;

        // Botones de respuesta rápida
        let actionsHtml = '';
        if (d.email) {
            actionsHtml += '<button type="button" class="btn btn-primary" style="padding:6px 14px;font-size:12px;" onclick="responderContactoEmail(\'' + escapeHtml(d.email) + '\', \'' + escapeHtml(d.nombre || '') + '\', \'' + escapeHtml(d.motivo || '') + '\')">✉️ Responder por Email</button>';
        }
        if (d.telefono) {
            const numLimpio = String(d.telefono).replace(/[^0-9]/g, '');
            actionsHtml += '<a href="https://wa.me/' + encodeURIComponent(numLimpio) + '?text=' + encodeURIComponent('Hola ' + (d.nombre || '') + ', te contactamos de TecnoMedic respecto a tu consulta web.') + '" target="_blank" class="btn" style="background:#25d366;color:#fff;padding:6px 14px;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">💬 WhatsApp</a>';
        }
        document.getElementById('modalContactoActions').innerHTML = actionsHtml;

        document.getElementById('modalContacto').classList.add('open');
    };

    window.closeModalContacto = function() {
        document.getElementById('modalContacto').classList.remove('open');
    };

    window.responderContactoEmail = function(toEmail, nombre, motivo) {
        closeModalContacto();
        const btnTabEmail = document.querySelector('.msg-tab-btn[data-tab="email"]');
        if (btnTabEmail) btnTabEmail.click();

        setTimeout(() => {
            const esc = s => String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            abrirComposeEmail({
                mode: 'new',
                to: toEmail,
                subject: 'Respuesta a tu consulta en TecnoMedic' + (motivo ? ' · ' + motivo : ''),
                bodyHtml: '<p>Hola ' + esc(nombre) + ',</p><p>Gracias por comunicarte con TecnoMedic.</p><p><br></p>'
            });
        }, 150);
    };

    // Email: logica movida a portal/js/mensajes-webmail.js

    // ── WhatsApp: refrescar chats ──
    window.refrescarWAChats = async function(selectId) {
        if (selectId && typeof selectId === 'object') selectId = null;
        const btn = document.getElementById('waRefreshBtn');
        if (btn) btn.disabled = true;

        try {
            const r = await fetch('<?= b('/api/get_wa_chats.php') ?>', {
                method: 'GET',
                credentials: 'include'
            });
            const data = await r.json();
            if (!data.ok) {
                alert('No se pudo refrescar chats WA: ' + (data.error || 'desconocido'));
                return;
            }

            const waList = document.getElementById('waList');
            if (!waList) return;

            const chats = data.chats || [];
            waList.innerHTML = '';

            if (!chats.length) {
                waList.innerHTML = '<div style="padding:24px; text-align:center; color:#94a3b8; font-size:12.5px;">💬 Sin conversaciones activas.</div>';
                const area = document.getElementById('waMessagesArea');
                if (area) area.innerHTML = '';
                return;
            }

            const targetId = selectId || chats[0].id;
            chats.forEach((c, idx) => {
                const div = document.createElement('div');
                div.className = 'wa-item' + (String(c.id) === String(targetId) ? ' active' : '');
                div.setAttribute('data-chat-id', c.id);
                div.setAttribute('onclick', 'cargarChatWA(' + c.id + ', this)');
                div.dataset.name = c.nombre_contacto || '';
                div.dataset.phone = c.telefono || '';

                const avatarText = (c.nombre_contacto || c.telefono || '').toString();
                const avatar = avatarText.length ? avatarText.substring(0, 2).toUpperCase() : '??';

                const unread = Number(c.mensajes_sin_leer || 0);
                const preview = (c.ultimo_mensaje || '');

                div.innerHTML =
                    '<div class="wa-avatar">' + escapeHtml(avatar) + '</div>' +
                    '<div class="ei-content">' +
                        '<div class="ei-top">' +
                            '<span class="ei-from">' + escapeHtml(c.nombre_contacto || c.telefono || '') + '</span>' +
                            '<span class="ei-time">' + (c.ultimo_mensaje_at ? new Date(c.ultimo_mensaje_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '') + '</span>' +
                        '</div>' +
                        '<div class="ei-preview">' + escapeHtml(preview).slice(0, 55) + (unread > 0 ? '  · ' + unread + ' sin leer' : '') + '</div>' +
                    '</div>';

                waList.appendChild(div);
            });

            const firstId = targetId;
            const firstEl = waList.querySelector('.wa-item[data-chat-id="' + firstId + '"]');
            if (firstEl) window.cargarChatWA(firstId, firstEl);
        } catch (e) {
            console.error(e);
            alert('Error al refrescar chats WA.');
        } finally {
            if (btn) btn.disabled = false;
        }
    };

    // ── WhatsApp: cargar conversación ──
    window.cargarChatWA = async function(id, el) {
        document.querySelectorAll('.wa-item').forEach(i => i.classList.remove('active'));
        if (el) {
            el.classList.add('active');
            const nm = el.dataset.name || el.dataset.phone || '';
            const ph = el.dataset.phone || '';
            const head = document.getElementById('waChatHead');
            if (head) head.style.visibility = 'visible';
            const hn = document.getElementById('waHeadName'); if (hn) hn.textContent = nm;
            const hp = document.getElementById('waHeadPhone'); if (hp) hp.textContent = ph + ' · WhatsApp Business';
            const ha = document.getElementById('waHeadAvatar'); if (ha) ha.textContent = nm.substring(0, 2).toUpperCase();
        }

        const area = document.getElementById('waMessagesArea');
        if (!area) return;
        area.innerHTML = '<div style="text-align:center; font-size:11px; color:#94a3b8;">Cargando…</div>';

        try {
            const url = '<?= b('/api/get_wa_messages.php') ?>?chat_id=' + encodeURIComponent(id);
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();
            if (!data.ok) {
                alert('No se pudo cargar la conversación: ' + (data.error || 'desconocido'));
                return;
            }

            const msgs = data.messages || [];
            if (!msgs.length) {
                area.innerHTML = '<div style="text-align:center; font-size:11px; color:#94a3b8; margin:10px 0;">Todavía no hay mensajes.</div>';
                return;
            }

            area.innerHTML = '';

            // Marcar chat como leído
            try {
                await fetch('<?= b('/api/mark_wa_chat_read.php') ?>', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: 'chat_id=' + encodeURIComponent(id)
                });
            } catch (e) {}

            msgs.forEach(m => {
                const dir = m.direccion === 'in' ? 'in' : 'out';
                const bubble = document.createElement('div');
                bubble.className = 'wa-bubble ' + dir;
                bubble.textContent = m.mensaje || '';

                const t = document.createElement('div');
                t.className = 'wa-bubble-time';
                const dt = m.creado_en ? new Date(m.creado_en) : null;
                t.textContent = dt ? dt.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '';

                bubble.appendChild(t);
                area.appendChild(bubble);
            });

            area.scrollTop = area.scrollHeight;
        } catch (e) {
            console.error(e);
            alert('Error al cargar conversación WA.');
        }
    };

    // ── WhatsApp: enviar mensaje ──
    window.enviarMensajeWA = function() {
        const input = document.getElementById('waInputMsg');
        if (!window.__WA_LAST_SENT_LOCK) window.__WA_LAST_SENT_LOCK = false;
        if (window.__WA_LAST_SENT_LOCK) return;

        if (!input || !input.value.trim()) return;

        window.__WA_LAST_SENT_LOCK = true;
        const msgText = input.value.trim();
        const msgArea = document.getElementById('waMessagesArea');

        const activeChatEl = document.querySelector('.wa-item.active');
        const chat_id = activeChatEl ? activeChatEl.getAttribute('data-chat-id') : null;

        if (!chat_id) {
            alert('Seleccioná un chat para enviar.');
            window.__WA_LAST_SENT_LOCK = false;
            return;
        }

        const chatIdNum = parseInt(chat_id, 10);

        if (msgArea) {
            const bubble = document.createElement('div');
            bubble.className = 'wa-bubble out';
            bubble.innerHTML = msgText + '<div class="wa-bubble-time">' + new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) + '</div>';
            msgArea.appendChild(bubble);
            msgArea.scrollTop = msgArea.scrollHeight;
        }

        const payload = new FormData();
        payload.append('chat_id', chatIdNum);
        payload.append('mensaje', msgText);

        fetch('<?= b('/api/send_wa_message.php') ?>', {
            method: 'POST',
            credentials: 'include',
            body: payload
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) {
                alert('No se pudo enviar el mensaje: ' + (data.error || 'desconocido'));
                return;
            }
            if (data.twilio_ok === false) {
                alert('El mensaje quedó guardado, pero Twilio no pudo entregarlo. Si el contacto no te escribió en las últimas 24 horas, WhatsApp exige una plantilla aprobada. Detalle en api/debug.log.');
            }
            return window.cargarChatWA(chatIdNum, activeChatEl);
        })
        .catch(err => {
            console.error(err);
            alert('Error al enviar mensaje WA.');
        })
        .finally(() => {
            input.value = '';
            window.__WA_LAST_SENT_LOCK = false;
        });
    };

    // ── WhatsApp: filtrar lista de chats ──
    const waSearchEl = document.getElementById('waSearchInput');
    if (waSearchEl) {
        waSearchEl.addEventListener('input', () => {
            const q = waSearchEl.value.trim().toLowerCase();
            document.querySelectorAll('#waList .wa-item').forEach(el => {
                const t = ((el.dataset.name || '') + ' ' + (el.dataset.phone || '') + ' ' + el.textContent).toLowerCase();
                el.style.display = t.includes(q) ? '' : 'none';
            });
        });
    }

    // ── WhatsApp: nuevo chat (desplegable con búsqueda) ──
    const waNcApi = '<?= b('/api/wa_nuevo_chat.php') ?>';
    let waNcTimer = null;
    let waNcSeq = 0;

    function waNcRender(list, q) {
        const box = document.getElementById('waNcResults');
        if (!box) return;
        let html = '';
        if (/^[\d\s+()-]+$/.test(q) && q.replace(/\D/g, '').length >= 8) {
            html += '<div class="wan-row" data-tel="' + escapeHtml(q) + '" data-nom="">' +
                '<div class="wa-avatar">#</div>' +
                '<div class="wan-info"><div class="wan-name">Iniciar chat con ' + escapeHtml(q) + '</div><div class="wan-tel">Número nuevo</div></div></div>';
        }
        html += (list || []).map(c => {
            const nom = c.nombre || c.telefono;
            return '<div class="wan-row" data-tel="' + escapeHtml(c.telefono) + '" data-nom="' + escapeHtml(c.nombre || '') + '">' +
                '<div class="wa-avatar">' + escapeHtml(nom.substring(0, 2).toUpperCase()) + '</div>' +
                '<div class="wan-info"><div class="wan-name">' + escapeHtml(nom) + '</div><div class="wan-tel">' + escapeHtml(c.telefono) + '</div></div>' +
                '<span class="wan-tag">' + escapeHtml(c.origen) + '</span></div>';
        }).join('');
        if (!html && q) html = '<div class="wan-empty">Sin resultados para "' + escapeHtml(q) + '"</div>';
        box.innerHTML = html;
        box.classList.toggle('show', html !== '');
    }

    window.toggleNuevoChatWA = function(ev) {
        if (ev) ev.stopPropagation();
        const pop = document.getElementById('waNewPop');
        if (!pop) return;
        if (pop.classList.contains('open')) { window.cerrarNuevoChatWA(); return; }
        pop.classList.add('open');
        const q = document.getElementById('waNcSearch');
        q.value = '';
        waNcRender([], '');
        setTimeout(() => q.focus(), 30);
    };

    window.cerrarNuevoChatWA = function() {
        const pop = document.getElementById('waNewPop');
        if (pop) pop.classList.remove('open');
    };

    async function waNcBuscar(q) {
        const box = document.getElementById('waNcResults');
        const my = ++waNcSeq;
        box.innerHTML = '<div class="wan-empty">Buscando…</div>';
        box.classList.add('show');
        try {
            const r = await fetch(waNcApi + '?action=buscar&q=' + encodeURIComponent(q), { credentials: 'include' });
            const d = await r.json();
            if (my !== waNcSeq) return;   // llegó una respuesta vieja
            if (!d.ok) { box.innerHTML = '<div class="wan-empty">Error: ' + escapeHtml(d.error || 'desconocido') + '</div>'; return; }
            waNcRender(d.results || [], q);
        } catch (e) {
            if (my !== waNcSeq) return;
            console.error(e);
            box.innerHTML = '<div class="wan-empty">No se pudo buscar contactos.</div>';
        }
    }

    window.iniciarChatWA = async function(tel, nombre) {
        tel = (tel || '').trim();
        if (!tel) return;
        const fd = new FormData();
        fd.append('action', 'crear');
        fd.append('telefono', tel);
        fd.append('nombre', (nombre || '').trim());
        try {
            const r = await fetch(waNcApi, { method: 'POST', credentials: 'include', body: fd });
            const d = await r.json();
            if (!d.ok) { alert('No se pudo crear el chat: ' + (d.error || 'desconocido')); return; }
            window.cerrarNuevoChatWA();
            await window.refrescarWAChats(d.chat.id);
            const inp = document.getElementById('waInputMsg');
            if (inp) inp.focus();
        } catch (e) {
            console.error(e);
            alert('Error al crear el chat.');
        }
    };

    (function() {
        const q = document.getElementById('waNcSearch');
        const box = document.getElementById('waNcResults');
        if (q) {
            q.addEventListener('input', () => {
                clearTimeout(waNcTimer);
                const v = q.value.trim();
                if (!v) { waNcSeq++; waNcRender([], ''); return; }
                waNcTimer = setTimeout(() => waNcBuscar(v), 200);
            });
            q.addEventListener('keydown', e => {
                if (e.key === 'Enter') { const first = box.querySelector('.wan-row'); if (first) first.click(); }
                if (e.key === 'Escape') window.cerrarNuevoChatWA();
            });
        }
        if (box) box.addEventListener('click', e => {
            const row = e.target.closest('.wan-row');
            if (row) window.iniciarChatWA(row.dataset.tel, row.dataset.nom);
        });
        document.addEventListener('click', e => {
            if (!e.target.closest('#waNewPop') && !e.target.closest('#waNewChatBtn')) window.cerrarNuevoChatWA();
        });
    })();

    // Carga inicial de chat activo
    if (typeof window.__WA_INIT_CHAT_ID !== 'undefined' && window.__WA_INIT_CHAT_ID) {
        const activePanel = document.querySelector('.msg-panel.active');
        const panelId = activePanel ? activePanel.getAttribute('id') : '';
        const chatEl = document.querySelector('.wa-item[data-chat-id="' + window.__WA_INIT_CHAT_ID + '"]');

        if (panelId === 'panel-whatsapp' && chatEl) {
            window.cargarChatWA(window.__WA_INIT_CHAT_ID, chatEl);
        } else {
            tabBtns.forEach(btn => {
                if (btn.dataset.tab === 'whatsapp') {
                    btn.addEventListener('click', function once() {
                        const elItem = document.querySelector('.wa-item[data-chat-id="' + window.__WA_INIT_CHAT_ID + '"]');
                        if (elItem) window.cargarChatWA(window.__WA_INIT_CHAT_ID, elItem);
                        btn.removeEventListener('click', once);
                    });
                }
            });
        }
    }

});
</script>

<!-- Nota: el modal de compose usa clases del mismo sistema (edit-modal-overlay/edit-input) -->

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
