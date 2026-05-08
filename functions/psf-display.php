<?php
// Prevent multiple registrations of the same hooks
if (defined('PSF_HOOKS_REGISTERED')) {
    return;
}
define('PSF_HOOKS_REGISTERED', true);

// Check if hook rendering is enabled - if not, don't register any hooks
$hook_rendering_enabled = get_option('hook_rendering_enabled', 'yes');
if ($hook_rendering_enabled === 'no') {
    // Hook rendering is disabled, exit early without registering any hooks
    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] Hook rendering is DISABLED - no hooks will be registered');
    }
    return;
}

// Get the visual hooks from the options, providing a default value if they're not set.
$woo_visual_hook = psf_get_option_safe('woo_visual_hook', 'woocommerce_after_shop_loop');
$page_visual_hook = psf_get_option_safe('page_visual_hook', 'the_content');

// Debug: Log what hooks we're trying to use
if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
    error_log('[PSF DEBUG] === HOOK SETUP ===');
    error_log('[PSF DEBUG] Target WooCommerce hook: ' . $woo_visual_hook);

    // Debug Elementor detection
    error_log('[PSF DEBUG] ELEMENTOR_VERSION defined: ' . (defined('ELEMENTOR_VERSION') ? 'YES' : 'NO'));
    if (defined('ELEMENTOR_VERSION')) {
        error_log('[PSF DEBUG] Elementor version: ' . ELEMENTOR_VERSION);
    }
    error_log('[PSF DEBUG] elementor_theme_do_location exists: ' . (function_exists('elementor_theme_do_location') ? 'YES' : 'NO'));
    // error_log('[PSF DEBUG] is_elementor_active check: ' . ($is_elementor_active ? 'YES' : 'NO'));
}

// HELLO ELEMENTOR COMPATIBILITY: Use hooks that actually work with the theme template structure
$hello_elementor_hooks = [
    'loop_end' => 10,                    // After WordPress loop in archive.php
    'get_footer' => 15,                  // Before footer is rendered  
    'wp_footer' => 99                    // Last resort
];

// Traditional WooCommerce hooks for WooCommerce-native themes
$woo_hooks_priority = [
    $woo_visual_hook => 15,
    'woocommerce_after_shop_loop' => 20,         // Most reliable
    'woocommerce_after_main_content' => 25,     // Standard WooCommerce
    'woocommerce_archive_description' => 30,    // Alternative position
];

// Check if we're using Hello Elementor theme specifically
$current_theme = get_template();
$is_hello_elementor = ($current_theme === 'hello-elementor');

if ($is_hello_elementor) {
    // STRATEGY: Try user's chosen hook first, then Hello Elementor compatible hooks as backup

    // 1. Register user's chosen WooCommerce hook first (highest priority)
    if (psf_is_safe_string($woo_visual_hook)) {
        add_action($woo_visual_hook, 'psf_faq_add_hello_elementor_markup', 5);

        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] Registered FAQ on USER CHOSEN hook: ' . $woo_visual_hook . ' (priority: 5)');
        }
    }

    // 2. Add Hello Elementor compatible hooks as backup (lower priority)
    foreach ($hello_elementor_hooks as $hook => $priority) {
        add_action($hook, 'psf_faq_add_hello_elementor_markup', $priority + 10); // Lower priority than user's choice

        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] Registered FAQ on Hello Elementor BACKUP hook: ' . $hook . ' (priority: ' . ($priority + 10) . ')');
        }
    }

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] Using HELLO ELEMENTOR HYBRID strategy - user hook + backup hooks');
    }
} else {
    // Use traditional WooCommerce hooks for other themes
    foreach ($woo_hooks_priority as $hook => $priority) {
        if (psf_is_safe_string($hook)) {
            add_action($hook, 'psf_faq_add_product_category_markup', $priority);

            if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
                error_log('[PSF DEBUG] Registered FAQ on WooCommerce hook: ' . $hook . ' (priority: ' . $priority . ')');
            }
        }
    }

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] Using TRADITIONAL WooCommerce hook strategy');
    }
}

