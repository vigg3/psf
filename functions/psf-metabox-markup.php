<?php

/**
 * Generates the FAQ rows markup for admin interface.
 *
 * @param array $psf_faqs The FAQ rows.
 */
function psf_generate_faq_rows($psf_faqs) {
?>
    <table id="repeateable-fieldset" class="inner-table" width="100%">
        <tbody id="repeatable-tbody">
            <?php
            if ($psf_faqs) :
                foreach ($psf_faqs as $key => $field) { ?>
                    <tr class="faqWrapper">
                        <td width="85%">
                            <div class="faqQuestion">
                                <label for="faqQuestion[<?php echo $key; ?>]"><?php _e('Question', 'page-specific-faq'); ?></label>
                                <input type="text" name="faqQuestion[<?php echo $key; ?>]" placeholder="<?php esc_attr_e('Enter the question here', 'page-specific-faq'); ?>"
                                    id="faqQuestion[<?php echo $key; ?>]"
                                    value="<?php if (!empty($field['faqQuestion'])) echo esc_attr($field['faqQuestion']); ?>">
                            </div>
                            <div class="faqAnswer">
                                <label for="faqAnswer[<?php echo $key; ?>]"><?php _e('Answer', 'page-specific-faq'); ?></label>
                                <textarea placeholder="<?php esc_attr_e('Enter the answer here', 'page-specific-faq'); ?>" cols="55" rows="3"
                                    name="faqAnswer[<?php echo $key; ?>]"
                                    id="faqAnswer[<?php echo $key; ?>]"><?php if (!empty($field['faqAnswer'])) echo esc_attr($field['faqAnswer']); ?></textarea>
                            </div>
                        </td>
                        <td class="button-wrapper" width="15%">
                            <?php
                            if ($key > 0) { ?>
                                <a class="button remove-row" href="#"><?php _e('Remove', 'page-specific-faq'); ?></a>
                            <?php
                            } ?>
                        </td>
                    </tr>
                <?php
                }
            else :
                ?>
                <tr class="faqWrapper">
                    <td width="85%">
                        <div class="faqQuestion">
                            <label for="faqQuestion[0]"><?php _e('Question', 'page-specific-faq'); ?></label>
                            <input type="text" placeholder="<?php esc_attr_e('Enter the question here', 'page-specific-faq'); ?>" title="<?php esc_attr_e('Question', 'page-specific-faq'); ?>" name="faqQuestion[0]"
                                id="faqQuestion[0]">
                        </div>
                        <div class="faqAnswer">
                            <label for="faqAnswer[0]"><?php _e('Answer', 'page-specific-faq'); ?></label>
                            <textarea placeholder="<?php esc_attr_e('Enter the answer here', 'page-specific-faq'); ?>" name="faqAnswer[0]" cols="55" rows="3"
                                id="faqAnswer[0]"></textarea>
                        </div>
                    </td>
                    <td class="button-wrapper" width=15%>

                    </td>
                </tr>
            <?php
            endif;
            ?>
            <tr class="faqWrapper empty-row screen-reader-text">
                <td width="85%">
                    <div class="faqQuestion">
                        <label for="faqQuestion[]"><?php _e('Question', 'page-specific-faq'); ?></label>
                        <input type="text" placeholder="<?php esc_attr_e('Enter the question here', 'page-specific-faq'); ?>" title="<?php esc_attr_e('Question', 'page-specific-faq'); ?>" name="faqQuestion[]"
                            id="faqQuestion[]">
                    </div>
                    <div class="faqAnswer">
                        <label for="faqAnswer[]"><?php _e('Answer', 'page-specific-faq'); ?></label>
                        <textarea placeholder="<?php esc_attr_e('Enter the answer here', 'page-specific-faq'); ?>" name="faqAnswer[]" cols="55" rows="3"
                            id="faqAnswer[]"></textarea>
                    </div>
                </td>
                <td class="button-wrapper" width=15%>
                    <a class="button remove-row" href="#"><?php _e('Remove', 'page-specific-faq'); ?></a>
                </td>
            </tr>
        </tbody>
    </table>
    <p><a href="#" class="button" id="add-row"><?php _e('Add FAQ', 'page-specific-faq'); ?></a></p>
<?php
}

/**
 * Generates the page metabox markup.
 *
 * @param WP_Post $post The post object.
 */
function psf_generate_page_metabox_markup($post) {
    $page_id = $post->ID;
    $psf_faqs = get_post_meta($page_id, 'psf_faqs', true);
    $psf_custom_heading = get_post_meta($page_id, 'psf_custom_heading', true);

    wp_nonce_field('psf_save_page_meta_data', 'psf_page_meta_box_nonce');
?>
    <div class="form-wrapper">
        <table>
            <tr class="form-field">
                <th scope="row" valign="top" width="25%">
                    <label for="psf_custom_heading"><?php _e('Custom Heading', 'page-specific-faq') ?></label>
                </th>
                <td>
                    <input type="text" name="psf_custom_heading" id="psf_custom_heading"
                        placeholder="<?php esc_attr_e('Enter a custom heading', 'page-specific-faq'); ?>"
                        value="<?php if (!empty($psf_custom_heading)) echo esc_attr($psf_custom_heading); ?>">
                    <p class="description" id="psf_custom_heading-description"><?php _e('If left empty, the default text will be used:', 'page-specific-faq'); ?>
                        <code><?php _e('Frequently Asked Questions', 'page-specific-faq'); ?></code>
                    </p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="psf_faqs"><?php _e('Questions & Answers', 'page-specific-faq'); ?></label>
                </th>
                <td> <?php psf_generate_faq_rows($psf_faqs); ?> </td>
            </tr>
        </table>
    </div>
<?php
}

/**
 * Generates the product category metabox markup.
 *
 * @param WP_Term $term The term object.
 */
function psf_generate_category_metabox_markup($term) {
    $term_id = $term->term_id;
    $product_cat_name = $term->name;

    $psf_faqs = get_term_meta($term_id, 'psf_faqs', true);
    $psf_custom_heading = get_term_meta($term_id, 'psf_custom_heading', true);
    wp_nonce_field('psf_meta_box_nonce', 'psf_meta_box_nonce');
?>
    <div>
        <tr class="form-field">
            <th scope="row">
                <h2><?php _e('Page Specific FAQ', 'page-specific-faq'); ?></h2>
            </th>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top"><label
                    for="psf_custom_heading"><?php _e('Custom Heading', 'page-specific-faq') ?></label></th>
            <td>
                <input type="text" name="psf_custom_heading" id="psf_custom_heading"
                    placeholder="<?php esc_attr_e('Enter a custom heading', 'page-specific-faq'); ?>"
                    value="<?php if (!empty($psf_custom_heading)) echo esc_attr($psf_custom_heading); ?>">
                <p class="description" id="psf_custom_heading-description"><?php _e('If left empty, the default text will be used:', 'page-specific-faq'); ?>
                    <code><?php printf(__('Frequently Asked Questions about %s', 'page-specific-faq'), esc_html($product_cat_name)); ?></code>
                </p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="psf_faqs"><?php _e('Questions & Answers', 'page-specific-faq'); ?></label>
            </th>
            <td> <?php psf_generate_faq_rows($psf_faqs); ?> </td>
        </tr>
    </div>
<?php
}
