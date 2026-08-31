<?php
/**
 * A single entry — a centered reading column on parchment. Generic: used for any
 * collection without its own entry-{handle} template. Self-contained (no plugin
 * dependency); every field value is escaped.
 *
 * @var array{handle:string,name:string} $collection
 * @var array{title:string,published_at:?string,fields:array<string,mixed>} $entry
 * @var callable $e
 */
$e = $e ?? static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Render a body-like string as escaped paragraphs (blank line = new paragraph).
$prose = static function (string $text) use ($e): string {
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim($text)) ?: [] as $para) {
        if (trim($para) !== '') {
            $out .= '<p>' . nl2br($e($para)) . '</p>';
        }
    }
    return $out;
};
$renderField = static function (mixed $value) use ($e, $prose): string {
    if ($value === null || $value === '' || $value === []) {
        return '';
    }
    if (is_array($value) && isset($value['url'])) {
        return '<img src="' . $e((string) $value['url']) . '" alt="' . $e((string) ($value['alt'] ?? '')) . '">';
    }
    if (is_array($value) && isset($value[0]) && is_array($value[0])) {
        return $e(implode(', ', array_map(static fn (array $t): string => (string) ($t['title'] ?? ''), $value)));
    }
    if (is_bool($value)) {
        return $value ? 'Yes' : 'No';
    }
    if (is_scalar($value)) {
        return $prose((string) $value);
    }
    return '';
};
$fields = $entry['fields'] ?? [];
unset($fields['summary']); // summary is meta, shown nowhere on its own
?>
<article class="section prose">
    <div class="container measure">
        <p class="kicker"><?= $e($collection['name']) ?></p>
        <h1><?= $e($entry['title']) ?></h1>
        <?php if (!empty($entry['published_at'])): ?>
            <p class="meta">Last updated <?= $e(date('j M Y', (int) strtotime($entry['published_at']))) ?></p>
        <?php endif; ?>
        <div class="entry-body">
            <?php foreach ($fields as $handle => $value): ?>
                <?php $r = $renderField($value); ?>
                <?php if ($r !== ''): ?><div class="field field-<?= $e((string) $handle) ?>"><?= $r ?></div><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</article>
