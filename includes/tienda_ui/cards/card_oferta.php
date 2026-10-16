<?php /** Variables: $p (producto en oferta) */ ?>
<div class="group bg-white rounded-xl border border-[#DCEDC8] overflow-hidden flex flex-col transition-all duration-200 hover:-translate-y-1 hover:border-[#4CAF50]">
    <a class="block relative" href="<?= tienda_url_producto($p['id']) ?>">
        <div class="w-full h-44 bg-[#F7FBF3] flex items-center justify-center overflow-hidden">
            <img src="<?= tienda_img($p['imagen']) ?>" alt="<?= tienda_e($p['nombre']) ?>"
                title="<?= tienda_e($p['nombre']) ?> en oferta - ortopedia TecnoMedic"
                class="w-full h-full object-contain p-4 transition-transform duration-300 group-hover:scale-105">
        </div>
        <span class="absolute top-3 left-3 text-[11px] font-bold text-white px-2.5 py-1 rounded-full" style="background-color: rgb(229, 57, 53);">-<?= tienda_descuento($p) ?>%</span>
    </a>
    <div class="p-4 flex flex-col flex-1">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-[#2E7D32]"><?= tienda_e($p['categoria']) ?></span>
        <a href="<?= tienda_url_producto($p['id']) ?>">
            <h3 class="mt-1 text-sm font-bold text-gray-800 leading-snug line-clamp-2 hover:text-[#4CAF50] transition-colors"><?= tienda_e($p['nombre']) ?></h3>
        </a>
        <div class="mt-3 flex items-end justify-between gap-2">
            <div class="flex flex-col">
                <span class="text-xs text-gray-400 line-through"><?= tienda_precio($p['precio_anterior']) ?></span>
                <span class="text-lg font-bold text-gray-900"><?= tienda_precio($p['precio']) ?></span>
            </div>
            <span class="text-[11px] font-semibold text-[#E53935] bg-[#FFEBEE] px-2 py-1 rounded-full whitespace-nowrap">Ahorras <?= tienda_precio(tienda_ahorro($p)) ?></span>
        </div>
        <button type="button" <?= tienda_attrs_carrito($p) ?>
            class="mt-4 w-full h-10 rounded-md text-sm font-semibold whitespace-nowrap flex items-center justify-center gap-2 transition-colors text-white hover:bg-[#43A047] cursor-pointer"
            style="background-color: rgb(76, 175, 80);">
            <i class="ri-shopping-cart-2-line text-base"></i>
            Aprovechar oferta
        </button>
    </div>
</div>
