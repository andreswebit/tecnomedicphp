<section class="px-4 md:px-10 pt-12" id="categorias">
    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Categorías</h2>
    <p class="mt-1 text-sm text-gray-500">Explora por tipo de producto.</p>

    <div class="mt-5 flex flex-wrap gap-3" data-chips>
        <button type="button" data-cat="Todos"
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors cursor-pointer border text-white border-transparent"
            style="background-color: rgb(76, 175, 80);">Todos</button>
        <?php foreach ($TIENDA_CATEGORIAS as $cat): ?>
        <button type="button" data-cat="<?= tienda_e($cat) ?>"
            class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors cursor-pointer border text-gray-600 border-gray-200 hover:border-[#C8E6C9]"><?= tienda_e($cat) ?></button>
        <?php endforeach; ?>
    </div>
</section>
