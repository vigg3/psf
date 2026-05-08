<?php

/**
 * Get plugin version dynamically
 *
 * @return string Plugin version
 */
function psf_get_plugin_version() {
  if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
  }

  $plugin_data = get_plugin_data(PSF_PLUGIN_PATH . 'register-psf.php');
  return $plugin_data['Version'] ?? '1.0.0';
}

/**
 * Retrives all products.
 * @return array The products.
 */
function get_all_products() {
  $all_products = $woocommerce->get_all_products();

  return $all_products;
}

/**
 * Retrives all product categories.
 * @return array The product categories.
 */
function get_all_product_categories() {
  $orderby = 'name';
  $order = 'asc';
  $hide_empty = false;
  $cat_args = array(
    'orderby'     => $orderby,
    'order'       => $order,
    'hide_empty'  => $hide_empty,
  );

  $all_product_categories = get_terms('product_cat', $cat_args);

  return $all_product_categories;
}

/**
 * Retrives the value of a textarea.
 * @param string $textarea The textarea value.
 */
function the_textarea_value($textarea) {
  // Ensure the input is a string before processing.
  if (!is_string($textarea)) {
    return;
  }
  $lines = explode("\n", $textarea);
  foreach ($lines as $line) {
    echo $line . '</br>';
  }
}

/**
 * Retrives the enabled pages.
 * @return array The enabled pages.
 */
function psf_enabled_pages() {
  $enabled_pages = get_option('enabled_pages', '');
  if (!is_string($enabled_pages) || empty($enabled_pages)) {
    return array();
  }
  return array_filter(explode(',', $enabled_pages));
}

/**
 * Checks if the plugin is enabled on the current page.
 * @return bool True if the plugin is enabled on the current page, false otherwise.
 */
function psf_enabled_on_current_page() {
  $enabled_on_current_page = (!empty(psf_enabled_pages()) && in_array(get_the_ID(), psf_enabled_pages()));
  return $enabled_on_current_page;
}

/**
 * Safe wrapper functions to prevent null/deprecated errors
 */

/**
 * Safely checks if a value is a non-empty string without using is_string()
 * @param mixed $value The value to check
 * @return bool True if value is a non-empty string, false otherwise
 */
function psf_is_safe_string($value) {
  return (gettype($value) === 'string' && $value !== '' && $value !== null);
}

/**
 * Safely gets an option with a guaranteed string return
 * @param string $option_name The option name
 * @param string $default The default value
 * @return string Always returns a string
 */
function psf_get_option_safe($option_name, $default = '') {
  $value = get_option($option_name, $default);
  if (gettype($value) === 'string') {
    return $value;
  }
  return $default;
}

/**
 * Safely gets post meta with a guaranteed return type
 * @param int $post_id The post ID
 * @param string $meta_key The meta key
 * @param mixed $default The default value
 * @return mixed The meta value or default
 */
function psf_get_post_meta_safe($post_id, $meta_key, $default = '') {
  $value = get_post_meta($post_id, $meta_key, true);
  if ($value === null || $value === false || $value === '') {
    return $default;
  }
  return $value;
}

/**
 * Safely gets term meta with a guaranteed return type
 * @param int $term_id The term ID
 * @param string $meta_key The meta key
 * @param mixed $default The default value
 * @return mixed The meta value or default
 */
function psf_get_term_meta_safe($term_id, $meta_key, $default = '') {
  $value = get_term_meta($term_id, $meta_key, true);
  if ($value === null || $value === false || $value === '') {
    return $default;
  }
  return $value;
}

/**
 * Safely checks if a value is not empty without using empty()
 * @param mixed $value The value to check
 * @return bool True if not empty, false otherwise
 */
function psf_has_value($value) {
  return ($value !== null && $value !== false && $value !== '' && $value !== 0 && $value !== '0');
}

/**
 * Custom error handler to catch deprecated warnings and log stack trace
 */
function psf_debug_error_handler($errno, $errstr, $errfile, $errline) {
  // Only handle deprecated warnings related to str_replace
  if ($errno === E_DEPRECATED && strpos($errstr, 'str_replace') !== false) {
    $stack_trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

    $log_message = "\n=== PSF DEBUG: str_replace deprecated warning ===\n";
    $log_message .= "Error: " . $errstr . "\n";
    $log_message .= "File: " . $errfile . " Line: " . $errline . "\n";
    $log_message .= "Stack trace:\n";

    foreach ($stack_trace as $i => $trace) {
      if (isset($trace['file']) && isset($trace['line'])) {
        $log_message .= "  #{$i} {$trace['file']}:{$trace['line']}";
        if (isset($trace['function'])) {
          $log_message .= " {$trace['function']}()";
        }
        $log_message .= "\n";
      }
    }
    $log_message .= "=== END PSF DEBUG ===\n";

    // Log to debug.log
    error_log($log_message);
  }

  // Return false to continue with normal error handling
  return false;
}

// Set our custom error handler only when PSF plugin is active
if (get_option('activate_psf', 'yes') === 'yes') {
  set_error_handler('psf_debug_error_handler', E_DEPRECATED);
}

/**
 * SEO optimization functions for Rank Math integration
 */

/**
 * Generates SEO-friendly excerpt from FAQ content
 * @param int $post_id The post ID
 * @return string SEO-friendly excerpt
 */
