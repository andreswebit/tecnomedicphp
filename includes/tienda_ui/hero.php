<section class="relative h-[420px] md:h-[600px] w-full flex items-center justify-center overflow-hidden">
    <img src="<?= tienda_img($TIENDA['hero_imagen']) ?>"
        alt="Tienda de ortopedia y movilidad TecnoMedic"
        title="Tienda de ortopedia TecnoMedic - sillas de ruedas y bastones"
        class="absolute inset-0 w-full h-full object-cover object-top">
    <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/40 to-black/50"></div>

    <div class="relative w-full px-4 md:px-10 max-w-3xl text-center flex flex-col items-center">
        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-white bg-white/15 px-3 py-1.5 rounded-full">
            <i class="ri-heart-pulse-line"></i>
            Ortopedia y movilidad
        </span>
        <h1 class="mt-5 text-3xl md:text-5xl font-bold text-white leading-tight">Todo para tu recuperación y bienestar</h1>
        <p class="mt-4 text-sm md:text-base text-white/90 max-w-xl">Sillas de ruedas, andadores, bastones, vendajes e insumos hospitalarios con calidad profesional y envío a domicilio.</p>

        <div class="mt-7 w-full max-w-xl">
            <div class="flex items-center bg-white rounded-full px-2 h-12 md:h-14 shadow-sm">
                <div class="w-10 h-10 flex items-center justify-center flex-shrink-0">
                    <i class="ri-search-line text-lg text-gray-400"></i>
                </div>
                <input id="tienda-buscar" type="text" value=""
                    class="flex-1 h-full bg-transparent outline-none text-sm text-gray-700 placeholder:text-gray-400"
                    placeholder="Buscar silla de ruedas, bastón, vendas...">
                <a href="#catalogo" data-buscar
                    class="h-9 md:h-10 px-4 md:px-5 rounded-full text-sm font-semibold text-white whitespace-nowrap flex items-center justify-center cursor-pointer"
                    style="background-color: rgb(76, 175, 80);">Buscar</a>
            </div>
        </div>
    </div>
</section>
