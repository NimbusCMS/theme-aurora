<?php
/**
 * Aurora order-confirmation page (overrides the storefront default, ADR 0026).
 * Private; shown only to the visitor who placed it. Every value escaped.
 *
 * @var callable(?string):string $e
 * @var string $ref
 * @var array{status:string,total:string,lines:list<array{name:string,sku_code:string,qty:int,unit_price:string,line_total:string}>}|null $order
 */
$order = $order ?? null;
?>
<section class="hero night">
    <div class="container">
        <p class="kicker">Thank you</p>
        <h1>Order received</h1>
        <p>Your order <strong><?= $e($ref) ?></strong> has been placed — we'll be in touch to arrange payment and delivery.</p>
        <div class="actions">
            <a class="btn btn-primary" href="/shop">Continue shopping</a>
        </div>
    </div>
</section>

<?php if ($order !== null && $order['lines'] !== []): ?>
    <div class="container section">
        <div class="receipt">
            <h2>Your order</h2>
            <?php foreach ($order['lines'] as $line): ?>
                <div class="receipt-row">
                    <span class="receipt-name"><?= $e($line['name']) ?> <span class="receipt-qty">× <?= $e((string) $line['qty']) ?></span></span>
                    <span class="receipt-price price"><?= $e($line['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="receipt-row receipt-total">
                <span>Total</span>
                <strong class="price"><?= $e($order['total']) ?></strong>
            </div>
        </div>
    </div>
<?php endif; ?>
