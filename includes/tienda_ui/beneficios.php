<?php
$beneficios = [
    ['icono' => 'ri-truck-line',            'titulo' => 'Envío a todo el país',   'texto' => 'Despachamos en 24 a 48 horas.'],
    ['icono' => 'ri-shield-check-line',     'titulo' => 'Garantía de calidad',    'texto' => 'Productos homologados y certificados.'],
    ['icono' => 'ri-customer-service-2-line','titulo' => 'Asesoramiento',          'texto' => 'Te ayudamos a elegir lo que necesitas.'],
];
?>
<section class="px-4 md:px-10 mt-16">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <?php foreach ($beneficios as $bf): ?>
        <div class="rounded-xl border border-gray-200 p-5 flex items-start gap-4">
            <div class="w-11 h-11 rounded-full bg-[#E8F5E9] flex items-center justify-center flex-shrink-0">
                <i class="<?= tienda_e($bf['icono']) ?> text-xl" style="color: rgb(46, 125, 50);"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800"><?= tienda_e($bf['titulo']) ?></h3>
                <p class="text-xs text-gray-500 mt-1"><?= tienda_e($bf['texto']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
