<?php
function page_specific_faq_settings_page() {
?>
<header class="psf-settings-header">
  <div class="psf-settings__grid-wrapper">
    <h1 style="color: #fff;">Page Specific FAQ</h1>
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
            <h2>Allmänna inställningar</h2>
          </th>
        </tr>
        <!-- Enable / Disable plugin -->
        <tr valign="top">
          <th scope="row">
            <h3>Aktiverad</h3>
            Ska pluginet vara aktiverat?
          </th>
          <td style="display:flex;gap:16px">
            <div class="radio-wrapper">
              <input type="radio" id="yes" name="activate_psf" value="yes"
                <?= ((get_option('activate_psf') == 'yes') ? 'checked' : ''); ?> style="margin-top:0;margin-right:6px;">
              <label for="yes">Ja</label>
            </div>
            <div class="radio-wrapper">
              <input type="radio" id="no" name="activate_psf" value="no"
                <?= ((get_option('activate_psf') == 'no') ? 'checked' : ''); ?> style="margin-top:0;margin-right:6px;">
              <label for="no">Nej</label>
            </div>
          </td>
        </tr>
        <!-- FAQ placement | archives -->
        <tr valign="top">
          <th scope="row">
            <h3>Position</h3>
            Hook för positionering av FAQ på kategorisidor.<br>
            <code>woocommerce_after_main_content</code> är längst ned på sidan.<br>
            <a target="_blank"
              href="https://www.businessbloomer.com/woocommerce-visual-hook-guide-archiveshopcat-page/">Woocommerce
              Visual Hook Guide</a>
          </th>
          <td>
            <div id="psf_visual_hook">
              <input type="text" id="woo_visual_hook" placeholder="woocommerce_after_main_content" class="code"
                value="<?= esc_attr(get_option('woo_visual_hook')); ?>" name="woo_visual_hook" />
            </div>
          </td>
        </tr>
        <!-- Enabled pages -->
        <tr valign="top">
          <th scope="row">
            <h3>Ytterligare sidor</h3>
            Välj fler sidor, utöver produktkategorierna, där FAQ ska aktiveras.
          </th>
          <td>
            <div class="enabled_pages_wrapper">
              <?php
                $enabled_pages_array = array_filter(explode(',', get_option('enabled_pages')));
                $frontpage_id = get_option('page_on_front');
                if ($frontpage_id == 0) {
                  $frontpage_id = 1;
                }
                $isChecked = in_array($frontpage_id, $enabled_pages_array) ? 'checked' : '';
                ?>
              <div class="checkbox_wrapper">
                <input type="checkbox" name="<?php echo $frontpage_id; ?>" id="<?php echo $frontpage_id; ?>"
                  value="<?php echo $frontpage_id; ?>" <?php echo $isChecked ?> />
                <label for="<?php echo $frontpage_id; ?>">
                  <span><?php echo get_option('blogname'); ?></span>
                  <span><?php echo get_site_url(); ?></span>
                </label>
              </div>
              <?php
                $pages = get_pages(array(
                  'exclude' => array($frontpage_id)
                ));
                foreach ($pages as $page) :
                  $isChecked = in_array($page->ID, $enabled_pages_array) ? 'checked' : ''; ?>
              <div class="checkbox_wrapper">
                <input type="checkbox" name="<?php echo $page->ID; ?>" id="<?php echo $page->ID; ?>"
                  value="<?php echo $page->ID; ?>" <?php echo $isChecked; ?> />
                <label for="<?php echo $page->ID; ?>">
                  <span><?php echo $page->post_title; ?></span>
                  <span><?php echo get_page_link($page->ID); ?></span>
                </label>
              </div>
              <?php
                endforeach; ?>
            </div>
            <input type="text" hidden id="enabled_pages" name="enabled_pages"
              value="<?php echo get_option('enabled_pages'); ?>" />
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