<?php
// Hook to add the FAQ markup on WooCommerce product category pages
$woo_visual_hook = get_option('woo_visual_hook') != '' ? get_option('woo_visual_hook') : 'woocommerce_after_main_content';
add_action($woo_visual_hook, 'psf_faq_add_product_category_markup');

// Hook to add the FAQ markup on pages
$page_visual_hook = get_option('page_visual_hook') != '' ? get_option('page_visual_hook') : 'the_content';
add_action($page_visual_hook, 'psf_faq_add_page_markup');

/**
 * Retrieves the FAQs and the custom heading for a given term or post.
 *
 * @param int $id The ID of the term or post.
 * @param string $type The type of the object ('term' or 'post').
 * @return array The FAQs and the heading.
 */
function psf_get_faqs_and_heading($id, $type) {
  if ($type === 'term') {
    $psf_custom_heading = get_term_meta($id, 'psf_custom_heading', true);
    $psf_category_name = get_term($id, 'product_cat')->name;
    $psf_heading = $psf_custom_heading != '' ? $psf_custom_heading : 'Vanliga frågor om ' . $psf_category_name;
    $psf_faqs = get_term_meta($id, 'psf_faqs', true);
  } else { // post
    $psf_custom_heading = get_post_meta($id, 'psf_custom_heading', true);
    $psf_heading = $psf_custom_heading != '' ? $psf_custom_heading : 'Vanliga frågor';
    $psf_faqs = get_post_meta($id, 'psf_faqs', true);
  }

  return [$psf_faqs, $psf_heading];
}

/**
 * Generates the FAQ markup for a given heading and FAQs.
 *
 * @param array $psf_faqs The FAQs.
 * @param string $faq_heading The heading for the FAQs.
 * @return string The generated FAQ markup.
 */
function psf_generate_faq_markup($psf_faqs, $faq_heading) {
  if (empty($psf_faqs)) return '';

  $className = count($psf_faqs) == 1 ? 'faqContent singleQuestion' : 'faqContent';

  ob_start();
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
  return ob_get_clean();
}

/**
 * Adds the FAQ markup to the product category page.
 */
function psf_faq_add_product_category_markup() {
  $queried_object = get_queried_object();
  if (!isset($queried_object->term_id)) {
    return;
  }
  list($psf_faqs, $faq_heading) = psf_get_faqs_and_heading($queried_object->term_id, 'term');

  echo psf_generate_faq_markup($psf_faqs, $faq_heading);
}

/**
 * Adds the FAQ markup to a page.
 */
function psf_faq_add_page_markup() {
  if (is_page()) {
    $post_id = get_the_ID();
    list($psf_faqs, $faq_heading) = psf_get_faqs_and_heading($post_id, 'post');

    echo psf_generate_faq_markup($psf_faqs, $faq_heading);
  }
}