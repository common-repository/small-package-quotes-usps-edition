<?php

/**
 * Test connection details.
 */

namespace EnUspsConnectionSettings;

/**
 * Add array for test connection.
 * Class EnUspsConnectionSettings
 * @package EnUspsConnectionSettings
 */
if (!class_exists('EnUspsConnectionSettings')) {

    class EnUspsConnectionSettings
    {

        static $get_connection_details = [];

        /**
         * Connection settings template.
         * @return array
         */
        static public function en_load()
        {
            echo '<div class="en_usps_connection_settings">';

            $start_settings = [
                'en_connection_settings_start_usps' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'title',
                    'id' => 'en_connection_settings_usps',
                ],
            ];

            // App Name Connection Settings Detail
            $eniture_settings = self::en_set_connection_settings_detail();

            $end_settings = [
                'en_connection_settings_end_usps' => [
                    'type' => 'sectionend',
                    'id' => 'en_connection_settings_end_usps'
                ]
            ];

            $settings = array_merge($start_settings, $eniture_settings, $end_settings);

            return $settings;
        }

        /**
         * Connection Settings Detail
         * @return array
         */
        static public function en_get_connection_settings_detail()
        {
            $connection_request = self::en_static_request_detail();
            $en_request_indexing = json_decode(EN_USPS_SET_CONNECTION_SETTINGS, true);
            foreach ($en_request_indexing as $key => $value) {
                $saved_connection_detail = get_option($key);
                $connection_request[$value['eniture_action']] = $saved_connection_detail;
                strlen($saved_connection_detail) > 0 ?
                    self::$get_connection_details[$value['eniture_action']] = $saved_connection_detail : '';
            }

            add_filter('en_usps_reason_quotes_not_returned', [__CLASS__, 'en_usps_reason_quotes_not_returned'], 99, 1);

            return $connection_request;
        }

        /**
         * Saving reasons to show proper error message on the cart or checkout page
         * When quotes are not returning
         * @param array $reasons
         * @return array
         */
        static public function en_usps_reason_quotes_not_returned($reasons)
        {
            return empty(self::$get_connection_details) ? array_merge($reasons, [EN_USPS_711]) : $reasons;
        }

        /**
         * Static Detail Set
         * @return array
         */
        static public function en_static_request_detail()
        {
            return
                [
                    'serverName' => EN_USPS_SERVER_NAME,
                    'platform' => 'WordPress',
                    'carrierType' => 'small',
                    'carrierName' => 'usps',
                    'carrierMode' => 'pro',
                    'requestVersion' => '2.0',
                    'requestKey' => time(),
                ];
        }

        /**
         * Connection Settings Detail Set
         * @return array
         */
        static public function en_set_connection_settings_detail()
        {
            return
                [

                    'usps_small_licence_key' => [
                        'eniture_action' => 'licenseKey',
                        'name' => __('Eniture API Key ', 'woocommerce-settings-usps'),
                        'type' => 'text',
                        'desc' => __('Obtain a Eniture API Key from <a href="' . EN_USPS_ROOT_URL_PRODUCTS . '" target="_blank" >eniture.com </a>', 'woocommerce-settings-usps'),
                        'id' => 'usps_small_licence_key'
                    ],
                ];
        }

    }

}