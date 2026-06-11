# Page Specific FAQ

**Contributors:** Viggebe
**Tags:** FAQ, FAQ generator, Schema, Schema.org, SEO, WooCommerce  
**Requires at least:** 5.0  
**Tested up to:** 6.4  
**Requires PHP:** 7.4  
**Stable tag:** 2.1.1  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

## Description

Page Specific FAQ is a WordPress plugin that allows you to add custom FAQ sections to specific pages and product categories. The plugin automatically generates Schema.org structured data for better SEO and search engine optimization.

### Key Features

-   **Custom FAQ sections** on any pages and product categories
-   **Schema.org structure** for better SEO and search engine results
-   **WooCommerce integration** for product categories
-   **Flexible positioning** with customizable WordPress hooks
-   **Easy administration** with drag and drop interface
-   **Responsive design** that works on all devices

### Use Cases

-   Product categories with frequently asked questions
-   Information pages with FAQ sections
-   Contact pages with common questions
-   Any page where you want to improve user experience

## Installation

1. Upload the `page-specific-faq` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Page Specific FAQ' in the admin menu to configure settings

## Usage

### For Product Categories

1. Go to **Products > Categories** in WordPress admin
2. Edit a product category
3. Scroll down to the "Page Specific FAQ" section
4. Add a custom heading (optional)
5. Add questions and answers using the "+" button
6. Save the category

### For Pages

1. Edit a page in WordPress
2. Scroll down to the "Page Specific FAQ" metabox
3. Add a custom heading (optional)
4. Add questions and answers using the "+" button
5. Update the page

### Settings

Go to **Page Specific FAQ > Settings** to configure:

-   **Position for category pages:** Choose WordPress hook for where FAQ should appear on product categories
-   **Position for pages:** Choose WordPress hook for where FAQ should appear on regular pages

## Technical Information

### Schema.org Structure

The plugin emits a single consolidated Schema.org FAQPage block as JSON-LD in the footer (`wp_footer`), built from the same FAQ items rendered on the page so the schema can never drift from the visible content. All FAQ blocks on a page are merged into one FAQPage with a single `mainEntity` array:

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "The question",
      "acceptedAnswer": { "@type": "Answer", "text": "The answer" }
    }
  ]
}
</script>
```

### Hooks and Filters

-   `woo_visual_hook` - Hook for product categories (default: `woocommerce_after_main_content`)
-   `page_visual_hook` - Hook for pages (default: `the_content`)

PHP filters for the FAQPage schema:

-   `psf_faqpage_schema` - filter the full FAQPage data array before it is encoded
-   `psf_faqpage_answer` - filter each answer string (receives `$text, $question`)
-   `psf_faqpage_schema_already_present` - return `true` to suppress output when another plugin already emits an FAQPage

### CSS Classes

-   `.faqWrapper` - Main container for FAQ section
-   `.faqContent` - Content container
-   `.faqEntity` - Individual FAQ item
-   `.faqQuestion` - Question container
-   `.faqAnswer` - Answer container

## Folder Structure

```bash
page-specific-faq/
├── assets/
│   ├── css/
│   │   ├── admin-styles.css      # Admin panel styling
│   │   └── styles.css            # Frontend styling
│   ├── images/
│   │   └── faqs.svg              # Plugin icon
│   └── js/
│       ├── admin-psf-scripts.js  # Admin JavaScript
│       └── scripts.js            # Frontend JavaScript
├── functions/
│   ├── helpers.php               # Helper functions and utilities
│   ├── psf-display.php           # Frontend display logic and hooks
│   ├── psf-frontend-markup.php   # Frontend HTML markup functions
│   ├── psf-meta-box-repeater.php # Metabox logic and data handling
│   ├── psf-metabox-markup.php    # Backend metabox HTML markup
│   ├── psf-settings.php          # Settings registration and page logic
│   └── psf-settings-markup.php   # Backend settings page HTML markup
├── README.md
└── register-psf.php              # Main plugin file
```

## Requirements

-   WordPress 5.0 or later
-   PHP 7.4 or later
-   WooCommerce (for product category functionality)

## Changelog

### 2.1.2 — Unreleased

#### Fixed

-   Critical: the page FAQ callback was registered as an *action* on the `the_content` *filter*; it returned `null`, so WordPress replaced every page's content with nothing, blanking all pages site-wide. `the_content`/`the_excerpt` hooks now get filter callbacks (`psf_append_page_faq_to_content`, `psf_append_category_faq_to_content`) that append the FAQ block and always return the content. Same guard applied to the configurable category hook (`woo_visual_hook`).
-   Removed the `psf_ensure_content_string` band-aid filter (ran at priority 1, before the broken callback, so it never helped).

### 2.1.1

-   **Fixed: configured FAQ position is now respected on category pages.** The category FAQ was registered on several WooCommerce hooks at once, so whichever hook fired first in the template won and the `Position` setting was ignored (`woocommerce_archive_description` fires early and hijacked it). It is now registered only on the configured hook, with a single late `wp_footer` fallback (priority 9998) that renders the FAQ only if the chosen hook never fired — covering themes (Flatsome, Hello Elementor) whose templating bypasses standard Woo hooks. The same footer fallback now also applies to page and shop FAQs.

### 2.1.0

-   **Automatic FAQPage JSON-LD** — emits one consolidated Schema.org `FAQPage` block per page in the footer, built from the exact FAQ items rendered (pages, product-category archives, shop page and `[psf_faq]` shortcode all included), so schema always matches the visible questions and answers
-   Answers are reduced to plain text, whitespace-collapsed and trimmed; empty items skipped and duplicate questions deduplicated
-   New setting **FAQ Schema (JSON-LD)** (default ON) to toggle the output
-   New filters: `psf_faqpage_schema`, `psf_faqpage_answer`, `psf_faqpage_schema_already_present`
-   Output uses `JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES` so Swedish characters (å ä ö) and URLs stay readable
-   Removed the old per-block inline JSON-LD (could produce multiple FAQPage blocks on one page) and the now-unused `psf_generate_structured_data()` helper; the schema is now generated from a single source

> Note: as of 7 May 2026 Google no longer shows FAQ rich results, so this markup is primarily for AI answer engines (ChatGPT, Perplexity, Gemini, AI Overviews) and Bing. It remains valid schema.org. If you use a page cache (e.g. W3 Total Cache), purge it after upgrading or toggling the setting.

### 2.0.5

-   Improved compatibility with WordPress 6.4
-   Optimized Schema.org structure
-   Bug fixes and improvements
-   Refactored code structure for better separation of concerns
-   Consolidated settings files and improved file naming
-   Split backend markup into separate files for better maintainability

### 2.0.0

-   New admin interface
-   Improved WooCommerce integration
-   Added Schema.org structure

## Support

For support and questions, visit [viktorborg.myportfolio.com](https://viktorborg.myportfolio.com)

## License

This plugin is licensed under GPL v2 or later.
