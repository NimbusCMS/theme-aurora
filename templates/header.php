<?php
/**
 * Aurora site header — the wordmark and the main menu, on the night ground.
 *
 * @var string $appName
 * @var callable $e
 * @var array<string,list<array{label:string,url:string}>> $menus
 * @var array{count:int,total:string}|null $cart_summary the cart count/total, on
 *      SECTION pages only (null on the path-cached content pages, so a count is
 *      never baked into a shared cached page — ADR 0026 cache-safety)
 * @var string $cspNonce
 */
$main  = ($menus ?? [])['main'] ?? [];
$count = isset($cart_summary['count']) ? (int) $cart_summary['count'] : 0;
?>
<header class="site-header night">
    <div class="container">
        <a class="wordmark" href="/"><span class="spark">✧</span> <?= $e($appName) ?></a>
        <nav class="site-nav" aria-label="Main">
            <?php foreach ($main as $item): ?>
                <a href="<?= $e($item['url']) ?>"><?= $e($item['label']) ?></a>
            <?php endforeach; ?>
            <a class="nav-cart" href="/cart" aria-label="Cart">
                <span>Cart</span>
                <span class="count"<?= $count > 0 ? '' : ' hidden' ?>><?= $count > 0 ? $e((string) $count) : '' ?></span>
            </a>
        </nav>
    </div>
</header>
<?php if ($count === 0): // content (page-cached) page: fill the badge per-visitor, never from cache ?>
<script nonce="<?= $e($cspNonce ?? '') ?>">
(function () {
    fetch('/ext/shop/cart/summary', { credentials: 'same-origin' })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (d) {
            if (!d) { return; }
            var n = parseInt(d.count, 10);
            if (!(n > 0)) { return; }
            var el = document.querySelector('.nav-cart .count');
            if (el) { el.textContent = String(n); el.hidden = false; }
        })
        .catch(function () {});
})();
</script>
<?php endif; ?>
