<?php
/**
 * Aurora checkout page (overrides the storefront default, ADR 0026). Private.
 *
 * @var callable(?string):string $e
 * @var array{lines:list<array<string,mixed>>,total:string,count:int} $cart
 * @var string $csrf
 * @var bool $available
 */
?>
<div class="container section checkout">
    <h1>Checkout</h1>

    <?php if (!$available || $cart['count'] === 0): ?>
        <p class="empty">Your cart is empty. <a href="/shop">Browse the shop</a>.</p>
    <?php else: ?>
        <div class="checkout-grid">
            <section class="summary">
                <h2>Order summary</h2>
                <?php foreach ($cart['lines'] as $line): ?>
                    <div class="summary-row">
                        <span><?= $e((string) $line['qty']) ?>× <?= $e($line['name']) ?></span>
                        <span class="price"><?= $e($line['line_total']) ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="summary-row summary-total"><strong>Total</strong> <strong class="price"><?= $e($cart['total']) ?></strong></div>
            </section>

            <form class="checkout-form" method="post" action="/ext/shop/checkout">
                <input type="hidden" name="_cart_csrf" value="<?= $e($csrf) ?>">
                <div class="field">
                    <label for="co-name">Name</label>
                    <input id="co-name" type="text" name="name" required maxlength="120" autocomplete="name">
                </div>
                <div class="field">
                    <label for="co-email">Email</label>
                    <input id="co-email" type="email" name="email" required maxlength="191" autocomplete="email">
                </div>
                <button type="submit" class="btn btn-primary">Place order</button>
                <p class="kicker">Payment is arranged after you place your order.</p>
            </form>
        </div>
    <?php endif; ?>
</div>
