<?php /** Variables: $p (producto destacado N.o 1) */ ?>
<div class="group bg-white rounded-2xl border border-gray-200 overflow-hidden flex flex-col transition-all duration-200 hover:border-[#C8E6C9]">
    <a class="relative block" href="<?= tienda_url_producto($p['id']) ?>">
        <div class="w-full h-64 md:h-80 bg-[#F7FBF3] flex items-center justify-center overflow-hidden">
            <img src="<?= tienda_img($p['imagen']) ?>" alt="<?= tienda_e($p['nombre']) ?>"
                title="<?= tienda_e($p['nombre']) ?> - el más vendido de TecnoMedic"
                class="w-full h-full object-contain p-6 transition-transform duration-300 group-hover:scale-105">
        </div>
        <span class="absolute top-4 left-4 inline-flex items-center gap-1.5 text-xs font-bold text-white px-3 py-1.5 rounded-full" style="background-color: rgb(76, 175, 80);">
            <i class="ri-trophy-line"></i>
            N.º 1 en ventas
        </span>
    </a>
    <div class="p-5 md:p-6 flex flex-col flex-1">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-[#2E7D32]"><?= tienda_e($p['categoria']) ?></span>
        <a href="<?= tienda_url_producto($p['id']) ?>">
            <h3 class="mt-1.5 text-lg font-bold text-gray-800 leading-snug hover:text-[#4CAF50] transition-colors"><?= tienda_e($p['nombre']) ?></h3>
        </a>
        <?php if (!empty($p['descripcion'])): ?>
        <p class="mt-2 text-sm text-gray-500 line-clamp-2"><?= tienda_e($p['descripcion']) ?></p>
        <?php endif; ?>
        <div class="mt-auto pt-5 flex items-end justify-between gap-3">
            <div class="flex flex-col">
                <span class="text-xs text-gray-500">Precio</span>
                <span class="text-2xl font-bold text-gray-900"><?= tienda_precio($p['precio']) ?></span>
            </div>
            <button type="button" <?= tienda_attrs_carrito($p) ?>
                class="h-11 px-5 rounded-md text-sm font-semibold whitespace-nowrap flex items-center justify-center gap-2 transition-colors text-white hover:bg-[#43A047] cursor-pointer"
                style="background-color: rgb(76, 175, 80);">
                <i class="ri-shopping-cart-2-line text-base"></i>
                Agregar
            </button>
        </div>
    </div>
</div>
