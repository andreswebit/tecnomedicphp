<?php
// Bloque "Ofertas del momento"
?>
<section class="px-4 md:px-10 mt-16" id="ofertas">
    <div class="rounded-2xl bg-[#F1F8E9] border border-[#DCEDC8] p-5 md:p-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#E53935] bg-[#FFEBEE] px-3 py-1.5 rounded-full">
                    <i class="ri-fire-line"></i>
                    Ofertas
                </span>
                <h2 class="mt-3 text-xl md:text-2xl font-bold text-gray-800">Ofertas del momento</h2>
                <p class="mt-1 text-sm text-gray-500">Descuentos por tiempo limitado en los productos más pedidos.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 text-sm font-semibold text-[#2E7D32] bg-white px-4 py-2 rounded-full border border-[#DCEDC8] whitespace-nowrap">
                    <i class="ri-time-line"></i>
                    Terminan pronto
                </span>
                <?php if (!empty($tienda_modo_admin)): ?>
                <a href="<?= b($TIENDA['url_admin_ofertas']) ?>"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-[#C62828] hover:text-[#E53935] transition-colors whitespace-nowrap">
                    <i class="ri-settings-2-line"></i>
                    Administrar
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($TIENDA_OFERTAS_IDS as $id): ?>
                <?php if (isset($TIENDA_PRODUCTOS[$id])) tienda_render('cards/card_oferta.php', ['p' => $TIENDA_PRODUCTOS[$id]]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
