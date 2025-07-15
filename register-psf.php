<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Page Specific FAQ
 * Description:       Enables FAQs on product categories and specified pages.
 * Version:           2.0.5
 * Author:            viggebe
 * Author URI:        viktorborg.myportfolio.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text-Domain:       page-specific-faq
 * Domain Path:       /languages
 */

?>
<?php
define('PSF_ROOT_PATH', plugin_dir_url(__FILE__));
define('PSF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PSF_CSS_PATH', untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/css/');
define('PSF_JS_PATH', untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/js/');
define('PSF_IMAGES_PATH', untrailingslashit(plugin_dir_url(__FILE__)) . '/assets/images/');
define('PSF_FUNCTIONS_PATH', untrailingslashit(plugin_dir_path(__FILE__)) . '/functions/');

define('PSF_SETTINGS_GROUP', 'psf-settings-group');

// Load all function files before anything else
foreach (glob(PSF_FUNCTIONS_PATH . '*.php') as $file) {
  require_once $file;
}

register_activation_hook(__FILE__, 'psf_activate');
function psf_activate() {
  add_action('admin_menu', 'psf_menu');
}

/**
 * Enqueue scripts & styles
 */
add_action('admin_enqueue_scripts', 'register_psf_admin_scripts_styles', 20);
function register_psf_admin_scripts_styles() {
  $screen = get_current_screen();

  if ($screen->taxonomy == 'product_cat' || $screen->id == 'toplevel_page_psf-settings' || $screen->id === 'page') {
    wp_enqueue_script(
      'psf-admin-scripts',
      PSF_JS_PATH . 'admin-psf-scripts.js',
      array('jquery'),
      psf_get_plugin_version()
    );
    wp_enqueue_style(
      'psf-admin-styles',
      PSF_CSS_PATH . 'admin-styles.css',
      array(),
      psf_get_plugin_version()
    );
  }
}

add_action('wp_enqueue_scripts', 'register_psf_scripts_styles');
function register_psf_scripts_styles() {
  wp_enqueue_style(
    'psf-styles',
    PSF_CSS_PATH . 'styles.css',
    array(),
    psf_get_plugin_version()
  );

  wp_register_script(
    'psf-scripts',
    PSF_JS_PATH . 'scripts.js',
    array('jquery'),
    psf_get_plugin_version()
  );

  wp_enqueue_script('psf-scripts');
}

add_action('admin_menu', 'psf_menu');
function psf_menu() {
  $page_title   = 'Settings';
  $menu_title   = 'Page Specific FAQ';
  $capability   = 'manage_options';
  $menu_slug    = 'psf-settings';
  $page_markup  = 'page_specific_faq_settings_page';

  // Use local file path instead of URL for SVG icon
  $svg_path = PSF_PLUGIN_PATH . 'assets/images/faqs.svg';
  $icon_url = '';

  if (file_exists($svg_path)) {
    $svg_content = file_get_contents($svg_path);
    if ($svg_content !== false) {
      $icon_url = 'data:image/svg+xml;base64,' . base64_encode($svg_content);
    }
  }

  $position = '3';

  add_menu_page(
    $page_title,
    $menu_title,
    $capability,
    $menu_slug,
    $page_markup,
    $icon_url,
    $position
  );
}