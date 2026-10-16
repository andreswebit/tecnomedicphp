<footer class="mt-20" style="background-color: rgb(241, 248, 233);">
    <div class="px-4 md:px-10 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div class="md:col-span-2">
            <span class="text-xl font-bold tracking-tight text-gray-800"><?= tienda_e($TIENDA['marca_1']) ?><span style="color: rgb(139, 195, 74);"><?= tienda_e($TIENDA['marca_2']) ?></span></span>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed max-w-md">Tienda de ortopedia y movilidad. Sillas de ruedas, bastones, vendajes e insumos hospitalarios con envío a todo el país y asesoramiento profesional.</p>
            <div class="mt-4 flex items-center gap-2 text-sm text-gray-600">
                <div class="w-8 h-8 flex items-center justify-center"><i class="ri-phone-line text-lg" style="color: rgb(46, 125, 50);"></i></div>
                <span><?= tienda_e($TIENDA['telefono']) ?></span>
            </div>
            <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                <div class="w-8 h-8 flex items-center justify-center"><i class="ri-mail-line text-lg" style="color: rgb(46, 125, 50);"></i></div>
                <span><?= tienda_e($TIENDA['email']) ?></span>
            </div>
        </div>

        <div>
            <h4 class="text-sm font-bold text-gray-800 mb-3">Categorías</h4>
            <ul class="space-y-2 text-sm text-gray-600">
                <?php foreach ($TIENDA_CATEGORIAS as $cat): ?>
                <li><?= tienda_e($cat) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div>
            <h4 class="text-sm font-bold text-gray-800 mb-3">Atención</h4>
            <ul class="space-y-2 text-sm text-gray-600">
                <li>Envíos y entregas</li>
                <li>Cambios y devoluciones</li>
                <li>Preguntas frecuentes</li>
                <li>Privacidad</li>
            </ul>
        </div>
    </div>

    <div class="border-t border-[#DCEDC8] px-4 md:px-10 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-xs text-gray-500">© <?= date('Y') ?> TecnoMedic. Todos los derechos reservados.</p>
        <div class="flex items-center gap-3 text-gray-500">
            <i class="ri-instagram-line text-lg"></i>
            <i class="ri-facebook-circle-line text-lg"></i>
            <i class="ri-whatsapp-line text-lg"></i>
        </div>
    </div>
</footer>
