# Page Specific FAQ

**Contributors:** Viggebe
**Tags:** FAQ, FAQ generator, Schema, Schema.org, SEO, WooCommerce  
**Requires at least:** 5.0  
**Tested up to:** 6.4  
**Requires PHP:** 7.4  
**Stable tag:** 2.0.5  
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

The plugin automatically generates correct Schema.org FAQPage structure:

```html
<div
    itemscope
    itemtype="https://schema.org/FAQPage">
    <div
        itemscope
        itemprop="mainEntity"
        itemtype="https://schema.org/Question">
        <h3 itemprop="name">The Question</h3>
        <div
            itemscope
            itemprop="acceptedAnswer"
            itemtype="https://schema.org/Answer">
            <div itemprop="text">The Answer</div>
        </div>
    </div>
</div>
```

### Hooks and Filters

-   `woo_visual_hook` - Hook for product categories (default: `woocommerce_after_main_content`)
-   `page_visual_hook` - Hook for pages (default: `the_content`)

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
