<?php

/**
 * Receiver address.
 */

namespace EnUspsReceiverAddress;

/**
 * Get address from cart|checkout page.
 * Class EnUspsReceiverAddress
 * @package EnUspsReceiverAddress
 */
if (!class_exists('EnUspsReceiverAddress')) {

    class EnUspsReceiverAddress
    {
        static public $woocommerce_version;

        /**
         * Receiver address
         * @return array
         */
        static public function get_address()
        {
            self::en_get_woo_version_number();

            return [
                'receiverZip' => strlen(WC()->customer->get_shipping_postcode()) > 0 ? WC()->customer->get_shipping_postcode() : self::get_postcode(),
                'receiverState' => strlen(WC()->customer->get_shipping_state()) > 0 ? WC()->customer->get_shipping_state() : self::get_state(),
                'receiverCountryCode' => strlen(WC()->customer->get_shipping_country()) > 0 ? WC()->customer->get_shipping_country() : self::get_country(),
                'receiverCity' => strlen(WC()->customer->get_shipping_city()) > 0 ? WC()->customer->get_shipping_city() : self::get_city(),
                'addressLine' => strlen(WC()->customer->get_shipping_address_1()) > 0 ? WC()->customer->get_shipping_address_1() : self::get_address1(),
            ];
        }

        /**
         * Declared woo version publically
         */
        static public function en_get_woo_version_number()
        {
            if (!function_exists('get_plugins')) {
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            $plugin_folder = get_plugins('/' . 'woocommerce');
            $plugin_file = 'woocommerce.php';
            (isset($plugin_folder[$plugin_file]['Version'])) ?
                self::$woocommerce_version = $plugin_folder[$plugin_file]['Version'] : '';
        }

        /**
         * Get Postcode
         * @return string
         */
        static public function get_postcode()
        {
            $postcode = "";
            switch (self::$woocommerce_version) {
                case (self::$woocommerce_version <= '2.7'):
                    $postcode = WC()->customer->get_postcode();
                    break;
                default:
                    $postcode = WC()->customer->get_billing_postcode();
                    break;
            }

            return $postcode;
        }

        /**
         * Get state
         * @return string
         */
        static public function get_state()
        {
            $state = "";
            switch (self::$woocommerce_version) {
                case (self::$woocommerce_version <= '2.7'):
                    $state = WC()->customer->get_state();
                    break;
                default:
                    $state = WC()->customer->get_billing_state();
                    break;
            }
            return $state;
        }

        /**
         * Get city
         * @return string
         */
        static public function get_city()
        {
            $city = "";
            switch (self::$woocommerce_version) {
                case (self::$woocommerce_version <= '2.7'):
                    $city = WC()->customer->get_city();
                    break;
                default:
                    $city = WC()->customer->get_billing_city();
                    break;
            }
            return $city;
        }

        /**
         * Get country
         * @return string
         */
        static public function get_country()
        {
            $country = "";
            switch (self::$woocommerce_version) {
                case (self::$woocommerce_version <= '2.7'):
                    $country = WC()->customer->get_country();
                    break;
                default:
                    $country = WC()->customer->get_billing_country();
                    break;
            }
            return $country;
        }

        /**
         * Get address
         * @return string
         */
        static public function get_address1()
        {
            $address = "";
            switch (self::$woocommerce_version) {
                case (self::$woocommerce_version <= '2.7'):
                    $address = WC()->customer->get_address();
                    break;
                default:
                    $address = WC()->customer->get_billing_address_1();
                    break;
            }
            return $address;
        }

    }

}