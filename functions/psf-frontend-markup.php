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

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] psf_generate_faq_markup: Generating HTML for ' . count($psf_faqs) . ' FAQs');
        error_log('[PSF DEBUG] psf_generate_faq_markup: Heading: ' . $faq_heading);
        error_log('[PSF DEBUG] psf_generate_faq_markup: FAQ data structure: ' . print_r($psf_faqs, true));
    }

    $className = count($psf_faqs) == 1 ? 'faqContent singleQuestion' : 'faqContent';
    ob_start();
?>
<div class="faqWrapper">
    <div class="<?= esc_attr($className); ?>" itemscope itemtype="https://schema.org/FAQPage">
        <?php if (!empty($faq_heading)): ?>
        <h2><?php echo esc_html($faq_heading); ?></h2>
        <?php endif; ?>
        <?php
            foreach ($psf_faqs as $field) { ?>
        <div class="faqEntity" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
            <div class="faqQuestion">
                <h3 class="faqQuestionText" itemprop="name"><?php echo esc_html($field['faqQuestion']); ?>
                </h3>
                <span class="accordionPlus"></span>
            </div>
            <div class="faqAnswer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                <div class="faqAnswerText" itemprop="text">
                    <?php
                            if (!empty($field['faqAnswer'])) {
                                the_textarea_value($field['faqAnswer']);
                            }
                            ?>
                </div>
            </div>
        </div>
        <?php
            } ?>
    </div>
</div>
<?php
    $html_output = ob_get_clean();

    if (defined('WP_DEBUG') && WP_DEBUG && get_option('debug_mode', '0') === '1') {
        error_log('[PSF DEBUG] psf_generate_faq_markup: Generated HTML length: ' . strlen($html_output) . ' characters');
    }

    return $html_output;
}