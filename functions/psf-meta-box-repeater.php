<?php

/**
 * Registers meta boxes for FAQ on product categories and pages.
 */
add_action('admin_init', 'psf_add_meta_boxes', 2);

/**
 * Adds meta boxes for FAQ to product categories and pages.
 */
function psf_add_meta_boxes() {
  add_action('product_cat_edit_form_fields', 'psf_meta_box_display', 10, 1);

  add_meta_box(
    'psf_page_meta_box',
    __('Page Specific FAQ', 'page-specific-faq'),
    'psf_page_meta_box_display',
    'page',
    'normal',
  );
}

/**
 * Displays the FAQ meta box for a page.
 */
function psf_page_meta_box_display() {
  global $post;
  psf_generate_page_metabox_markup($post);
}

/**
 * Handles saving FAQ meta data for a page.
 *
 * @param int $post_id The post ID.
 */
function psf_save_page_meta_data($post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!isset($_POST['psf_page_meta_box_nonce'])) return;
  if (!wp_verify_nonce($_POST['psf_page_meta_box_nonce'], 'psf_save_page_meta_data')) return;

  if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id)) return;
  } else {
    if (!current_user_can('edit_post', $post_id)) return;
  }

  $oldHeading = get_post_meta($post_id, 'psf_custom_heading', true);
  $newHeading = stripslashes(strip_tags($_POST['psf_custom_heading']));

  $old = get_post_meta($post_id, 'psf_faqs', true);
  $new = array();

  if (isset($_POST['faqQuestion']) && isset($_POST['faqAnswer'])) {
    $faqQuestions = $_POST['faqQuestion'];
    $faqAnswers = $_POST['faqAnswer'];
    $count = count($faqQuestions);
    for ($i = 0; $i < $count; $i++) {
      if (!empty($faqQuestions[$i])) {
        $new[$i]['faqQuestion'] = stripslashes(strip_tags($faqQuestions[$i]));
        $new[$i]['faqAnswer'] = isset($faqAnswers[$i]) ? $faqAnswers[$i] : '';
      }
    }
  }

  if ($newHeading != $oldHeading) update_post_meta($post_id, 'psf_custom_heading', $newHeading);
  if (!empty($new) && $new != $old) update_post_meta($post_id, 'psf_faqs', $new);
  if (empty($new) && $old) delete_post_meta($post_id, 'psf_faqs', $old);
}
add_action('save_post', 'psf_save_page_meta_data');

/**
 * Displays the FAQ meta box for a product category.
 *
 * @param WP_Term $term The term object.
 */
function psf_meta_box_display($term) {
  psf_generate_category_metabox_markup($term);
}

/**
 * Handles saving FAQ meta data for a product category.
 *
 * @param int $term_id The term ID.
 */
function custom_psf_meta_box_save($term_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  $oldHeading = get_term_meta($term_id, 'psf_custom_heading', true);
  $newHeading = stripslashes(strip_tags($_POST['psf_custom_heading']));

  $old = get_term_meta($term_id, 'psf_faqs', true);
  $new = array();

  if (isset($_POST['faqQuestion']) && isset($_POST['faqAnswer'])) {
    $faqQuestions = $_POST['faqQuestion'];
    $faqAnswers = $_POST['faqAnswer'];
    $count = count($faqQuestions);
    for ($i = 0; $i < $count; $i++) {
      if (!empty($faqQuestions[$i])) {
        $new[$i]['faqQuestion'] = stripslashes(strip_tags($faqQuestions[$i]));
        $new[$i]['faqAnswer'] = isset($faqAnswers[$i]) ? $faqAnswers[$i] : '';
      }
    }
  }

  if ($newHeading != $oldHeading) update_term_meta($term_id, 'psf_custom_heading', $newHeading);
  if (!empty($new) && $new != $old) update_term_meta($term_id, 'psf_faqs', $new);
  if (empty($new) && $old) delete_term_meta($term_id, 'psf_faqs', $old);
}

add_action('edited_product_cat', 'custom_psf_meta_box_save', 10, 1);