<?php
/** Vidriera compacta de productos destacados para la página principal. */
$destacados_home = array_values(array_filter(
    array_map(
        static fn($id) => $TIENDA_PRODUCTOS[(string)(int)$id] ?? null,
        $TIENDA_DESTACADOS_IDS
    )
));
?>
<div class="tn-grid">
    <?php foreach ($destacados_home as $p): ?>
    <article class="tn-card">
        <a class="tn-card-img" href="<?= tienda_url_producto($p['id']) ?>">
            <img src="<?= tienda_img($p['imagen']) ?>" alt="<?= tienda_e($p['nombre']) ?>" loading="lazy">
        </a>
        <div class="tn-card-body">
            <div class="tn-card-cat"><?= tienda_e($p['categoria']) ?></div>
            <a href="<?= tienda_url_producto($p['id']) ?>" class="tn-card-name">
                <?= tienda_e($p['nombre']) ?>
            </a>
            <?php if (!empty($p['precio_anterior'])): ?>
                <div class="tn-card-price">
                    <small style="display:block;color:#8aada9;text-decoration:line-through;font-size:12px;font-weight:500;">
                        <?= tienda_precio($p['precio_anterior']) ?>
                    </small>
                    <?= tienda_precio($p['precio']) ?>
                </div>
            <?php else: ?>
                <div class="tn-card-price"><?= tienda_precio($p['precio']) ?></div>
            <?php endif; ?>
            <div class="tn-card-footer">
                <span style="font-size:12px;color:#8aada9;">Ver producto</span>
                <a href="<?= tienda_url_producto($p['id']) ?>" class="tn-add-btn" aria-label="Ver <?= tienda_e($p['nombre']) ?>">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<div style="text-align:center;margin-top:24px;">
    <a href="<?= b('/tienda/ortopedia.php') ?>" class="tn-cta-link">
        Ver toda la tienda <i class="fa-solid fa-arrow-right"></i>
    </a>
</div>
