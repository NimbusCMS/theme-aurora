<?php
/**
 * Aurora storefront listing (overrides the plugin default via ADR 0023).
 * Escapes every author + reflected value through $e; styling lives in app.css.
 *
 * @var callable(?string):string $e
 * @var callable(?int):?array{url:string,alt:?string} $media
 * @var list<array<string,mixed>> $items
 * @var list<array{id:int,name:string,slug:string,parent_id:?int}> $categories
 * @var array{category:string,q:string,sort:string} $current
 * @var int $page
 * @var int $pages
 * @var bool $available
 * @var string $cart_csrf
 * @var array{sku:string,name:string}|null $added the just-added item (flash + card state)
 * @var ?string $notice a validated notice code
 */
$labels = ['in_stock' => 'In stock', 'low' => 'Low stock', 'out' => 'Out of stock'];
$sorts  = ['featured' => 'Featured', 'name' => 'Name', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low'];
$notices = [
    'unavailable' => 'That item is unavailable right now.',
    'expired'     => 'Your session expired — please try again.',
    'empty'       => 'Your cart is empty.',
    'stock'       => 'Sorry, that item just went out of stock.',
];
$added  = $added ?? null;
$notice = $notice ?? null;
$pageUrl = static function (int $n) use ($current): string {
    $q = array_filter(['category' => $current['category'], 'q' => $current['q'], 'sort' => $current['sort'], 'page' => $n > 1 ? (string) $n : '']);
    return '/shop' . ($q === [] ? '' : '?' . http_build_query($q));
};
?>
<section class="shop-hero night">
    <div class="container">
        <p class="kicker">The shop</p>
        <h1>Everything under the aurora</h1>
    </div>
</section>

<div class="container section">
    <?php if ($added !== null): ?>
        <p class="flash flash-ok" role="status">Added <strong><?= $e($added['name']) ?></strong> to your cart. <a href="/cart">View cart →</a></p>
    <?php elseif ($notice !== null && isset($notices[$notice])): ?>
        <p class="flash flash-warn" role="status"><?= $e($notices[$notice]) ?></p>
    <?php endif; ?>

    <form class="filters" method="get" action="/shop" role="search">
        <div class="field">
            <label for="q">Search</label>
            <input id="q" type="search" name="q" value="<?= $e($current['q']) ?>" placeholder="Search products">
        </div>
        <?php if ($categories !== []): ?>
            <div class="field">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $e($c['slug']) ?>"<?= $current['category'] === $c['slug'] ? ' selected' : '' ?>><?= $c['parent_id'] !== null ? '— ' : '' ?><?= $e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="field">
            <label for="sort">Sort</label>
            <select id="sort" name="sort">
                <?php foreach ($sorts as $value => $label): ?>
                    <option value="<?= $e($value) ?>"<?= $current['sort'] === $value ? ' selected' : '' ?>><?= $e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Apply</button>
    </form>

    <?php if (!$available): ?>
        <p class="empty">The catalogue is unavailable right now.</p>
    <?php elseif ($items === []): ?>
        <p class="empty">No products found<?= $current['q'] !== '' ? ' for “' . $e($current['q']) . '”' : '' ?>.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($items as $it): ?>
                <?php $img = $media($it['image_media_id']); $href = '/shop/' . rawurlencode($it['sku_code']); ?>
                <?php $isAdded = $added !== null && $added['sku'] === $it['sku_code']; ?>
                <article class="product<?= $isAdded ? ' is-added' : '' ?>">
                    <a href="<?= $e($href) ?>" aria-label="<?= $e($it['name']) ?>">
                        <?php if ($img !== null): ?>
                            <img class="thumb" src="<?= $e($img['url']) ?>" alt="<?= $e($img['alt'] ?? $it['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="thumb thumb-empty" aria-hidden="true">✦</div>
                        <?php endif; ?>
                    </a>
                    <div class="product-body">
                        <a class="product-name" href="<?= $e($href) ?>"><?= $e($it['name']) ?></a>
                        <span class="price"><?= $e($it['price']) ?><?php if ($it['unit'] !== null): ?> <span class="unit">/ <?= $e($it['unit']) ?></span><?php endif; ?></span>
                        <span class="pill <?= $e($it['availability']) ?>"><?= $e($labels[$it['availability']] ?? $it['availability']) ?></span>
                        <?php if ($it['availability'] !== 'out'): ?>
                            <form class="add" method="post" action="/ext/shop/cart/add">
                                <input type="hidden" name="_cart_csrf" value="<?= $e($cart_csrf ?? '') ?>">
                                <input type="hidden" name="sku" value="<?= $e($it['sku_code']) ?>">
                                <input type="hidden" name="qty" value="1">
                                <input type="hidden" name="return" value="shop">
                                <input type="hidden" name="category" value="<?= $e($current['category']) ?>">
                                <input type="hidden" name="q" value="<?= $e($current['q']) ?>">
                                <input type="hidden" name="sort" value="<?= $e($current['sort']) ?>">
                                <input type="hidden" name="page" value="<?= $e((string) $page) ?>">
                                <button type="submit" class="btn btn-quiet btn-sm"><?= $isAdded ? 'Added ✓ — add another' : 'Add to cart' ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="pager" aria-label="Pagination">
                <?php if ($page > 1): ?><a class="btn btn-quiet" href="<?= $e($pageUrl($page - 1)) ?>">← Previous</a><?php endif; ?>
                <span>Page <?= $e((string) $page) ?> of <?= $e((string) $pages) ?></span>
                <?php if ($page < $pages): ?><a class="btn btn-quiet" href="<?= $e($pageUrl($page + 1)) ?>">Next →</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
