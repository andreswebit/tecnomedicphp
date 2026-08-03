<?php require_once __DIR__ . '/includes/db.php'; $base = BASE_URL; ?>

<?php require_once __DIR__ . '/includes/db.php'; ?>
<?php ini_set('display_errors', 1);
error_reporting(E_ALL); ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>TECNOMEDIC – Solicitar Turno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/static/tecnomedic.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
</head>

<body class="page-form">

    <div class="form-square">
        <div class="square"></div>
        <div class="square"></div>
        <div class="square"></div>
    </div>

    <div style="display:flex;min-height:100vh;position:relative;z-index:1;">
        <div class="wrapper-form" style="flex:1;">
            <header class="site-header">
                <div class="logo">
                    <a href="<?= HOME_URL ?>/">
                        <img src="<?= $base ?>/static/img/tecno-logo.jpeg" alt="TECNOMEDIC" class="logo-img">
                    </a>
                </div>
                <div>
                    <p style="color:transparent;">.....</p>
                </div>
                <div class="header-badge">
                    <div class="badge-dot"></div>
                    Turnos disponibles
                </div>
            </header>

            <main class="form-main">
                <div class="form-content">
                    <div class="hero-label">Cámara Hiperbárica</div>
                    <h1>Solicitá tu <span>turno</span><br>en línea</h1>
                    <p class="form-subtitle">Elegí fecha y horario. Te confirmamos a la brevedad.</p>

                    <?php if (!empty($error)): ?>
                    <div class="form-error">⚠️ <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="form-card">
                        <form action="<?= $base ?>/guardar.php" method="post" id="turnoForm">
                            <input type="hidden" name="fecha" id="fechaHoja">
                            <input type="hidden" name="hora" id="horaElegida">

                            <div class="form-grid">

                                <div class="form-group">
                                    <label>Nombre <span style="color:#f87171">*</span></label>
                                    <div class="input-wrap">
                                        <input type="text" name="nombre" placeholder="Tu nombre" required
                                            autocomplete="given-name"
                                            value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                                        <span class="input-icon">👤</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Apellido <span style="color:#f87171">*</span></label>
                                    <div class="input-wrap">
                                        <input type="text" name="apellido" placeholder="Tu apellido" required
                                            autocomplete="family-name"
                                            value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                                        <span class="input-icon">👤</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>DNI <span
                                            style="color:var(--muted);font-size:10px;">(opcional)</span></label>
                                    <div class="input-wrap">
                                        <input type="text" name="dni" placeholder="Ej: 30.123.456" inputmode="numeric"
                                            pattern="[\d\.\-]*" value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
                                        <span class="input-icon">🪪</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Obra social <span
                                            style="color:var(--muted);font-size:10px;">(opcional)</span></label>
                                    <div class="select-icon-wrap">
                                        <span class="input-icon">🏥</span>
                                        <select name="obra_social" id="obraSocialSelect" class="obra-select">
                                            <option value="">— Seleccioná —</option>
                                            <?php
                                        $obras = ['Particular','PAMI','IOSCOR','OSDE','Swiss Medical','Galeno','Medifé','OSECAC','OSPAT','IOMA','Otra'];
                                        $sel   = $_POST['obra_social'] ?? '';
                                        foreach ($obras as $o):
                                        ?>
                                            <option value="<?= $o ?>" <?= $sel===$o?'selected':'' ?>>
                                                <?= $o === 'Particular' ? '✦ Particular' : $o ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Teléfono <span style="color:#f87171">*</span></label>
                                    <div class="input-wrap">
                                        <input type="tel" name="telefono" placeholder="+54 9 3794 …" required
                                            autocomplete="tel"
                                            value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                                        <span class="input-icon">📱</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Email <span style="color:#f87171">*</span></label>
                                    <div class="input-wrap">
                                        <input type="email" name="email" placeholder="correo@mail.com" required
                                            autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                        <span class="input-icon">✉</span>
                                    </div>
                                </div>

                                <div class="form-group full">
                                    <label>Fecha <span style="color:#f87171">*</span></label>
                                    <div class="input-wrap">
                                        <input type="text" id="dateInput" placeholder="Hacé click para elegir fecha"
                                            readonly>
                                        <span class="input-icon">📅</span>
                                    </div>
                                </div>

                            </div>

                            <div class="slots-section" id="slotsSection" style="display:none;">
                                <div class="slots-label">Horarios disponibles</div>
                                <div class="slots-note">🕐 Sesión de 1h 15min · Hasta 2 personas por horario</div>
                                <div class="slots-grid" id="slotsGrid"></div>
                            </div>

                            <div class="form-divider" style="margin-top:24px;"></div>

                            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                                <a href="<?= HOME_URL ?>/" class="btn btn-outline" style="flex:0 0 auto;">
                                    <span class="ai-undo"></span> Volver
                                </a>
                                <button type="submit" class="submit-btn" id="submitBtn" disabled style="flex:1;">
                                    ✦ &nbsp; Solicitar Turno
                                </button>
                            </div>

                        </form>
                        <p class="form-note">
                            Te enviaremos confirmación por email y WhatsApp.<br>
                            Los campos con <span style="color:#f87171;font-size:18px"> * </span> son obligatorios.
                        </p>
                    </div>

                    <div class="features">
                        <div class="feature">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M1.475 9C2.702 10.84 4.779 12.871 8 15c3.221-2.129 5.298-4.16 6.525-6H12a.5.5 0 0 1-.464-.314l-1.457-3.642-1.598 5.593a.5.5 0 0 1-.945.049L5.889 6.568l-1.473 2.21A.5.5 0 0 1 4 9z" />
                                    <path
                                        d="M.88 8C-2.427 1.68 4.41-2 7.823 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C11.59-2 18.426 1.68 15.12 8h-2.783l-1.874-4.686a.5.5 0 0 0-.945.049L7.921 8.956 6.464 5.314a.5.5 0 0 0-.88-.091L3.732 8z" />
                                </svg>
                            </div>
                            <div class="feature-text">Oxigenoterapia<br>hiperbárica</div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                                </svg>
                            </div>
                            <div class="feature-text">Equipo<br>médico</div>
                        </div>
                        <div class="feature">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M2 2A2 2 0 0 0 .05 3.555L8 8.414l7.95-4.859A2 2 0 0 0 14 2zm-2 9.8V4.698l5.803 3.546zm6.761-2.97-6.57 4.026A2 2 0 0 0 2 14h6.256A4.5 4.5 0 0 1 8 12.5a4.49 4.49 0 0 1 1.606-3.446l-.367-.225L8 9.586zM16 9.671V4.697l-5.803 3.546.338.208A4.5 4.5 0 0 1 12.5 8c1.414 0 2.675.652 3.5 1.671" />
                                </svg>
                            </div>
                            <div class="feature-text">Confirmación<br>por email</div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="site-footer">© <?= date('Y') ?> TECNOMEDIC · C. Pellegrini 799 · Corrientes</footer>
        </div>
    </div>

    <script>
    flatpickr("#dateInput", {
        locale: "es",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        minDate: "today",
        disable: [function(date) {
            return date.getDay() === 0 || date.getDay() === 6;
        }],
        onChange: function(selectedDates, dateStr) {
            if (dateStr) cargarHorarios(dateStr);
        }
    });

    function cargarHorarios(fechaISO) {
        var section = document.getElementById('slotsSection');
        var grid = document.getElementById('slotsGrid');
        var submit = document.getElementById('submitBtn');
        document.getElementById('horaElegida').value = '';
        document.getElementById('fechaHoja').value = '';
        submit.disabled = true;
        section.style.display = 'block';
        grid.innerHTML = '<div class="slots-loading">⏳ Consultando disponibilidad…</div>';
        fetch('<?= $base ?>/api/horarios.php?fecha=' + fechaISO)
            .then(function(r) {
                if (!r.ok) throw new Error('Error ' + r.status);
                return r.json();
            })
            .then(function(data) {
                renderSlots(data.slots, data.fecha);
            })
            .catch(function(err) {
                grid.innerHTML =
                    '<div class="slots-loading" style="color:#f87171">❌ Error al consultar. Intentá de nuevo.</div>';
            });
    }

    function renderSlots(slots, fechaHoja) {
        var grid = document.getElementById('slotsGrid');
        grid.innerHTML = '';
        var hayLibres = slots.some(function(s) {
            return s.disponible;
        });
        if (!hayLibres) {
            grid.innerHTML =
                '<div class="slots-loading">😔 No hay horarios disponibles para esta fecha. Por favor elegí otra.</div>';
            return;
        }
        var manana = slots.filter(function(s) {
            return s.hora <= "12:00";
        });
        var tarde = slots.filter(function(s) {
            return s.hora >= "16:00";
        });

        function addGroup(label, group) {
            if (!group.length) return;
            var sep = document.createElement('div');
            sep.className = 'slots-period';
            sep.textContent = label;
            grid.appendChild(sep);
            group.forEach(function(slot) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'slot-btn ' + (slot.disponible ? 'disponible' : 'lleno');
                btn.disabled = !slot.disponible;
                var badge = slot.disponible ? (slot.libres === 2 ? 'Libre' : '1 lugar') : 'Completo';
                btn.innerHTML = '<span class="slot-time">' + slot.hora + '</span><span class="slot-badge">' +
                    badge + '</span>';
                if (slot.disponible) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.slot-btn.seleccionado').forEach(function(b) {
                            b.classList.replace('seleccionado', 'disponible');
                        });
                        btn.classList.replace('disponible', 'seleccionado');
                        document.getElementById('horaElegida').value = slot.hora;
                        document.getElementById('fechaHoja').value = fechaHoja;
                        document.getElementById('submitBtn').disabled = false;
                    });
                }
                grid.appendChild(btn);
            });
        }
        addGroup('☀️  Mañana', manana);
        addGroup('🌙  Tarde', tarde);
    }

    document.getElementById('turnoForm').addEventListener('submit', function(e) {
        var hora = document.getElementById('horaElegida').value;
        var fecha = document.getElementById('fechaHoja').value;
        if (!hora || !fecha) {
            e.preventDefault();
            alert('Por favor seleccioná una fecha y un horario disponible.');
            return;
        }
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').textContent = '⏳ Enviando…';
    });

    document.querySelectorAll('.form-group').forEach(function(el, i) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(12px)';
        el.style.transition = 'opacity .4s ease, transform .4s ease';
        setTimeout(function() {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 200 + i * 60);
    });
    </script>
</body>

</html>