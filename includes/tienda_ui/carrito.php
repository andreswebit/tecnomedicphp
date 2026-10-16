<!-- Fondo oscuro del carrito -->
<div id="tienda-overlay" class="fixed inset-0 bg-black/40 z-50 transition-opacity duration-300 opacity-0 pointer-events-none"></div>

<!-- Carrito lateral -->
<aside id="tienda-carrito" class="fixed top-0 right-0 h-screen w-full sm:w-[400px] bg-white z-50 flex flex-col transition-transform duration-300 translate-x-full">
    <div class="flex items-center justify-between px-5 h-16 border-b border-gray-200">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 flex items-center justify-center">
                <i class="ri-shopping-cart-2-line text-lg" style="color: rgb(76, 175, 80);"></i>
            </div>
            <h2 class="text-base font-bold text-gray-800">Tu carrito</h2>
        </div>
        <button type="button" aria-label="Cerrar carrito" data-cart-close
            class="w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-colors cursor-pointer">
            <i class="ri-close-line text-xl"></i>
        </button>
    </div>

    <!-- Carrito vacío -->
    <div data-cart-empty class="flex-1 flex flex-col items-center justify-center px-6 text-center">
        <div class="w-16 h-16 rounded-full bg-[#E8F5E9] flex items-center justify-center mb-4">
            <i class="ri-shopping-bag-3-line text-2xl" style="color: rgb(76, 175, 80);"></i>
        </div>
        <p class="text-sm text-gray-500">Tu carrito está vacío.</p>
        <button type="button" data-cart-close data-ir-catalogo class="mt-4 text-sm font-semibold text-[#2E7D32] cursor-pointer">Ver productos</button>
    </div>

    <!-- Productos del carrito (lo completa el JS) -->
    <div data-cart-list class="tm-cart-list" hidden></div>

    <div data-cart-footer class="tm-cart-footer" hidden>
        <div class="tm-cart-total"><span>Total</span><strong data-cart-total>$0.00</strong></div>
        <a data-cart-checkout href="#" class="tm-cart-checkout">Finalizar compra</a>
        <button type="button" data-cart-clear class="tm-cart-clear">Vaciar carrito</button>
    </div>
</aside>
