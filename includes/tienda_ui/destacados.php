<?php
// Bloque "Productos destacados": 1 destacado grande + lista numerada
$destacado_principal = $TIENDA_PRODUCTOS[$TIENDA_DESTACADOS_IDS[0]] ?? null;
$destacados_lista    = array_slice($TIENDA_DESTACADOS_IDS, 1);
?>
<section class="px-4 md:px-10 mt-16" id="destacados">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#2E7D32] bg-[#E8F5E9] px-3 py-1.5 rounded-full">
                <i class="ri-award-line"></i>
                Lo más vendido
            </span>
            <h2 class="mt-3 text-xl md:text-2xl font-bold text-gray-800">Productos destacados</h2>
            <p class="mt-1 text-sm text-gray-500">Los favoritos de nuestros clientes, elegidos por su calidad y confiabilidad.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="#catalogo" class="inline-flex items-center gap-2 text-sm font-semibold text-[#2E7D32] hover:text-[#4CAF50] transition-colors whitespace-nowrap">
                Ver todo el catálogo
                <i class="ri-arrow-right-line"></i>
            </a>
            <?php if (!empty($tienda_modo_admin)): ?>
            <a href="<?= b($TIENDA['url_admin_destacados']) ?>"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[#2E7D32] hover:text-[#4CAF50] transition-colors whitespace-nowrap">
                <i class="ri-settings-2-line"></i>
                Administrar
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($destacado_principal): ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <?php tienda_render('cards/card_destacado_principal.php', ['p' => $destacado_principal]); ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <?php foreach ($destacados_lista as $i => $id): ?>
                <?php if (isset($TIENDA_PRODUCTOS[$id])) tienda_render('cards/card_destacado_item.php', ['p' => $TIENDA_PRODUCTOS[$id], 'puesto' => $i + 2]); ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