function psf_generate_seo_excerpt($post_id) {
  $faqs = psf_get_post_meta_safe($post_id, 'psf_faqs', array());

  if (empty($faqs) || !is_array($faqs)) {
    return '';
  }

  $excerpt_parts = array();
  $max_length = 150; // Good length for meta descriptions
  $current_length = 0;

  foreach ($faqs as $faq) {
    if (!isset($faq['faqQuestion']) || !isset($faq['faqAnswer'])) {
      continue;
    }

    $question = trim(strip_tags($faq['faqQuestion']));
    $answer = trim(strip_tags($faq['faqAnswer']));

    if (psf_has_value($question)) {
      $part = $question;
      if (psf_has_value($answer)) {
        $part .= ': ' . $answer;
      }

      if ($current_length + strlen($part) > $max_length) {
        break;
      }

      $excerpt_parts[] = $part;
      $current_length += strlen($part) + 2; // +2 for separator
    }
  }

  return implode('. ', $excerpt_parts);
}

/**
 * Generates structured data for FAQs
 * @param array $faqs FAQ array
 * @return array Structured data
 */
function psf_generate_structured_data($faqs) {
  if (empty($faqs) || !is_array($faqs)) {
    return array();
  }

  $structured_data = array(
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array()
  );

  foreach ($faqs as $faq) {
    if (!isset($faq['faqQuestion']) || !isset($faq['faqAnswer'])) {
      continue;
    }

    $question = trim(strip_tags($faq['faqQuestion']));
    $answer = trim(strip_tags($faq['faqAnswer']));

    if (psf_has_value($question) && psf_has_value($answer)) {
      $structured_data['mainEntity'][] = array(
        '@type' => 'Question',
        'name' => $question,
        'acceptedAnswer' => array(
          '@type' => 'Answer',
          'text' => $answer
        )
      );
    }
  }

  return $structured_data;
}

/**
 * Hook into Rank Math to provide FAQ data
 */
add_filter('rank_math/frontend/description', 'psf_enhance_meta_description', 10, 2);
function psf_enhance_meta_description($description, $object_id = null) {
  // Always ensure we return a string, never null
  if ($description === null) {
    $description = '';
  }

  // Only enhance if no description exists and we have FAQ data
  if (!empty($description)) {
    return $description;
  }

  // Get object ID from parameter or current context
  if (!$object_id) {
    $object_id = get_the_ID();
  }

  if (!$object_id) {
    return (string) $description; // Ensure string return
  }

  $faq_excerpt = psf_generate_seo_excerpt($object_id);
  if (psf_has_value($faq_excerpt)) {
    return $faq_excerpt;
  }

  return (string) $description; // Ensure string return
}

/**
 * Add structured data for FAQs
 */
add_action('wp_head', 'psf_add_structured_data', 20);
function psf_add_structured_data() {
  if (!is_singular()) {
    return;
  }

  $post_id = get_the_ID();
  $faqs = psf_get_post_meta_safe($post_id, 'psf_faqs', array());

  if (empty($faqs)) {
    return;
  }

  $structured_data = psf_generate_structured_data($faqs);
  if (!empty($structured_data['mainEntity'])) {
    echo '<script type="application/ld+json">' . wp_json_encode($structured_data, JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
  }
}

/**
 * Ensure posts with FAQs have proper excerpts
 */
add_filter('get_the_excerpt', 'psf_ensure_excerpt', 5, 2);
function psf_ensure_excerpt($excerpt, $post) {
  // Always ensure we return a string, never null
  if ($excerpt === null) {
    $excerpt = '';
  }

  // If excerpt already exists, don't modify it
  if (!empty($excerpt)) {
    return $excerpt;
  }

  // Only for posts that might have FAQs
  if (!$post || !in_array($post->post_type, array('post', 'page', 'product'))) {
    return (string) $excerpt; // Ensure string return
  }

  $faq_excerpt = psf_generate_seo_excerpt($post->ID);
  if (psf_has_value($faq_excerpt)) {
    return $faq_excerpt;
  }

  return (string) $excerpt; // Ensure string return
}

/**
 * Prevent null values from being passed to content filters
 * This helps prevent deprecated warnings in WordPress functions
 */
add_filter('the_content', 'psf_ensure_content_string', 1);
function psf_ensure_content_string($content) {
  return ($content === null) ? '' : $content;
}

/**
 * Debug function to list all product categories and their FAQ data
 * Only works when WP_DEBUG and plugin debug mode are both enabled
 */
function psf_debug_list_all_category_faqs() {
  // Only run if both WP_DEBUG and plugin debug mode are enabled
  if (!defined('WP_DEBUG') || !WP_DEBUG || get_option('debug_mode', '0') !== '1') {
    return;
  }

  $categories = get_terms(array(
    'taxonomy' => 'product_cat',
    'hide_empty' => false,
  ));

  error_log('[PSF DEBUG] === ALL PRODUCT CATEGORIES ===');

  if (is_wp_error($categories) || empty($categories)) {
    error_log('[PSF DEBUG] No categories found or error occurred');
    return;
  }

  foreach ($categories as $category) {
    $faq_data = get_term_meta($category->term_id, 'psf_faqs', true);
    $has_faqs = !empty($faq_data) && is_array($faq_data);

    error_log(sprintf(
      '[PSF DEBUG] Category: %s (ID: %d, Slug: %s) - Has FAQs: %s',
      $category->name,
      $category->term_id,
      $category->slug,
      $has_faqs ? 'YES (' . count($faq_data) . ')' : 'NO'
    ));

    if ($has_faqs) {
      foreach ($faq_data as $index => $faq) {
        error_log(sprintf(
          '[PSF DEBUG]   FAQ %d: %s',
          $index + 1,
          isset($faq['faqQuestion']) ? substr($faq['faqQuestion'], 0, 50) . '...' : 'No question'
        ));
      }
    }
  }

  error_log('[PSF DEBUG] === END CATEGORY LIST ===');
}

// Call this function on admin pages to help with debugging
if (is_admin()) {
  add_action('admin_init', 'psf_debug_list_all_category_faqs');
}