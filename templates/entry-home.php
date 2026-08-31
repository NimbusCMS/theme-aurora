<?php
/**
 * The landing page (the `home` singleton) — a night hero over the aurora, then
 * the body on parchment. Generic: uses common field handles if present, with
 * sensible fallbacks, so it fits any site. Every value escaped.
 *
 * @var array{title:string,fields:array<string,mixed>} $entry
 * @var string $appName
 * @var callable $e
 */
$e = $e ?? static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$f       = $entry['fields'] ?? [];
$str     = static fn (string $k): string => is_scalar($f[$k] ?? null) ? (string) $f[$k] : '';
$hero    = is_array($f['hero'] ?? null) && isset($f['hero']['url']) ? $f['hero'] : null;
$heading = $str('tagline') !== '' ? $str('tagline') : (string) ($entry['title'] ?? $appName);
$lead    = $str('subhead') !== '' ? $str('subhead') : $str('summary');
$body    = $str('body');

$prose = static function (string $text) use ($e): string {
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim($text)) ?: [] as $para) {
        if (trim($para) !== '') {
            $out .= '<p>' . nl2br($e($para)) . '</p>';
        }
    }
    return $out;
};
?>
<section class="hero night">
    <div class="container">
        <p class="kicker">Welcome</p>
        <h1><?= $e($heading) ?></h1>
        <?php if ($lead !== ''): ?><p><?= $e($lead) ?></p><?php endif; ?>
    </div>
</section>
<?php if ($hero !== null): ?>
    <div class="container section">
        <img class="hero-image" src="<?= $e((string) $hero['url']) ?>" alt="<?= $e((string) ($hero['alt'] ?? '')) ?>">
    </div>
<?php endif; ?>
<?php if ($body !== ''): ?>
    <section class="section">
        <div class="container measure entry-body"><?= $prose($body) ?></div>
    </section>
<?php endif; ?>
