<?php

/**
 * Register plugin settings
 *
 * @return void
 */
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
            'sanitize_callback' => 'wp_filter_nohtml_kses',
            'default'           => ''
        )
    );
    register_setting(
        PSF_SETTINGS_GROUP,
        'woo_visual_hook',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses',
            'default'           => 'woocommerce_after_main_content'
        )
    );
    register_setting(
        PSF_SETTINGS_GROUP,
        'page_visual_hook',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses',
            'default'           => 'the_content'
        )
    );
    register_setting(
        PSF_SETTINGS_GROUP,
        'debug_mode',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses',
            'default'           => '0'
        )
    );
    register_setting(
        PSF_SETTINGS_GROUP,
        'hook_detection_enabled',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses',
            'default'           => '0'
        )
    );
    register_setting(
        PSF_SETTINGS_GROUP,
        'hook_rendering_enabled',
        array(
            'sanitize_callback' => 'wp_filter_nohtml_kses',
            'default'           => 'yes'
        )
    );
}

/**
 * Settings page callback function
 *
 * @return void
 */
function page_specific_faq_settings_page() {
    psf_generate_settings_page_markup();
}
