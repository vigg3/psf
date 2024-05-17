<?php
// Hook to add the FAQ markup on WooCommerce product category pages
$woo_visual_hook = get_option('woo_visual_hook') != '' ? get_option('woo_visual_hook') : 'woocommerce_after_main_content';
$page_visual_hook = get_option('page_visual_hook') != '' ? get_option('page_visual_hook') : 'the_content';
add_action($woo_visual_hook, 'psf_faq_add_product_category_markup', 5);

function psf_faq_add_product_category_markup() {
  $queried_object = get_queried_object();
  if (!isset($queried_object->term_id)) {
    return; // Exit the function if there's no term_id
  }
  $product_cat_id = $queried_object->term_id;
  $product_cat_name = $queried_object->name;
  $psf_custom_heading = get_term_meta($product_cat_id, 'psf_custom_heading', true);
  $psf_faqs = get_term_meta($product_cat_id, 'psf_faqs', true);

  $faq_heading = $psf_custom_heading != '' ? $psf_custom_heading : 'Vanliga frågor om ' . $product_cat_name;

  if ($psf_faqs != '') {
    $className = count($psf_faqs) == 1 ? 'faqContent singleQuestion' : 'faqContent';
?>
    <div class="faqWrapper">
      <div class="<?= $className; ?>" itemscope itemtype="https://schema.org/FAQPage">
        <h2><?php echo $faq_heading; ?></h2>
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

// Hook to add the FAQ markup on regular pages
// add_action('wp_footer', 'psf_faq_add_page_markup', 5);
function psf_faq_add_page_markup() {
  if (is_page()) {
    $post_id = get_the_ID();
    $psf_faqs = get_post_meta($post_id, 'psf_faqs', true);

    if (empty($psf_faqs)) return;

    $psf_custom_heading = get_post_meta($post_id, 'psf_custom_heading', true);
    $faq_heading = $psf_custom_heading != '' ? $psf_custom_heading : 'Vanliga frågor';

    $className = count($psf_faqs) == 1 ? 'faqContent singleQuestion' : 'faqContent';
  ?>
    <div class="faqWrapper">
      <div class="<?= $className; ?>" itemscope itemtype="https://schema.org/FAQPage">
        <h2><?php echo $faq_heading; ?></h2>
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

add_filter('the_content', 'psf_append_faq_to_content');
function psf_append_faq_to_content($content) {
  if (is_page()) {
    $post_id = get_the_ID();
    $psf_faqs = get_post_meta($post_id, 'psf_faqs', true);

    if (empty($psf_faqs)) return $content;

    $psf_custom_heading = get_post_meta($post_id, 'psf_custom_heading', true);
    $faq_heading = $psf_custom_heading != '' ? $psf_custom_heading : 'Vanliga frågor';

    $className = count($psf_faqs) == 1 ? 'faqContent singleQuestion' : 'faqContent';

    // Start capturing the FAQ content
    ob_start();
  ?>
    <div class="faqWrapper">
      <div class="<?= esc_attr($className); ?>" itemscope itemtype="https://schema.org/FAQPage">
        <h2><?php echo esc_html($faq_heading); ?></h2>
        <?php foreach ($psf_faqs as $field) { ?>
          <div class="faqEntity" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <div class="faqQuestion">
              <h3 class="faqQuestionText" itemprop="name"><?= esc_html($field['faqQuestion']); ?></h3>
              <span class="accordionPlus"></span>
            </div>
            <div class="faqAnswer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
              <div class="faqAnswerText" itemprop="text">
                <?php the_textarea_value($field['faqAnswer']); ?>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
<?php
    // Get the captured content and append it to the main content
    $faq_content = ob_get_clean();
    $content .= $faq_content;
  }
  return $content;
}
