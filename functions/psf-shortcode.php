<?php

/**
 * Shortcode functionality for Page Specific FAQ
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode for displaying FAQ on category pages
 * Usage: [psf_faq] or [psf_faq category_id="123"] or [psf_faq category_slug="men"]
 * 
 * @param array $atts Shortcode attributes
 * @return string FAQ HTML or empty string
 */
function psf_faq_shortcode($atts = []) {
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'category_id' => '',
        'category_slug' => '',
        'heading' => '', // Optional custom heading
        'no_heading' => 'false', // Set to 'true' to hide heading completely
    ], $atts, 'psf_faq');

    $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1');

    if ($debug_enabled) {
        error_log('[PSF DEBUG] === SHORTCODE CALLED ===');
        error_log('[PSF DEBUG] Shortcode attributes: ' . print_r($atts, true));
    }

    // Determine which category to use
    $category_id = 0;
    $queried_object = null;

    if (!empty($atts['category_id'])) {
        // Use specified category ID
        $category_id = intval($atts['category_id']);
        $queried_object = get_term($category_id, 'product_cat');
    } elseif (!empty($atts['category_slug'])) {
        // Use specified category slug
        $queried_object = get_term_by('slug', $atts['category_slug'], 'product_cat');
        if ($queried_object) {
            $category_id = $queried_object->term_id;
        }
    } elseif (is_product_category()) {
        // Auto-detect current category
        $queried_object = get_queried_object();
        if ($queried_object && isset($queried_object->term_id)) {
            $category_id = $queried_object->term_id;
        }
    }

    if ($debug_enabled) {
        error_log('[PSF DEBUG] Determined category ID: ' . $category_id);
        error_log('[PSF DEBUG] Current page is_product_category: ' . (is_product_category() ? 'YES' : 'NO'));
    }

    // If no valid category found, return empty
    if (!$category_id || !$queried_object) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No valid category found, returning empty');
        }
        return '';
    }

    // Get FAQ data for this category
    $faq_data = psf_get_term_meta_safe($category_id, 'psf_faqs', []);

    if (empty($faq_data) || !is_array($faq_data)) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No FAQ data found for category ID: ' . $category_id);
        }
        return '';
    }

    if ($debug_enabled) {
        error_log('[PSF DEBUG] Found ' . count($faq_data) . ' FAQs for category');
    }

    // Determine heading
    $faq_heading = '';

    // Check if heading should be completely hidden
    if ($atts['no_heading'] === 'true' || $atts['no_heading'] === '1') {
        $faq_heading = ''; // No heading at all
        if ($debug_enabled) {
            error_log('[PSF DEBUG] no_heading=true - heading will be hidden');
        }
    } elseif (!empty($atts['heading'])) {
        // Use custom heading from shortcode
        $faq_heading = $atts['heading'];
    } else {
        // Use category's custom heading or generate default
        $psf_custom_heading = psf_get_term_meta_safe($category_id, 'psf_custom_heading', '');
        if (psf_has_value($psf_custom_heading)) {
            $faq_heading = $psf_custom_heading;
        } else {
            $faq_heading = 'Vanliga frågor om ' . $queried_object->name;
        }
    }

    if ($debug_enabled) {
        error_log('[PSF DEBUG] Using heading: ' . $faq_heading);
        error_log('[PSF DEBUG] About to generate shortcode FAQ markup');
    }

    // Generate and return the FAQ HTML
    $faq_html = psf_generate_faq_markup($faq_data, $faq_heading);

    if ($debug_enabled) {
        error_log('[PSF DEBUG] Generated shortcode FAQ HTML length: ' . strlen($faq_html) . ' characters');
    }

    return $faq_html;
}

// Register the shortcode
add_shortcode('psf_faq', 'psf_faq_shortcode');