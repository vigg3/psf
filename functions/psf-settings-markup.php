<?php

/**
 * Generates the settings page markup.
 */
function psf_generate_settings_page_markup() {
?>
<header class="psf-settings-header">
    <div class="psf-settings__grid-wrapper">
        <h1 style="color: #fff;"><?php _e('Page Specific FAQ', 'page-specific-faq'); ?></h1>
    </div>
</header>

<div class="psf-settings__wrapper">
    <form class="form-table" method="post" action="options.php">
        <?php settings_fields(PSF_SETTINGS_GROUP); ?>
        <?php do_settings_sections(PSF_SETTINGS_GROUP);
            ?>
        <table>
            <tbody>
                <tr class="form-field">
                    <th scope="row">
                        <h2><?php _e('General Settings', 'page-specific-faq'); ?></h2>
                    </th>
                </tr>
                <!-- Enable / Disable plugin -->
                <tr style="display:none;" valign="top">
                    <th scope="row">
                        <h3><?php _e('Activated', 'page-specific-faq'); ?></h3>
                        <?php _e('Should the plugin be activated?', 'page-specific-faq'); ?>
                    </th>
                    <td style="display:flex;gap:16px">
                        <div class="radio-wrapper">
                            <input type="radio" id="yes" name="activate_psf" value="yes"
                                   <?= ((get_option('activate_psf') == 'yes') ? 'checked' : ''); ?>
                                   style="margin-top:0;margin-right:6px;">
                            <label for="yes"><?php _e('Yes', 'page-specific-faq'); ?></label>
                        </div>
                        <div class="radio-wrapper">
                            <input type="radio" id="no" name="activate_psf" value="no"
                                   <?= ((get_option('activate_psf') == 'no') ? 'checked' : ''); ?>
                                   style="margin-top:0;margin-right:6px;">
                            <label for="no"><?php _e('No', 'page-specific-faq'); ?></label>
                        </div>
                    </td>
                </tr>
                <!-- Enable/Disable Hook Rendering -->
                <tr valign="top">
                    <th scope="row">
                        <h3><?php _e('Hook Rendering', 'page-specific-faq'); ?></h3>
                        <?php _e('Enable automatic FAQ placement using WordPress hooks', 'page-specific-faq'); ?>
                    </th>
                    <td style="display:flex;gap:16px">
                        <div class="radio-wrapper">
                            <input type="radio" id="hook_rendering_yes" name="hook_rendering_enabled"
                                   value="yes"
                                   <?= ((get_option('hook_rendering_enabled', 'yes') == 'yes') ? 'checked' : ''); ?>
                                   style="margin-top:0;margin-right:6px;">
                            <label
                                   for="hook_rendering_yes"><?php _e('Enabled', 'page-specific-faq'); ?></label>
                        </div>
                        <div class="radio-wrapper">
                            <input type="radio" id="hook_rendering_no" name="hook_rendering_enabled"
                                   value="no"
                                   <?= ((get_option('hook_rendering_enabled', 'yes') == 'no') ? 'checked' : ''); ?>
                                   style="margin-top:0;margin-right:6px;">
                            <label
                                   for="hook_rendering_no"><?php _e('Disabled', 'page-specific-faq'); ?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 0; padding-left: 0;">
                        <p class="description">
                            <?php _e('When enabled, FAQs will automatically appear at the position selected below. When disabled, use the [psf_faq] shortcode to place FAQs manually wherever you want.', 'page-specific-faq'); ?>
                        </p>
                    </td>
                </tr>

                <!-- FAQ placement | archives -->
                <tr valign="top" id="hook_position_setting">
                    <th scope="row">
                        <h3><?php _e('Position', 'page-specific-faq'); ?></h3>
                        <?php _e('Hook for positioning FAQ on category pages.', 'page-specific-faq'); ?><br>
                        <code>woocommerce_after_main_content</code>
                        <?php _e('is at the bottom of the page.', 'page-specific-faq'); ?><br>
                        <a target="_blank"
                           href="https://www.businessbloomer.com/woocommerce-visual-hook-guide-archiveshopcat-page/">Woocommerce
                            Visual Hook Guide</a>
                    </th>
                    <td>
                        <div id="psf_visual_hook">
                            <input type="text" id="woo_visual_hook"
                                   placeholder="woocommerce_after_main_content" class="code"
                                   value="<?= esc_attr(get_option('woo_visual_hook', '')); ?>"
                                   name="woo_visual_hook" />
                        </div>

                        <!-- Hook Discovery Section -->
                        <div id="psf_hook_discovery"
                             style="margin-top: 15px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
                            <h4 style="margin-top: 0;"><?php _e('Hook Discovery', 'page-specific-faq'); ?>
                            </h4>
                            <p><?php _e('Common WooCommerce category page hooks:', 'page-specific-faq'); ?>
                            </p>

                            <div
                                 style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                                <button type="button" class="button-secondary psf-hook-suggestion"
                                        data-hook="woocommerce_before_main_content">
                                    woocommerce_before_main_content
                                </button>
                                <button type="button" class="button-secondary psf-hook-suggestion"
                                        data-hook="woocommerce_after_main_content">
                                    woocommerce_after_main_content
                                </button>
                                <button type="button" class="button-secondary psf-hook-suggestion"
                                        data-hook="woocommerce_archive_description">
                                    woocommerce_archive_description
                                </button>
                                <button type="button" class="button-secondary psf-hook-suggestion"
                                        data-hook="woocommerce_before_shop_loop">
                                    woocommerce_before_shop_loop
                                </button>
                                <button type="button" class="button-secondary psf-hook-suggestion"
                                        data-hook="woocommerce_after_shop_loop">
                                    woocommerce_after_shop_loop
                                </button>
                                <button type="button" class="button-secondary psf-hook-suggestion"
                                        data-hook="wp_footer">
                                    wp_footer
                                </button>
                            </div>

                            <div id="psf_detected_hooks" style="margin-top: 15px;">
                                <h5><?php _e('Detected hooks on your category pages:', 'page-specific-faq'); ?>
                                </h5>
                                <div id="psf_hooks_list"
                                     style="background: white; padding: 10px; border: 1px solid #ddd; border-radius: 3px; min-height: 40px;">
                                    <em
                                        style="color: #666;"><?php _e('Visit a product category page to detect available hooks...', 'page-specific-faq'); ?></em>
                                </div>
                                <button type="button" id="psf_refresh_hooks" class="button"
                                        style="margin-top: 10px;">
                                    <?php _e('Refresh Hook Detection', 'page-specific-faq'); ?>
                                </button>
                            </div>

                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                                <h5><?php _e('Quick Test:', 'page-specific-faq'); ?></h5>
                                <p style="margin: 5px 0;">
                                    <?php _e('Enter a hook name and click test to see where FAQs would appear on your category pages.', 'page-specific-faq'); ?>
                                </p>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <input type="text" id="psf_test_hook"
                                           placeholder="<?php _e('Hook name to test...', 'page-specific-faq'); ?>"
                                           style="flex: 1;" />
                                    <button type="button" id="psf_test_hook_btn"
                                            class="button"><?php _e('Test Hook', 'page-specific-faq'); ?></button>
                                </div>
                                <div id="psf_test_result"
                                     style="margin-top: 10px; font-size: 12px; color: #666;"></div>
                            </div>


                        </div>
                    </td>
                </tr>
                <!-- FAQ placement | pages -->
                <tr valign="top">
                    <th scope="row">
                        <h3><?php _e('Position', 'page-specific-faq'); ?></h3>
                        <?php _e('Hook for positioning FAQ on other pages.', 'page-specific-faq'); ?><br>
                        <code>the_content</code>
                        <?php _e('adds the FAQ directly after the content.', 'page-specific-faq'); ?>
                    </th>
                    <td>
                        <div id="psf_page_visual_hook">
                            <input type="text" id="page_visual_hook" placeholder="the_content" class="code"
                                   value="<?= esc_attr(get_option('page_visual_hook', '')); ?>"
                                   name="page_visual_hook" />
                        </div>
                    </td>
                </tr>
                <!-- Enabled pages -->
                <tr style="display:none;" valign="top">
                    <th scope="row">
                        <h3><?php _e('Additional Pages', 'page-specific-faq'); ?></h3>
                        <?php _e('Select more pages, in addition to product categories, where FAQ should be enabled.', 'page-specific-faq'); ?>
                    </th>
                    <td>
                        <div class="enabled_pages_wrapper">
                            <?php
                                $enabled_pages_option = get_option('enabled_pages', '');
                                $enabled_pages_array = !empty($enabled_pages_option) ? array_filter(explode(',', $enabled_pages_option)) : array();
                                $frontpage_id = get_option('page_on_front');
                                if ($frontpage_id == 0) {
                                    $frontpage_id = 1;
                                }
                                $isChecked = in_array($frontpage_id, $enabled_pages_array) ? 'checked' : '';
                                ?>
                            <div class="checkbox_wrapper">
                                <input type="checkbox" name="<?php echo $frontpage_id; ?>"
                                       id="<?php echo $frontpage_id; ?>"
                                       value="<?php echo $frontpage_id; ?>" <?php echo $isChecked ?> />
                                <label for="<?php echo $frontpage_id; ?>">
                                    <span><?php echo esc_html(get_option('blogname')); ?></span>
                                    <span><?php echo esc_url(get_site_url()); ?></span>
                                </label>
                            </div>
                            <?php
                                $pages = get_pages(array(
                                    'exclude' => array($frontpage_id)
                                ));
                                foreach ($pages as $page) :
                                    $isChecked = in_array($page->ID, $enabled_pages_array) ? 'checked' : ''; ?>
                            <div class="checkbox_wrapper">
                                <input type="checkbox" name="<?php echo $page->ID; ?>"
                                       id="<?php echo $page->ID; ?>"
                                       value="<?php echo $page->ID; ?>" <?php echo $isChecked; ?> />
                                <label for="<?php echo $page->ID; ?>">
                                    <span><?php echo esc_html($page->post_title); ?></span>
                                    <span><?php echo esc_url(get_page_link($page->ID)); ?></span>
                                </label>
                            </div>
                            <?php
                                endforeach; ?>
                        </div>
                        <input type="text" hidden id="enabled_pages" name="enabled_pages"
                               value="<?php echo esc_attr(get_option('enabled_pages', '')); ?>" />
                    </td>
                </tr>

                <!-- Debug Settings -->
                <tr valign="top">
                    <th scope="row">
                        <h3><?php _e('Debug Settings', 'page-specific-faq'); ?></h3>
                        <?php _e('Tools for troubleshooting FAQ display issues.', 'page-specific-faq'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox"
                                   name="debug_mode"
                                   value="1"
                                   <?php checked(get_option('debug_mode', '0'), '1'); ?> />
                            <?php _e('Enable Debug Mode', 'page-specific-faq'); ?>
                        </label>
                        <p class="description">
                            <?php _e('When enabled, detailed debug information will be logged when FAQs are processed. Check your debug.log file for output.', 'page-specific-faq'); ?><br>
                            <strong><?php _e('Note:', 'page-specific-faq'); ?></strong>
                            <?php _e('WP_DEBUG must also be enabled in wp-config.php for logging to work.', 'page-specific-faq'); ?>
                        </p>

                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                            <label>
                                <input type="checkbox"
                                       name="hook_detection_enabled"
                                       value="1"
                                       <?php checked(get_option('hook_detection_enabled', '0'), '1'); ?> />
                                <?php _e('Enable Hook Detection JavaScript', 'page-specific-faq'); ?>
                            </label>
                            <p class="description">
                                <?php _e('When enabled, JavaScript will run on category pages to detect available hooks for the "Detected hooks" list above. Disable this to improve page performance when you don\'t need hook detection.', 'page-specific-faq'); ?>
                            </p>
                        </div>

                        <?php if (get_option('debug_mode', '0') === '1'): ?>
                        <div
                             style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin-top: 10px; border-radius: 4px;">
                            <strong
                                    style="color: #856404;"><?php _e('Debug Mode Active', 'page-specific-faq'); ?></strong><br>
                            <?php _e('Debug information is being logged. Visit your category pages to generate debug output.', 'page-specific-faq'); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Shortcode Documentation -->
                <tr valign="top">
                    <th scope="row">
                        <h3><?php _e('Shortcode Usage', 'page-specific-faq'); ?></h3>
                        <?php _e('Use shortcodes to display FAQs anywhere on your site', 'page-specific-faq'); ?>
                    </th>
                    <td>
                        <div class="psf-shortcode-documentation-wrapper">
                            <h4 style="margin-top: 0;"><?php _e('Basic Usage', 'page-specific-faq'); ?></h4>
                            <code>[psf_faq]</code>
                            <p class="description">
                                <?php _e('Automatically displays FAQs for the current product category (if any exist).', 'page-specific-faq'); ?>
                            </p>

                            <h4><?php _e('Advanced Usage', 'page-specific-faq'); ?></h4>
                            <p>
                                <code>[psf_faq category_id="106"]</code><br>
                                <span
                                      class="description"><?php _e('Display FAQs for a specific category ID', 'page-specific-faq'); ?></span>
                            </p>
                            <p>
                                <code>[psf_faq category_slug="men"]</code><br>
                                <span
                                      class="description"><?php _e('Display FAQs for a specific category slug', 'page-specific-faq'); ?></span>
                            </p>
                            <p>
                                <code>[psf_faq heading="Custom FAQ Title"]</code><br>
                                <span
                                      class="description"><?php _e('Override the default heading', 'page-specific-faq'); ?></span>
                            </p>
                            <p>
                                <code>[psf_faq no_heading="true"]</code><br>
                                <span
                                      class="description"><?php _e('Hide the heading completely (no H2 tag will be rendered)', 'page-specific-faq'); ?></span>
                            </p>

                            <h4><?php _e('Where to Use', 'page-specific-faq'); ?></h4>
                            <ul style="margin-left: 20px;">
                                <li><?php _e('In page/post content', 'page-specific-faq'); ?></li>
                                <li><?php _e('In Elementor Text widgets', 'page-specific-faq'); ?></li>
                                <li><?php _e('In WordPress widgets', 'page-specific-faq'); ?></li>
                                <li><?php _e('In template files using do_shortcode()', 'page-specific-faq'); ?>
                                </li>
                            </ul>

                            <div
                                 style="background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin-top: 10px; border-radius: 4px;">
                                <strong
                                        style="color: #155724;"><?php _e('Tip:', 'page-specific-faq'); ?></strong>
                                <?php _e('If no FAQs exist for the specified category, the shortcode returns nothing (no empty containers).', 'page-specific-faq'); ?>
                            </div>
                        </div>
                    </td>
                </tr>

        </table>
        <?php submit_button(__('Save Changes', 'page-specific-faq')); ?>
    </form>
</div>


<?php
}