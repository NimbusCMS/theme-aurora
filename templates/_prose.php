<?php
/**
 * Aurora's tiny, dependency-free prose renderer for author copy (content bodies,
 * the home body). It is deliberately minimal — a theme rendering authored text, not
 * a Markdown engine — and its ONE security rule is **escape first, format second**:
 * the whole string is HTML-escaped up front, so every transform below operates on
 * already-inert text and can only ever emit the tags it constructs. Author links
 * are gated by the {@see _url.php} allow-list (relative/same-origin only), so
 * `[x](javascript:…)` renders as plain text, never a live href.
 *
 * Supported: blank line = paragraph; `## ` = h2; `- ` = bullet list; `1. ` =
 * ordered list; `**bold**`; `[text](url)` for safe relative URLs. Anything else is
 * escaped text.
 *
 * @return callable(string, callable(?string):string):string
 */
return static function (string $text, callable $e): string {
    /** @var callable(string):?string $safeHref */
    $safeHref = require __DIR__ . '/_url.php';

    // Inline formatting applied to an ALREADY-ESCAPED fragment: bold, then links.
    $inline = static function (string $s) use ($safeHref): string {
        $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s) ?? $s;
        return (string) preg_replace_callback(
            '/\[([^\]]+)\]\(([^)\s]+)\)/',
            static function (array $m) use ($safeHref): string {
                $href = $safeHref($m[2]);
                // $m[1]/$m[2] are slices of escaped text; a rejected href → plain text.
                return $href === null ? $m[0] : '<a href="' . $href . '">' . $m[1] . '</a>';
            },
            $s,
        );
    };

    $esc   = $e($text);                                  // escape EVERYTHING first
    $lines = preg_split('/\r\n|\r|\n/', $esc) ?: [];
    $html  = '';
    $list  = null;                                       // 'ul' | 'ol' | null
    /** @var list<string> $para */
    $para  = [];

    $flushPara = static function () use (&$para, &$html, $inline): void {
        if ($para !== []) {
            $html .= '<p>' . implode('<br>', array_map($inline, $para)) . '</p>';
            $para = [];
        }
    };
    $flushList = static function () use (&$list, &$html): void {
        if ($list !== null) {
            $html .= '</' . $list . '>';
            $list = null;
        }
    };

    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '') {
            $flushPara();
            $flushList();
            continue;
        }
        if (str_starts_with($t, '## ')) {
            $flushPara();
            $flushList();
            $html .= '<h2>' . $inline(substr($t, 3)) . '</h2>';
            continue;
        }
        if (str_starts_with($t, '- ')) {
            $flushPara();
            if ($list !== 'ul') {
                $flushList();
                $html .= '<ul>';
                $list = 'ul';
            }
            $html .= '<li>' . $inline(substr($t, 2)) . '</li>';
            continue;
        }
        if (preg_match('/^\d+\.\s+(.*)$/', $t, $m) === 1) {
            $flushPara();
            if ($list !== 'ol') {
                $flushList();
                $html .= '<ol>';
                $list = 'ol';
            }
            $html .= '<li>' . $inline($m[1]) . '</li>';
            continue;
        }
        $flushList();
        $para[] = $t;
    }
    $flushPara();
    $flushList();

    return $html;
};
