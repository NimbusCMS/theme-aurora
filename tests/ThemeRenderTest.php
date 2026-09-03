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

    public function test_order_confirmation_renders_an_itemised_receipt_escaped(): void
    {
        $html = $this->view->renderBare('shop-order', [
            'ref'   => 'ORD-9',
            'order' => [
                'status' => 'placed', 'total' => '1.80',
                'lines'  => [['name' => '<script>alert(1)</script>', 'sku_code' => 'x', 'qty' => 2, 'unit_price' => '0.90', 'line_total' => '1.80']],
            ],
        ]);

        self::assertStringContainsString('Your order', $html);
        self::assertStringNotContainsString('<script>alert(1)</script>', $html, 'the line name is escaped');
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringContainsString('1.80', $html, 'the total renders');
    }

    /**
     * The prose parser's one security rule: escape FIRST, format second. A script tag
     * in author copy is inert; `**bold**`/`## `/lists produce only the tags the
     * parser constructs; and `[text](url)` becomes a link ONLY for a safe relative
     * URL — `javascript:`/protocol-relative/absolute-external render as plain text.
     */
    public function test_prose_parser_escapes_first_and_gates_links(): void
    {
        $prose = require dirname(__DIR__) . '/templates/_prose.php';
        $e     = static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

        $inert = $prose('<script>alert(1)</script>', $e);
        self::assertStringNotContainsString('<script>alert(1)', $inert);
        self::assertStringContainsString('&lt;script&gt;', $inert);

        $rich = $prose("## Title\n\n- one\n- two\n\n**bold** words", $e);
        self::assertStringContainsString('<h2>Title</h2>', $rich);
        self::assertStringContainsString('<ul><li>one</li><li>two</li></ul>', $rich);
        self::assertStringContainsString('<strong>bold</strong>', $rich);

        $links = $prose('[ok](/shop) [a](javascript:alert(1)) [b](//evil.example) [c](https://evil.example)', $e);
        self::assertStringContainsString('<a href="/shop">ok</a>', $links);
        self::assertStringNotContainsString('href="javascript', $links);
        self::assertStringNotContainsString('<a href="//', $links);
        self::assertStringNotContainsString('<a href="https://', $links);
    }

    /** The shared safe-href allow-list: site-relative allowed; every scheme + //host rejected. */
    public function test_safe_href_allow_list(): void
    {
        $safe = require dirname(__DIR__) . '/templates/_url.php';

        self::assertSame('/shop', $safe('/shop'));
        self::assertSame('/shop?category=fruit', $safe('/shop?category=fruit'));
        self::assertSame('#top', $safe('#top'));
        self::assertSame('about', $safe('about'));

        self::assertNull($safe('javascript:alert(1)'));
        self::assertNull($safe('data:text/html,x'));
        self::assertNull($safe('//evil.example'));
        self::assertNull($safe('https://evil.example'));
        self::assertNull($safe('mailto:x@y.z'));
        self::assertNull($safe('foo:bar'));
        self::assertNull($safe(''));
    }

    /** The home landing escapes authored values and gates aisle links (relative-only). */
    public function test_entry_home_escapes_and_gates_aisle_links(): void
    {
        $html = $this->view->renderBare('entry-home', [
            'appName' => 'Foodmart',
            'entry'   => ['title' => 'Home', 'fields' => [
                'tagline' => '<script>alert(1)</script>',
                'aisles'  => "Fruit | fresh | /shop?category=fruit\nEvil | x | javascript:alert(1)\nAbs | y | https://evil.example",
                'body'    => "## About us\n\nWe **care** about food.",
            ]],
        ]);

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringContainsString('href="/shop?category=fruit"', $html, 'a safe aisle URL becomes a link');
        self::assertStringNotContainsString('href="javascript', $html);
        self::assertStringNotContainsString('href="https://evil', $html);
        self::assertStringContainsString('<h2>About us</h2>', $html);
        self::assertStringContainsString('<strong>care</strong>', $html);
    }

    /** The home renders a live "Featured this week" row from contributed view data (ADR 0027), escaped. */
    public function test_entry_home_renders_contributed_featured_products_escaped(): void
    {
        $html = $this->view->renderBare('entry-home', [
            'appName' => 'Shop',
            'entry'   => ['title' => 'Home', 'fields' => ['tagline' => 'Hi']],
            'media'   => static fn (?int $id): ?array => $id === 7 ? ['url' => '/uploads/items/milk.jpg', 'alt' => 'Milk'] : null,
            'contrib' => ['nimbuscms.storefront' => ['featured' => [[
                'sku_code' => 'a b', 'name' => '<b>Milk</b>', 'price' => '1.20', 'unit' => 'litre',
                'availability' => 'in_stock', 'image_media_id' => 7,
            ]]]],
        ]);

        self::assertStringContainsString('Featured this week', $html);
        self::assertStringNotContainsString('<b>Milk</b>', $html, 'the featured name is escaped');
        self::assertStringContainsString('&lt;b&gt;Milk', $html);
        self::assertStringContainsString('href="/shop/a%20b"', $html, 'sku is rawurlencoded into the link');
        self::assertStringContainsString('src="/uploads/items/milk.jpg"', $html, 'the resolved product photo renders');
    }

    /** No contribution → no featured row (the seam is inert without a plugin). */
    public function test_entry_home_has_no_featured_row_without_a_contribution(): void
    {
        $html = $this->view->renderBare('entry-home', [
            'appName' => 'Shop',
            'entry'   => ['title' => 'Home', 'fields' => ['tagline' => 'Hi']],
        ]);
        self::assertStringNotContainsString('Featured this week', $html);
    }

    /**
     * The header cart count renders ONLY when a cart_summary is supplied (section
     * pages). On content pages (null summary) it must not render — a count baked into
     * a path-cached page would leak across visitors (ADR 0026 cache-safety).
     */
    public function test_header_cart_count_is_section_only(): void
    {
        $section = $this->view->renderBare('header', ['appName' => 'X', 'menus' => [], 'cart_summary' => ['count' => 3, 'total' => '9.99']]);
        self::assertStringContainsString('class="count"', $section);
        self::assertStringContainsString('>3</span>', $section);

        $content = $this->view->renderBare('header', ['appName' => 'X', 'menus' => [], 'cart_summary' => null]);
        self::assertStringNotContainsString('class="count"', $content, 'no count on a content (cached) page');
    }

    /** The listing flash escapes the item name and flags the just-added card. */
    public function test_shop_index_flash_escapes_and_marks_the_added_card(): void
    {
        $data           = $this->shopData('milk');
        $data['added']  = ['sku' => 'x', 'name' => '<b>Milk</b>'];
        $data['notice'] = null;
        $html           = $this->view->renderBare('shop-index', $data);

        self::assertStringContainsString('flash-ok', $html);
        self::assertStringNotContainsString('<b>Milk</b>', $html, 'the added name is escaped');
        self::assertStringContainsString('&lt;b&gt;Milk', $html);
        self::assertStringContainsString('is-added', $html, 'the matching card (sku=x) is flagged');
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
