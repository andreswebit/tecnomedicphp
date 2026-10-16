/**
 * TECNOMEDIC Tienda de ortopedia
 * Funciones: menú móvil, filtro por categoría, buscador, carrito lateral (guardado en el navegador).
 * Configuración que llega desde PHP: window.TIENDA_CFG = { checkoutUrl }
 */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var money = function (n) { return '$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    // Minúsculas y sin tildes: "bastón" encuentra "Baston"
    var norm = function (s) { return String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, ''); };
    var esc = function (s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };

    /* ---------- Menú móvil ---------- */
    var nav = $('#tienda-nav');
    var navBtn = $('[data-nav-toggle]');
    if (nav && navBtn) {
        navBtn.addEventListener('click', function () { nav.classList.toggle('tm-nav-open'); });
        $$('a', nav).forEach(function (a) { a.addEventListener('click', function () { nav.classList.remove('tm-nav-open'); }); });
    }

    /* ---------- Filtro por categoría + buscador ---------- */
    var cards = $$('#tienda-grid [data-card]');
    var chips = $$('[data-chips] [data-cat]');
    var input = $('#tienda-buscar');
    var count = $('#tienda-count');
    var vacio = $('#tienda-vacio');
    var estado = { cat: 'Todos', q: '' };

    var CHIP_ON = ['text-white', 'border-transparent'];
    var CHIP_OFF = ['text-gray-600', 'border-gray-200', 'hover:border-[#C8E6C9]'];

    function aplicarFiltros() {
        var visibles = 0;
        cards.forEach(function (c) {
            var okCat = estado.cat === 'Todos' || c.getAttribute('data-cat') === estado.cat;
            var okQ = !estado.q || norm(c.getAttribute('data-nombre')).indexOf(estado.q) !== -1;
            var mostrar = okCat && okQ;
            c.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });
        if (count) {
            count.textContent = visibles + (visibles === 1 ? ' producto disponible' : ' productos disponibles');
        }
        if (vacio) vacio.hidden = visibles !== 0;
    }

    function marcarChip(cat) {
        chips.forEach(function (b) {
            var on = b.getAttribute('data-cat') === cat;
            CHIP_ON.forEach(function (k) { b.classList.toggle(k, on); });
            CHIP_OFF.forEach(function (k) { b.classList.toggle(k, !on); });
            b.style.backgroundColor = on ? 'rgb(76, 175, 80)' : '';
        });
    }

    chips.forEach(function (b) {
        b.addEventListener('click', function () {
            estado.cat = b.getAttribute('data-cat');
            marcarChip(estado.cat);
            aplicarFiltros();
            var cat = $('#catalogo');
            if (cat) cat.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    if (input) {
        input.addEventListener('input', function () {
            estado.q = norm(input.value.trim());
            aplicarFiltros();
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { var c = $('#catalogo'); if (c) c.scrollIntoView({ behavior: 'smooth' }); }
        });
    }

    /* ---------- Carrito ---------- */
    var KEY = 'tm_carrito_ortopedia';
    var drawer = $('#tienda-carrito');
    var overlay = $('#tienda-overlay');
    var elEmpty = $('[data-cart-empty]');
    var elList = $('[data-cart-list]');
    var elFoot = $('[data-cart-footer]');
    var elTotal = $('[data-cart-total]');
    var elBadge = $('[data-cart-count]');
    var elCheckout = $('[data-cart-checkout]');
    var items = [];

    try { items = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { items = []; }
    if (!Array.isArray(items)) items = [];

    function guardar() { try { localStorage.setItem(KEY, JSON.stringify(items)); } catch (e) { /* sin almacenamiento */ } }

    function abrir() { if (drawer) drawer.classList.add('tm-open'); if (overlay) overlay.classList.add('tm-open'); }
    function cerrar() { if (drawer) drawer.classList.remove('tm-open'); if (overlay) overlay.classList.remove('tm-open'); }

    function pintar() {
        var totalUnidades = 0, total = 0;
        items.forEach(function (i) { totalUnidades += i.cant; total += i.cant * i.precio; });

        if (elBadge) { elBadge.textContent = totalUnidades; elBadge.hidden = totalUnidades === 0; }
        if (elEmpty) elEmpty.hidden = items.length > 0;
        if (elList) elList.hidden = items.length === 0;
        if (elFoot) elFoot.hidden = items.length === 0;
        if (elTotal) elTotal.textContent = money(total);

        if (elList) {
            elList.innerHTML = items.map(function (i) {
                return '<div class="tm-cart-item" data-id="' + esc(i.id) + '">' +
                    '<img src="' + esc(i.imagen) + '" alt="">' +
                    '<div class="tm-cart-info">' +
                        '<div class="tm-cart-name">' + esc(i.nombre) + '</div>' +
                        '<div class="tm-cart-price">' + money(i.precio) + ' c/u</div>' +
                        '<div class="tm-cart-qty">' +
                            '<button type="button" data-q="-1" aria-label="Restar">−</button>' +
                            '<span>' + i.cant + '</span>' +
                            '<button type="button" data-q="1" aria-label="Sumar">+</button>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="tm-cart-remove" data-rm aria-label="Quitar">×</button>' +
                '</div>';
            }).join('');
        }
    }

    function agregar(d) {
        var ex = items.filter(function (i) { return String(i.id) === String(d.id); })[0];
        if (ex) ex.cant += 1; else items.push({ id: d.id, nombre: d.nombre, precio: d.precio, imagen: d.imagen, cant: 1 });
        guardar(); pintar();
    }

    document.addEventListener('click', function (e) {
        var add = e.target.closest('[data-add-cart]');
        if (add) {
            agregar({
                id: add.getAttribute('data-id'),
                nombre: add.getAttribute('data-nombre'),
                precio: parseFloat(add.getAttribute('data-precio')),
                imagen: add.getAttribute('data-imagen')
            });
            abrir();
            return;
        }
        if (e.target.closest('[data-cart-open]')) { abrir(); return; }
        if (e.target.closest('[data-cart-close]')) {
            cerrar();
            if (e.target.closest('[data-ir-catalogo]')) { var c = $('#catalogo'); if (c) c.scrollIntoView({ behavior: 'smooth' }); }
            return;
        }
        if (e.target.closest('[data-cart-clear]')) { items = []; guardar(); pintar(); return; }

        var row = e.target.closest('.tm-cart-item');
        if (row) {
            var id = row.getAttribute('data-id');
            var it = items.filter(function (i) { return String(i.id) === id; })[0];
            if (!it) return;
            var q = e.target.closest('[data-q]');
            if (q) {
                it.cant += parseInt(q.getAttribute('data-q'), 10);
                if (it.cant <= 0) items = items.filter(function (i) { return i !== it; });
            } else if (e.target.closest('[data-rm]')) {
                items = items.filter(function (i) { return i !== it; });
            } else { return; }
            guardar(); pintar();
        }
    });

    if (overlay) overlay.addEventListener('click', cerrar);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrar(); });
    if (elCheckout && window.TIENDA_CFG && window.TIENDA_CFG.checkoutUrl) elCheckout.setAttribute('href', window.TIENDA_CFG.checkoutUrl);

    pintar();
    aplicarFiltros();
})();
