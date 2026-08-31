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
        <?php if ($main !== []): ?>
            <nav class="site-nav" aria-label="Main">
                <?php foreach ($main as $item): ?>
                    <a href="<?= $e($item['url']) ?>"><?= $e($item['label']) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>
</header>
