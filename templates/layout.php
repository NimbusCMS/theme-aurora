<?php
/**
 * Aurora — the HTML shell every page renders inside.
 *
 * @var string   $appName   the site name
 * @var string   $__content the rendered page
 * @var string   $title     the page title (optional)
 * @var callable $partial   include another theme template
 * @var callable $e         escape a value for output
 * @var array<string,array<string,mixed>> $blocks reusable content blocks by slug
 * @var array{title:string,description:string,canonical:string,og_type:string} $meta
 * @var string $head extra <head> HTML contributed by plugins (already-rendered, trusted)
 * @var array{count:int,total:string}|null $cart_summary present only on section pages
 */
$pageTitle = isset($title) && $title !== '' ? $title . ' · ' . $appName : $appName;
$meta = $meta ?? [];
$announcement = ($blocks ?? [])['announcement'] ?? null;
$announcementText = null;
if ($announcement !== null) {
    $body = $announcement['fields']['body'] ?? null;
    $announcementText = is_string($body) && $body !== '' ? $body : ($announcement['title'] ?? null);
}
// Cache-bust the stylesheet by its content hash (fresh through a CDN on change).
$cssVer = substr((string) @hash_file('crc32b', __DIR__ . '/../assets/app.css'), 0, 8);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($pageTitle) ?></title>
    <?php if (!empty($meta['description'])): ?>
        <meta name="description" content="<?= $e($meta['description']) ?>">
    <?php endif; ?>
    <?php if (!empty($meta['canonical'])): ?>
        <link rel="canonical" href="<?= $e($meta['canonical']) ?>">
        <meta property="og:url" content="<?= $e($meta['canonical']) ?>">
    <?php endif; ?>
    <meta property="og:site_name" content="<?= $e($appName) ?>">
    <meta property="og:title" content="<?= $e($pageTitle) ?>">
    <?php if (!empty($meta['description'])): ?>
        <meta property="og:description" content="<?= $e($meta['description']) ?>">
    <?php endif; ?>
    <meta property="og:type" content="<?= $e($meta['og_type'] ?? 'website') ?>">
    <meta name="twitter:card" content="summary">
    <?= $head ?? '' ?>
    <link rel="stylesheet" href="/theme/assets/app.css?v=<?= $e($cssVer) ?>">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<?php if ($announcementText !== null && $announcementText !== ''): ?>
    <div class="announcement"><?= $e($announcementText) ?></div>
<?php endif; ?>
<?= $partial('header', ['cart_summary' => $cart_summary ?? null]) ?>
<main id="main">
    <?= $__content ?>
</main>
<?= $partial('footer') ?>
</body>
</html>
