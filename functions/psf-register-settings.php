<?php
add_action('admin_init', 'page_specific_faq_settings');
function page_specific_faq_settings() {
  register_setting(
    PSF_SETTINGS_GROUP,
    'activate_psf',
    array(
      'sanitize_callback' => 'wp_filter_nohtml_kses',
      'default'           => 'yes'
    )
  );
  register_setting(
    PSF_SETTINGS_GROUP,
    'enabled_pages',
    array(
      'sanitize_callback' => 'wp_filter_nohtml_kses'
    )
  );
  register_setting(
    PSF_SETTINGS_GROUP,
    'woo_visual_hook',
    array(
      'sanitize_callback' => 'wp_filter_nohtml_kses'
    )
  );
  register_setting(
    PSF_SETTINGS_GROUP,
    'page_visual_hook',
    array(
      'sanitize_callback' => 'wp_filter_nohtml_kses'
    )
  );
}
