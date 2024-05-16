<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Page Specific FAQ
 * Description:       Enables FAQs on product categories and specified pages.
 * Version:           1.1.81
 * Author:            Viktor Borg
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

register_activation_hook(__FILE__, 'psf_activate');
function psf_activate() {
  add_action('admin_menu', 'psf_menu');
}

/**
 * Enabled pages
 */
function psf_enabled_pages() {
  return array_filter(explode(',', get_option('enabled_pages')));
}
function psf_enabled_on_current_page() {
  $enabled_on_current_page = (!empty(psf_enabled_pages()) && in_array(get_the_ID(), psf_enabled_pages()));
  return $enabled_on_current_page;
}

/**
 * Enqueue scripts & styles
 */
add_action('admin_enqueue_scripts', 'register_psf_admin_scripts_styles', 20);
function register_psf_admin_scripts_styles() {
  $screen = get_current_screen();
  $enabled_on_admin_page = false;

  print_r($screen->id);

  if ($screen->id === 'page') {
    global $post;
    $page_id = $post->ID;

    $enabled_pages = psf_enabled_pages();
    $enabled_on_admin_page = (!empty($enabled_pages) && in_array($page_id, $enabled_pages));
  }

  if ($screen->id == 'toplevel_page_psf-settings') {
    wp_enqueue_style(
      'psf-admin-styles',
      PSF_CSS_PATH . '/admin-styles.css',
      array(),
      psf_get_version()
    );
  }

  if ($screen->taxonomy == 'product_cat' || $screen->id == 'toplevel_page_psf-settings' || $enabled_on_admin_page) {
    wp_enqueue_script(
      'psf-admin-scripts',
      PSF_JS_PATH . '/admin-psf-scripts.js',
      array('jquery'),
      psf_get_version()
    );
    wp_enqueue_style(
      'psf-admin-styles',
      PSF_CSS_PATH . '/admin-styles.css',
      array(),
      psf_get_version()
    );
  }
}

add_action('wp_enqueue_scripts', 'register_psf_scripts_styles');
function register_psf_scripts_styles() {
  wp_enqueue_style(
    'psf-styles',
    PSF_CSS_PATH . '/styles.css',
    array(),
    psf_get_version()
  );

  $enabled_on_current_page = psf_enabled_on_current_page();

  $disabled_on_current_page = false;
  $script_params = array(
    'enabled_on_current_page'       => $enabled_on_current_page,
    'disabled_on_current_page'      => $disabled_on_current_page,
    'id'                            => get_the_ID(),
  );

  wp_register_script(
    'psf-scripts',
    PSF_JS_PATH . '/scripts.js',
    array('jquery'),
    psf_get_version()
  );

  wp_localize_script('psf-scripts', 'psfScriptParams', $script_params);
  wp_enqueue_script('psf-scripts');
}

add_action('admin_menu', 'psf_menu');
function psf_menu() {
  $page_title   = 'Settings';
  $menu_title   = 'Page Specific FAQ';
  $capability   = 'manage_options';
  $menu_slug    = 'psf-settings';
  $page_markup  = 'page_specific_faq_settings_page';
  $icon_url     = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(PSF_IMAGES_PATH . '/faqs.svg'));
  $position     = '3';

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

foreach (glob(PSF_FUNCTIONS_PATH . '*.php') as $file) {
  require_once $file;
}
