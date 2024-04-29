<?php
function page_specific_faq_settings_page() {
?>
  <style type="text/css" id="settings_stylesheet">
    .psf-settings__grid-wrapper {
      display: -ms-grid;
      display: grid;
      grid-column-gap: 0.875rem;
      -ms-grid-columns: 15rem auto;
      grid-template-columns: 15rem auto;
      margin-left: auto;
      margin-right: auto;
      max-width: 69.5rem;
      padding: 1.5rem;
    }

    .psf-settings__wrapper {
      margin: 10px 20px 0 2px;
    }

    .psf-settings__wrapper .form-table th {
      width: 30%;
    }

    .psf-settings-header {
      background: #3e7da6;
      box-shadow: 1px 1px 3px rgb(0 0 0 / 3%);

    }

    .psf-settings__wrapper input.code {
      line-height: 1;
      height: 2em;
      min-width: 300px;
    }
  </style>

  <header class="psf-settings-header">
    <div class="psf-settings__grid-wrapper">
      <h1 style="color: #fff;">Page Specific FAQ</h1>
    </div>
  </header>

  <div class="psf-settings__wrapper">
    <form class="form-table" method="post" action="options.php">
      <?php settings_fields('page-specific-faq-settings-group'); ?>
      <?php do_settings_sections('page-specific-faq-settings-group');
      ?>
      <table>

        <tbody>
          <!-- Enable / Disable plugin -->
          <tr valign="top">
            <th scope="row">
              Aktiverad
            </th>
            <td style="vertical-align:top;display:flex;gap:16px">
              <div style="display:flex;justify-content:center;align-items:center;">
                <input type="radio" id="yes" name="activate_psf" value="yes" <?= ((get_option('activate_psf') == 'yes') ? 'checked' : ''); ?> style="margin-top:0;margin-right:6px;">
                <label for="yes">Ja</label>
              </div>
              <div style="display:flex;justify-content:center;align-items:center;">
                <input type="radio" id="no" name="activate_psf" value="no" <?= ((get_option('activate_psf') == 'no') ? 'checked' : ''); ?> style="margin-top:0;margin-right:6px;">
                <label for="no">Nej</label>
              </div>
            </td>
          </tr>
          <!-- FAQ placement | archives -->
          <tr valign="top">
            <th scope="row">
              Position
              <br><span style="font-weight:400;">Hook för positionering av FAQ på kategorisidor.</span>
              <br><span style="font-weight:400;">Standardvärde är <code>woocommerce_after_main_content</code></span>
              <br><span style="font-weight:400;">Detta innebär att den är placerad längst ned på sidan.</span>
              <br><span style="font-weight:400;"><a target="_blank" href="https://www.businessbloomer.com/woocommerce-visual-hook-guide-archiveshopcat-page/">Woocommerce
                  Visual Hook
                  Guide</a></span>
            </th>
            <td style="vertical-align:top;">
              <div id="psf_visual_hook">
                <input type="text" id="woo_visual_hook" placeholder="woocommerce_after_main_content" class="code" value="<?= esc_attr(get_option('woo_visual_hook')); ?>" name="woo_visual_hook" />
              </div>
            </td>
          </tr>
          <!-- Enabled pages -->
          <tr valign="top" style="display: none;">
            <th scope="row">
              Aktivera
              <br><span style="font-weight:400;">på följande posttyper och sidor.</span>
            </th>
            <td style="vertical-align:top;">
              <div id="psf_enabled_pages">
                <?php
                $enabled_pages_array = array_filter(explode(',', get_option('enabled_pages_array')));
                $frontpage_id = get_option('page_on_front'); // page_on_front returns 0 if value hasn't been set
                if ($frontpage_id == 0) {
                  $frontpage_id = 1;
                }
                $parent_checkbox = '<input type="checkbox" ';
                $parent_checkbox .= (in_array($frontpage_id, $enabled_pages_array)) ? 'checked ' : '';
                $parent_checkbox .= 'value="' . $frontpage_id . '">';
                $parent_checkbox .= get_option('blogname') . ' | ' . get_site_url() . ' ';
                $parent_checkbox .= '</input><br>';
                echo $parent_checkbox;

                $pages = get_pages(array(
                  'exclude' => array($frontpage_id) // exclude frontpage_id
                ));
                foreach ($pages as $page) {
                  $checkbox = '<input type="checkbox"';
                  $checkbox .= (in_array($page->ID, $enabled_pages_array)) ? 'checked ' : '';
                  $checkbox .= 'value="' . $page->ID . '">';
                  $checkbox .= $page->post_title . ' | ' . get_page_link($page->ID) . ' ';
                  $checkbox .= '</input><br>';
                  echo $checkbox;
                }
                ?>
              </div>
              <?php
              echo '<input type="text" hidden id="enabled_pages_array" name="enabled_pages_array" value="' . get_option('enabled_pages_array') . '" />';
              ?>
            </td>
          </tr>
        </tbody>
      </table>

      <?php submit_button('Spara ändringar'); ?>
    </form>
  </div>

<?php
}
?>