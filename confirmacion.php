<?php
// Este archivo se incluye desde guardar.php con la variable $turno ya definida.
// Si alguien entra directo por URL sin datos, lo mandamos al form.
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
iniciar_sesion_php();

$base = BASE_URL;

if (!isset($turno) || empty($turno)) {
    header('Location: ' . $base . '/turnos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Turno Agendado – TECNOMEDIC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/static/tecnomedic.css">
</head>
<body class="page-confirm">

    <div class="confirm-square" style="z-index: 999;">
        <div class="square"></div><div class="square"></div><div class="square"></div>
    </div>

    <div class="confirm-page">
        <aside class="sidebar" style="width: 16rem;">
            <div class="confirm-logo">
                <a href="<?= HOME_URL ?>/">
                    <img src="<?= $base ?>/static/img/tecno-logo.jpeg" alt="TECNOMEDIC" class="logo-icon" style="width:15rem;height:15rem;">
                </a>
            </div>
        </aside>

        <div class="success-ring">
            <span class="checkmark">✓</span>
        </div>

        <div class="confirm-heading">
            <div class="label-tag">✦ Solicitud enviada</div>
            <br>
            <h1>¡Turno agendado!</h1>
            <br>
        </div>

        <!-- Card con datos del turno -->
        <div class="turno-card">
            <div class="turno-card-header">
                <div class="turno-card-title">Detalle del turno</div>
                <div class="status-pill">● Pendiente de confirmación</div>
            </div>

            <div class="data-grid">
                <div class="data-item full">
                    <div class="data-icon">👤</div>
                    <div class="data-label">Paciente</div>
                    <div class="data-value">
                        <?= htmlspecialchars($turno['nombre'] . ' ' . $turno['apellido']) ?>
                        <?php if (!empty($turno['dni'])): ?>
                        <span style="font-size:12px;color:var(--green)"> · DNI <?= htmlspecialchars($turno['dni']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($turno['obra_social'])): ?>
                <div class="data-item full">
                    <div class="data-icon">🏥</div>
                    <div class="data-label">Obra social</div>
                    <div class="data-value"><?= htmlspecialchars($turno['obra_social']) ?></div>
                </div>
                <?php endif; ?>

                <div class="data-item">
                    <div class="data-icon">📅</div>
                    <div class="data-label">Fecha</div>
                    <div class="data-value"><?= htmlspecialchars($turno['fecha']) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-icon">🕐</div>
                    <div class="data-label">Hora</div>
                    <div class="data-value"><?= htmlspecialchars($turno['hora']) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-icon">📱</div>
                    <div class="data-label">Teléfono</div>
                    <div class="data-value"><?= htmlspecialchars($turno['telefono']) ?></div>
                </div>
                <div class="data-item">
                    <div class="data-icon">✉</div>
                    <div class="data-label">Email</div>
                    <div class="data-value"><?= htmlspecialchars($turno['email']) ?></div>
                </div>
            </div>

            <div class="highlight-box">
                <span class="highlight-icon">📧</span>
                <span>
                    Se envió un correo a <span style="color:var(--green-dk)"><strong><?= htmlspecialchars($turno['email']) ?></strong></span>.
                    Recibirás un aviso cuando el turno sea confirmado por nuestro equipo.
                </span>
            </div>
        </div>

        <!-- Botones: solo Imprimir y Volver -->
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-outline">🖨 Imprimir</button>
            <?php if (esta_logueado()): ?>
            <a href="<?= $base ?>/admin/index.php" class="btn btn-outpanel">← Volver</a>
            <?php else: ?>
            <a href="<?= HOME_URL ?>/" class="btn btn-primary">Inicio</a>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
