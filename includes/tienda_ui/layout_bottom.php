</div><!-- /min-h-screen -->

<script>
    // Configuracion para static/js/tienda-ortopedia.js
    window.TIENDA_CFG = { checkoutUrl: <?= json_encode(b($TIENDA['url_checkout'])) ?> };
</script>
<script src="<?= b('/static/js/tienda-ortopedia.js') ?>"></script>
</body>
</html>