// Register page FAQ function
if (psf_is_safe_string($page_visual_hook)) {
    add_action($page_visual_hook, 'psf_page_faq_add_markup', 15);
    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] Registered page FAQ on: ' . $page_visual_hook);
    }
} else {
    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] WARNING: Page hook is not safe: ' . var_export($page_visual_hook, true));
    }
}

// Debug function to log available hooks on category pages
add_action('template_redirect', function () {
    // Only run debug if both WP_DEBUG and plugin debug mode are enabled
    if (!defined('WP_DEBUG') || !WP_DEBUG || get_option('debug_mode', '0') !== '1') {
        return;
    }

    if (is_product_category()) {
        error_log('[PSF DEBUG] We are on a product category page');
        error_log('[PSF DEBUG] Current URL: ' . $_SERVER['REQUEST_URI']);

        $queried_object = get_queried_object();
        if ($queried_object && isset($queried_object->term_id)) {
            error_log('[PSF DEBUG] Category name: ' . $queried_object->name);
            error_log('[PSF DEBUG] Category ID: ' . $queried_object->term_id);
            error_log('[PSF DEBUG] Category slug: ' . $queried_object->slug);
        }
    }
});

// Hook detection for admin interface
add_action('wp_footer', function () {
    // Only run hook detection if it's enabled in settings and we're on a category page
    if (!is_product_category() || get_option('hook_detection_enabled', '0') !== '1') {
        return;
    }

?>
    <script>
        (function() {
            // Only run if we can access localStorage
            if (typeof(Storage) === "undefined") return;

            var detectedHooks = [];
            var hookNames = [
                'woocommerce_before_main_content',
                'woocommerce_after_main_content',
                'woocommerce_archive_description',
                'woocommerce_before_shop_loop',
                'woocommerce_after_shop_loop',
                'wp_footer'
            ];

            // Check for presence of hooks by looking for elements or running actions
            hookNames.forEach(function(hookName) {
                // Simple check - if we've gotten this far, these hooks probably exist
                detectedHooks.push(hookName);
            });

            // Store in localStorage for admin interface
            try {
                localStorage.setItem('psf_detected_hooks', JSON.stringify(detectedHooks));
                console.log('PSF: Detected hooks saved:', detectedHooks);
            } catch (e) {
                console.log('PSF: Could not save detected hooks to localStorage');
            }
        })();
    </script>
<?php
});

/**
 * Retrieves the FAQs and the custom heading for a given term or post.
 *
 * @param int $id The ID of the term or post.
 * @param string $type The type of the object ('term' or 'post').
 * @return array The FAQs and the heading.
 */
function psf_get_faqs_and_heading($id, $type) {
    if ($type === 'term') {
        $psf_custom_heading = psf_get_term_meta_safe($id, 'psf_custom_heading', '');
        $psf_category_name = get_term($id, 'product_cat')->name;
        $psf_heading = psf_has_value($psf_custom_heading) ? $psf_custom_heading : 'Vanliga frågor om ' . $psf_category_name;
        $psf_faqs = psf_get_term_meta_safe($id, 'psf_faqs', array());
    } else { // post
        $psf_custom_heading = psf_get_post_meta_safe($id, 'psf_custom_heading', '');
        $psf_heading = psf_has_value($psf_custom_heading) ? $psf_custom_heading : 'Vanliga frågor';
        $psf_faqs = psf_get_post_meta_safe($id, 'psf_faqs', array());
    }

    return [$psf_faqs, $psf_heading];
}

/**
 * Adds the FAQ markup to the product category page.
 */
