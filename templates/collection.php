<?php
/**
 * A collection's live entries, newest first, as a card grid. Generic — used for
 * any collection without its own collection-{handle} template. Every value escaped.
 *
 * @var array{handle:string,name:string} $collection
 * @var list<array{title:string,slug:string,fields:array<string,mixed>}> $entries
 * @var int $page
 * @var int $total_pages
 * @var callable $e
 */
$e = $e ?? static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$summary = static function (array $entry): string {
    $f = $entry['fields'] ?? [];
    foreach (['summary', 'excerpt', 'description'] as $k) {
        if (is_scalar($f[$k] ?? null) && (string) $f[$k] !== '') {
            $s = trim((string) $f[$k]);
            return mb_strlen($s) > 160 ? mb_substr($s, 0, 157) . '…' : $s;
        }
    }
    return '';
};
$pageUrl = static fn (int $n): string => '/' . $collection['handle'] . ($n > 1 ? '?page=' . $n : '');
?>
<section class="hero night">
    <div class="container">
        <p class="kicker">Browse</p>
        <h1><?= $e($collection['name']) ?></h1>
    </div>
</section>

<div class="container section">
    <?php if ($entries === []): ?>
        <p class="empty">Nothing here yet.</p>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($entries as $entry): ?>
                <?php $href = '/' . $collection['handle'] . '/' . rawurlencode($entry['slug']); $s = $summary($entry); ?>
                <a class="card" href="<?= $e($href) ?>">
                    <div class="card-body">
                        <h3><?= $e($entry['title']) ?></h3>
                        <?php if ($s !== ''): ?><p><?= $e($s) ?></p><?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (($total_pages ?? 1) > 1): ?>
            <nav class="pager" aria-label="Pagination">
                <?php if ($page > 1): ?><a class="btn btn-quiet" href="<?= $e($pageUrl($page - 1)) ?>">← Previous</a><?php endif; ?>
                <span>Page <?= $e((string) $page) ?> of <?= $e((string) $total_pages) ?></span>
                <?php if ($page < $total_pages): ?><a class="btn btn-quiet" href="<?= $e($pageUrl($page + 1)) ?>">Next →</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
