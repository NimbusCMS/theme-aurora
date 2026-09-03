<?php
/**
 * Aurora footer — a night band under an aurora hairline, with the footer menu.
 *
 * @var string $appName
 * @var callable $e
 * @var array<string,list<array{label:string,url:string}>> $menus
 */
$foot = ($menus ?? [])['footer'] ?? [];
?>
<footer class="site-footer">
    <div class="inner night">
        <div class="container foot-grid">
            <div class="foot-brand">
                <a class="wordmark" href="/"><span class="spark">✧</span> <?= $e($appName) ?></a>
            </div>
            <?php if ($foot !== []): ?>
                <nav class="foot-nav" aria-label="Footer">
                    <?php foreach ($foot as $item): ?>
                        <a href="<?= $e($item['url']) ?>"><?= $e($item['label']) ?></a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>
        </div>
        <div class="container foot-legal">
            <p>© <?= $e(date('Y')) ?> <?= $e($appName) ?> · built with <a href="https://nimbuscms.dev">NimbusCMS</a></p>
        </div>
    </div>
</footer>
