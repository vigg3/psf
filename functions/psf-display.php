<?php

// Idempotent: glob() loader could pull this twice on edge configs.
if (defined('PSF_HOOKS_REGISTERED')) return;
define('PSF_HOOKS_REGISTERED', true);

// Master kill switch.
if (get_option('hook_rendering_enabled', 'yes') === 'no') {
    psf_debug_log('Hook rendering DISABLED — no hooks will be registered');
    return;
}

$woo_visual_hook  = psf_get_option_safe('woo_visual_hook', 'woocommerce_after_shop_loop');
$page_visual_hook = psf_get_option_safe('page_visual_hook', 'the_content');

/**
 * Content FILTERS that WordPress replaces with the callback's return value.
 * An echo-style action callback on one of these returns null and blanks the
 * content site-wide — such hooks must get the append-style filter callbacks.
 */
function psf_is_content_filter($hook) {
    return in_array($hook, ['the_content', 'the_excerpt'], true);
}

// Render each FAQ at the user-CONFIGURED hook only, so the chosen position is
// respected. (Previously the category FAQ was registered on several Woo hooks
// at once; priority numbers only order callbacks within one hook, so whichever
// hook fired first in the template won and the setting was ignored —
// woocommerce_archive_description fires early and hijacked it.)
//
// A single late wp_footer safety net (priority 9998) then renders anything the
// configured hook never fired for — e.g. themes like Flatsome or Hello
// Elementor whose templating bypasses the chosen Woo hook. Each render fn has a
// static guard, so the footer pass is a no-op once the FAQ is already placed:
// the configured position always wins when its hook exists, and the footer is
// pure fallback. 9998 runs just before the FAQPage schema (wp_footer 9999 in
// functions/psf-schema.php) so footer-rendered items still feed the schema.
if ($woo_visual_hook !== '') {
    if (psf_is_content_filter($woo_visual_hook)) {
        add_filter($woo_visual_hook, 'psf_append_category_faq_to_content', 15);
        psf_debug_log("Registered category FAQ filter on {$woo_visual_hook}");
    } else {
        add_action($woo_visual_hook, 'psf_render_category_faq', 15);
        psf_debug_log("Registered category FAQ on {$woo_visual_hook}");
    }
}
add_action('wp_footer', 'psf_render_category_faq', 9998);

if ($page_visual_hook !== '') {
    if (psf_is_content_filter($page_visual_hook)) {
        add_filter($page_visual_hook, 'psf_append_page_faq_to_content', 15);
        psf_debug_log("Registered page FAQ filter on {$page_visual_hook}");
    } else {
        add_action($page_visual_hook, 'psf_render_page_faq', 15);
        psf_debug_log("Registered page FAQ on {$page_visual_hook}");
    }
}
add_action('wp_footer', 'psf_render_page_faq', 9998);

// Shop page (separate hook because is_shop() is mutually exclusive with is_product_category()).
add_action('woocommerce_after_shop_loop', 'psf_render_shop_faq', 15);
add_action('wp_footer', 'psf_render_shop_faq', 9998);

/**
 * Fetch [faqs, heading] for a term or post. Heading falls back to a
 * Swedish default ("Vanliga frågor om <name>" or "Vanliga frågor").
 *
 * @param int    $id   term_id or post_id
 * @param string $type 'term' | 'post'
 * @return array{0: array, 1: string}
 */
function psf_get_faqs_and_heading($id, $type) {
    if ($type === 'term') {
        $custom = psf_get_term_meta_safe($id, 'psf_custom_heading', '');
        $term   = get_term($id, 'product_cat');
        $name   = ($term && !is_wp_error($term)) ? $term->name : '';
        $heading = psf_has_value($custom) ? $custom : ('Vanliga frågor om ' . $name);
        $faqs    = psf_get_term_meta_safe($id, 'psf_faqs', []);
    } else {
        $custom = psf_get_post_meta_safe($id, 'psf_custom_heading', '');
        $heading = psf_has_value($custom) ? $custom : 'Vanliga frågor';
        $faqs    = psf_get_post_meta_safe($id, 'psf_faqs', []);
    }
    return [$faqs, $heading];
}

