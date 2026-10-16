<?php /** Variables: $p (producto), $puesto (posicion en el ranking) */ ?>
<div class="group bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col transition-all duration-200 hover:-translate-y-1 hover:border-[#C8E6C9]">
    <a class="relative block" href="<?= tienda_url_producto($p['id']) ?>">
        <div class="w-full h-36 bg-[#F7FBF3] flex items-center justify-center overflow-hidden">
            <img src="<?= tienda_img($p['imagen']) ?>" alt="<?= tienda_e($p['nombre']) ?>"
                title="<?= tienda_e($p['nombre']) ?> - destacado TecnoMedic"
                class="w-full h-full object-contain p-3 transition-transform duration-300 group-hover:scale-105">
        </div>
        <span class="absolute top-3 left-3 w-7 h-7 rounded-full bg-white border border-gray-200 text-xs font-bold text-[#2E7D32] flex items-center justify-center"><?= (int)$puesto ?></span>
    </a>
    <div class="p-3.5 flex flex-col flex-1">
        <a href="<?= tienda_url_producto($p['id']) ?>">
            <h3 class="text-sm font-bold text-gray-800 leading-snug line-clamp-2 hover:text-[#4CAF50] transition-colors"><?= tienda_e($p['nombre']) ?></h3>
        </a>
        <div class="mt-auto pt-3 flex items-center justify-between gap-2">
            <span class="text-base font-bold text-gray-900"><?= tienda_precio($p['precio']) ?></span>
            <button type="button" <?= tienda_attrs_carrito($p) ?> aria-label="Agregar <?= tienda_e($p['nombre']) ?> al carrito"
                class="w-9 h-9 rounded-md flex items-center justify-center transition-colors text-white hover:bg-[#43A047] cursor-pointer"
                style="background-color: rgb(76, 175, 80);">
                <i class="ri-add-line text-lg"></i>
            </button>
        </div>
    </div>
</div>
