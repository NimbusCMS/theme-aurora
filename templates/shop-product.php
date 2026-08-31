<?php
/**
 * Aurora storefront product page (overrides the plugin default via ADR 0023).
 *
 * @var callable(?string):string $e
 * @var callable(?int):?array{url:string,alt:?string} $media
 * @var array<string,mixed> $item
 */
$labels = ['in_stock' => 'In stock', 'low' => 'Low stock', 'out' => 'Out of stock'];
$img    = $media($item['image_media_id']);
?>
<div class="container section">
    <a class="back" href="/shop">← Back to shop</a>
    <div class="product-detail">
        <div class="media">
            <?php if ($img !== null): ?>
                <img src="<?= $e($img['url']) ?>" alt="<?= $e($img['alt'] ?? $item['name']) ?>">
            <?php else: ?>
                <div class="thumb-empty" aria-hidden="true">✦</div>
            <?php endif; ?>
        </div>
        <div>
            <?php if ($item['category'] !== null): ?><p class="kicker"><?= $e($item['category']) ?></p><?php endif; ?>
            <h1><?= $e($item['name']) ?></h1>
            <p class="price"><?= $e($item['price']) ?><?php if ($item['unit'] !== null): ?> <span class="unit">/ <?= $e($item['unit']) ?></span><?php endif; ?></p>
            <p><span class="pill <?= $e($item['availability']) ?>"><?= $e($labels[$item['availability']] ?? $item['availability']) ?></span></p>
            <?php if ($item['description'] !== null): ?>
                <p class="desc"><?= $e($item['description']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
