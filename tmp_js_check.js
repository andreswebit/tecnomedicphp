document.addEventListener('DOMContentLoaded', function() {
    // ── Helper de escape HTML ──
    function escapeHtml(s) {
        return String(s || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function nl2br(s) {
        return String(s || '').replace(/\n/g, '<br>');
    }

    // ── Toast Centrado en Pantalla ──
    window.mostrarToast = function(mensaje, tipo = 'success', duracion = 2800) {
        let toast = document.getElementById('msgToastCenter');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'msgToastCenter';
            toast.className = 'msg-toast-center';
            document.body.appendChild(toast);
        }

        let icon = '';
        if (tipo === 'success') {
            if (!mensaje.includes('✅') && !mensaje.includes('✓')) icon = '✅ ';
        } else if (tipo === 'error') {
            if (!mensaje.includes('⚠️') && !mensaje.includes('❌')) icon = '❌ ';
        } else if (tipo === 'warning') {
            if (!mensaje.includes('⚠️')) icon = '⚠️ ';
        } else if (tipo === 'info') {
            if (!mensaje.includes('ℹ️') && !mensaje.includes('🔄')) icon = 'ℹ️ ';
        }

        toast.className = 'msg-toast-center ' + tipo;
        toast.innerHTML = '<span>' + icon + escapeHtml(mensaje) + '</span>';

        toast.classList.remove('show');
        void toast.offsetWidth; // Forzar reflow para reiniciar animación
        toast.classList.add('show');

        if (window._msgToastTimer) clearTimeout(window._msgToastTimer);
        window._msgToastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, duracion);
    };

    // Detección de ?ok=1 para confirmar guardado/cambio de estado
    const urlParams = new URLSearchParams(location.search);
    if (urlParams.get('ok') === '1') {
        mostrarToast('Acción guardada correctamente', 'success');
        const u = new URL(location.href);
        u.searchParams.delete('ok');
        history.replaceState({}, '', u.toString());
    }

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

            const div = document.createElement('div');
            div.className = cls;
            div.setAttribute('onclick', 'cargarEmail(' + m.id + ', this)');
            div.innerHTML = unreadDot +
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

        const url = '"__PHP__"?action=list&cuenta=' + encodeURIComponent(cuenta);
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

            const url = '"__PHP__"?action=sync&cuenta=' + encodeURIComponent(cuenta);
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();

            if (!data.ok) {
                mostrarToast('Error al sincronizar email: ' + (data.error || 'desconocido'), 'error');
                return;
            }

            if (btn) btn.disabled = false;
            renderEmailsList(data.emails || []);
            mostrarToast('Bandeja sincronizada correctamente', 'success');
        } catch (e) {
            console.error(e);
            mostrarToast('No se pudo sincronizar IMAP. Verifique logs / credenciales.', 'error');
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
            const url = '"__PHP__"?action=detail&id=' + encodeURIComponent(id);
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();
            if (!data.ok) {
                mostrarToast('No se pudo cargar email: ' + (data.error || 'desconocido'), 'error');
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
            mostrarToast('Error al cargar el email.', 'error');
        }
    };

    // ── Responder (ya existía) ──
    window.enviarRespuestaEmail = async function() {
        const input = document.getElementById('emailReplyInput');
        if (!input || !input.value.trim()) return;

        const email = window.__EMAIL_CURRENT;
        if (!email || !email.remitente_email) {
            mostrarToast('Seleccioná un email para responder.', 'warning');
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

            const r = await fetch('"__PHP__"', {
                method: 'POST',
                credentials: 'include',
                body: payload
            });
            const data = await r.json();

            if (!data.ok) {
                mostrarToast('No se pudo enviar la respuesta: ' + (data.error || 'desconocido'), 'error');
                return;
            }

            mostrarToast('Respuesta enviada con éxito', 'success');
            input.value = '';
        } catch (e) {
            console.error(e);
            mostrarToast('Error al enviar la respuesta de email.', 'error');
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
            mostrarToast('Completá Para, Asunto y Mensaje.', 'warning');
            return;
        }

        const sendBtn = document.getElementById('composeSendBtn');
        if (sendBtn) sendBtn.disabled = true;

        try {
            const payload = new FormData();
            payload.append('to', to);
            payload.append('subject', subject);
            payload.append('message', body);

            const r = await fetch('"__PHP__"', {
                method: 'POST',
                credentials: 'include',
                body: payload
            });
            const data = await r.json();

            if (!data.ok) {
                mostrarToast('No se pudo enviar el mensaje: ' + (data.error || 'desconocido'), 'error');
                return;
            }

            mostrarToast('Mensaje enviado con éxito', 'success');
            closeComposeEmail();
        } catch (e) {
            console.error(e);
            mostrarToast('Error al enviar el mensaje.', 'error');
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
            mostrarToast('Seleccioná un email para eliminar.', 'warning');
            return;
        }

        const ok = confirm('¿Eliminar este correo de la bandeja (cache local)?');
        if (!ok) return;

        try {
            const payload = new FormData();
            payload.append('id', String(email.id));

            const r = await fetch('"__PHP__"', {
                method: 'POST',
                credentials: 'include',
                body: payload
            });

            const data = await r.json();
            if (!data.ok) {
                mostrarToast('No se pudo eliminar: ' + (data.error || 'desconocido'), 'error');
                return;
            }

            mostrarToast('Correo eliminado correctamente', 'success');
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
            mostrarToast('Error al eliminar el correo.', 'error');
        }
    };

    // ── WhatsApp: refrescar chats ──
    window.refrescarWAChats = async function() {
        const btn = document.getElementById('waRefreshBtn');
        if (btn) btn.disabled = true;

        try {
            const r = await fetch('"__PHP__"', {
                method: 'GET',
                credentials: 'include'
            });
            const data = await r.json();
            if (!data.ok) {
                mostrarToast('No se pudo refrescar chats WA: ' + (data.error || 'desconocido'), 'warning');
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
            mostrarToast('Error al refrescar chats WA.', 'error');
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
            const url = '"__PHP__"?chat_id=' + encodeURIComponent(id);
            const r = await fetch(url, { method: 'GET', credentials: 'include' });
            const data = await r.json();
            if (!data.ok) {
                mostrarToast('No se pudo cargar la conversación: ' + (data.error || 'desconocido'), 'error');
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
                await fetch('"__PHP__"', {
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
            mostrarToast('Error al cargar conversación WA.', 'error');
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
            mostrarToast('Seleccioná un chat para enviar.', 'warning');
            window.__WA_LAST_SENT_LOCK = false;
            return;
        }

        const chatIdNum = parseInt(chat_id, 10);

        if (msgArea) {
            const bubble = document.createElement('div');
            bubble.className = 'wa-bubble out';
            bubble.innerHTML = escapeHtml(msgText) + '<div class="wa-bubble-time">' + new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) + '</div>';
            msgArea.appendChild(bubble);
            msgArea.scrollTop = msgArea.scrollHeight;
        }

        const payload = new FormData();
        payload.append('chat_id', chatIdNum);
        payload.append('mensaje', msgText);

        fetch('"__PHP__"', {
            method: 'POST',
            credentials: 'include',
            body: payload
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) {
                mostrarToast('No se pudo enviar el mensaje: ' + (data.error || 'desconocido'), 'error');
                return;
            }
            mostrarToast('Mensaje de WhatsApp enviado', 'success');
            return window.cargarChatWA(chatIdNum, activeChatEl);
        })
        .catch(err => {
            console.error(err);
            mostrarToast('Error al enviar mensaje WA.', 'error');
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