<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_contacto.php';
require_once __DIR__ . '/includes/email.php';
$base = BASE_URL;

$error = null;
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'nombre'   => trim($_POST['nombre'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'motivo'   => trim($_POST['motivo'] ?? ''),
        'mensaje'  => trim($_POST['mensaje'] ?? ''),
    ];

    if (!$d['nombre'] || !$d['email'] || !$d['mensaje']) {
        $error = 'Completá al menos nombre, email y mensaje.';
    } elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'El email ingresado no es válido.';
    } else {
        contacto_crear($d);

        // Aviso interno a TECNOMEDIC (mismo buzón que usa el resto del sistema)
        try {
            $destino = env('MAIL_CONTACTO', MAIL_FROM);
            $txt = "Nuevo mensaje de contacto\n\nNombre: {$d['nombre']}\nEmail: {$d['email']}\nTeléfono: {$d['telefono']}\nMotivo: {$d['motivo']}\n\nMensaje:\n{$d['mensaje']}";
            enviar_email($destino, 'Nuevo mensaje de contacto – TECNOMEDIC', $txt);
        } catch (Throwable $e) {
            error_log('Error email contacto: ' . $e->getMessage());
        }

        $enviado = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TECNOMEDIC – Contacto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/static/tecnomedic.css">
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
                <div><p style="color:transparent;">.....</p></div>
                <div class="header-badge">
                    <div class="badge-dot"></div>
                    Te respondemos a la brevedad
                </div>
            </header>

            <main class="form-main">
                <div class="form-content">
                    <div class="hero-label">TECNOMEDIC</div>
                    <h1>Hablemos <span>directo</span></h1>
                    <p class="form-subtitle">
                        Dejanos tu consulta y te contactamos. Para reservar un turno,
                        mejor usá <a href="<?= $base ?>/turnos.php" style="color:var(--green);">este formulario</a>.
                    </p>

                    <?php if ($enviado): ?>
                        <div class="form-card" style="text-align:center;">
                            <div style="font-size:2.4rem;margin-bottom:10px;">✅</div>
                            <h2 style="margin:0 0 10px; color:var(--g50);">¡Recibimos tu mensaje!</h2>
                            <p style="color:var(--g100);">Te vamos a responder a la brevedad al email que dejaste.</p>
                            <a href="<?= portal_dashboard_url() ?>" class="submit-btn" style="display:inline-block;margin-top:16px;text-decoration:none;"><?= (portal_logueado() || esta_logueado()) ? 'Volver al panel' : 'Volver al inicio' ?></a>
                        </div>
                    <?php else: ?>

                    <?php if ($error): ?>
                    <div class="form-error">⚠️ <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" class="form-card" action="<?= $base ?>/contacto.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nombre <span style="color:#f87171">*</span></label>
                                <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Email <span style="color:#f87171">*</span></label>
                                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Motivo</label>
                                <div class="select-icon-wrap">
                                    <select name="motivo" class="obra-select">
                                        <option value="">— Seleccioná —</option>
                                        <option value="consulta_general">Consulta general</option>
                                        <option value="turnos">Consulta sobre un turno</option>
                                        <option value="presupuesto">Presupuesto</option>
                                        <option value="tienda">Tienda / productos</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="login-divider"></div>
                        <div class="form-group">
                            <label>Mensaje <span style="color:#f87171">*</span></label>
                            <textarea name="mensaje" rows="5" required style="width:100%;box-sizing:border-box;padding:12px;border-radius:10px;border:1px solid #d1d5db;font-family:inherit;"><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
                        </div>
                        <div class="login-divider"></div>
                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <a href="<?= portal_dashboard_url() ?>" class="btn btn-outline" style="flex:0 0 auto;">
                                <span class="ai-undo"></span> Volver
                            </a>
                            <button type="submit" class="submit-btn" style="flex:1;">Enviar mensaje</button>
                        </div>
                    </form>

                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

</body>
</html>