function psf_faq_add_product_category_markup() {
    static $faq_rendered = false;

    // Prevent multiple renderings
    if ($faq_rendered) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] FAQ already rendered, skipping on: ' . current_action());
        }
        return;
    }

    // Check if debug mode is enabled via settings
    $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1');

    if ($debug_enabled) {
        error_log('[PSF DEBUG] === FAQ FUNCTION CALLED ===');
        error_log('[PSF DEBUG] Hook that triggered this: ' . current_action());
        error_log('[PSF DEBUG] Current page type: ' . (is_product_category() ? 'PRODUCT_CATEGORY' : 'NOT_PRODUCT_CATEGORY'));
        error_log('[PSF DEBUG] Is WooCommerce page: ' . (function_exists('is_woocommerce') && is_woocommerce() ? 'YES' : 'NO'));
    }

    $queried_object = get_queried_object();

    if (!isset($queried_object->term_id)) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No term_id - exiting');
        }
        return;
    }

    $faq_data = get_term_meta($queried_object->term_id, 'psf_faqs', true);

    if (empty($faq_data) || !is_array($faq_data)) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No FAQ data - exiting');
        }
        return;
    }

    // Mark as rendered before actually rendering
    $faq_rendered = true;

    if ($debug_enabled) {
        error_log('[PSF DEBUG] RENDERING FAQ ON: ' . current_action());
    }

    // Get the proper heading
    $psf_custom_heading = psf_get_term_meta_safe($queried_object->term_id, 'psf_custom_heading', '');
    $psf_category_name = get_term($queried_object->term_id, 'product_cat')->name;
    $faq_heading = psf_has_value($psf_custom_heading) ? $psf_custom_heading : 'Vanliga frågor om ' . $psf_category_name;

    // Use the proper rendering function and echo the output
    echo psf_generate_faq_markup($faq_data, $faq_heading);
}

/**
 * Adds the FAQ markup to a page.
 */
function psf_page_faq_add_markup() {
    static $page_faq_rendered = false;

    // Prevent multiple renderings
    if ($page_faq_rendered) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] Page FAQ already rendered, skipping on: ' . current_action());
        }
        return;
    }

    if (!is_page()) {
        return;
    }

    $post_id = get_the_ID();
    list($psf_faqs, $faq_heading) = psf_get_faqs_and_heading($post_id, 'post');

    if (empty($psf_faqs)) {
        return;
    }

    // Mark as rendered before actually rendering
    $page_faq_rendered = true;

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] RENDERING Page FAQ ON: ' . current_action());
    }

    echo psf_generate_faq_markup($psf_faqs, $faq_heading);
}

/**
 * Adds the FAQ markup to a page. (Legacy function name)
 */
function psf_faq_add_page_markup() {
    // Call the new function for backwards compatibility
    psf_page_faq_add_markup();
}

/**
 * Adds the FAQ markup to the shop page.
 */
function psf_faq_add_shop_page_markup() {
    static $shop_faq_rendered = false;

    // Prevent multiple renderings
    if ($shop_faq_rendered) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] Shop FAQ already rendered, skipping on: ' . current_action());
        }
        return;
    }

    if (!is_shop()) {
        return;
    }

    $shop_id = wc_get_page_id('shop');
    list($psf_faqs, $faq_heading) = psf_get_faqs_and_heading($shop_id, 'post');

    if (empty($psf_faqs)) {
        return;
    }

    // Mark as rendered before actually rendering
    $shop_faq_rendered = true;

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] RENDERING Shop FAQ ON: ' . current_action());
    }

    echo psf_generate_faq_markup($psf_faqs, $faq_heading);
}

/**
 * Adds the FAQ markup for Elementor-powered category pages.
 * This function is triggered by Elementor-specific hooks.
 */
function psf_faq_add_elementor_category_markup() {
    static $elementor_faq_rendered = false;

    // Prevent multiple renderings
    if ($elementor_faq_rendered) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] Elementor FAQ already rendered, skipping on: ' . current_action());
        }
        return;
    }

    // Only run on product category pages
    if (!is_product_category()) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] Elementor hook fired but not on category page: ' . current_action());
        }
        return;
    }

    // Check if debug mode is enabled via settings
    $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1');

    if ($debug_enabled) {
        error_log('[PSF DEBUG] === ELEMENTOR FAQ FUNCTION CALLED ===');
        error_log('[PSF DEBUG] Hook that triggered this: ' . current_action());
        error_log('[PSF DEBUG] Current page type: PRODUCT_CATEGORY');
        error_log('[PSF DEBUG] Is WooCommerce page: ' . (function_exists('is_woocommerce') && is_woocommerce() ? 'YES' : 'NO'));
    }

    $queried_object = get_queried_object();

    if (!isset($queried_object->term_id)) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No term_id - exiting');
        }
        return;
    }

    $faq_data = get_term_meta($queried_object->term_id, 'psf_faqs', true);

    if (empty($faq_data) || !is_array($faq_data)) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No FAQ data - exiting');
        }
        return;
    }

    // Mark as rendered before actually rendering
    $elementor_faq_rendered = true;

    if ($debug_enabled) {
        error_log('[PSF DEBUG] RENDERING ELEMENTOR FAQ ON: ' . current_action());
    }

    // Get the proper heading
    $psf_custom_heading = psf_get_term_meta_safe($queried_object->term_id, 'psf_custom_heading', '');
    $psf_category_name = get_term($queried_object->term_id, 'product_cat')->name;
    $faq_heading = psf_has_value($psf_custom_heading) ? $psf_custom_heading : 'Vanliga frågor om ' . $psf_category_name;

    // Use the proper rendering function and echo the output
    echo psf_generate_faq_markup($faq_data, $faq_heading);
}

