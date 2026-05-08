# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WordPress plugin (`page-specific-faq`, prefix `psf_`) that adds FAQ sections to pages and WooCommerce product categories, with Schema.org `FAQPage` JSON-LD output and Rank Math meta-description integration.

Runtime: WordPress 5.0+, PHP 7.4+. Optional dep: WooCommerce (product categories), Rank Math (SEO filter), Hello Elementor (themed render path). No build, no test suite, no package manager — edit PHP/JS/CSS directly and reload WP.

## Bootstrap order

`register-psf.php` is the entry point. It defines path constants (`PSF_PLUGIN_PATH`, `PSF_FUNCTIONS_PATH`, `PSF_SETTINGS_GROUP`, etc.) then `glob()`s every file in `functions/*.php` and `require_once`s them. **Loading is alphabetical** — files cannot rely on each other being loaded in a specific order beyond what alphabetical glob produces. Don't add files that mutate constants from other function files at top-level.

`functions/psf-display.php` registers all frontend rendering hooks at top-level on include. It guards against double-include with `PSF_HOOKS_REGISTERED` and bails early if option `hook_rendering_enabled === 'no'`.

## Data model

Two storage targets, identical shape:
- **Pages**: `post_meta` keys `psf_faqs` (array of `['faqQuestion' => ..., 'faqAnswer' => ...]`) and `psf_custom_heading` on `post_type=page`.
- **Product categories**: `term_meta` same keys on `product_cat` taxonomy.

Saved by `functions/psf-meta-box-repeater.php` via `save_post` (pages) and `edited_product_cat` (terms). Read everywhere via `psf_get_post_meta_safe` / `psf_get_term_meta_safe` in `helpers.php` — these wrappers normalize null/false/'' to a default. Prefer them over raw `get_*_meta` to keep behavior consistent.

## Render paths (there are several — know which one fires)

All paths funnel into `psf_generate_faq_markup($faqs, $heading)` in `functions/psf-frontend-markup.php` (single source of truth for HTML). Multiple entry points exist because different themes expose different hooks:

1. **Pages**: `psf_page_faq_add_markup` on configurable `page_visual_hook` option (default `the_content`).
2. **Product categories, traditional themes**: `psf_faq_add_product_category_markup` registered on multiple WooCommerce hooks (`woocommerce_after_shop_loop`, `woocommerce_after_main_content`, `woocommerce_archive_description`, plus user-chosen `woo_visual_hook`).
3. **Product categories, Hello Elementor theme**: `psf_faq_add_hello_elementor_markup` registered on `loop_end` / `get_footer` / `wp_footer` as backups (Hello Elementor's templating bypasses normal Woo hooks).
4. **Shop page**: `psf_faq_add_shop_page_markup` on `woocommerce_after_shop_loop`, reads FAQs from the shop page's post meta via `wc_get_page_id('shop')`.
5. **Shortcode**: `[psf_faq]` / `[psf_faq category_id="N"]` / `[psf_faq category_slug="x"]` / `[psf_faq no_heading="true"]` in `psf-shortcode.php`.
6. **JSON-LD**: `psf_add_structured_data` on `wp_head` emits `<script type="application/ld+json">` for any singular post/page with `psf_faqs`.

Each render fn uses a `static $rendered` guard to avoid double-render when multiple registered hooks fire. If you add a new render path, follow that pattern or you'll get duplicates on themes that fire several Woo hooks.

## Settings (wp_options)

Registered in `functions/psf-settings.php` under group `PSF_SETTINGS_GROUP`:
- `activate_psf` (`yes`/`no`)
- `enabled_pages` (comma-separated post IDs)
- `woo_visual_hook` (default `woocommerce_after_main_content`) — note `psf-display.php` reads default `woocommerce_after_shop_loop` for that var; mismatch is intentional fallback chain
- `page_visual_hook` (default `the_content`)
- `debug_mode` (`'0'`/`'1'`) — gates the `[PSF DEBUG]` `error_log` calls scattered across the codebase
- `hook_detection_enabled` — emits a footer `<script>` storing detected hooks in `localStorage` under key `psf_detected_hooks` for the admin UI to read
- `hook_rendering_enabled` (`yes`/`no`) — kill switch; `no` skips all hook registration

Settings page slug: `psf-settings` (top-level admin menu, position 3, SVG icon base64-embedded from `assets/images/faqs.svg`).

## File layout

- `register-psf.php` — entry, constants, asset enqueue, admin menu
- `functions/helpers.php` — safe meta wrappers, Rank Math `rank_math/frontend/description` filter, `wp_head` JSON-LD, deprecated-warning logger, debug dumper
- `functions/psf-display.php` — frontend hook registration + render entry points (the file with the most theme-specific logic)
- `functions/psf-frontend-markup.php` — `psf_generate_faq_markup` (only HTML emitter for FAQ block)
- `functions/psf-meta-box-repeater.php` — registers metaboxes, handles saves
- `functions/psf-metabox-markup.php` — admin metabox HTML
- `functions/psf-settings.php` — `register_setting` calls + settings page callback
- `functions/psf-settings-markup.php` — settings page HTML
- `functions/psf-shortcode.php` — `[psf_faq]` shortcode
- `assets/{css,js,images}/` — `admin-styles.css` + `admin-psf-scripts.js` enqueued only on `product_cat` / `page` / settings screens; `styles.css` + `scripts.js` always enqueued frontend

Asset versioning uses `psf_get_plugin_version()` which reads the plugin header — bump `Version:` in `register-psf.php` to bust caches.

## Conventions

- All globals/functions prefixed `psf_` (some legacy ones not — `custom_psf_meta_box_save`, `get_all_products`, `get_all_product_categories` — the unprefixed ones are pre-existing, don't add new unprefixed names).
- Heading fallback strings are Swedish (`'Vanliga frågor'`, `'Vanliga frågor om <category>'`) — this is intentional, not a typo.
- 2-space indent in PHP.
- Debug logs always gated by `WP_DEBUG && get_option('debug_mode','0')==='1'` — keep that guard if you add more.
