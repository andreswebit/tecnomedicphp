<?php
require_once __DIR__ . '/../includes/tienda.php';
$base = BASE_URL;

$slug = $_GET['slug'] ?? '';
$cat  = get_categoria_by_slug($slug);

if (!$cat) {
    http_response_code(404);
    die('Categoría no encontrada.');
}

$familias = get_familias_con_productos((int)$cat['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($cat['nombre']) ?> – Tienda TECNOMEDIC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/home.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/areas.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/tienda.css" />
</head>
<body>

    <nav class="navbar" id="navbar">
        <div class="nb-brand">
            <a href="<?= HOME_URL ?>/"><img class="nb-logo" src="<?= $base ?>/static/img/tecno-logo.jpeg" alt="TECNOMEDIC" /></a>
        </div>
        <div class="nb-links">
            <a href="<?= HOME_URL ?>/#areas">Áreas</a>
            <a href="<?= $base ?>/tienda/">Tienda</a>
            <a href="<?= $base ?>/turnos.php">Turnos</a>
            <a href="<?= HOME_URL ?>/#contacto">Contacto</a>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="<?= $base ?>/turnos.php" class="btn btn-dark" style="border-radius:10px;padding:11px 20px;">
                <i class="fa-regular fa-calendar"></i> Reservar Turno
            </a>
            <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
        </div>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-close" id="mobileClose"><i class="fa-solid fa-xmark"></i></button>
        <a href="<?= $base ?>/tienda/" onclick="closeMobile()">Tienda</a>
        <a href="<?= $base ?>/#contacto" onclick="closeMobile()">Contacto</a>
        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="font-size:17px;margin-top:8px;">Reservar Turno</a>
    </div>

    <div class="area-breadcrumb">
        <div class="container">
            <a href="<?= HOME_URL ?>/">Inicio</a> <span>/</span>
            <a href="<?= $base ?>/tienda/">Tienda</a> <span>/</span>
            <?= htmlspecialchars($cat['nombre']) ?>
        </div>
    </div>

    <section class="area-hero" style="padding:44px 0;">
        <div class="container area-hero-inner">
            <div class="area-hero-icon"><i class="fa-solid <?= htmlspecialchars($cat['icono']) ?>"></i></div>
            <h1 style="font-size:clamp(24px,3.5vw,34px);"><?= htmlspecialchars($cat['nombre']) ?></h1>
        </div>
    </section>

    <section class="area-section">
        <div class="container">
            <?php if (empty($familias)): ?>
            <p style="text-align:center;color:#64748b;">Todavía no hay productos cargados en esta categoría.</p>
            <?php endif; ?>

            <?php foreach ($familias as $f): if (empty($f['productos'])) continue; ?>
            <div class="tienda-familia">
                <h2 class="tienda-familia-titulo"><?= htmlspecialchars($f['nombre']) ?></h2>
                <div class="tienda-prod-grid">
                    <?php foreach ($f['productos'] as $p): ?>
                    <a href="<?= $base ?>/tienda/producto.php?id=<?= $p['id'] ?>" class="tienda-prod-card">
                        <?php if ($p['destacado']): ?><span class="tienda-prod-badge">Destacado</span><?php endif; ?>
                        <div class="tienda-prod-img">
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="<?= $base ?>/static/img/productos/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                            <?php else: ?>
                                <i class="fa-solid <?= htmlspecialchars($cat['icono']) ?>"></i>
                            <?php endif; ?>
                        </div>
                        <div class="tienda-prod-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
                        <div class="tienda-prod-modalidad"><?= modalidad_label($p['modalidad']) ?></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="ftr-bottom" style="border-top:none;padding-top:0;">
                <span>© <?= date('Y') ?> TECNOMEDIC. Todos los derechos reservados.</span>
            </div>
        </div>
    </footer>

    <script>
        const mMenu = document.getElementById('mobileMenu');
        document.getElementById('hamburger').onclick = () => { mMenu.classList.add('open'); document.body.style.overflow = 'hidden'; };
        document.getElementById('mobileClose').onclick = closeMobile;
        function closeMobile() { mMenu.classList.remove('open'); document.body.style.overflow = ''; }
        mMenu.addEventListener('click', e => { if (e.target === mMenu) closeMobile(); });
    </script>
</body>
</html>
