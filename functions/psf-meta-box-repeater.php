<?php
add_action('admin_init', 'psf_add_meta_boxes', 2);

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

function psf_page_meta_box_display() {
  global $post;
  $page_id = $post->ID;
  $psf_faqs = get_post_meta($page_id, 'psf_faqs', true);
  $psf_custom_heading = get_post_meta($page_id, 'psf_custom_heading', true);

  wp_nonce_field('psf_page_meta_box_nonce', 'psf_page_meta_box_nonce');

?>
  <div>
    <h3>Vanliga frågor</h3>
  </div>
  <div>
    <table>
      <tr class="form-field">
        <th scope="row" valign="top" width="25%">
          <label for="psf_custom_heading"><?php _e('Egen överskrift', 'page-specific-faq') ?></label>
        </th>
        <td>
          <input type="text" name="psf_custom_heading" id="psf_custom_heading" placeholder="Ange en egen överskrift" value="<?php if ($psf_custom_heading != '') echo $psf_custom_heading; ?>">
          <p class="description" id="psf_custom_heading-description">Om fältet lämnas tomt används standardtexten:
            <code>Vanliga frågor</code>
          </p>
        </td>
      </tr>
      <tr class="form-field">
        <th scope="row" valign="top">
          <label for="psf_faqs"><?php _e('Frågor & Svar', 'page-specific-faq'); ?></label>
        </th>
        <td> <?php psf_generate_faq_rows($psf_faqs); ?> </td>
      </tr>
    </table>
  </div>
<?php
}

function psf_meta_box_display($term) {
  $term_id = $term->term_id;
  $product_cat_name = $term->name;

  $psf_faqs = get_term_meta($term_id, 'psf_faqs', true);
  $psf_custom_heading = get_term_meta($term_id, 'psf_custom_heading', true);
  wp_nonce_field('psf_meta_box_nonce', 'psf_meta_box_nonce');
  // $screen = get_current_screen();
?>
  <div>
    <tr class="form-field">
      <th scope="row">
        <h2>Page Specific FAQ</h2>
      </th>
    </tr>
    <tr class="form-field">
      <th scope="row" valign="top"><label for="psf_custom_heading"><?php _e('Egen överskrift', 'page-specific-faq') ?></label></th>
      <td>
        <input type="text" name="psf_custom_heading" id="psf_custom_heading" placeholder="Ange en egen överskrift" value="<?php if ($psf_custom_heading != '') echo $psf_custom_heading; ?>">
        <p class="description" id="psf_custom_heading-description">Om fältet lämnas tomt används standardtexten:
          <code>Vanliga frågor om <?php echo $product_cat_name; ?></code>
        </p>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row" valign="top">
        <label for="psf_faqs"><?php _e('Frågor & Svar', 'page-specific-faq'); ?></label>
      </th>
      <td> <?php psf_generate_faq_rows($psf_faqs); ?> </td>
    </tr>
  </div>
<?php
}


add_action('edited_product_cat', 'custom_psf_meta_box_save', 10, 1);
function custom_psf_meta_box_save($term_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  $oldHeading = get_term_meta($term_id, 'psf_custom_heading', true);
  $newHeading = stripslashes(strip_tags($_POST['psf_custom_heading']));

  $old = get_term_meta($term_id, 'psf_faqs', true);
  $new = array();

  $faqQuestions = $_POST['faqQuestion'];
  $faqAnswers = $_POST['faqAnswer'];
  $count = count($faqQuestions);
  for ($i = 0; $i < $count; $i++) {
    if ($faqQuestions[$i] != '') {
      $new[$i]['faqQuestion'] = stripslashes(strip_tags($faqQuestions[$i]));
      $new[$i]['faqAnswer'] = $faqAnswers[$i];
    }
  }

  if ($newHeading != $oldHeading) update_term_meta($term_id, 'psf_custom_heading', $newHeading);
  if (!empty($new) && $new != $old) update_term_meta($term_id, 'psf_faqs', $new);
  if (empty($new) && $old) delete_term_meta($term_id, 'psf_faqs', $old);
}
