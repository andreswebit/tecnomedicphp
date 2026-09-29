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

<div style="padding: 16px 28px 0;">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-family:'Poppins',sans-serif; font-size:20px; font-weight:700; color:#0d1b2a; margin:0;">
                📬 Centro de Comunicaciones
            </h1>
            <p style="font-size:12.5px; color:#64748b; margin:2px 0 0;">
                Gestión unificada de Mensajes Web (Contacto), Correo (Ferozo) y WhatsApp (Twilio)
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

        <!-- PANEL 2: EMAIL -->
        <div class="msg-panel msg-panel-email" id="panel-email">

            <div class="email-sidebar">
                <div class="email-sidebar-head">
                    <div class="account-selector" style="justify-content:space-between; align-items:center;">
                        <div style="font-size:12px; font-weight:800; color:#0d1b2a;">Cuenta</div>
                        <div style="display:flex; gap:6px; align-items:center;">
                            <button class="account-btn active" data-cuenta="contacto">contacto@</button>
                            <button class="account-btn" onclick="window.actualizarEmailsIMAP()" title="Sincronizar IMAP Ferozo" style="margin-left:auto; padding:5px 8px;">🔄 Sync</button>
                        </div>
                    </div>

                    <div class="email-search-wrap">
                        <span>🔍</span>
                        <input type="text" id="emailSearchInput" placeholder="Buscar en bandeja…" />
                    </div>

                    <div class="email-folders" id="emailFoldersBox">
                        <div class="email-folders-title">📁 Carpetas</div>

                        <div class="email-tree">
                            <div class="email-tree-section">
                                <button type="button" class="email-tree-toggle" data-target="#emailTreePredef" aria-expanded="true">
                                    <span class="email-tree-toggle-icon">📄</span>
                                    <span class="email-tree-toggle-text">Bandejas</span>
                                    <span class="email-tree-toggle-caret">▾</span>
                                </button>
                                <div class="email-tree-children is-expanded" id="emailTreePredef">
                                    <div class="email-folder-btns">
                                        <button type="button" class="email-folder-btn active" data-folder="INBOX" onclick="setEmailFolder('INBOX')">📥 Bandeja</button>
                                        <button type="button" class="email-folder-btn" data-folder="DELETED" onclick="setEmailFolder('DELETED')">✅ Eliminado</button>
                                        <button type="button" class="email-folder-btn" data-folder="SPAM" onclick="setEmailFolder('SPAM')">🚫 No deseado</button>
                                        <button type="button" class="email-folder-btn" data-folder="SENT" onclick="setEmailFolder('SENT')">📤 Enviados</button>
                                        <button type="button" class="email-folder-btn" data-folder="TRASH" onclick="setEmailFolder('TRASH')">🗑 Papelera</button>
                                        <button type="button" class="email-folder-btn" data-folder="DRAFTS" onclick="setEmailFolder('DRAFTS')">✍️ Redactar</button>
                                    </div>
                                </div>
                            </div>

                            <div class="email-tree-section">
                                <button type="button" class="email-tree-toggle" data-target="#emailTreeCustom" aria-expanded="true">
                                    <span class="email-tree-toggle-icon">⭐</span>
                                    <span class="email-tree-toggle-text">Personalizadas</span>
                                    <span class="email-tree-toggle-caret">▾</span>
                                </button>
                                <div class="email-tree-children is-expanded" id="emailTreeCustom">
                                    <div class="email-folder-btns" id="emailCustomFolders"></div>
                                </div>
                            </div>
                        </div>

                        <div class="email-folder-create">
                            <input type="text" id="emailCreateFolderInput" placeholder="Nueva carpeta (ej: FACTURAS)" />
                            <button type="button" id="emailCreateFolderBtn" onclick="crearCarpetaEmail()">Crear</button>
                        </div>
                    </div>
                </div>

                <div class="email-list" id="emailList">
                    <?php if ($emails): ?>
                        <?php foreach ($emails as $idx => $m): ?>
                            <div class="email-item <?= $idx === 0 ? 'active' : '' ?> <?= (int)($m['leido'] ?? 0) === 0 ? 'unread' : '' ?>" onclick="cargarEmail(<?= (int)$m['id'] ?>, this)">
                                <?php if ((int)($m['leido'] ?? 0) === 0): ?><div class="unread-indicator"></div><?php endif; ?>
                                <div class="email-card-avatar">
                                    <?= htmlspecialchars(strtoupper(substr($m['remitente_nombre'] ?: $m['remitente_email'], 0, 1)) ?: '?') ?>
                                </div>
                                <div class="ei-content">
                                    <div class="ei-top">
                                        <span class="ei-from"><?= htmlspecialchars($m['remitente_nombre'] ?: $m['remitente_email']) ?></span>
                                        <span class="ei-time"><?= htmlspecialchars(date('H:i', strtotime($m['creado_en'] ?? 'now'))) ?></span>
                                    </div>
                                    <div class="ei-subject"><?= htmlspecialchars($m['asunto']) ?></div>
                                    <div class="ei-preview"><?= htmlspecialchars(mb_substr(strip_tags($m['cuerpo_txt'] ?: ($m['cuerpo_html'] ?? '')), 0, 60)) ?>...</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:24px; text-align:center; color:#94a3b8; font-size:12.5px;">
                            ✉️ Sin correos en la bandeja.<br>
                            <small style="color:#cbd5e1;">Presioná <b>Sync</b> para consultar Ferozo IMAP.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="email-detail" id="emailDetailView">
                <?php if ($emails && isset($emails[0])): $m0 = $emails[0]; ?>
                    <div class="ed-header">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                            <div style="flex:1; min-width:220px;">
                                <h2 class="ed-subject" id="edSubject"><?= htmlspecialchars($m0['asunto']) ?></h2>
                                <div class="ed-meta">
                                    <div class="ed-avatar" id="edAvatar"><?= strtoupper(substr($m0['remitente_nombre'] ?: $m0['remitente_email'], 0, 1)) ?></div>
                                    <div>
                                        <div style="font-size:13px; font-weight:600; color:#0d1b2a;" id="edFrom">
                                            <?= htmlspecialchars($m0['remitente_nombre'] ?: $m0['remitente_email']) ?>
                                            <span style="font-weight:400; color:#64748b;">&lt;<?= htmlspecialchars($m0['remitente_email']) ?>&gt;</span>
                                        </div>
                                        <div style="font-size:11px; color:#94a3b8;" id="edDate">Para: <?= htmlspecialchars($m0['destinatario']) ?> · <?= date('d/m/Y H:i', strtotime($m0['creado_en'])) ?></div>
                                    </div>
                                </div>

                                <div id="edActions" class="ed-actions" style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                                    <button type="button" class="btn btn-outline" onclick="abrirComposeEmail()">➕ Nuevo</button>
                                    <button type="button" class="btn btn-primary" onclick="enviarRespuestaEmail()">↩ Responder</button>
                                    <button type="button" class="btn btn-outpanel" onclick="eliminarEmailActual()">🗑 Eliminar</button>
                                </div>
                            </div>

                            <div style="min-width:220px; display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
                                <div id="edBadgeEstado" style="font-size:11px; color:#64748b;"> </div>
                            </div>
                        </div>
                    </div>

                    <div class="ed-body" id="edBody">
                        <?= nl2br(htmlspecialchars($m0['cuerpo_txt'] ?: strip_tags($m0['cuerpo_html'] ?? ''))) ?>
                    </div>

                    <div class="ed-reply-bar">
                        <input type="text" class="ed-reply-input" id="emailReplyInput" placeholder="Escribir respuesta a <?= htmlspecialchars($m0['remitente_email']) ?>…">
                        <button type="button" class="btn-send-email" onclick="enviarRespuestaEmail()">Responder ➔</button>
                    </div>

                <?php else: ?>
                    <div style="display:flex; flex:1; align-items:center; justify-content:center; color:#94a3b8; flex-direction:column; gap:8px; padding:24px;">
                        <div style="font-size:36px;">📨</div>
                        <div>Selecciona un correo para leerlo</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANEL 3: WHATSAPP -->
        <div class="msg-panel msg-panel-wa" id="panel-whatsapp">

            <div class="wa-sidebar">
                <div class="wa-sidebar-head">
                    <div class="email-search-wrap" style="border:none; background:#f8fafc;">
                        <span>🔍</span>
                        <input type="text" id="waSearchInput" placeholder="Buscar chat por nombre o cel…" />
                    </div>
                    <button class="wa-refresh-btn" id="waRefreshBtn" onclick="refrescarWAChats()" title="Recargar listas desde el backend">🔄 Actualizar</button>
                </div>

                <div class="wa-list" id="waList">
                    <?php if ($wa_chats): ?>
                        <?php foreach ($wa_chats as $idx => $c): ?>
                            <div class="wa-item <?= $idx === 0 ? 'active' : '' ?>" data-chat-id="<?= (int)$c['id'] ?>" onclick="cargarChatWA(<?= (int)$c['id'] ?>, this)">
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
                <?php if ($wa_chats && isset($wa_chats[0])): $c0 = $wa_chats[0]; ?>
                    <script>
                        window.__WA_INIT_CHAT_ID = <?= (int)$c0['id'] ?>;
                    </script>

                    <div class="wa-chat-head">
                        <div class="wa-avatar" id="waHeadAvatar"><?= htmlspecialchars(strtoupper(substr($c0['nombre_contacto'] ?: ($c0['telefono'] ?? ''), 0, 2))) ?></div>
                        <div>
                            <div style="font-weight:600; font-size:14px; color:#0d1b2a;" id="waHeadName"><?= htmlspecialchars($c0['nombre_contacto'] ?: $c0['telefono']) ?></div>
                            <div style="font-size:11px; color:#1aa5a5;" id="waHeadPhone"><?= htmlspecialchars($c0['telefono']) ?> · WhatsApp Business</div>
                        </div>
                    </div>

                    <div class="wa-messages" id="waMessagesArea">
                        <div style="text-align:center; font-size:11px; color:#94a3b8; margin:10px 0;">Conversación iniciada vía Twilio</div>
                    </div>

                    <div class="wa-input-bar">
                        <input type="text" class="wa-input" id="waInputMsg" placeholder="Escribir mensaje de WhatsApp…" onkeypress="if(event.key==='Enter') enviarMensajeWA()">
                        <button class="btn-wa-send" onclick="enviarMensajeWA()" title="Enviar">➔</button>
                    </div>

                <?php else: ?>
                    <div style="display:flex; flex:1; align-items:center; justify-content:center; color:#94a3b8; flex-direction:column; gap:8px;">
                        <div style="font-size:40px;">💬</div>
                        <div>Selecciona un chat para ver la conversación</div>
                    </div>
                <?php endif; ?>
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
            abrirComposeEmail();
            const toInput = document.getElementById('composeToInput');
            const subjInput = document.getElementById('composeSubjectInput');
            const bodyInput = document.getElementById('composeBodyInput');

            if (toInput) toInput.value = toEmail;
            if (subjInput) subjInput.value = 'Respuesta a tu consulta en TecnoMedic' + (motivo ? ' · ' + motivo : '');
            if (bodyInput) bodyInput.value = 'Hola ' + (nombre ? nombre : '') + ',\n\nGracias por comunicarte con TecnoMedic.\n\n';
        }, 150);
    };

    // ── Email: búsqueda rápida (front-end) ──
    const emailSearch = document.getElementById('emailSearchInput');
    if (emailSearch) {
        emailSearch.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#emailList .email-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // ── Email: carpetas (árbol) ──
    window.__EMAIL_CURRENT_FOLDER = 'INBOX';

    const emailFoldersBox = document.getElementById('emailFoldersBox');

    // Collapsible sections (Bandejas / Personalizadas)
    document.querySelectorAll('.email-tree-toggle[data-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetSel = btn.getAttribute('data-target');
            if (!targetSel) return;
            const child = document.querySelector(targetSel);
            if (!child) return;

            const isCollapsed = child.classList.contains('is-collapsed');
            if (isCollapsed) child.classList.remove('is-collapsed');
            else child.classList.add('is-collapsed');

            btn.setAttribute('aria-expanded', String(!isCollapsed));
        });
    });


    function _setFolderActiveUI(folder) {
        if (!emailFoldersBox) return;
        document.querySelectorAll('#emailFoldersBox .email-folder-btn').forEach(btn => {
            const f = (btn.dataset.folder || '').toString();
            if (f === String(folder)) btn.classList.add('active');
            else btn.classList.remove('active');
        });
    }

    window.setEmailFolder = async function(folder) {
        if (!folder) return;
        window.__EMAIL_CURRENT_FOLDER = String(folder);
        _setFolderActiveUI(window.__EMAIL_CURRENT_FOLDER);

        await refrescarEmailsLista(null);

        const firstItem = document.querySelector('#emailList .email-item');
        if (firstItem) {
            firstItem.click();
        } else {
            window.__EMAIL_CURRENT = null;
            const subject = document.getElementById('edSubject');
            const from = document.getElementById('edFrom');
            const date = document.getElementById('edDate');
            const avatar = document.getElementById('edAvatar');
            const body = document.getElementById('edBody');
            if (subject) subject.textContent = '';
            if (from) from.innerHTML = '';
            if (date) date.textContent = '';
            if (avatar) avatar.textContent = '';
            if (body) body.innerHTML = '<div style="color:#94a3b8; text-align:center; padding:30px;">Sin correos en esta carpeta</div>';
        }
    };

    window.cargarCarpetasEmail = async function() {
        let cuenta = 'contacto';
        document.querySelectorAll('.account-btn').forEach(b => {
            if (b.classList.contains('active')) cuenta = b.dataset.cuenta || 'contacto';
        });

        try {
            const url = '<?= b('/api/get_emails.php') ?>?action=folders&cuenta=' + encodeURIComponent(cuenta);
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();
            if (!data.ok) return;

            const customBox = document.getElementById('emailCustomFolders');
            if (!customBox) return;
            customBox.innerHTML = '';

            const predef = new Set(['INBOX','DELETED','SPAM','SENT','TRASH','DRAFTS'].map(x => x.toLowerCase()));
            const folders = (data.folders || []).map(f => String(f)).filter(f => f.trim() !== '');

            // Dejamos solo algunas para que no sature el sidebar
            const max = 14;
            let shown = 0;

            folders.forEach(f => {
                if (shown >= max) return;
                if (predef.has(String(f).toLowerCase())) return;

                shown++;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'email-folder-btn';
                btn.dataset.folder = f;

                const iconMap = {
                    inbox: '📥',
                    deleted: '✅',
                    spam: '🚫',
                    sent: '📤',
                    trash: '🗑',
                    drafts: '✍️'
                };

                const key = String(f).toLowerCase();
                const icon = iconMap[key] || '📁';
                btn.textContent = icon + ' ' + f;

                if (String(window.__EMAIL_CURRENT_FOLDER) === f) btn.classList.add('active');

                btn.addEventListener('click', () => window.setEmailFolder(f));
                customBox.appendChild(btn);
            });
        } catch (e) {
            console.error(e);
        }
    };

    window.crearCarpetaEmail = async function() {
        const input = document.getElementById('emailCreateFolderInput');
        if (!input) return;
        const folderName = String(input.value || '').trim();
        if (!folderName) {
            alert('Ingresá el nombre de la carpeta.');
            return;
        }

        try {
            input.disabled = true;
            const payload = new FormData();
            payload.append('folder_name', folderName);

            let cuenta = 'contacto';
            document.querySelectorAll('.account-btn').forEach(b => {
                if (b.classList.contains('active')) cuenta = b.dataset.cuenta || 'contacto';
            });

            const url = '<?= b('/api/get_emails.php') ?>?action=create_folder&cuenta=' + encodeURIComponent(cuenta);
            const r = await fetch(url, { method: 'POST', credentials: 'include', body: payload });
            const data = await r.json();

            if (!data.ok) {
                alert('No se pudo crear carpeta: ' + (data.error || 'desconocido'));
                return;
            }

            input.value = '';
            await window.cargarCarpetasEmail();
            await window.setEmailFolder(data.folder || folderName);
        } catch (e) {
            console.error(e);
            alert('Error al crear carpeta.');
        } finally {
            input.disabled = false;
        }
    };

    // Inicialización: URL param folder (opcional) + cargar carpetas personalizadas
    (async function initEmailFolders() {
        const params = new URLSearchParams(location.search);
        const f = (params.get('folder') || '').trim();
        if (f) {
            window.__EMAIL_CURRENT_FOLDER = f;
            _setFolderActiveUI(window.__EMAIL_CURRENT_FOLDER);
        }
        await window.cargarCarpetasEmail();
    })();

    // ── Email: cambio de cuenta (contacto / noreply) ──
    document.querySelectorAll('.account-btn[data-cuenta]').forEach(btn => {
        btn.addEventListener('click', async function() {
            document.querySelectorAll('.account-btn[data-cuenta]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            await refrescarEmailsLista();
            const firstItem = document.querySelector('#emailList .email-item');
            if (firstItem) {
                firstItem.click();
            } else {
                window.__EMAIL_CURRENT = null;
                const subject = document.getElementById('edSubject');
                const from = document.getElementById('edFrom');
                const date = document.getElementById('edDate');
                const avatar = document.getElementById('edAvatar');
                const body = document.getElementById('edBody');
                if (subject) subject.textContent = '';
                if (from) from.innerHTML = '';
                if (date) date.textContent = '';
                if (avatar) avatar.textContent = '';
                if (body) body.innerHTML = '<div style="color:#94a3b8; text-align:center; padding:30px;">(Bandeja vacía en esta cuenta)</div>';
            }
        });
    });

    // ── Email: helpers ──
    function escapeHtml(s) {
        return String(s)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function nl2br(s) {
        return String(s).replace(/\n/g, '<br>');
    }

    function sanitizeEmailHtml(html) {
        const div = document.createElement('div');
        div.innerHTML = html;

        const peligrosos = div.querySelectorAll('script, iframe, object, embed');
        peligrosos.forEach(n => n.remove());

        div.querySelectorAll('*').forEach(el => {
            [...el.attributes].forEach(attr => {
                const name = (attr.name || '').toLowerCase();
                const value = (attr.value || '').toString();
                if (name.startsWith('on')) el.removeAttribute(attr.name);
                if ((name === 'href' || name === 'src') && value.trim().toLowerCase().startsWith('javascript:')) {
                    el.removeAttribute(attr.name);
                }
            });
        });

        return div.innerHTML;
    }

    function renderEmailsList(emails) {
        const list = document.getElementById('emailList');
        if (!list) return;

        if (!emails || !emails.length) {
            list.innerHTML = '<div style="padding:24px; text-align:center; color:#94a3b8; font-size:12.5px;">✉️ Sin correos en la bandeja.<br><small style="color:#cbd5e1;">Presioná <b>Sync</b> para consultar Ferozo IMAP.</small></div>';
            return;
        }

        list.innerHTML = '';
        emails.forEach((m, idx) => {
            const unread = String(m.leido) === '0' ? true : false;
            const cls = 'email-item' + (idx === 0 ? ' active' : '') + (unread ? ' unread' : '');
            const unreadDot = unread ? '<div class="unread-indicator"></div>' : '';
            const fromText = (m.remitente_display || m.remitente_email || '').toString();
            const timeText = m.creado_en ? new Date(m.creado_en).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '';
            const preview = (m.preview || '').toString();
            const subject = (m.asunto || '').toString();

            const avatarLetter = fromText ? fromText.substring(0, 1).toUpperCase() : '?';
            const div = document.createElement('div');
            div.className = cls;
            div.setAttribute('onclick', 'cargarEmail(' + m.id + ', this)');
            div.innerHTML = unreadDot +
                '<div class="email-card-avatar">' + escapeHtml(avatarLetter) + '</div>' +
                '<div class="ei-content">' +
                    '<div class="ei-top">' +
                        '<span class="ei-from">' + escapeHtml(fromText) + '</span>' +
                        '<span class="ei-time">' + escapeHtml(timeText) + '</span>' +
                    '</div>' +
                    '<div class="ei-subject">' + escapeHtml(subject) + '</div>' +
                    '<div class="ei-preview">' + escapeHtml(preview) + '</div>' +
                '</div>';

            list.appendChild(div);
        });
    }

    async function refrescarEmailsLista(activeId) {
        const list = document.getElementById('emailList');
        if (!list) return;

        let cuenta = 'contacto';
        document.querySelectorAll('.account-btn').forEach(b => {
            if (b.classList.contains('active')) cuenta = b.dataset.cuenta || 'contacto';
        });

        const folder = (window.__EMAIL_CURRENT_FOLDER || 'INBOX');
        const url = '<?= b('/api/get_emails.php') ?>?action=list&cuenta=' + encodeURIComponent(cuenta) + '&folder=' + encodeURIComponent(folder);
        try {
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();
            if (!data.ok) return;

            const emails = data.emails || [];
            const prevActive = activeId ? String(activeId) : null;
            renderEmailsList(emails);

            if (prevActive) {
                const next = document.querySelector('.email-item[onclick^="cargarEmail(' + prevActive + '"]');
                // (fallback) si no coincide por selector, igual no pasa nada.
            }
        } catch (e) {
            console.error(e);
        }
    }

    window.actualizarEmailsIMAP = async function() {
        const btn = document.querySelector('[data-tab="email"]');
        try {
            window.actualizarEmailsIMAP._busy = true;
            if (btn) btn.disabled = true;

            const accountBtns = document.querySelectorAll('.account-btn');
            let cuenta = 'contacto';
            accountBtns.forEach(b => { if (b.classList.contains('active')) cuenta = b.dataset.cuenta || 'contacto'; });

            const folder = (window.__EMAIL_CURRENT_FOLDER || 'INBOX');
            const url = '<?= b('/api/get_emails.php') ?>?action=sync&cuenta=' + encodeURIComponent(cuenta) + '&folder=' + encodeURIComponent(folder);
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();

            if (!data.ok) {
                alert('Error sync email: ' + (data.error || 'desconocido'));
                return;
            }

            if (btn) btn.disabled = false;
            renderEmailsList(data.emails || []);
        } catch (e) {
            console.error(e);
            alert('No se pudo sincronizar IMAP. Revisa logs / credenciales IMAP.');
        } finally {
            window.actualizarEmailsIMAP._busy = false;
            if (btn) btn.disabled = false;
        }
    };

    window.cargarEmail = async function(id, el) {
        document.querySelectorAll('.email-item').forEach(i => i.classList.remove('active'));
        if (el) el.classList.add('active');

        const loading = document.getElementById('edBody');
        if (loading) loading.innerHTML = '<div style="color:#94a3b8;">Cargando…</div>';

        try {
            const url = '<?= b('/api/get_emails.php') ?>?action=detail&id=' + encodeURIComponent(id);
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();
            if (!data.ok) {
                alert('No se pudo cargar email: ' + (data.error || 'desconocido'));
                return;
            }

            const email = data.email;
            window.__EMAIL_CURRENT = email;

            const subject = document.getElementById('edSubject');
            const from = document.getElementById('edFrom');
            const date = document.getElementById('edDate');
            const avatar = document.getElementById('edAvatar');
            const body = document.getElementById('edBody');

            if (subject) subject.textContent = email.asunto || '';
            if (from) {
                const name = (email.remitente_nombre || email.remitente_email || '');
                const mail = email.remitente_email ? (' <span style="font-weight:400; color:#64748b;">&lt;' + escapeHtml(email.remitente_email) + '&gt;</span>') : '';
                from.innerHTML = escapeHtml(name) + mail;
            }
            if (date) {
                const para = email.destinatario ? escapeHtml(email.destinatario) : '';
                const dt = email.creado_en ? new Date(email.creado_en) : null;
                const f = dt ? dt.toLocaleString([], {year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit'}) : '';
                date.textContent = 'Para: ' + para + (f ? ' · ' + f : '');
            }
            if (avatar) {
                const txt = (email.remitente_nombre || email.remitente_email || '?').toString();
                avatar.textContent = txt.substring(0, 1).toUpperCase();
            }

            if (body) {
                if (email.cuerpo_txt && email.cuerpo_txt.trim() !== '') {
                    body.innerHTML = nl2br(escapeHtml(email.cuerpo_txt));
                } else if (email.cuerpo_html && email.cuerpo_html.trim() !== '') {
                    body.innerHTML = sanitizeEmailHtml(email.cuerpo_html);
                } else {
                    body.innerHTML = '<div style="color:#94a3b8;">(Sin contenido)</div>';
                }
            }

            await refrescarEmailsLista(id);
        } catch (e) {
            console.error(e);
            alert('Error al cargar email.');
        }
    };

    // ── Responder (ya existía) ──
    window.enviarRespuestaEmail = async function() {
        const input = document.getElementById('emailReplyInput');
        if (!input || !input.value.trim()) return;

        const email = window.__EMAIL_CURRENT;
        if (!email || !email.remitente_email) {
            alert('Seleccioná un email para responder.');
            return;
        }

        const to = email.remitente_email;
        const asuntoBase = (email.asunto || '').toString().trim();
        const subject = asuntoBase ? ('Re: ' + asuntoBase) : 'Respuesta';
        const message = input.value.trim();

        input.disabled = true;
        try {
            const payload = new FormData();
            payload.append('to', to);
            payload.append('subject', subject);
            payload.append('message', message);

            const r = await fetch('<?= b('/api/send_email.php') ?>', {
                method: 'POST',
                credentials: 'include',
                body: payload
            });
            const data = await r.json();

            if (!data.ok) {
                alert('No se pudo enviar la respuesta: ' + (data.error || 'desconocido'));
                return;
            }

            alert('Respuesta enviada ✅');
            input.value = '';
        } catch (e) {
            console.error(e);
            alert('Error al enviar la respuesta de email.');
        } finally {
            input.disabled = false;
        }
    };

    // ── UI: Compose (➕ Nuevo) ──
    window.abrirComposeEmail = function() {
        let overlay = document.getElementById('modalComposeEmail');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'modalComposeEmail';
            overlay.className = 'edit-modal-overlay';
            overlay.innerHTML = `
                <div class="edit-modal" style="max-width:700px;">
                    <div class="edit-modal-header">
                        <div class="edit-modal-title">✉️ Nuevo mensaje</div>
                        <button type="button" class="edit-modal-close" id="composeCloseBtn">✕</button>
                    </div>

                    <div style="padding-top:10px; padding-bottom:10px;">
                        <div style="display:flex; gap:12px; flex-wrap:wrap;">
                            <div style="flex:1; min-width:220px;">
                                <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">Para</div>
                                <input id="composeToInput" class="edit-input" type="email" placeholder="email@dominio.com" />
                            </div>
                            <div style="flex:1; min-width:220px;">
                                <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">Asunto</div>
                                <input id="composeSubjectInput" class="edit-input" type="text" placeholder="Asunto del mensaje" />
                            </div>
                        </div>

                        <div style="margin-top:12px;">
                            <div style="font-size:12px; font-weight:700; color:#64748b; margin-bottom:6px;">Mensaje</div>
                            <textarea id="composeBodyInput" class="edit-input" rows="8" style="resize:none;" placeholder="Escribí tu mensaje..."></textarea>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:12px; border-top:1px solid rgba(226,232,240,0.9);">
                        <button type="button" class="btn btn-outline" id="composeCancelBtn">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="composeSendBtn">Enviar ➔</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            const cancelBtn = document.getElementById('composeCancelBtn');
            const closeBtn = document.getElementById('composeCloseBtn');
            if (cancelBtn) cancelBtn.addEventListener('click', () => closeComposeEmail());
            if (closeBtn) closeBtn.addEventListener('click', () => closeComposeEmail());

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeComposeEmail();
            });

            const sendBtn = document.getElementById('composeSendBtn');
            if (sendBtn) {
                sendBtn.addEventListener('click', async () => {
                    await enviarComposeEmail();
                });
            }
        }

        const email = window.__EMAIL_CURRENT;
        const toInput = document.getElementById('composeToInput');
        const subjInput = document.getElementById('composeSubjectInput');
        const bodyInput = document.getElementById('composeBodyInput');

        if (toInput) toInput.value = (email && email.remitente_email) ? email.remitente_email : '';
        if (subjInput) {
            if (!subjInput.value.trim()) subjInput.value = (email && email.asunto) ? ('Re: ' + String(email.asunto).trim()) : '';
        }
        if (bodyInput) bodyInput.value = '';

        overlay.classList.add('open');

        setTimeout(() => {
            const input = document.getElementById('composeBodyInput');
            if (input) input.focus();
        }, 50);
    };

    window.enviarComposeEmail = async function() {
        const overlay = document.getElementById('modalComposeEmail');
        if (!overlay) return;

        const to = (document.getElementById('composeToInput')?.value || '').trim();
        const subject = (document.getElementById('composeSubjectInput')?.value || '').trim();
        const body = (document.getElementById('composeBodyInput')?.value || '').trim();

        if (!to || !subject || !body) {
            alert('Completá Para, Asunto y Mensaje.');
            return;
        }

        const sendBtn = document.getElementById('composeSendBtn');
        if (sendBtn) sendBtn.disabled = true;

        try {
            const payload = new FormData();
            payload.append('to', to);
            payload.append('subject', subject);
            payload.append('message', body);

            const r = await fetch('<?= b('/api/send_email.php') ?>', {
                method: 'POST',
                credentials: 'include',
                body: payload
            });
            const data = await r.json();

            if (!data.ok) {
                alert('No se pudo enviar el mensaje: ' + (data.error || 'desconocido'));
                return;
            }

            alert('Mensaje enviado ✅');
            closeComposeEmail();
        } catch (e) {
            console.error(e);
            alert('Error al enviar el mensaje.');
        } finally {
            if (sendBtn) sendBtn.disabled = false;
        }
    };

    window.closeComposeEmail = function() {
        const overlay = document.getElementById('modalComposeEmail');
        if (!overlay) return;
        overlay.classList.remove('open');
    };

    // ── UI: Eliminar (🗑) ──
    window.eliminarEmailActual = async function() {
        const email = window.__EMAIL_CURRENT;
        if (!email || !email.id) {
            alert('Seleccioná un email para eliminar.');
            return;
        }

        const ok = confirm('¿Eliminar este correo de la bandeja (cache local)?');
        if (!ok) return;

        try {
            const payload = new FormData();
            payload.append('id', String(email.id));

            const r = await fetch('<?= b('/api/delete_email.php') ?>', {
                method: 'POST',
                credentials: 'include',
                body: payload
            });

            const data = await r.json();
            if (!data.ok) {
                alert('No se pudo eliminar: ' + (data.error || 'desconocido'));
                return;
            }

            alert('Correo eliminado ✅');
            window.__EMAIL_CURRENT = null;

            // Limpia vista
            const subject = document.getElementById('edSubject');
            const from = document.getElementById('edFrom');
            const date = document.getElementById('edDate');
            const avatar = document.getElementById('edAvatar');
            const body = document.getElementById('edBody');

            if (subject) subject.textContent = '';
            if (from) from.innerHTML = '';
            if (date) date.textContent = '';
            if (avatar) avatar.textContent = '';
            if (body) body.innerHTML = '<div style="color:#94a3b8;">Seleccioná un correo para leerlo</div>';

            await refrescarEmailsLista(null);
        } catch (e) {
            console.error(e);
            alert('Error al eliminar el correo.');
        }
    };

    // ── WhatsApp: refrescar chats ──
    window.refrescarWAChats = async function() {
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

            chats.forEach((c, idx) => {
                const div = document.createElement('div');
                div.className = 'wa-item' + (idx === 0 ? ' active' : '');
                div.setAttribute('data-chat-id', c.id);
                div.setAttribute('onclick', 'cargarChatWA(' + c.id + ', this)');

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

            const firstId = chats[0].id;
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
        if (el) el.classList.add('active');

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
