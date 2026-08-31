<?php
/**
 * Aurora site header — the wordmark and the main menu, on the night ground.
 *
 * @var string $appName
 * @var callable $e
 * @var array<string,list<array{label:string,url:string}>> $menus
 */
$main = ($menus ?? [])['main'] ?? [];
?>
<header class="site-header night">
    <div class="container">
        <a class="wordmark" href="/"><span class="spark">✧</span> <?= $e($appName) ?></a>
        <nav class="site-nav" aria-label="Main">
            <?php foreach ($main as $item): ?>
                <a href="<?= $e($item['url']) ?>"><?= $e($item['label']) ?></a>
            <?php endforeach; ?>
            <a class="nav-cart" href="/cart" aria-label="Cart">Cart</a>
        </nav>
    </div>
</header>
