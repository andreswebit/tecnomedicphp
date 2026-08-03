<?php
require_once __DIR__ . '/../includes/tienda.php';
$base = BASE_URL;
$categorias = get_categorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda – TECNOMEDIC</title>
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
            <a href="<?= HOME_URL ?>/#equipo">Nosotros</a>
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
        <a href="<?= $base ?>/#areas" onclick="closeMobile()">Áreas</a>
        <a href="<?= $base ?>/tienda/" onclick="closeMobile()">Tienda</a>
        <a href="<?= $base ?>/#contacto" onclick="closeMobile()">Contacto</a>
        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="font-size:17px;margin-top:8px;">Reservar Turno</a>
    </div>

    <div class="area-breadcrumb">
        <div class="container"><a href="<?= $base ?>/">Inicio</a> <span>/</span> Tienda</div>
    </div>

    <section class="area-hero">
        <div class="container area-hero-inner">
            <div class="area-hero-icon"><i class="fa-solid fa-cart-shopping"></i></div>
            <h1>Tienda TECNOMEDIC</h1>
            <p>Productos y equipamiento para cada necesidad, organizados por especialidad.</p>
        </div>
    </section>

    <section class="area-section">
        <div class="container">
            <div class="tienda-cat-grid">
                <?php foreach ($categorias as $c): ?>
                <a href="<?= $base ?>/tienda/categoria.php?slug=<?= urlencode($c['slug']) ?>" class="tienda-cat-card">
                    <div class="tienda-cat-icon"><i class="fa-solid <?= htmlspecialchars($c['icono']) ?>"></i></div>
                    <div class="tienda-cat-nombre"><?= htmlspecialchars($c['nombre']) ?></div>
                    <span class="tienda-cat-link">Ver productos <i class="fa-solid fa-arrow-right"></i></span>
                </a>
                <?php endforeach; ?>
                <?php if (empty($categorias)): ?>
                <p style="color:#64748b;">Todavía no hay categorías cargadas. Corré <code>tienda_schema.sql</code> en phpMyAdmin.</p>
                <?php endif; ?>
            </div>

            <p style="text-align:center;color:#64748b;font-size:14px;margin-top:32px;">
                ¿Buscás algo puntual? <a href="https://wa.me/5493794775341" target="_blank" style="color:var(--teal);font-weight:600;">Consultanos por WhatsApp <i class="fa-brands fa-whatsapp"></i></a>
            </p>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="ftr-bottom" style="border-top:none;padding-top:0;">
                <span>© <?= date('Y') ?> TECNOMEDIC. Todos los derechos reservados.</span>
                <span>Corrientes, Argentina</span>
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