/**
 * Adds the FAQ markup for Elementor archive locations.
 * This is triggered specifically when Elementor renders archive templates.
 */
function psf_faq_add_elementor_archive_markup() {
    static $archive_faq_rendered = false;

    // Prevent multiple renderings
    if ($archive_faq_rendered) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] Archive FAQ already rendered, skipping on: ' . current_action());
        }
        return;
    }

    // Only run on product category pages
    if (!is_product_category()) {
        return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] === ELEMENTOR ARCHIVE FAQ CALLED ===');
        error_log('[PSF DEBUG] Hook: ' . current_action());
    }

    // Call the main Elementor function but mark this as rendered too
    $archive_faq_rendered = true;
    psf_faq_add_elementor_category_markup();
}

/**
 * Adds the FAQ markup for Hello Elementor theme.
 * This function works with Hello Elementor's template structure.
 */
function psf_faq_add_hello_elementor_markup() {
    static $hello_elementor_faq_rendered = false;

    // Check if debug mode is enabled via settings
    $debug_enabled = (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1');

    if ($debug_enabled) {
        error_log('[PSF DEBUG] === HELLO ELEMENTOR FAQ FUNCTION CALLED ===');
        error_log('[PSF DEBUG] Hook that triggered this: ' . current_action());
        error_log('[PSF DEBUG] is_product_category(): ' . (is_product_category() ? 'YES' : 'NO'));
        error_log('[PSF DEBUG] Current URL: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'UNKNOWN'));
        error_log('[PSF DEBUG] WordPress main query: ' . (is_main_query() ? 'YES' : 'NO'));
        error_log('[PSF DEBUG] In the loop: ' . (in_the_loop() ? 'YES' : 'NO'));
    }

    // Prevent multiple renderings
    if ($hello_elementor_faq_rendered) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] Hello Elementor FAQ already rendered, skipping on: ' . current_action());
        }
        return;
    }

    // Only run on product category pages
    if (!is_product_category()) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] Hello Elementor hook fired but not on category page: ' . current_action());
        }
        return;
    }

    if ($debug_enabled) {
        error_log('[PSF DEBUG] Current page type: PRODUCT_CATEGORY');
        error_log('[PSF DEBUG] Is WooCommerce page: ' . (function_exists('is_woocommerce') && is_woocommerce() ? 'YES' : 'NO'));
    }

    $queried_object = get_queried_object();

    if (!isset($queried_object->term_id)) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No term_id - exiting');
        }
        return;
    }

    $faq_data = get_term_meta($queried_object->term_id, 'psf_faqs', true);

    if ($debug_enabled) {
        error_log('[PSF DEBUG] Term ID: ' . $queried_object->term_id);
        error_log('[PSF DEBUG] FAQ data type: ' . gettype($faq_data));
        error_log('[PSF DEBUG] FAQ data empty: ' . (empty($faq_data) ? 'YES' : 'NO'));
        error_log('[PSF DEBUG] FAQ data is array: ' . (is_array($faq_data) ? 'YES' : 'NO'));
        if (is_array($faq_data)) {
            error_log('[PSF DEBUG] FAQ count: ' . count($faq_data));
        }
    }

    if (empty($faq_data) || !is_array($faq_data)) {
        if ($debug_enabled) {
            error_log('[PSF DEBUG] No FAQ data - exiting');
        }
        return;
    }

    // Mark as rendered before actually rendering
    $hello_elementor_faq_rendered = true;

    if ($debug_enabled) {
        error_log('[PSF DEBUG] RENDERING HELLO ELEMENTOR FAQ ON: ' . current_action());
    }

    // Get the proper heading
    $psf_custom_heading = psf_get_term_meta_safe($queried_object->term_id, 'psf_custom_heading', '');
    $psf_category_name = get_term($queried_object->term_id, 'product_cat')->name;
    $faq_heading = psf_has_value($psf_custom_heading) ? $psf_custom_heading : 'Vanliga frågor om ' . $psf_category_name;

    if ($debug_enabled) {
        error_log('[PSF DEBUG] FAQ heading: ' . $faq_heading);
        error_log('[PSF DEBUG] About to call psf_generate_faq_markup()');
    }

    // Use the proper rendering function and echo the output
    echo psf_generate_faq_markup($faq_data, $faq_heading);

    if ($debug_enabled) {
        error_log('[PSF DEBUG] FAQ markup echoed successfully');
    }
}

