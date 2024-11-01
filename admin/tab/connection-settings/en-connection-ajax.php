<?php

/**
 * Curl http request.
 */

namespace EnUspsTestConnection;

use EnUspsCurl\EnUspsCurl;

/**
 * Test connection request.
 * Class EnUspsTestConnection
 * @package EnUspsTestConnection
 */
if (!class_exists('EnUspsTestConnection')) {

    class EnUspsTestConnection {

        /**
         * Hook in ajax handlers.
         */
        public function __construct() {
            add_action('wp_ajax_nopriv_en_usps_test_connection', [$this, 'en_usps_test_connection']);
            add_action('wp_ajax_en_usps_test_connection', [$this, 'en_usps_test_connection']);
        }

        /**
         * Handle Connection Settings Ajax Request
         */
        public function en_usps_test_connection() {
            $en_post_data = [];
            if (isset($_POST['en_post_data']) && !empty($_POST['en_post_data'])) {
                $en_dollar_post = urldecode(base64_decode(sanitize_text_field($_POST['en_post_data'])));
                parse_str($en_dollar_post, $en_post_data);
            }

            $en_request_indexing = json_decode(EN_USPS_SET_CONNECTION_SETTINGS, true);
            $en_connection_request = json_decode(EN_USPS_GET_CONNECTION_SETTINGS, true);

            foreach ($en_post_data as $en_request_name => $en_request_value) {
                $en_connection_request[$en_request_indexing[$en_request_name]['eniture_action']] = $en_request_value;
            }

            $en_connection_request['carrierMode'] = 'test';

            $en_connection_request['receiverCity'] = 'Chicago';
            $en_connection_request['receiverState'] = 'IL';
            $en_connection_request['receiverZip'] = '60603';
            $en_connection_request['receiverCountryCode'] = 'US';

            $en_connection_request = apply_filters('en_usps_add_connection_request', $en_connection_request);

            echo EnUspsCurl::en_usps_sent_http_request(
                    EN_USPS_HITTING_API_URL, $en_connection_request, 'POST', 'Connection'
            );
            exit;
        }

    }

}