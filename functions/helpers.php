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
