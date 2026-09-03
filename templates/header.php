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
            <a class="nav-cart" href="/cart" aria-label="Cart<?= $count > 0 ? ', ' . $e((string) $count) . ' item' . ($count === 1 ? '' : 's') : '' ?>">
                <span>Cart</span>
                <?php if ($count > 0): ?><span class="count"><?= $e((string) $count) ?></span><?php endif; ?>
            </a>
        </nav>
    </div>
</header>
