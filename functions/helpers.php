<?php

/**
 * Get plugin version dynamically.
 */
function psf_get_plugin_version() {
  if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
  }
  $plugin_data = get_plugin_data(PSF_PLUGIN_PATH . 'register-psf.php');
  return $plugin_data['Version'] ?? '1.0.0';
}

/**
 * Render an FAQ answer textarea value with newlines preserved.
 */
function the_textarea_value($textarea) {
  if (!is_string($textarea)) return;
  echo nl2br(esc_html($textarea));
}

/**
 * Pages explicitly enabled in plugin settings.
 *
 * @return int[]
 */
function psf_enabled_pages() {
  $raw = get_option('enabled_pages', '');
  if (!is_string($raw) || $raw === '') return [];
  return array_values(array_filter(array_map('absint', explode(',', $raw))));
}

function psf_enabled_on_current_page() {
  $enabled = psf_enabled_pages();
  return !empty($enabled) && in_array(get_the_ID(), $enabled, true);
}

/**
 * get_option() coerced to a string.
 */
function psf_get_option_safe($option_name, $default = '') {
  $value = get_option($option_name, $default);
  return is_string($value) ? $value : $default;
}

function psf_get_post_meta_safe($post_id, $meta_key, $default = '') {
  $value = get_post_meta($post_id, $meta_key, true);
  return ($value === null || $value === false || $value === '') ? $default : $value;
}

function psf_get_term_meta_safe($term_id, $meta_key, $default = '') {
  $value = get_term_meta($term_id, $meta_key, true);
  return ($value === null || $value === false || $value === '') ? $default : $value;
}

/**
 * Truthiness wrapper that treats both 0 and "0" as truthy.
 */
function psf_has_value($value) {
  return ($value !== null && $value !== false && $value !== '');
}

/**
 * Conditional debug logger. Single gate for all [PSF DEBUG] lines.
 */
function psf_debug_log($message) {
  if (!defined('WP_DEBUG') || !WP_DEBUG) return;
  if (get_option('debug_mode', '0') !== '1') return;
  error_log('[PSF DEBUG] ' . $message);
}

/**
 * Stack-trace logger for str_replace deprecation warnings. Only registered
 * when both WP_DEBUG and plugin debug_mode are on, so production never pays
 * for set_error_handler.
 */
function psf_debug_error_handler($errno, $errstr, $errfile, $errline) {
  if ($errno === E_DEPRECATED && strpos($errstr, 'str_replace') !== false) {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $msg  = "\n=== PSF DEBUG: str_replace deprecated ===\n";
    $msg .= "Error: {$errstr}\nFile: {$errfile}:{$errline}\nStack:\n";
    foreach ($trace as $i => $t) {
      if (isset($t['file'], $t['line'])) {
        $msg .= "  #{$i} {$t['file']}:{$t['line']}";
        if (isset($t['function'])) $msg .= " {$t['function']}()";
        $msg .= "\n";
      }
    }
    error_log($msg . "=== END ===\n");
  }
  return false;
}

if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
  set_error_handler('psf_debug_error_handler', E_DEPRECATED);
}

/**
 * Build a meta-description-friendly excerpt from FAQ content.
 */
function psf_generate_seo_excerpt($post_id) {
  $faqs = psf_get_post_meta_safe($post_id, 'psf_faqs', []);
  if (empty($faqs) || !is_array($faqs)) return '';

  $parts = [];
  $max = 150;
  $len = 0;

  foreach ($faqs as $faq) {
    if (!isset($faq['faqQuestion'], $faq['faqAnswer'])) continue;
    $q = trim(strip_tags($faq['faqQuestion']));
    $a = trim(strip_tags($faq['faqAnswer']));
    if ($q === '') continue;

    $part = $a !== '' ? "{$q}: {$a}" : $q;
    if ($len + strlen($part) > $max) break;
    $parts[] = $part;
    $len += strlen($part) + 2;
  }
  return implode('. ', $parts);
}

/**
 * Schema.org FAQPage JSON-LD payload.
 */
function psf_generate_structured_data($faqs) {
  if (empty($faqs) || !is_array($faqs)) return [];

  $data = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => [],
  ];

  foreach ($faqs as $faq) {
    if (!isset($faq['faqQuestion'], $faq['faqAnswer'])) continue;
    $q = trim(strip_tags($faq['faqQuestion']));
    $a = trim(strip_tags($faq['faqAnswer']));
    if ($q === '' || $a === '') continue;

    $data['mainEntity'][] = [
      '@type'          => 'Question',
      'name'           => $q,
      'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
    ];
  }
  return $data;
}

add_filter('rank_math/frontend/description', 'psf_enhance_meta_description', 10, 2);
function psf_enhance_meta_description($description, $object_id = null) {
  $description = (string) ($description ?? '');
  if ($description !== '') return $description;

  if (!$object_id) $object_id = get_the_ID();
  if (!$object_id) return $description;

  $excerpt = psf_generate_seo_excerpt($object_id);
  return $excerpt !== '' ? $excerpt : $description;
}

add_action('wp_head', 'psf_add_structured_data', 20);
function psf_add_structured_data() {
  if (!is_singular()) return;

  $faqs = psf_get_post_meta_safe(get_the_ID(), 'psf_faqs', []);
  if (empty($faqs)) return;

  $data = psf_generate_structured_data($faqs);
  if (!empty($data['mainEntity'])) {
    echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_UNICODE) . "</script>\n";
  }
}

add_filter('get_the_excerpt', 'psf_ensure_excerpt', 5, 2);
function psf_ensure_excerpt($excerpt, $post) {
  $excerpt = (string) ($excerpt ?? '');
  if ($excerpt !== '') return $excerpt;
  if (!$post || !in_array($post->post_type, ['post', 'page', 'product'], true)) return $excerpt;

  $faq_excerpt = psf_generate_seo_excerpt($post->ID);
  return $faq_excerpt !== '' ? $faq_excerpt : $excerpt;
}

add_filter('the_content', 'psf_ensure_content_string', 1);
function psf_ensure_content_string($content) {
  return $content === null ? '' : $content;
}
