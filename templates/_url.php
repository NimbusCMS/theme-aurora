<?php
/**
 * Aurora's safe-href allow-list for AUTHOR-provided links (prose `[text](url)` and
 * the home page's aisle tiles). Returns the URL when it is a site-internal path,
 * a fragment, or a scheme-less relative link; returns null to REJECT anything that
 * could leave the site or run script — `javascript:`/`data:`/any scheme, a
 * protocol-relative `//host`, or a colon in the first path segment (which a browser
 * would parse as a scheme). Callers use null to render the link as plain text.
 *
 * The caller escapes the surrounding text first; this only decides link-worthiness,
 * so an escaped URL (e.g. `/shop?a=1&amp;b=2`) round-trips unchanged.
 *
 * @return callable(string):?string
 */
return static function (string $url): ?string {
    $u = trim($url);
    if ($u === '' || str_starts_with($u, '//')) {
        return null;                       // empty, or protocol-relative //host
    }
    if (preg_match('#^[a-z][a-z0-9+.\-]*:#i', $u) === 1) {
        return null;                       // any scheme: javascript:, data:, http(s):, mailto: …
    }
    // A colon anywhere in the first segment would be read as a scheme by the browser.
    $firstSegment = preg_split('~[/?#]~', $u, 2)[0] ?? $u;
    if (str_contains((string) $firstSegment, ':')) {
        return null;
    }
    return $u;                             // site-absolute (/x), fragment (#x), or relative
};
