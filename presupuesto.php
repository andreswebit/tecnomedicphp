<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_presupuestos.php';
require_once __DIR__ . '/includes/email.php';
$base = BASE_URL;

$error = null;
$enviado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'nombre'      => trim($_POST['nombre'] ?? ''),
        'apellido'    => trim($_POST['apellido'] ?? ''),
        'dni_cuit'    => preg_replace('/\D/', '', $_POST['dni_cuit'] ?? ''),
        'telefono'    => trim($_POST['telefono'] ?? ''),
        'email'       => trim($_POST['email'] ?? ''),
        'area'        => trim($_POST['area'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
    ];

    if (!$d['nombre'] || !$d['apellido'] || !$d['dni_cuit'] || !$d['email'] || !$d['descripcion']) {
        $error = 'Completá nombre, apellido, DNI/CUIT, email y el detalle de lo que necesitás presupuestar.';
    } elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'El email ingresado no es válido.';
    } else {
        presupuesto_crear($d);
        try {
            email_presupuesto_solicitud($d);
        } catch (Throwable $e) {
            error_log('Error email presupuesto: ' . $e->getMessage());
        }
        $enviado = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TECNOMEDIC – Solicitar presupuesto</title>
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
                    Te lo enviamos por email
                </div>
            </header>

            <main class="form-main">
                <div class="form-content">
                    <div class="hero-label">TECNOMEDIC</div>
                    <h1>Pedí tu <span>presupuesto</span></h1>
                    <p class="form-subtitle">
                        Contanos qué necesitás y te mandamos el presupuesto por email.
                        Con tu  DNI/CUIT  también podés consultarlo después por WhatsApp en nuestro BOt.
                    </p>

                    <?php if ($enviado): ?>
                        <div class="form-card" style="text-align:center;">
                            <div style="font-size:2.4rem;margin-bottom:10px;">✅</div>
                            <h2 style="margin:0 0 10px;">¡Recibimos tu solicitud!</h2>
                            <p style="color:#64748b;">Te vamos a enviar el presupuesto por email a la brevedad.</p>
                            <a href="<?= portal_dashboard_url() ?>" class="submit-btn" style="display:inline-block;margin-top:16px;text-decoration:none;"><?= (portal_logueado() || esta_logueado()) ? 'Volver al panel' : 'Volver al inicio' ?></a>
                        </div>
                    <?php else: ?>

                    <?php if ($error): ?>
                    <div class="form-error">⚠️ <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="post" class="form-card" action="<?= $base ?>/presupuesto.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nombre <span style="color:#f87171">*</span></label>
                                <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Apellido <span style="color:#f87171">*</span></label>
                                <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>DNI o CUIT/CUIL <span style="color:#f87171">*</span></label>
                                <input type="text" name="dni_cuit" required value="<?= htmlspecialchars($_POST['dni_cuit'] ?? '') ?>">
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
                                <label>Área / Servicio</label>
                                <div class="select-icon-wrap">
                                    <select name="area" class="obra-select">
                                        <option value="">— Seleccioná —</option>
                                        <option value="audiologia">Audiología</option>
                                        <option value="hiperbarica">Medicina Hiperbárica</option>
                                        <option value="nutricion">Nutrición</option>
                                        <option value="ortopedia">Ortopedia y Rehabilitación</option>
                                        <option value="equipamiento">Equipamiento Médico y Quirúrgico</option>
                                        <option value="tienda">Tienda / Productos</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>¿Qué necesitás presupuestar? <span style="color:#f87171">*</span></label>
                            <textarea name="descripcion" rows="4" required placeholder="Ej: Silla de ruedas plegable, sesiones de fonoaudiología..." style="width:100%;box-sizing:border-box;padding:12px;border-radius:10px;border:1px solid #d1d5db;font-family:inherit;"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="form-divider" style="margin-top:24px;"></div>
                        <button type="submit" class="submit-btn">Solicitar presupuesto</button>
                        <div class="form-divider" style="margin-top:24px;"></div>
                            <a href="<?= portal_dashboard_url() ?>" class="btn btn-outline" style="flex:0 0 auto;">
                                    <span class="ai-undo"></span> Volver
                            </a>
                        
                    </form>

                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

</body>
</html>
