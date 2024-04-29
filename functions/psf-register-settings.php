<?php
add_action('admin_init', 'page_specific_faq_settings');
function page_specific_faq_settings() {
  register_setting(
    'page-specific-faq-settings-group',
    'activate_psf',
    array(
      'sanitize_callback' => 'wp_filter_nohtml_kses',
      'default'           => 'yes'
    )
  );
  register_setting(
    'page-specific-faq-settings-group',
    'display_on_pages',
    array(
      'sanitize_callback' => 'wp_filter_nohtml_kses'
    )
  );
  register_setting(
    'page-specific-faq-settings-group',
    'woo_visual_hook',
    array(
      'sanitize_callback' => 'wp_filter_nohtml_kses'
    )
  );
}
