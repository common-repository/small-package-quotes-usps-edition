<?php
/**
 * Plugin Name: Small Package Quotes - USPS Edition
 * Plugin URI: https://eniture.com/products/
 * Description: Dynamically retrieves your negotiated shipping rates from Usps Express and displays the results in the WooCommerce shopping cart.
 * Version: 1.3.5
 * Author: Eniture Technology
 * Author URI: http://eniture.com/
 * Text Domain: eniture-technology
 * License: GPL version 2 or later - http://www.eniture.com/
 * WC requires at least: 6.4
 * WC tested up to: 9.3.1
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once 'vendor/autoload.php';

define('EN_USPS_MAIN_DIR', __DIR__);
define('EN_USPS_MAIN_FILE', __FILE__);

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

if (empty(\EnUspsGuard\EnUspsGuard::en_check_prerequisites('USPS', '5.6', '4.0', '2.3'))) {
    require_once 'en-install.php';
}
