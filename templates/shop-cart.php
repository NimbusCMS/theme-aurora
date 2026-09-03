<?php
/**
 * Aurora cart page (overrides the storefront default, ADR 0026). Private
 * (no-store). Every value escaped; update/remove forms carry the cart CSRF token.
 *
 * @var callable(?string):string $e
 * @var array{lines:list<array<string,mixed>>,total:string,count:int} $cart
 * @var string $csrf
 * @var bool $available
 * @var ?string $notice a validated notice code (e.g. after a failed checkout)
 */
$notices = [
    'unavailable' => 'That item is unavailable right now.',
    'expired'     => 'Your session expired — please try again.',
    'empty'       => 'Your cart is empty.',
    'stock'       => 'Sorry, an item just went out of stock — please review your cart.',
];
$notice = $notice ?? null;
?>
<div class="container section">
    <h1>Your cart</h1>

    <?php if ($notice !== null && isset($notices[$notice])): ?>
        <p class="flash flash-warn" role="status"><?= $e($notices[$notice]) ?></p>
    <?php endif; ?>

    <?php if (!$available): ?>
        <p class="empty">The cart is unavailable right now.</p>
    <?php elseif ($cart['count'] === 0): ?>
        <div class="cart-empty">
            <p class="cart-empty-mark" aria-hidden="true">🛒</p>
            <p>Your cart is empty.</p>
            <a class="btn btn-primary" href="/shop">Browse the shop →</a>
        </div>
    <?php else: ?>
        <div class="cart">
            <?php foreach ($cart['lines'] as $line): ?>
                <div class="cart-row">
                    <div class="cart-name">
                        <?= $e($line['name']) ?><?php if ($line['unit'] !== null): ?> <span class="cart-unit">/ <?= $e($line['unit']) ?></span><?php endif; ?>
                        <span class="cart-each"><?= $e($line['unit_price']) ?> each</span>
                    </div>
                    <form class="cart-qty" method="post" action="/ext/shop/cart/update">
                        <input type="hidden" name="_cart_csrf" value="<?= $e($csrf) ?>">
                        <input type="hidden" name="sku" value="<?= $e($line['sku_code']) ?>">
                        <input type="number" name="qty" min="0" max="999" value="<?= $e((string) $line['qty']) ?>" inputmode="numeric" aria-label="Quantity of <?= $e($line['name']) ?>">
                        <button type="submit" class="btn btn-quiet">Update</button>
                    </form>
                    <div class="cart-price price"><?= $e($line['line_total']) ?></div>
                    <form class="cart-remove" method="post" action="/ext/shop/cart/update">
                        <input type="hidden" name="_cart_csrf" value="<?= $e($csrf) ?>">
                        <input type="hidden" name="sku" value="<?= $e($line['sku_code']) ?>">
                        <input type="hidden" name="qty" value="0">
                        <button type="submit" class="link-danger">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-foot">
            <p class="cart-total">Total <strong class="price"><?= $e($cart['total']) ?></strong></p>
            <a class="btn btn-primary" href="/checkout">Checkout →</a>
        </div>
    <?php endif; ?>
</div>