// Register shop page FAQ hook (moved from inline registration)
add_action('woocommerce_after_shop_loop', 'psf_faq_add_shop_page_markup', 15);

/**
 * Hook discovery function for Hello Elementor theme
 * This logs all available hooks on category pages
 */
function psf_discover_available_hooks() {
    static $hooks_logged = false;

    if ($hooks_logged) {
        return;
    }

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        $hooks_logged = true;

        error_log('[PSF DEBUG] === HOOK DISCOVERY START ===');

        // List of all potential hooks to test
        $test_hooks = [
            // WordPress core hooks
            'wp_head',
            'wp_footer',
            'get_header',
            'get_footer',
            'loop_start',
            'loop_end',
            'the_post',
            'pre_get_posts',
            'wp_enqueue_scripts',
            'template_redirect',

            // WooCommerce hooks  
            'woocommerce_before_main_content',
            'woocommerce_after_main_content',
            'woocommerce_before_shop_loop',
            'woocommerce_after_shop_loop',
            'woocommerce_archive_description',
            'woocommerce_before_shop_loop_item',
            'woocommerce_after_shop_loop_item',
            'woocommerce_shop_loop',
            'woocommerce_before_single_product',
            'woocommerce_after_single_product',
            'woocommerce_sidebar',
            'woocommerce_before_template_part',
            'woocommerce_after_template_part',
            'woocommerce_output_content_wrapper',
            'woocommerce_output_content_wrapper_end',

            // Elementor hooks
            'elementor/frontend/before_render',
            'elementor/frontend/after_render',
            'elementor/theme/before_do_location',
            'elementor/theme/after_do_location',
            'elementor/page/before_render',
            'elementor/page/after_render',
            'elementor/widget/before_render_content',
            'elementor/widget/after_render_content',

            // Hello Elementor specific hooks
            'hello_elementor_content_top',
            'hello_elementor_content_bottom',
            'hello_elementor_header',
            'hello_elementor_footer',

            // Archive/Template hooks
            'get_template_part_content',
            'get_template_part_archive',
            'archive_template',
        ];

        // Add discovery callbacks to all test hooks
        foreach ($test_hooks as $hook) {
            add_action($hook, function () use ($hook) {
                // Check if we're on a category page using multiple methods
                $is_cat_page = false;
                if (function_exists('is_product_category')) {
                    $is_cat_page = is_product_category();
                } elseif (function_exists('is_woocommerce') && function_exists('is_archive')) {
                    $is_cat_page = is_woocommerce() && is_archive();
                } elseif (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/perfume/men/') !== false) {
                    $is_cat_page = true;
                }

                if ($is_cat_page) {
                    error_log('[PSF DEBUG] HOOK FOUND: ' . $hook . ' (triggered on category page)');
                }
            }, 999);
        }

        error_log('[PSF DEBUG] Hook discovery callbacks added for ' . count($test_hooks) . ' hooks');
        error_log('[PSF DEBUG] === HOOK DISCOVERY END ===');
    }
}

// Call hook discovery when WordPress is fully loaded
add_action('wp_loaded', 'psf_discover_available_hooks', 1);
