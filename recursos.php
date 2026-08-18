<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/db_recursos.php';
$base = BASE_URL;

$recursos = recursos_listar_publicos();

$areasNombres = [
    'audiologia' => 'Audiología', 'hiperbarica' => 'Medicina Hiperbárica',
    'nutricion' => 'Nutrición', 'ortopedia' => 'Ortopedia y Rehabilitación',
    'equipamiento' => 'Equipamiento Médico y Quirúrgico',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TECNOMEDIC – Recursos y formularios</title>
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
                    Descarga directa, sin registro
                </div>
            </header>

            <main class="form-main">
                <div class="form-content">
                    <div class="hero-label">TECNOMEDIC</div>
                    <h1>Recursos y <span>formularios</span></h1>
                    <p class="form-subtitle">
                        Documentos y formularios de uso general. Si buscás material específico
                        de tu tratamiento, encontralo dentro de
                        <a href="<?= $base ?>/login.php" style="color:var(--green);">Mi Portal</a>.
                    </p>

                    <?php if (!$recursos): ?>
                        <div class="form-card">
                            <p style="color:#64748b;">Todavía no hay recursos públicos cargados.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recursos as $r): ?>
                        <div class="form-card" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
                            <div>
                                <h3 style="margin:0 0 4px;"><?= htmlspecialchars($r['titulo']) ?></h3>
                                <?php if ($r['descripcion']): ?>
                                    <p style="margin:0 0 6px;color:#64748b;font-size:0.9rem;"><?= htmlspecialchars($r['descripcion']) ?></p>
                                <?php endif; ?>
                                <span style="font-size:0.78rem;color:#94a3b8;">
                                    <?= htmlspecialchars($r['tipo']) ?><?= $r['area'] ? ' · ' . htmlspecialchars($areasNombres[$r['area']] ?? $r['area']) : '' ?>
                                </span>
                            </div>
                            <a href="<?= $base ?>/recursos_descargar.php?id=<?= $r['id'] ?>" target="_blank" class="submit-btn" style="text-decoration:none;display:inline-block;white-space:nowrap;">⬇ Descargar</a>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

</body>
</html>
