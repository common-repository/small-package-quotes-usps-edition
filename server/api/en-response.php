<?php

/**
 * Customize the api response.
 */

namespace EnUspsResponse;

use EnUspsFdo\EnUspsFdo;
use EnUspsFilterQuotes\EnUspsFilterQuotes;
use EnUspsOtherRates\EnUspsOtherRates;
use EnUspsQuoteSettings\EnUspsQuoteSettings;
use EnUspsVersionCompact\EnUspsVersionCompact;

/**
 * Compile the rates.
 * Class EnUspsResponse
 * @package EnUspsResponse
 */
if (!class_exists('EnUspsResponse')) {

    class EnUspsResponse {

        static public $en_step_for_rates = [];
        static public $en_small_package_quotes = [];
        static public $en_step_for_sender_origin = [];
        static public $en_step_for_product_name = [];
        static public $en_quotes_info_api = [];
        static public $en_accessorial = [];
        static public $en_always_accessorial = [];
        static public $en_settings = [];
        static public $en_package = [];
        static public $en_origin_address = [];
        static public $en_is_shipment = '';
        static public $en_auto_residential_status = '';
        static public $en_hazardous_status = '';
        static public $rates;
        static public $bin_packaging_filtered = [];
        static public $bins = [];
        static public $box_fee = [];
        static public $box_product_quantity = [];
        static public $products = [];
        // FDO
        static public $fdo;
        static public $en_fdo_meta_data = [];

        /**
         * Address set for order widget
         * @param array $sender_origin
         * @return string
         */
        static public function en_step_for_sender_origin($sender_origin) {
            return $sender_origin['senderLocation'] . ": " . $sender_origin['senderCity'] . ", " . $sender_origin['senderState'] . " " . $sender_origin['senderZip'];
        }

        /**
         * filter detail for order widget detail
         * @param array $en_package
         * @param mixed $key
         */
        static public function en_save_detail_for_order_widget($en_package, $key) {
            // FDO
            self::$fdo = new EnUspsFdo();
            self::$en_fdo_meta_data = self::$fdo::en_cart_package($en_package, $key);
            if(isset($en_package['originAddress']) && isset($en_package['originAddress'][$key])) {
                self::$en_step_for_sender_origin = self::en_step_for_sender_origin($en_package['originAddress'][$key]);
            }

            self::$en_step_for_product_name = (isset($en_package['product_name'][$key])) ? $en_package['product_name'][$key] : [];
        }

        /**
         * Shipping rates
         * @param array $response
         * @param array $en_package
         * @return array
         */
        static public function en_rates($response, $en_package) {
            self::$rates = $instor_pickup_local_delivery = [];
            self::$en_package = $en_package;
            $en_response = (!empty($response) && is_array($response)) ? $response : [];
            $en_response = self::en_is_shipment_api_response($en_response);
            $autoResidentialSubscriptionExpired = $isHazmatLineItem = $autoResidentialStatus = '';
            extract(self::$en_quotes_info_api);

            self::$en_step_for_rates = self::$en_package;
            $receiver_country_code = self::en_sanitize_rate('receiverCountryCode', '');
            $domestic = EnUspsQuoteSettings::domestic_services();
            $international = EnUspsQuoteSettings::international_services();

            $domestic_services = $domestic_services_markup = $international_services = $international_services_markup = [];

            // Domestic services
            foreach ($domestic as $domestic_service_key => $domestic_service) {
                $id = (isset($domestic_service['id'])) ? $domestic_service['id'] : '';
                $name = (isset($domestic_service['name'])) ? $domestic_service['name'] : '';
                $request = (isset($domestic_service['request'])) ? $domestic_service['request'] : '';
                if (get_option($id) == 'yes') {
                    $domestic_services[$request] = $name;
                    $domestic_services_markup[$request] = get_option('en_usps_markup_' . $id);
                }
            }

            // International services
            foreach ($international as $international_service_key => $international_service) {
                $id = (isset($international_service['id'])) ? $international_service['id'] : '';
                $name = (isset($international_service['name'])) ? $international_service['name'] : '';
                $request = (isset($international_service['request'])) ? $international_service['request'] : '';
                if (get_option($id) == 'yes') {
                    $international_services[$request] = $name;
                    $international_services_markup[$request] = get_option('en_usps_markup_' . $id);
                }
            }

            $origin_address = self::en_sanitize_rate('originAddress', []);

            foreach ($en_response as $key => $value) {

                $senderCountryCode = (isset($origin_address[$key]['senderCountryCode'])) ? $origin_address[$key]['senderCountryCode'] : '';
                $us_possession_arr = ['PR', 'VI', 'UM', 'GU', 'CC', 'AS', 'PS', 'MP', 'FM'];
                $services = ((strtolower($receiver_country_code) == strtolower($senderCountryCode)) || (in_array($receiver_country_code, $us_possession_arr))) ? $domestic_services : $international_services;
                $markup = ((strtolower($receiver_country_code) == strtolower($senderCountryCode))  || (in_array($receiver_country_code, $us_possession_arr))) ? $domestic_services_markup : $international_services_markup;

                self::en_save_detail_for_order_widget(self::$en_package, $key);
                self::$en_step_for_rates = $value;

                $residential_detecion_flag = get_option("en_woo_addons_auto_residential_detecion_flag");
                $auto_renew_plan = get_option("auto_residential_delivery_plan_auto_renew");

                if (($auto_renew_plan == "disable") &&
                        ($residential_detecion_flag == "yes") && $autoResidentialSubscriptionExpired == 1) {
                    update_option("en_woo_addons_auto_residential_detecion_flag", "no");
                }

                (isset(self::$en_package['originAddress'][$key])) ? self::$en_origin_address = self::$en_package['originAddress'][$key] : '';

                self::$en_auto_residential_status = $autoResidentialStatus;

                if (isset(self::$en_package['productDetails'][$key])) {
                    extract(self::$en_package['productDetails'][$key]);
                    self::$en_hazardous_status = strtolower($isHazmatLineItem);
                }

                isset(self::$en_package['bins']) ? self::$bins = self::$en_package['bins'] : '';
                isset(self::$en_package['usps_bins']) ? self::$bins = self::$bins + self::$en_package['usps_bins'] : '';
                isset(self::$en_package['extra_widget_detail'][$key]['en_box_fee']) ? self::$box_fee = self::$en_package['extra_widget_detail'][$key]['en_box_fee'] : '';
                isset(self::$en_package['extra_widget_detail'][$key]['en_multi_box_qty']) ? self::$box_product_quantity = self::$en_package['extra_widget_detail'][$key]['en_multi_box_qty'] : '';
                isset(self::$en_package['extra_widget_detail'][$key]['products']) ? self::$products = self::$en_package['extra_widget_detail'][$key]['products'] : '';

                $instor_pickup_local_delivery = self::en_sanitize_rate('InstorPickupLocalDelivery', []);

                // Return empty quotes if any shipment contains error
                $severity = self::en_sanitize_rate('severity', '');
                if (is_string($severity) && strlen($severity) > 0 && strtolower($severity) == 'error') {
                    return [];
                }

                self::$bin_packaging_filtered = self::en_sanitize_rate('binPackagingData', []);
                $origin_level_markup = isset($en_package['originAddress'][$key]['origin_markup']) ? $en_package['originAddress'][$key]['origin_markup'] : 0;
                $product_level_markup = 0;
                $products = $en_package['commdityDetails'][$key];
                
                if (!empty($products)) {
                    foreach ($products as $pdct) {
                        $product_level_markup += !empty($pdct['markup']) ? floatval($pdct['markup']) : 0;
                    }
                }

                self::en_arrange_rates(self::en_sanitize_rate('q', []), $services, $markup, $origin_level_markup, $product_level_markup);
            }

            self::$rates = EnUspsOtherRates::en_extra_custom_services
                            (
                            $instor_pickup_local_delivery, self::$en_is_shipment, self::$en_origin_address, self::$rates, self::$en_settings
            );

            return self::$rates;
        }

        /**
         * Multi shipment query
         * @param array $en_rates
         * @param string $accessorial
         */
        static public function en_multi_shipment($en_rates, $accessorials) {
            $accessorial = 'en_usps_multi_shipment';
            $en_rates = (isset($en_rates) && (is_array($en_rates))) ? array_slice($en_rates, 0, 1) : [];
            $en_calculated_cost = array_sum(EnUspsVersionCompact::en_array_column($en_rates, 'cost'));

            // FDO
            $en_fdo_meta_data = [];
            if (!isset($en_rates['meta_data']) && !empty($en_rates) && is_array($en_rates)) {
                $rate = reset($en_rates);
                $en_fdo_meta_data[] = (isset($rate['meta_data']['en_fdo_meta_data'])) ? $rate['meta_data']['en_fdo_meta_data'] : [];
            }

            if (isset(self::$rates[$accessorial])) {
                self::$rates[$accessorial]['id'] = isset(self::$rates[$accessorial]['id']) ? self::$rates[$accessorial]['id'] : EnUspsFilterQuotes::rand_string();
                self::$rates[$accessorial]['cost'] += $en_calculated_cost;
                self::$rates[$accessorial]['min_prices'] = array_merge(self::$rates[$accessorial]['min_prices'], $en_rates);
                // FDO
                self::$rates[$accessorial]['en_fdo_meta_data'] = array_merge(self::$rates[$accessorial]['en_fdo_meta_data'], $en_fdo_meta_data);
            } else {
                self::$rates[$accessorial] = [
                    'id' => 'usps_' . $accessorial,
                    'label' => 'Shipping',
                    'cost' => $en_calculated_cost,
                    'label_sufex' => str_split($accessorials),
                    'min_prices' => $en_rates,
                    // FDO
                    'en_fdo_meta_data' => $en_fdo_meta_data,
                    'plugin_name' => EN_USPS_SHIPPING_NAME,
                    'plugin_type' => 'small',
                    'owned_by' => 'eniture'
                ];
            }
        }

        /**
         * Single shipment query
         * @param array $en_rates
         * @param string $accessorial
         */
        static public function en_single_shipment($en_rates, $accessorial) {
            self::$rates = array_merge(self::$rates, $en_rates);
        }

        /**
         * Sanitize the value from array
         * @param string $index
         * @param dynamic $is_not_matched
         * @return dynamic mixed
         */
        static public function en_sanitize_rate($index, $is_not_matched) {
            return (isset(self::$en_step_for_rates[$index])) ? self::$en_step_for_rates[$index] : $is_not_matched;
        }

        /**
         * There is single or multiple shipment
         * @param array $en_response
         */
        static public function en_is_shipment_api_response($en_response) {
            if (isset($en_response['quotesInfo'])) {
                self::$en_quotes_info_api = $en_response['quotesInfo'];
                unset($en_response['quotesInfo']);
            }
            self::$en_is_shipment = count($en_response) > 1 ? 'en_multi_shipment' : 'en_single_shipment';
            return $en_response;
        }

        /**
         * Get accessorials prices from api response
         * @param array $accessorials
         * @return array
         */
        static public function en_get_accessorials_prices($accessorials) {
            $surcharges = [];
            $mapp_surcharges = [
                'residentialFee' => 'R',
                'liftgateFee' => 'L',
            ];

            $accessorials = isset($accessorials) && is_array($accessorials) ? $accessorials : [];
            foreach ($accessorials as $key => $accessorial) {
                if (isset($mapp_surcharges[$key])) {
                    in_array($mapp_surcharges[$key], self::$en_always_accessorial) ?
                                    $accessorial = 0 : '';
                    self::$en_auto_residential_status == 'r' && $mapp_surcharges[$key] == 'R' ?
                                    $accessorial = 0 : '';
                    $surcharges[$mapp_surcharges[$key]] = $accessorial;
                }
            }

            return $surcharges;
        }

        /**
         * Filter quotes
         * @param array $rates
         */
        static public function en_arrange_rates($rates, $services, $markup, $origin_level_markup, $product_level_markup) {
            $en_rates = [];
            $en_sorting_rates = [];
            $en_count_rates = 0;
            $handling_fee = $en_settings_label = $rating_method = $hazardous_material = $transit_day_option = $transit_days = $delivery_estimate_option = '';
            $hazardous_ground_fee = $hazardous_international_fee = 0;
            self::$en_settings = json_decode(EN_USPS_SET_QUOTE_SETTINGS, true);
            self::$en_accessorial = json_decode(EN_USPS_ACCESSORIAL, true);
            self::$en_always_accessorial = json_decode(EN_USPS_ALWAYS_ACCESSORIAL, true);
            extract(self::$en_settings);

            // Eniture Debug Mood
            do_action("eniture_debug_mood", EN_USPS_NAME . " Settings ", self::$en_settings);
            do_action("eniture_debug_mood", EN_USPS_NAME . " Accessorials ", self::$en_accessorial);


            $usps_services = [
                'Ground Advantage' => [
                    'direct' => 'customBoxes',
                    'default' => '',
                ],
                'First-Class Package International Service' => [
                    'direct' => 'customBoxes',
                    'default' => '',
                ],
                'First Class Mail' => [
                    'direct' => 'customBoxes',
                    'default' => '',
                ],
                'Priority Mail' => [
                    'direct' => 'UPMB',
                    'default' => 'customBoxes',
                ],
                'Priority Mail International' => [
                    'direct' => 'UPMB',
                    'default' => 'customBoxes',
                ],
                'Priority Mail Express' => [
                    'direct' => 'UMEB',
                    'default' => 'customBoxes',
                ],
                'Priority Mail Flat Rate' => [
                    'direct' => 'UFLAT',
                    'default' => '',
                ],
                'Priority Mail International Flat Rate Box' => [
                    'direct' => 'UFLAT',
                    'default' => '',
                ],
            ];

            // Hazardous material settings
            $hazardous_material_option = apply_filters("usps_plans_suscription_and_features", 'hazardous_material');

            // Ground transit time restriction
            $transit_days_option = apply_filters("usps_plans_suscription_and_features", 'transit_days');

            foreach ($rates as $en_key => $en_rate) {
                if (!isset($services[$en_key])) {
                    continue;
                }

                self::$en_step_for_rates = $en_rate;

                $cust_total_net_charge = self::en_sanitize_rate('totalNetCharge', 0);

                // Product level markup
                if (!empty($product_level_markup)) {
                    $cust_total_net_charge = self::en_add_handling_fee($cust_total_net_charge, $product_level_markup);
                }
                
                // origin level markup
                if (!empty($origin_level_markup)) {
                    $cust_total_net_charge = self::en_add_handling_fee($cust_total_net_charge, $origin_level_markup);
                }

                // Service level markup
                $markup_fee = isset($markup[$en_key]) ? $markup[$en_key] : 0;
                $carrier_scac = self::en_sanitize_rate('serviceId', '');
                $cust_total_net_charge = self::en_add_handling_fee($cust_total_net_charge, $markup_fee);

                // Quote settings handle fee / markup
                $en_add_handling_fee = self::en_add_handling_fee($cust_total_net_charge, $handling_fee);

                if ($en_add_handling_fee > 0) {

                    $name_from_api = self::en_sanitize_rate('serviceName', '');
                    $name_from_api_formated = self::get_formated_api_service_name($name_from_api);
                    
                    $label = (!empty($name_from_api_formated)) ? $name_from_api_formated : $services[$en_key];

                    // Cut Off Time & Ship Date Offset
                    $transitDays = self::en_sanitize_rate('transitDays', '');
                    $calender_date = self::en_sanitize_rate('calenderDate', '');
                    $calender_days_in_transit = self::en_sanitize_rate('CalenderDaysInTransit', '');

                    if ($delivery_estimate_option == "delivery_date" && strlen($calender_date) > 0) {
                        $label .= ' ( Expected delivery by ' . date('Y-m-d', strtotime($calender_date)) . ')';
                    } elseif ($delivery_estimate_option == "delivery_days" && strlen($calender_days_in_transit) > 0) {
                        $correct_word = $calender_days_in_transit == 1 ? 'is' : 'are';
                        $label .= ' ( Estimated number of days until delivery ' . $correct_word . ' ' . $calender_days_in_transit . ' )';
                    }

                    // Ground transit time restriction
                    if (!is_array($transit_days_option) && strlen($transit_day_option) > 0 && $transit_days > 0 && $transitDays > 0 && ($carrier_scac == 'FEDEX_GROUND' || $carrier_scac == 'GROUND_HOME_DELIVERY')) {
                        $transit = self::en_sanitize_rate($transit_day_option, '');
                        if ($transit > 0 && $transit >= $transit_days) {
                            continue;
                        }
                    }

                    // make data for order widget detail
                    $meta_data['service_type'] = $label;
                    $meta_data['accessorials'] = EN_USPS_ALWAYS_ACCESSORIAL;
                    $meta_data['sender_origin'] = self::$en_step_for_sender_origin;
                    $meta_data['product_name'] = wp_json_encode(self::$en_step_for_product_name);

                    // FDO
                    $meta_data['en_fdo_meta_data'] = self::$en_fdo_meta_data;

                    // Bins functionality
                    $en_box_fee = 0;
                    $package_bins = self::$bins;
                    $en_box_fee_arr = self::$box_fee;
                    $en_multi_box_qty = self::$box_product_quantity;
                    $products = self::$products;

                    if (isset($en_box_fee_arr) && is_array($en_box_fee_arr) && !empty($en_box_fee_arr)) {
                        foreach ($en_box_fee_arr as $en_box_fee_key => $en_box_fee_value) {
                            $en_multi_box_quantity = (isset($en_multi_box_qty[$en_box_fee_key])) ? $en_multi_box_qty[$en_box_fee_key] : 0;
                            $en_box_fee += $en_box_fee_value * $en_multi_box_quantity;
                        }
                    }

                    $bin_packaging_filtered = self::$bin_packaging_filtered;
                    if (isset($bin_packaging_filtered) && !empty($bin_packaging_filtered)) {
                        if (isset($usps_services[$carrier_scac])) {
                            $direct = $default = '';
                            extract($usps_services[$carrier_scac]);

                            if (isset($bin_packaging_filtered[$direct])) {
                                $bin_packaging_filtered['bins_packed'] = $bin_packaging_filtered[$direct];
                            } else if (isset($bin_packaging_filtered[$default])) {
                                $bin_packaging_filtered['bins_packed'] = $bin_packaging_filtered[$default];
                            }
                        }
                    }

                    // Bin Packaging Box Fee|Product Title Start
                    $en_box_total_price = 0;
                    if (isset($bin_packaging_filtered['bins_packed']) && !empty($bin_packaging_filtered['bins_packed'])) {

                        foreach ($bin_packaging_filtered['bins_packed'] as $bins_packed_key => $bins_packed_value) {
                            $bin_data = (isset($bins_packed_value['bin_data'])) ? $bins_packed_value['bin_data'] : [];
                            $bin_items = (isset($bins_packed_value['items'])) ? $bins_packed_value['items'] : [];
                            $bin_id = (isset($bin_data['id'])) ? $bin_data['id'] : '';
                            $bin_type = (isset($bin_data['type'])) ? $bin_data['type'] : '';
                            $bins_detail = (isset($package_bins[$bin_id])) ? $package_bins[$bin_id] : [];
                            $en_box_price = (isset($bins_detail['box_price'])) ? $bins_detail['box_price'] : 0;
                            $en_box_total_price += $en_box_price;

                            foreach ($bin_items as $bin_items_key => $bin_items_value) {
                                $bin_item_id = (isset($bin_items_value['id'])) ? $bin_items_value['id'] : '';
                                $get_product_name = (isset($products[$bin_item_id])) ? $products[$bin_item_id] : '';
                                if ($bin_type == 'item') {
                                    $bin_packaging_filtered['bins_packed'][$bins_packed_key]['bin_data']['product_name'] = $get_product_name;
                                }

                                if (isset($bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key])) {
                                    $bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key]['product_name'] = $get_product_name;
                                }
                            }
                        }
                    }

                    $en_box_total_price += $en_box_fee;

                    // FDO
                    $meta_data['bin_packaging'] = wp_json_encode($bin_packaging_filtered);
                    $meta_data['en_fdo_meta_data']['bins'] = $package_bins;
                    $meta_data['en_fdo_meta_data']['bin_packaging'] = $bin_packaging_filtered;

                    // standard rate
                    $rate = [
                        'id' => $carrier_scac,
                        'label' => $label,
                        'cost' => $en_add_handling_fee + (float) $en_box_total_price,
                        'scac' => $carrier_scac,
                        'surcharges' => self::en_get_accessorials_prices(self::en_sanitize_rate('surcharges', '')),
                        'meta_data' => $meta_data,
                        'transit_days' => $transitDays,
                        'services_id' => self::get_service_id($carrier_scac),
                        'service_code' => $carrier_scac,
                        'plugin_name' => EN_USPS_SHIPPING_NAME,
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    ];

                    foreach (self::$en_accessorial as $key => $accessorial) {
                        $en_fliped_accessorial = array_flip($accessorial);

                        // When auto-rad detected
                        if (self::$en_auto_residential_status == 'r') {
                            $accessorial[] = 'R';
                        }

                        // When hazardous materials detected
                        if (!is_array($hazardous_material_option) && strtolower(self::$en_hazardous_status) == 'y') {
                            $accessorial[] = 'H';

                            $ground_service = $rate['scac'] == 'FEDEX_GROUND' || $rate['scac'] == 'GROUND_HOME_DELIVERY' ? true : false;
                            if (strlen($rate['scac']) > 0) {
                                $hazardous_material_fee = $ground_service ? $hazardous_ground_fee : $hazardous_international_fee;
                            }

                            $rate['cost'] = self::en_add_handling_fee
                                            (
                                            $rate['cost'], $hazardous_material_fee
                            );

                            if ($hazardous_material == 'yes' && !$ground_service) {
                                $rate = [];
                            }
                        }

                        if (empty($rate)) {
                            continue;
                        }

                        self::$en_step_for_rates = $rate;

                        $en_accessorial_charges = array_diff_key(self::en_sanitize_rate('surcharges', []), $en_fliped_accessorial);

                        $en_accessorial_type = implode('', $accessorial);
                        self::$en_step_for_rates = $en_rates[$en_accessorial_type][$en_count_rates] = $rate;

                        // Cost of the rates
                        $en_sorting_rates
                                [$en_accessorial_type]
                                [$en_count_rates]['cost'] = // Used for sorting of rates
                                $en_rates
                                [$en_accessorial_type]
                                [$en_count_rates]['cost'] = self::en_sanitize_rate('cost', 0) - array_sum($en_accessorial_charges);

                        $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['label_sufex'] = wp_json_encode($accessorial);
                        $en_rates[$en_accessorial_type][$en_count_rates]['label_sufex'] = $accessorial;
                        $en_rates[$en_accessorial_type][$en_count_rates]['id'] .= $en_accessorial_type;

                        // FDO
                        if (in_array('R', $accessorial)) {
                            $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true;
                        }

                        $calculated_rate = $en_rates[$en_accessorial_type][$en_count_rates];
                        $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data']['rate'] = [
                            'id' => $calculated_rate['id'],
                            'label' => $calculated_rate['label'],
                            'cost' => $calculated_rate['cost'],
                            'services_id' => self::get_service_id($carrier_scac),
                            'service_code' => $carrier_scac,
                            'plugin_name' => EN_USPS_SHIPPING_NAME,
                            'plugin_type' => 'small',
                            'owned_by' => 'eniture'
                        ];
                    }

                    $en_count_rates++;
                }
            }

            foreach ($en_rates as $accessorial => $usps_services) {
                (!empty($en_rates[$accessorial])) ? array_multisort($en_sorting_rates[$accessorial], SORT_ASC, $en_rates[$accessorial]) : $en_rates[$accessorial] = [];
                $en_is_shipment = self::$en_is_shipment;
                self::$en_is_shipment($en_rates[$accessorial], $accessorial);
            }
        }

        /**
         * Generic function to add handling fee in cost of the rate
         * @param float $price
         * @param float $en_handling_fee
         * @return float
         */
        static public function en_add_handling_fee($price, $en_handling_fee) {
            $handling_fee = 0;
            if ($en_handling_fee != '' && $en_handling_fee != 0) {
                if (strrchr($en_handling_fee, "%")) {

                    $percent = (float) $en_handling_fee;
                    $handling_fee = (float) $price / 100 * $percent;
                } else {
                    $handling_fee = (float) $en_handling_fee;
                }
            }

            $handling_fee = self::en_smooth_round($handling_fee);
            $price = (float) $price + $handling_fee;
            return $price;
        }

        /**
         * Round the cost of the quote
         * @param float type $val
         * @param int type $min
         * @param int type $max
         * @return float type
         */
        static public function en_smooth_round($val, $min = 2) {
            return number_format($val, $min, ".", "");
        }

        /**
         * Add static service IDs with resposne for all customer but  on the request of mgs4u
         */
        static function get_service_id($service_name){
            $service_array = [
                'First Class Mail' => 25,
                'Priority Mail Express' => 26,
                'Priority Mail' => 27,
                'Priority Mail Flat Rate' => 28,
                'Retail Ground' => 29,
                'Priority Mail International Express' => 30,
                'Priority Mail International' => 31,
                'Priority Mail International Flat Rate Box' => 32,
                'First-Class Package International Service' => 33,
                'Ground Advantage' => 34
            ];

            return (isset($service_array[$service_name])) ? $service_array[$service_name] : 0;
        }

        /**
         * This function formates service name, with returns from API
         */
        static function get_formated_api_service_name($name_from_api){
            if (str_contains($name_from_api, "<sup")) {
                $name_from_api = str_replace('<sup>®</sup>', "", $name_from_api);
                $name_from_api = (isset(explode('<', $name_from_api)[0])) ? explode('<', $name_from_api)[0] : $name_from_api;
            } else {
                $name_from_api = str_replace('&lt;sup&gt;&#174;&lt;/sup&gt;', "", $name_from_api);
                $name_from_api = (isset(explode('&', $name_from_api)[0])) ? explode('&', $name_from_api)[0] : $name_from_api;
            }

            return $name_from_api;
        }

    }

}
