<?php

/**
 * App Name details.
 */

namespace EnUspsConfig;

use EnUspsConnectionSettings\EnUspsConnectionSettings;
use EnUspsQuoteSettingsDetail\EnUspsQuoteSettingsDetail;

/**
 * Config values.
 * Class EnUspsConfig
 * @package EnUspsConfig
 */
if (!class_exists('EnUspsConfig')) {

    class EnUspsConfig
    {
        /**
         * Save config settings
         */
        static public function do_config()
        {
            define('EN_USPS_PLAN', get_option('en_usps_plan_number'));
            !empty(get_option('en_usps_plan_message')) ? define('EN_USPS_PLAN_MESSAGE', get_option('en_usps_plan_message')) : define('EN_USPS_PLAN_MESSAGE', EN_USPS_704);
            define('EN_USPS_NAME', 'USPS');
            define('EN_USPS_PLUGIN_URL', plugins_url());
            define('EN_USPS_ABSPATH', ABSPATH);
            define('EN_USPS_DIR', plugins_url(EN_USPS_MAIN_DIR));
            define('EN_USPS_DIR_FILE', plugin_dir_url(EN_USPS_MAIN_FILE));
            define('EN_USPS_FILE', plugins_url(EN_USPS_MAIN_FILE));
            define('EN_USPS_BASE_NAME', plugin_basename(EN_USPS_MAIN_FILE));
            define('EN_USPS_SERVER_NAME', self::en_get_server_name());

            define('EN_USPS_DECLARED_ZERO', 0);
            define('EN_USPS_DECLARED_ONE', 1);
            define('EN_USPS_DECLARED_ARRAY', []);
            define('EN_USPS_DECLARED_FALSE', false);
            define('EN_USPS_DECLARED_TRUE', true);
            define('EN_USPS_SHIPPING_NAME', 'usps_small');

            $weight_threshold = get_option('en_weight_threshold_lfq');
            $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
            define('EN_USPS_SHIPMENT_WEIGHT_EXCEEDS_PRICE', $weight_threshold);
            define('EN_USPS_SHIPMENT_WEIGHT_EXCEEDS', get_option('en_quote_settings_return_ltl_rates_usps'));
            if (!defined('EN_USPS_ROOT_URL')) {
                define('EN_USPS_ROOT_URL', esc_url('https://eniture.com'));
            }
            define('EN_USPS_ROOT_URL_QUOTES', esc_url('https://ws060.eniture.com'));
            define('EN_USPS_ROOT_URL_PRODUCTS', EN_USPS_ROOT_URL . '/products/');
            define('EN_USPS_RAD_URL', EN_USPS_ROOT_URL . '/woocommerce-residential-address-detection/');
            define('EN_USPS_SBS_URL', EN_USPS_ROOT_URL . '/woocommerce-standard-box-sizes/');
            define('EN_USPS_SUPPORT_URL', esc_url('https://support.eniture.com/home'));
            define('EN_USPS_DOCUMENTATION_URL', EN_USPS_ROOT_URL . '/woocommerce-usps-small-package-plugin/#documentation');
            define('EN_USPS_HITTING_API_URL', EN_USPS_ROOT_URL_QUOTES . '/usps/quotes.php');
            define('EN_USPS_ADDRESS_HITTING_URL', EN_USPS_ROOT_URL_QUOTES . '/addon/google-location.php');
            define('EN_USPS_PLAN_HITTING_URL', EN_USPS_ROOT_URL_QUOTES . '/web-hooks/subscription-plans/create-plugin-webhook.php?');
            define('EN_USPS_ORDER_EXPORT_HITTING_URL', 'https://analytic-data.eniture.com/index.php');

            define('EN_USPS_SET_CONNECTION_SETTINGS', wp_json_encode(EnUspsConnectionSettings::en_set_connection_settings_detail()));
            define('EN_USPS_GET_CONNECTION_SETTINGS', wp_json_encode(EnUspsConnectionSettings::en_get_connection_settings_detail()));
            define('EN_USPS_SET_QUOTE_SETTINGS', wp_json_encode(EnUspsQuoteSettingsDetail::en_usps_quote_settings()));
            define('EN_USPS_GET_QUOTE_SETTINGS', wp_json_encode(EnUspsQuoteSettingsDetail::en_usps_get_quote_settings()));

            $en_app_set_quote_settings = json_decode(EN_USPS_SET_QUOTE_SETTINGS, true);

            define('EN_USPS_ALWAYS_ACCESSORIAL', wp_json_encode(EnUspsQuoteSettingsDetail::en_usps_always_accessorials($en_app_set_quote_settings)));
            define('EN_USPS_ACCESSORIAL', wp_json_encode(EnUspsQuoteSettingsDetail::en_usps_compare_accessorial($en_app_set_quote_settings)));
        }

        /**
         * Get Host
         * @param type $url
         * @return type
         */
        static public function en_get_host($url)
        {
            $parse_url = parse_url(trim($url));
            if (isset($parse_url['host'])) {
                $host = $parse_url['host'];
            } else {
                $path = explode('/', $parse_url['path']);
                $host = $path[0];
            }
            return trim($host);
        }

        /**
         * Get Domain Name
         */
        static public function en_get_server_name()
        {
            global $wp;
            $wp_request = (isset($wp->request)) ? $wp->request : '';
            $url = home_url($wp_request);
            return self::en_get_host($url);
        }

    }

}
