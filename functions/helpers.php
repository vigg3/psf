<?php

/**
 * Retrives the plugin version.
 * @return string The plugin version.
 */
function psf_get_version() {
  $plugin_data = get_plugin_data(PSF_PLUGIN_PATH . 'register-psf.php');
  return $plugin_data['Version'];
}

function get_all_products() {
  $all_products = $woocommerce->get_all_products();

  return $all_products;
}

function get_all_product_categories() {
  $orderby = 'name';
  $order = 'asc';
  $hide_empty = false;
  $cat_args = array(
    'orderby'     => $orderby,
    'order'       => $order,
    'hide_empty'  => $hide_empty,
  );

  $all_product_categories = get_terms('product_cat', $cat_args);

  return $all_product_categories;
}

function the_textarea_value($textarea) {
  $lines = explode("\n", $textarea);
  foreach ($lines as $line) {
    echo $line . '</br>';
  }
}

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
        <tr class="faqWrapper">
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
      <tr class="faqWrapper empty-row screen-reader-text">
        <td width="85%">
          <div class="faqQuestion">
            <label for="faqQuestion[]">Fråga</label>
            <input type="text" placeholder="Skriv frågan här" title="Fråga" name="faqQuestion[]" id="faqQuestion[]">
          </div>
          <div class="faqAnswer">
            <label for="faqAnswer[]">Svar</label>
            <textarea placeholder="Skriv svaret på frågan här" name="faqAnswer[]" cols="55" rows="3" id="faqAnswer[]"></textarea>
          </div>
        </td>
        <td class="button-wrapper" width=15%>
          <a class="button remove-row" href="#">Ta bort</a>
        </td>
      </tr>
    </tbody>
  </table>
  <p><a href="#" class="button" id="add-row">Lägg till FAQ</a></p>
<?php
}
