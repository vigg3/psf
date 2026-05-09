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

// Hello Elementor's templating bypasses normal Woo hooks; fall back to
// loop_end / get_footer / wp_footer when the user's chosen Woo hook never fires.
if (get_template() === 'hello-elementor') {
    if ($woo_visual_hook !== '') {
        add_action($woo_visual_hook, 'psf_render_category_faq', 5);
        psf_debug_log("Hello Elementor: registered on user hook {$woo_visual_hook} (priority 5)");
    }
    foreach (['loop_end' => 20, 'get_footer' => 25, 'wp_footer' => 109] as $hook => $priority) {
        add_action($hook, 'psf_render_category_faq', $priority);
        psf_debug_log("Hello Elementor: registered backup hook {$hook} (priority {$priority})");
    }
} else {
    $woo_hooks = [
        $woo_visual_hook                 => 15,
        'woocommerce_after_shop_loop'    => 20,
        'woocommerce_after_main_content' => 25,
        'woocommerce_archive_description' => 30,
    ];
    foreach ($woo_hooks as $hook => $priority) {
        if (is_string($hook) && $hook !== '') {
            add_action($hook, 'psf_render_category_faq', $priority);
            psf_debug_log("Registered FAQ on Woo hook {$hook} (priority {$priority})");
        }
    }
}

if ($page_visual_hook !== '') {
    add_action($page_visual_hook, 'psf_render_page_faq', 15);
    psf_debug_log("Registered page FAQ on {$page_visual_hook}");
}

// Shop page (separate hook because is_shop() is mutually exclusive with is_product_category()).
add_action('woocommerce_after_shop_loop', 'psf_render_shop_faq', 15);

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
 * Echo the FAQ block for a term once per request (idempotent across all
 * the redundant hooks in the Woo + Hello Elementor fan-out).
 */
function psf_render_faq_for_term($term_id) {
    static $rendered = [];
    if (isset($rendered[$term_id])) {
        psf_debug_log("FAQ for term {$term_id} already rendered (hook " . current_action() . ')');
        return;
    }

    list($faqs, $heading) = psf_get_faqs_and_heading($term_id, 'term');
    if (empty($faqs) || !is_array($faqs)) return;

    $rendered[$term_id] = true;
    psf_debug_log('Rendering FAQ on ' . current_action() . " for term {$term_id}");
    echo psf_generate_faq_markup($faqs, $heading);
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