/**
 * Build the FAQ block for a term once per request (idempotent across the
 * configured hook and the wp_footer fallback). Returns '' when already
 * rendered or no FAQs exist.
 */
function psf_get_faq_markup_for_term($term_id) {
    static $rendered = [];
    if (isset($rendered[$term_id])) {
        psf_debug_log("FAQ for term {$term_id} already rendered (hook " . current_filter() . ')');
        return '';
    }

    list($faqs, $heading) = psf_get_faqs_and_heading($term_id, 'term');
    if (empty($faqs) || !is_array($faqs)) return '';

    $rendered[$term_id] = true;
    psf_debug_log('Rendering FAQ on ' . current_filter() . " for term {$term_id}");
    return psf_generate_faq_markup($faqs, $heading);
}

function psf_render_faq_for_term($term_id) {
    echo psf_get_faq_markup_for_term($term_id);
}

/**
 * Filter-safe variant of psf_render_category_faq: APPENDS to the filtered
 * value instead of echoing, so content-modifying filters keep their input.
 */
function psf_append_category_faq_to_content($content) {
    $content = (string) $content;
    if (!is_product_category() || !is_main_query()) return $content;
    $obj = get_queried_object();
    if (!isset($obj->term_id)) return $content;
    return $content . psf_get_faq_markup_for_term((int) $obj->term_id);
}

function psf_render_category_faq() {
    if (!is_product_category()) return;
    $obj = get_queried_object();
    if (!isset($obj->term_id)) return;
    psf_render_faq_for_term((int) $obj->term_id);
}

function psf_render_shop_faq() {
    if (!is_shop()) return;
    static $done = false;
    if ($done) return;

    $shop_id = wc_get_page_id('shop');
    if ($shop_id < 1) return;

    list($faqs, $heading) = psf_get_faqs_and_heading($shop_id, 'post');
    if (empty($faqs)) return;

    $done = true;
    psf_debug_log('Rendering shop FAQ on ' . current_action());
    echo psf_generate_faq_markup($faqs, $heading);
}

function psf_render_page_faq() {
    if (!is_page()) return;
    static $done = false;
    if ($done) return;

    list($faqs, $heading) = psf_get_faqs_and_heading(get_the_ID(), 'post');
    if (empty($faqs)) return;

    $done = true;
    psf_debug_log('Rendering page FAQ on ' . current_action());
    echo psf_generate_faq_markup($faqs, $heading);
}

/**
 * Filter-safe variant of psf_render_page_faq for the_content/the_excerpt:
 * MUST return the (possibly appended) content — returning nothing on these
 * filters blanks every page site-wide.
 */
function psf_append_page_faq_to_content($content) {
    $content = (string) $content;
    if (!is_page() || !in_the_loop() || !is_main_query()) return $content;

    static $done = false;
    if ($done) return $content;

    list($faqs, $heading) = psf_get_faqs_and_heading(get_the_ID(), 'post');
    if (empty($faqs)) return $content;

    $done = true;
    psf_debug_log('Appending page FAQ via filter ' . current_filter());
    return $content . psf_generate_faq_markup($faqs, $heading);
}

/**
 * Footer script that records which Woo hooks fired on the current
 * category page into localStorage for the admin UI to consume.
 */
add_action('wp_footer', function () {
    if (!is_product_category() || get_option('hook_detection_enabled', '0') !== '1') return;
    ?>
    <script>
        (function () {
            if (typeof Storage === "undefined") return;
            try {
                localStorage.setItem('psf_detected_hooks', JSON.stringify([
                    'woocommerce_before_main_content',
                    'woocommerce_after_main_content',
                    'woocommerce_archive_description',
                    'woocommerce_before_shop_loop',
                    'woocommerce_after_shop_loop',
                    'wp_footer'
                ]));
            } catch (e) {}
        })();
    </script>
    <?php
});
