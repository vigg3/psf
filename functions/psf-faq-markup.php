<?php
$woo_visual_hook = get_option('woo_visual_hook') != '' ? get_option('woo_visual_hook') : 'woocommerce_after_main_content';
add_action($woo_visual_hook, 'psf_faq_add_markup', 5);

function psf_faq_add_markup() {
  $queried_object = get_queried_object();
  if (!isset($queried_object->term_id)) {
    return; // Exit the function if there's no term_id
  }
  $product_cat_id = $queried_object->term_id;
  $product_cat_name = $queried_object->name;
  $psf_faqs = get_term_meta($product_cat_id, 'psf_faqs', true);

  if ($psf_faqs != '') {
    $className = count($psf_faqs) == 1 ? 'faqContent singleQuestion' : 'faqContent';
?>
    <div class="faqWrapper">
      <div class="<?= $className; ?>" itemscope itemtype="https://schema.org/FAQPage">
        <h2>Vanliga frågor om <?= $product_cat_name; ?></h2>
        <?php
        foreach ($psf_faqs as $field) { ?>
          <div class="faqEntity" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <div class="faqQuestion">
              <h3 class="faqQuestionText" itemprop="name"><?= $field['faqQuestion'] ?></h3>
              <span class="accordionPlus"></span>
            </div>
            <div class="faqAnswer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div class="faqAnswerText" itemprop="text">
                <?php the_textarea_value($field['faqAnswer']); ?>
              </div>
            </div>
          </div>
        <?php
        } ?>
      </div>
    </div>
<?php
  }
}
