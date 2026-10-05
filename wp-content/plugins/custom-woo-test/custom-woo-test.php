<?php
/**
 * Plugin Name: Custom woo test
 * Add a "Customer Reference" field to WooCommerce checkout. Make it mandatory and save it to the order.
 */


if(!defined('ABSPATH')){
 exit;
}

require_once plugin_dir_path(__FILE__).'includes/class-customer-checkout.php';

new CustomerCheckout();