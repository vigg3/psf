<?php
add_action('admin_init', 'psf_add_meta_boxes', 2);

function psf_add_meta_boxes() {
  // add_meta_box('psf-group', 'FAQ', 'psf_meta_box_display', 'product_cat', 'normal', 'default');
  add_action('product_cat_edit_form_fields', 'psf_meta_box_display', 10, 1);
}

function psf_meta_box_display($term) {
  $term_id = $term->term_id;
  $product_cat_name = $term->name;

  $psf_faqs = get_term_meta($term_id, 'psf_faqs', true);
  $psf_custom_heading = get_term_meta($term_id, 'psf_custom_heading', true);
  wp_nonce_field('psf_meta_box_nonce', 'psf_meta_box_nonce');
?>
  <div>
    <style type="text/css">
      .inner-table tr>td {
        padding-left: 0;
        padding-right: 0;
      }

      td .faqQuestion {
        padding-top: 0;
      }

      td .faqAnswer {
        padding-top: 0;
        margin-top: 0.5rem;
      }

      .faqQuestion input,
      .faqAnswer input {
        margin-top: 0.2rem;
      }

      .inner-table>tbody>tr>td {
        vertical-align: top;
      }

      .inner-table>tbody>tr>td:first-of-type {
        padding-top: 0;
      }

      .inner-table .button-wrapper {
        padding-top: 1.4rem;
      }
    </style>
    <script type="text/javascript">
      jQuery(document).ready(function($) {
        $('#add-row').on('click', function() {
          var currentLength = $('#repeatable-tbody')[0].children.length;
          var row = $('.empty-row.screen-reader-text').clone(true).removeClass('empty-row screen-reader-text');
          row.insertBefore($('#repeatable-tbody>tr:last'));
          if (currentLength == 2) {
            $('#repeatable-tbody>tr:first>td:last').append('<a class="button remove-row" href="#">Ta bort</a>')
          }
          return false;
        })
        $('.remove-row').on('click', function() {
          $(this).parent().parent().remove();
          var currentLength = $('#repeatable-tbody')[0].children.length;
          if (currentLength == 2) {
            $('#repeatable-tbody>tr:first').find('.remove-row').remove();
          }
          return false;
        });
      });
    </script>
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
      <th scope="row" valign="top"><label for="psf_faqs"><?php _e('Frågor & Svar', 'page-specific-faq'); ?></label></th>
      <td>
        <table id="repeateable-fieldset-one" class="inner-table" width="100%">
          <tbody id="repeatable-tbody">
            <?php
            if ($psf_faqs) :
              foreach ($psf_faqs as $key => $field) {
            ?>
                <tr>
                  <td width="85%">
                    <div class="faqQuestion">
                      <label for="faqQuestion[<?php echo $key; ?>]">Fråga</label>
                      <input type="text" name="faqQuestion[<?php echo $key; ?>]" placeholder="Skriv frågan här" id="faqQuestion[<?php echo $key; ?>]" value="<?php if ($field['faqQuestion'] != '') echo esc_attr($field['faqQuestion']); ?>">
                    </div>
                    <div class="faqAnswer">
                      <label for="faqAnswer[<?php echo $key; ?>]">Svar</label>
                      <textarea placeholder="Skriv svaret på frågan här" cols="55" rows="3" name="faqAnswer[<?php echo $key; ?>]" id="faqAnswer[<?php echo $key; ?>]"><?php if ($field['faqAnswer'] != '') echo esc_attr($field['faqAnswer']); ?></textarea>
                    </div>
                  </td>
                  <td class="button-wrapper" width="15%">
                    <a class="button remove-row" href="#">Ta bort</a>
                  </td>
                </tr>
              <?php
              }
            else :
              ?>
              <tr>
                <td width="85%">
                  <div class="faqQuestion">
                    <label for="faqQuestion[0]">Fråga</label>
                    <input type="text" placeholder="Skriv frågan här" title="Fråga" name="faqQuestion[0]" id="faqQuestion[0]">
                  </div>
                  <div class="faqAnswer">
                    <label for="faqAnswer[0]">Svar</label>
                    <textarea placeholder="Skriv svaret på frågan här" name="faqAnswer[0]" cols="55" rows="3" id="faqAnswer[0]"></textarea>
                  </div>
                </td>
                <td class="button-wrapper" width=15%>

                </td>
              </tr>

            <?php
            endif;
            ?>
            <tr class="empty-row screen-reader-text">
              <td width="85%">
                <div>
                  <label for="faqQuestion[]">Fråga</label>
                  <input type="text" placeholder="Skriv frågan här" title="Fråga" name="faqQuestion[]" id="faqQuestion[]">
                </div>
                <div class="faqAnswer">
                  <label for="faqAnswer[]">Svar</label>
                  <textarea placeholder="Skriv svaret på frågan här" name="faqAnswer[]" cols="55" rows="3" id="faqAnswer[]"></textarea>
                </div>
              </td>
              <td class="button-wrapper" width="15%">
                <a class="button remove-row" href="#">Ta bort</a>
              </td>
            </tr>
          </tbody>
        </table>
        <p><a href="#" class="button" id="add-row">Lägg till</a></p>
      </td>
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
