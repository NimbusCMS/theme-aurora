<?php

declare(strict_types=1);

namespace NimbusCMS\ThemeAurora\Tests;

use Nimbus\View\View;
use PHPUnit\Framework\TestCase;

/**
 * Aurora renders author content to the PUBLIC, so the one real risk is an
 * unescaped value (stored/reflected XSS). These render the storefront and content
 * templates through core's View with hostile input and assert escape-on-render,
 * and scan the templates + stylesheet for CSP-unsafe patterns (inline style=,
 * external asset loads). The render machinery itself is core-tested; this guards
 * that *these templates* escape.
 */
final class ThemeRenderTest extends TestCase
{
    private View $view;

    protected function setUp(): void
    {
        // View resolves templates from {path}/templates — the theme root.
        $this->view = new View(dirname(__DIR__));
    }

    /** @return array<string,mixed> a storefront listing view-model with a hostile value */
    private function shopData(string $payload): array
    {
        return [
            'items' => [[
                'sku_code' => 'x', 'name' => $payload, 'price' => '1.00', 'unit' => 'each',
                'description' => $payload, 'image_media_id' => null, 'category_id' => null,
                'category' => null, 'featured' => false, 'availability' => 'in_stock',
            ]],
            'categories' => [],
            'current'    => ['category' => '', 'q' => $payload, 'sort' => ''],
            'page' => 1, 'pages' => 1, 'total' => 1, 'available' => true,
            'cspNonce' => 'test-nonce',
            'media'    => static fn (?int $id): ?array => null,
        ];
    }

    public function test_shop_index_escapes_item_name_and_reflected_search(): void
    {
        $html = $this->view->renderBare('shop-index', $this->shopData('<script>alert(1)</script>'));

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html, 'item name + reflected ?q are escaped');
    }

    public function test_shop_product_escapes_fields(): void
    {
        $item = [
            'sku_code' => 'x', 'name' => '<b>x</b>', 'price' => '1.00', 'unit' => 'each',
            'description' => '<script>alert(1)</script>', 'image_media_id' => null,
            'category_id' => null, 'category' => '<i>cat</i>', 'featured' => false, 'availability' => 'in_stock',
        ];
        $html = $this->view->renderBare('shop-product', [
            'item' => $item, 'cspNonce' => 'test-nonce', 'media' => static fn (?int $id): ?array => null,
        ]);

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringNotContainsString('<b>x</b>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_entry_escapes_title_and_fields(): void
    {
        $html = $this->view->renderBare('entry', [
            'collection' => ['handle' => 'p', 'name' => 'Pages'],
            'entry' => ['title' => '<script>alert(1)</script>', 'published_at' => null, 'fields' => ['body' => '<img src=x onerror=alert(1)>']],
        ]);

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringNotContainsString('onerror=alert(1)>', $html, 'a field value is escaped, not live markup');
        self::assertStringContainsString('&lt;', $html);
    }

    public function test_cart_escapes_item_names_and_carries_the_csrf_token(): void
    {
        $cart = ['lines' => [[
            'sku_code' => 'x', 'name' => '<script>alert(1)</script>', 'unit' => null,
            'qty' => 2, 'unit_price' => '1.00', 'line_total' => '2.00', 'availability' => 'in_stock',
        ]], 'total' => '2.00', 'count' => 1];
        $html = $this->view->renderBare('shop-cart', ['cart' => $cart, 'csrf' => 'CSRF123', 'available' => true]);

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringContainsString('value="CSRF123"', $html, 'the CSRF token is in the update/remove forms');
    }

    public function test_order_confirmation_escapes_the_reference(): void
    {
        $html = $this->view->renderBare('shop-order', ['ref' => '<b>ORD</b>']);
        self::assertStringNotContainsString('<b>ORD</b>', $html);
        self::assertStringContainsString('&lt;b&gt;', $html);
    }

    /**
     * @dataProvider templateFiles
     */
    public function test_templates_have_no_inline_style_and_no_script_or_style_blocks(string $file): void
    {
        $src = (string) file_get_contents($file);
        self::assertDoesNotMatchRegularExpression('/\sstyle\s*=\s*["\']/', $src, "no inline style= in {$file} (dropped by the nonce-only CSP)");
        // Aurora keeps ALL styling in app.css — no <style>/<script> in templates.
        self::assertStringNotContainsString('<style', $src, "no <style> block in {$file}");
        self::assertStringNotContainsString('<script', $src, "no <script> in {$file}");
    }

    /** @return list<array{string}> */
    public static function templateFiles(): array
    {
        return array_map(
            static fn (string $f): array => [$f],
            glob(dirname(__DIR__) . '/templates/*.php') ?: [],
        );
    }

    public function test_the_stylesheet_loads_no_external_assets(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__) . '/assets/app.css');
        self::assertDoesNotMatchRegularExpression('#url\(\s*["\']?https?:#i', $css, 'no remote url() in app.css');
        self::assertStringNotContainsString('@import', $css, 'no @import (external stylesheet) in app.css');
        self::assertDoesNotMatchRegularExpression('#fonts\.(googleapis|gstatic)#i', $css, 'no external fonts');
    }
}
