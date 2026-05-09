<?php

if (!defined('ABSPATH')) exit;

/**
 * [psf_faq]                       — current product_cat archive
 * [psf_faq category_id="123"]     — explicit term by ID
 * [psf_faq category_slug="men"]   — explicit term by slug
 * [psf_faq heading="Custom"]      — override heading
 * [psf_faq no_heading="true"]     — render without heading
 */
function psf_faq_shortcode($atts = []) {
    $atts = shortcode_atts([
        'category_id'   => '',
        'category_slug' => '',
        'heading'       => '',
        'no_heading'    => 'false',
    ], $atts, 'psf_faq');

    psf_debug_log('Shortcode called: ' . wp_json_encode($atts));

    $category_id = 0;
    $term = null;

    if (!empty($atts['category_id'])) {
        $category_id = (int) $atts['category_id'];
        $term = get_term($category_id, 'product_cat');
    } elseif (!empty($atts['category_slug'])) {
        $term = get_term_by('slug', $atts['category_slug'], 'product_cat');
        if ($term) $category_id = (int) $term->term_id;
    } elseif (is_product_category()) {
        $term = get_queried_object();
        if ($term && isset($term->term_id)) $category_id = (int) $term->term_id;
    }

    if (!$category_id || !$term || is_wp_error($term)) {
        psf_debug_log('Shortcode: no valid category');
        return '';
    }

    $faqs = psf_get_term_meta_safe($category_id, 'psf_faqs', []);
    if (empty($faqs) || !is_array($faqs)) {
        psf_debug_log("Shortcode: no FAQs for term {$category_id}");
        return '';
    }

    if ($atts['no_heading'] === 'true' || $atts['no_heading'] === '1') {
        $heading = '';
    } elseif ($atts['heading'] !== '') {
        $heading = $atts['heading'];
    } else {
        $custom = psf_get_term_meta_safe($category_id, 'psf_custom_heading', '');
        $heading = psf_has_value($custom) ? $custom : ('Vanliga frågor om ' . $term->name);
    }

    return psf_generate_faq_markup($faqs, $heading);
}

add_shortcode('psf_faq', 'psf_faq_shortcode');
