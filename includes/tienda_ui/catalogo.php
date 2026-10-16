<section class="px-4 md:px-10 pt-10" id="catalogo">
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl md:text-2xl font-bold text-gray-800">Nuestros productos</h2>
            <p class="mt-1 text-sm text-gray-500" id="tienda-count" data-total="<?= count($TIENDA_PRODUCTOS) ?>"><?= count($TIENDA_PRODUCTOS) ?> productos disponibles</p>
        </div>
    </div>

    <div id="tienda-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($TIENDA_PRODUCTOS as $p): ?>
            <?php tienda_render('cards/card_catalogo.php', ['p' => $p]); ?>
        <?php endforeach; ?>
    </div>

    <p id="tienda-vacio" class="tm-empty" hidden>No encontramos productos con ese criterio. Probá con otra búsqueda o categoría.</p>
</section>
