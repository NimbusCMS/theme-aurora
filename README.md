# Aurora — a magical-sky theme for NimbusCMS

An official [NimbusCMS](https://github.com/NimbusCMS/nimbus) theme: deep-indigo
**night** grounds carry the chrome, content and products sit on light **moonlit
parchment**, and an **aurora** gradient (teal → violet → rose) lights the
wordmark, links, hero, and card hovers. One stylesheet, plain PHP templates, no
build step, and **no external assets** — system fonts and CSS-only motifs, so it
loads under the strict same-origin CSP.

## What it renders

- **Storefront (`/shop`)** — Aurora overrides the [Storefront plugin](https://github.com/NimbusCMS/plugin-storefront)'s
  `shop-index` and `shop-product` templates (ADR 0023), so the catalog is fully
  themed: a night hero, a filter bar, and a product grid with availability pills.
- **Content** — generic `entry-home` (a night hero), `entry` (a reading column),
  `collection` (a card grid), and a themed `404`.

It is a **generic** theme — the "magical sky" look suits any shop, brand, or
landing site, not one use case.

## Family

Aurora joins the official theme gallery: **Starter** · **Willow** · **Lumos** ·
**Aurora**. Pick one in the admin under Settings → Theme.

## Using it

Install the theme where your site reads themes (`themes/aurora`, or a mounted
volume), then select **Aurora** in Settings → Theme. To customise, edit
`assets/app.css` (all the design tokens live at the top) or override any template.

## License

MIT.
