<?php
/**
 * Aurora order-confirmation page (overrides the storefront default, ADR 0026).
 * Private; shown only to the visitor who placed it. Every value escaped.
 *
 * @var callable(?string):string $e
 * @var string $ref
 */
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
