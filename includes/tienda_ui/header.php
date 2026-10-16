<header class="fixed top-0 left-0 right-0 z-40 transition-all duration-300 bg-white shadow-sm">
    <div class="flex items-center justify-between px-4 md:px-10 h-16 md:h-20">
        <a href="<?= b('/tienda/ortopedia.php') ?>/"><img class="nb-logo" src="<?= b('/static/img/tecno-logoSF.png') ?>" alt="TECNOMEDIC" /></a>
        <!-- <a class="flex items-center gap-2" href="<?= b('/tienda/ortopedia.php') ?>">
            <span class="text-xl md:text-2xl font-bold tracking-tight whitespace-nowrap text-gray-800"><?= tienda_e($TIENDA['marca_1']) ?><span style="color: rgb(76, 175, 80);"><?= tienda_e($TIENDA['marca_2']) ?></span></span>
            <span class="hidden sm:inline text-xs font-semibold uppercase tracking-widest px-2 py-1 rounded-full bg-[#E8F5E9] text-[#2E7D32]"><?= tienda_e($TIENDA['etiqueta']) ?></span>
        </a> -->

        <nav id="tienda-nav" class="hidden md:flex items-center gap-8">
            <a class="text-sm font-medium transition-colors text-gray-700 hover:text-[#4CAF50]" href="<?= b('/tienda/ortopedia.php') ?>">Tienda</a>
            <a class="text-sm font-medium transition-colors text-gray-700 hover:text-[#4CAF50]" href="#catalogo">Catálogo</a>
            <a class="text-sm font-medium transition-colors text-gray-700 hover:text-[#4CAF50]" href="#ofertas">Ofertas</a>
            <a class="text-sm font-medium transition-colors text-gray-700 hover:text-[#4CAF50]" href="#destacados">Más vendidos</a>
            <a class="text-sm font-medium transition-colors text-gray-700 hover:text-[#4CAF50]" href="#categorias">Categorías</a>
            <?php if (!empty($tienda_modo_admin)): ?>
            <a class="text-sm font-medium transition-colors text-gray-700 hover:text-[#4CAF50]" href="<?= b($TIENDA['url_panel']) ?>">Volver al panel</a>
            <?php endif; ?>
        </nav>

        <div class="flex items-center gap-3">
            <button type="button" aria-label="Abrir carrito" data-cart-open
                class="relative w-10 h-10 rounded-full flex items-center justify-center transition-colors cursor-pointer bg-[#E8F5E9] text-[#2E7D32]">
                <i class="ri-shopping-cart-2-line text-xl"></i>
                <span class="tm-cart-badge" data-cart-count hidden>0</span>
            </button>
            <button type="button" aria-label="Menú" data-nav-toggle
                class="md:hidden w-10 h-10 rounded-full flex items-center justify-center cursor-pointer bg-gray-100 text-gray-700">
                <i class="ri-menu-line text-xl"></i>
            </button>
        </div>
    </div>
</header>
