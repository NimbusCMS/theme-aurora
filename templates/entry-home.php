<?php
/**
 * Aurora landing page (the `home` singleton). A composed storefront home — night
 * hero, a promise strip, shop-by-aisle tiles, an about teaser, and a closing night
 * band — all from authored singleton fields, each optional so the page degrades
 * gracefully. Every value is escaped; the body renders through the shared prose
 * parser and aisle links through the shared safe-href allow-list (relative-only).
 *
 * Fields: eyebrow, tagline, subhead, hero{url,alt}, promise (lines "Label | detail"),
 * aisles (lines "Label | detail | /url"), body, closing_title, closing.
 *
 * @var array{title:string,fields:array<string,mixed>} $entry
 * @var string $appName
 * @var callable(?string):string $e
 * @var array<string,array<string,mixed>> $contrib live plugin view-data (ADR 0027)
 */
$e = $e ?? static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$prose    = require __DIR__ . '/_prose.php';
$safeHref = require __DIR__ . '/_url.php';

// Live featured products, if a storefront plugin contributed them (ADR 0027).
$featured = $contrib['nimbuscms.storefront']['featured'] ?? [];
$availLabels = ['in_stock' => 'In stock', 'low' => 'Low stock', 'out' => 'Out of stock'];

$f    = $entry['fields'] ?? [];
$str  = static fn (string $k): string => is_scalar($f[$k] ?? null) ? trim((string) $f[$k]) : '';
$rows = static function (string $k) use ($f): array {
    $raw = is_scalar($f[$k] ?? null) ? (string) $f[$k] : '';
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', trim($raw)) ?: [] as $line) {
        if (trim($line) === '') {
            continue;
        }
        $out[] = array_map('trim', explode('|', $line));
    }
    return $out;
};

$eyebrow = $str('eyebrow') !== '' ? $str('eyebrow') : 'Welcome';
$heading = $str('tagline') !== '' ? $str('tagline') : (string) ($entry['title'] ?? $appName);
$lead    = $str('subhead');
$hero    = is_array($f['hero'] ?? null) && isset($f['hero']['url']) ? $f['hero'] : null;
$promise = $rows('promise');
$aisles  = $rows('aisles');
$body    = $str('body');
$closing = $str('closing');
?>
<section class="hero night">
    <div class="container">
        <p class="kicker"><?= $e($eyebrow) ?></p>
        <h1><?= $e($heading) ?></h1>
        <?php if ($lead !== ''): ?><p><?= $e($lead) ?></p><?php endif; ?>
        <div class="actions">
            <a class="btn btn-primary" href="/shop">Shop the aisles →</a>
            <?php if ($aisles !== []): ?><a class="btn btn-ghost" href="#aisles">Browse by aisle</a><?php endif; ?>
        </div>
    </div>
</section>

<?php if ($hero !== null): ?>
    <div class="container section">
        <img class="hero-image" src="<?= $e((string) $hero['url']) ?>" alt="<?= $e((string) ($hero['alt'] ?? '')) ?>">
    </div>
<?php endif; ?>

<?php if ($promise !== []): ?>
    <section class="section">
        <div class="container promise">
            <?php foreach ($promise as $p): ?>
                <div class="promise-item">
                    <p class="promise-label"><?= $e($p[0] ?? '') ?></p>
                    <?php if (($p[1] ?? '') !== ''): ?><p class="promise-detail"><?= $e($p[1]) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($featured !== []): ?>
    <section class="section featured">
        <div class="container">
            <h2>Featured this week</h2>
            <div class="featured-grid">
                <?php foreach ($featured as $it): ?>
                    <?php $avail = (string) ($it['availability'] ?? 'in_stock'); ?>
                    <a class="featured-card" href="/shop/<?= $e(rawurlencode((string) $it['sku_code'])) ?>">
                        <span class="thumb thumb-empty" aria-hidden="true">✦</span>
                        <span class="featured-name"><?= $e((string) $it['name']) ?></span>
                        <span class="price"><?= $e((string) $it['price']) ?><?php if (($it['unit'] ?? null) !== null): ?> <span class="unit">/ <?= $e((string) $it['unit']) ?></span><?php endif; ?></span>
                        <span class="pill <?= $e($avail) ?>"><?= $e($availLabels[$avail] ?? $avail) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($aisles !== []): ?>
    <section class="section" id="aisles">
        <div class="container">
            <h2>Shop by aisle</h2>
            <div class="aisle-grid">
                <?php foreach ($aisles as $a): ?>
                    <?php $href = isset($a[2]) ? $safeHref($a[2]) : null; ?>
                    <?php if ($href !== null): ?>
                        <a class="aisle" href="<?= $e($href) ?>">
                            <span class="aisle-label"><?= $e($a[0] ?? '') ?></span>
                            <?php if (($a[1] ?? '') !== ''): ?><span class="aisle-detail"><?= $e($a[1]) ?></span><?php endif; ?>
                            <span class="aisle-go" aria-hidden="true">→</span>
                        </a>
                    <?php else: ?>
                        <div class="aisle">
                            <span class="aisle-label"><?= $e($a[0] ?? '') ?></span>
                            <?php if (($a[1] ?? '') !== ''): ?><span class="aisle-detail"><?= $e($a[1]) ?></span><?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($body !== ''): ?>
    <section class="section home-about">
        <div class="container measure entry-body prose"><?= $prose($body, $e) ?></div>
    </section>
<?php endif; ?>

<?php if ($closing !== ''): ?>
    <section class="cta-band night">
        <div class="container">
            <?php if ($str('closing_title') !== ''): ?><h2><?= $e($str('closing_title')) ?></h2><?php endif; ?>
            <p><?= $e($closing) ?></p>
            <div class="actions">
                <a class="btn btn-primary" href="/shop">Start your order →</a>
            </div>
        </div>
    </section>
<?php endif; ?>
