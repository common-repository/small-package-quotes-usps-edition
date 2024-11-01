<?php

namespace EnUspsDistance;

use EnUspsCurl\EnUspsCurl;

if (!class_exists('EnUspsDistance')) {

    class EnUspsDistance
    {
        static public function get_address($map_address, $en_access_level, $en_destination_address = [])
        {
            $post_data = array(
                'acessLevel' => $en_access_level,
                'address' => $map_address,
                'originAddresses' => $map_address,
                'destinationAddress' => (isset($en_destination_address)) ? $en_destination_address : '',
                'eniureLicenceKey' => get_option('usps_small_licence_key'),
                'ServerName' => EN_USPS_SERVER_NAME,
            );

            return EnUspsCurl::en_usps_sent_http_request(EN_USPS_ADDRESS_HITTING_URL, $post_data, 'POST', 'Address');
        }
    }
}