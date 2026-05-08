<?php

/**
 * Generates the FAQ markup for a given heading and FAQs.
 *
 * @param array<array{faqQuestion: string, faqAnswer: string}> $psf_faqs The FAQs array.
 * @param string $faq_heading The heading for the FAQs.
 * @return string The generated FAQ markup.
 */
function psf_generate_faq_markup($psf_faqs, $faq_heading) {
    // Type validation
    if (!is_array($psf_faqs)) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] psf_generate_faq_markup: ERROR - $psf_faqs is not an array, got: ' . gettype($psf_faqs));
        }
        return '';
    }

    if (!is_string($faq_heading)) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] psf_generate_faq_markup: ERROR - $faq_heading is not a string, got: ' . gettype($faq_heading));
        }
        $faq_heading = 'Vanliga frågor'; // Fallback
    }

    if (empty($psf_faqs)) {
        if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
            error_log('[PSF DEBUG] psf_generate_faq_markup: No FAQs provided - returning empty');
        }
        return '';
    }

    psf_debug_log('psf_generate_faq_markup: ' . count($psf_faqs) . ' FAQs, heading: ' . $faq_heading);

    $className = count($psf_faqs) === 1 ? 'faqContent singleQuestion' : 'faqContent';
    $jsonld    = psf_generate_structured_data($psf_faqs);

    ob_start();
?>
<div class="faqWrapper">
    <?php if (!empty($jsonld['mainEntity'])): ?>
    <script type="application/ld+json"><?= wp_json_encode($jsonld, JSON_UNESCAPED_UNICODE); ?></script>
    <?php endif; ?>
    <div class="<?= esc_attr($className); ?>">
        <?php if (!empty($faq_heading)): ?>
        <h2><?php echo esc_html($faq_heading); ?></h2>
        <?php endif; ?>
        <?php foreach ($psf_faqs as $field): ?>
        <div class="faqEntity">
            <div class="faqQuestion">
                <h3 class="faqQuestionText"><?php echo esc_html($field['faqQuestion']); ?></h3>
                <span class="accordionPlus"></span>
            </div>
            <div class="faqAnswer">
                <div class="faqAnswerText">
                    <?php if (!empty($field['faqAnswer'])) the_textarea_value($field['faqAnswer']); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
    return ob_get_clean();
}