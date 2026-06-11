<?php

if (!defined('ABSPATH')) exit;

/**
 * FAQPage JSON-LD output.
 *
 * The schema is collected from the SAME $faqs arrays the render paths pass to
 * psf_generate_faq_markup() (pages, product-category archives, the shop page
 * and the [psf_faq] shortcode all funnel through it). Each render pushes its
 * items into a request-scoped store via psf_collect_faq_items();
 * psf_output_faqpage_schema() then emits ONE consolidated FAQPage on wp_footer.
 * Because both come from the same array, the structured data can never drift
 * from the visible HTML.
 *
 * CACHING: the output is deterministic per URL, so it is safe for W3 Total
 * Cache (or any page cache) to store. Purge the W3TC page cache after enabling
 * the setting so cached pages pick up the new schema. The schema is produced
 * during the normal render pass (no output buffering of the whole page).
 *
 * SEO NOTE: as of 7 May 2026 Google no longer shows FAQ rich results, so this
 * markup is primarily for AI answer engines (ChatGPT, Perplexity, Gemini, AI
 * Overviews) and Bing. It remains valid schema.org and is harmless to keep.
 */

/**
 * Request-scoped accumulator of rendered FAQ items.
 *
 * Returned by reference so callers can append. Reset is unnecessary: a fresh
 * PHP request starts with an empty static.
 *
 * @return array<int, array{faqQuestion: string, faqAnswer: string}>
 */
function &psf_faq_store() {
    static $store = [];
    return $store;
}

/**
 * Collect the FAQ items for the page-level FAQPage schema. Called from
 * psf_generate_faq_markup() for every render path so schema and HTML stay in
 * lock-step.
 *
 * @param array $faqs Array of ['faqQuestion' => ..., 'faqAnswer' => ...].
 * @return void
 */
function psf_collect_faq_items($faqs) {
    if (empty($faqs) || !is_array($faqs)) return;

    $store = &psf_faq_store();
    foreach ($faqs as $faq) {
        if (!is_array($faq) || !isset($faq['faqQuestion'], $faq['faqAnswer'])) continue;
        $store[] = $faq;
    }
}

/**
 * Build the FAQPage data array from the collected items.
 *
 * Questions and answers are reduced to plain text, whitespace-collapsed and
 * trimmed. Empty items are skipped and duplicate questions deduplicated.
 *
 * @return array Empty array when there is nothing usable to output.
 */
function psf_build_faqpage_data() {
    $store = &psf_faq_store();
    if (empty($store)) return [];

    $main = [];
    $seen = [];

    foreach ($store as $faq) {
        $q = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($faq['faqQuestion'])));
        $a = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($faq['faqAnswer'])));
        if ($q === '' || $a === '') continue;

        // Deduplicate identical questions (case-insensitive).
        $key = function_exists('mb_strtolower') ? mb_strtolower($q) : strtolower($q);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;

        // Per-answer filter, e.g. to append a CTA or rewrite for answer engines.
        $a = (string) apply_filters('psf_faqpage_answer', $a, $q);
        if ($a === '') continue;

        $main[] = [
            '@type'          => 'Question',
            'name'           => $q,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $a,
            ],
        ];
    }

    if (empty($main)) return [];

    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $main,
    ];
}

/**
 * Emit the consolidated FAQPage JSON-LD in the footer.
 *
 * Registered late (priority 9999) so it runs AFTER every render path — note
 * the Hello Elementor fallback renders FAQs on wp_footer at priority 109, so
 * anything earlier would miss those items.
 *
 * @return void
 */
add_action('wp_footer', 'psf_output_faqpage_schema', 9999);
function psf_output_faqpage_schema() {
    // Toggleable, default ON.
    if (get_option('faqpage_schema_enabled', 'yes') === 'no') return;

    // Output once per request even if the hook somehow fires twice.
    static $done = false;
    if ($done) return;

    // Defensive: let integrations signal that an FAQPage already exists on the
    // page (e.g. emitted by another plugin) so we don't add a second one.
    // Yoast does not emit FAQ schema, so by default this is false.
    if (apply_filters('psf_faqpage_schema_already_present', false)) return;

    $data = psf_build_faqpage_data();
    if (empty($data) || empty($data['mainEntity'])) return;

    // Allow full customisation of the payload before encoding.
    $data = apply_filters('psf_faqpage_schema', $data);
    if (empty($data) || empty($data['mainEntity'])) return;

    $done = true;

    // JSON_UNESCAPED_UNICODE keeps Swedish characters (å ä ö) readable;
    // JSON_UNESCAPED_SLASHES keeps URLs clean.
    echo "\n" . '<script type="application/ld+json">'
        . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . '</script>' . "\n";
}
