<?php

/**
 * App Name settings.
 */

namespace EnUspsQuoteSettingsDetail;

/**
 * Get and save settings.
 * Class EnUspsQuoteSettingsDetail
 * @package EnUspsQuoteSettingsDetail
 */
if (!class_exists('EnUspsQuoteSettingsDetail')) {

    class EnUspsQuoteSettingsDetail
    {
        static public $en_usps_accessorial = [];

        /**
         * Set quote settings detail
         */
        static public function en_usps_get_quote_settings()
        {
            $accessorials = [];
            $en_settings = json_decode(EN_USPS_SET_QUOTE_SETTINGS, EN_USPS_DECLARED_TRUE);
            $en_settings['residential_delivery'] == 'yes' ? $accessorials['residentialDelivery'] = EN_USPS_DECLARED_TRUE : "";


            return $accessorials;
        }

        /**
         * Set quote settings detail
         */
        static public function en_usps_always_accessorials()
        {
            $accessorials = [];
            $en_settings = self::en_usps_quote_settings();
            $en_settings['residential_delivery'] == 'yes' ? $accessorials[] = 'R' : "";

            return $accessorials;
        }

        /**
         * Set quote settings detail
         */
        static public function en_usps_quote_settings()
        {
            $usps_shipment_days = ['all', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            $shipment_days = [];
            foreach ($usps_shipment_days as $key => $day) {
                get_option('en_usps_' . $day . '_shipment') == 'yes' ? $shipment_days[] = $key : '';
            }
            $quote_settings = [
                // Cut Off Time & Ship Date Offset
                'delivery_estimate_option' => get_option('en_delivery_estimate_options_usps'),
                'cutt_off_time' => get_option('en_usps_cutt_off_time'),
                'fulfilment_offset_days' => get_option('en_usps_fulfilment_offset_days'),
                'shipment_days' => $shipment_days,
                // Ground transit time restriction
                'transit_days' => get_option('en_usps_transit_days'),
                'transit_day_option' => get_option('en_usps_transit_day_options'),
                // Hazardous material settings
                'hazardous_material' => get_option('en_usps_hazardous_material_settings'),
                'hazardous_ground_fee' => get_option('en_usps_hazardous_material_settings_ground_fee'),
                'hazardous_international_fee' => get_option('en_usps_hazardous_material_settings_international_fee'),
                'handling_fee' => get_option('en_usps_handling_fee'),
                'residential_delivery' => get_option('en_usps_residential_delivery'),
                'custom_error_message' => get_option('en_usps_checkout_error_message'),
                'custom_error_enabled' => get_option('en_usps_unable_retrieve_shipping'),
            ];

            return $quote_settings;
        }

        /**
         * Get quote settings detail
         * @param array $en_settings
         * @return array
         */
        static public function en_usps_compare_accessorial($en_settings)
        {
            self::$en_usps_accessorial[] = ['S'];
            return self::$en_usps_accessorial;
        }

    }

}