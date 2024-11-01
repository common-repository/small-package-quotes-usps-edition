<?php

/**
 * Package array of cart items.
 */

namespace EnUspsPackage;

use EnUspsDistance\EnUspsDistance;
use EnUspsProductDetail\EnUspsProductDetail;
use EnUspsQuoteSettings\EnUspsQuoteSettings;
use EnUspsReceiverAddress\EnUspsReceiverAddress;
use EnUspsWarehouse\EnUspsWarehouse;

/**
 * Get items detail from added product in cart|checkout page.
 * Class EnUspsPackage
 * @package EnUspsPackage
 */
if (!class_exists('EnUspsPackage')) {

    class EnUspsPackage {

        static public $post_id;
        static public $locations;
        static public $product_key_name;
        static public $origin_zip_code = '';
        static public $shipment_type = '';
        static public $get_minimum_warehouse = '';
        static public $instore_pickup_local_delivery = 0;
        static public $en_step_for_package = [];
        static public $en_request = [];
        static public $receiver_address = [];
        // Images for FDO
        static public $en_fdo_image_urls = [];

        /**
         * Get detail from added product in the cart|checkout page
         * @param array $package
         * @return array
         */
        static public function en_package_converter($package) {
            $product_detail_obj = new EnUspsProductDetail();
            $en_product_fields = $product_detail_obj->en_product_fields_arr();
            // micro warehouse
            $dropship_list = $product_detail_obj->en_dropship_list();
            // cart|checkout receiver address
            self::$receiver_address = EnUspsReceiverAddress::get_address();
            self::$en_request['includeDeclaredValue'] = 0;
            $insurance_plan_status = apply_filters("usps_plans_suscription_and_features", 'insurance');
            foreach ($package['contents'] as $key => $product) {
                if (isset($product['data'])) {

                    $product_data = $product['data'];
                    $p_height = str_replace( array( "'",'"' ),'',$product_data->get_height());
                    $p_width = str_replace( array( "'",'"' ),'',$product_data->get_width());
                    $p_length = str_replace( array( "'",'"' ),'',$product_data->get_length());
                    $height = is_numeric($p_height) ? $p_height : 0;
                    $width = is_numeric($p_width) ? $p_width : 0;
                    $length = is_numeric($p_length) ? $p_length : 0;
                    // Images for FDO
                    self::en_fdo_image_urls($product, $product_data);

                    $shipping_class = $product_data->get_shipping_class();
                    if(!empty($shipping_class) && $shipping_class == "do-not-ship-usps"){
                        return [];
                    }

                    $terms = get_the_terms($product['product_id'], 'product_tag');
                    $product_tag = self::usps_get_tag($terms);
                    
                    $dimension_unit = strtolower(get_option('woocommerce_dimension_unit'));
                    $calculate_dimension = [
                        'ft' => 12,
                        'cm' => 0.3937007874,
                        'mi' => 63360,
                        'km' => 39370.1,
                    ];

                    switch ($dimension_unit) {
                        case (isset($calculate_dimension[$dimension_unit])):
                            $get_height = round($height * $calculate_dimension[$dimension_unit], 2);
                            $get_length = round($length * $calculate_dimension[$dimension_unit], 2);
                            $get_width = round($width * $calculate_dimension[$dimension_unit], 2);
                            break;
                        default;
                            $get_height = wc_get_dimension($height, 'in');
                            $get_length = wc_get_dimension($length, 'in');
                            $get_width = wc_get_dimension($width, 'in');
                            break;
                    }

                    $product_detail = [];

                    $product_title = str_replace(array("'", '"'), '', $product_data->get_title());
                    // Get product level markup value
                    $product_level_markup = self::en_get_product_level_markup($product_data, $product['variation_id'], $product['product_id'], $product['quantity']);

                    self::$post_id = (isset($product['variation_id']) && $product['variation_id'] > 0) ?
                        $product['variation_id'] : $product_data->get_id();
                    $parent_id = self::$post_id;
                    if(isset($product['variation_id']) && $product['variation_id'] > 0){
                        $variation = wc_get_product($product['variation_id']);
                        $parent_id = $variation->get_parent_id();
                        $product_title = $variation->get_name();
                    }

                    $isInsuranceIctive = 0;
                    if(is_bool($insurance_plan_status) === true && $insurance_plan_status){
                        $insurance_flag = maybe_unserialize(get_post_meta(self::$post_id, '_en_insurance_fee', true));
                        if(!empty($insurance_flag) && $insurance_flag == 'yes'){
                            if(empty(self::$en_request['includeDeclaredValue'])){
                                self::$en_request['includeDeclaredValue'] = 1;
                            }
                            $isInsuranceIctive = 1;
                        }
                    }

                    $product_item = [
                        'productId' => $parent_id,
                        'lineItemHeight' => $get_height,
                        'lineItemLength' => $get_length,
                        'lineItemWidth' => $get_width,
                        'lineItemWeight' => wc_get_weight($product_data->get_weight(), 'lbs'),
                        'piecesOfLineItem' => $product['quantity'],
                        'productQty' => $product['quantity'],
                        'lineItemPrice' => $product_data->get_price(),
                        // FDO
                        'productId' => self::$post_id,
                        'productType' => ($product_data->get_type() == 'variation') ? 'variant' : 'simple',
                        'productSku' => $product_data->get_sku(),
                        'attributes' => $product_data->get_attributes(),
                        'productName' => str_replace(array("'", '"'), '', $product_data->get_name()),
                        'variantId' => ($product_data->get_type() == 'variation') ? $product_data->get_id() : '',
                        'isInsuranceActive' => $isInsuranceIctive,
                        'lineItemSlug' => $product_tag,
                        'markup' => $product_level_markup
                    ];

                    $product_weight = $product_item['lineItemWeight'];
                    $product_quantity = $product_item['piecesOfLineItem'];

                    $shipment_type = EN_USPS_DECLARED_FALSE;
                    $origin_zip_code = EN_USPS_DECLARED_ZERO;
                    // Micro Warehouse
                    $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                    if (stripos(implode($all_plugins), 'micro-warehouse-shipping.php')  || is_plugin_active_for_network('micro-warehouse-shipping-for-woocommerce/micro-warehouse-shipping.php')) {
                        $enable_dropship = maybe_unserialize(get_post_meta(self::$post_id, '_enable_dropship', true));
                        $dropship_arr = [];
                        $loc_checkbox = [];
                        if($enable_dropship == 'yes') {
                            $dropship_arr[] = ['type' => 'dropdown', 'id' => '_dropship_location', 'plans' => 'multi_dropships', 'line_item' => 'locationId'];
                            $loc_checkbox[] = ['type' => 'checkbox', 'id' => '_enable_dropship', 'plans' => 'multi_dropships', 'line_item' => 'location'];
                            foreach($en_product_fields as $duplicate_id) {
                                if($duplicate_id['id'] == '_dropship_location') {
                                    $dropship_arr = [];
                                }
                                if($duplicate_id['id'] == '_enable_dropship') {
                                    $loc_checkbox = [];
                                }
                            }

                            $dropship_arr = array_merge($loc_checkbox, $dropship_arr);

                            $en_product_fields = array_merge($dropship_arr, $en_product_fields);
                        }else {
                            $dropship_arr[] = ['type' => 'dropdown', 'id' => '_dropship_location', 'plans' => 'multi_dropships', 'line_item' => 'locationId', 'options' => $dropship_list];
                            $loc_checkbox[] = ['type' => 'checkbox', 'id' => '_enable_dropship', 'plans' => 'multi_dropships', 'line_item' => 'location'];
                            foreach($en_product_fields as $duplicate_id) {
                                if($duplicate_id['id'] == '_dropship_location') {
                                    $dropship_arr = [];
                                }
                                if($duplicate_id['id'] == '_enable_dropship') {
                                    $loc_checkbox = [];
                                }
                            }

                            $dropship_arr = array_merge($loc_checkbox, $dropship_arr);

                            $en_product_fields = array_merge($dropship_arr, $en_product_fields);
                        }
                    }
                    foreach ($en_product_fields as $en_field_key => $en_custom_product) {
                        self::$en_step_for_package = $en_custom_product;
                        self::$product_key_name = self::en_sanitize_package('id', '');
                        $en_function_trigger = 'en_product_' . self::en_sanitize_package('type', '');
                        $is_line_item = self::en_sanitize_package('line_item', '');
                        $is_plans = self::en_sanitize_package('plans', '');

                        if (is_callable(array(__CLASS__, $en_function_trigger)) && strlen($is_line_item) > EN_USPS_DECLARED_ZERO) {
                            $en_location_value = self::$en_function_trigger();
                            $en_location_value = is_string($en_location_value) && $en_location_value == 'yes' ? 'Y' : $en_location_value;
                            $suscription_and_features = apply_filters(
                                    "usps_plans_suscription_and_features", $is_plans
                            );

                            if (is_array($suscription_and_features)) {
                                $en_location_value = 'no';
                            }

                            if (!is_array($en_location_value) && strlen($en_location_value) > EN_USPS_DECLARED_ZERO) {
                                $product_item[$is_line_item] = $en_location_value;
                                strtolower($en_location_value) == 'y' ? $product_detail[$is_line_item] = $en_location_value : '';
                            }

                            if (isset($en_location_value['senderZip']) &&
                                    is_array($en_location_value) &&
                                    $en_function_trigger = 'en_product_dropdown') {

                                $origin_address = $en_location_value;
                                $origin_zip_code = $en_location_value['senderLocation'] . '-' . $en_location_value['senderZip'];

                                $total_weight = $product_weight * $product_quantity;
                                $shipment_weight = (isset(self::$en_request['shipment_weight'][$origin_zip_code])) ?
                                        self::$en_request['shipment_weight'][$origin_zip_code] : 0;

                                $shipment_weight += $total_weight;

                                switch (EN_USPS_DECLARED_TRUE) {
                                    case $shipping_class == 'ltl_freight':
                                        $shipment_type = EN_USPS_DECLARED_TRUE;
                                        self::en_set_ltl_shipment($origin_zip_code);
                                        self::$en_request['LTL_FREIGHT'] = EN_USPS_DECLARED_ONE;
                                        break;
                                    case $shipment_weight > EN_USPS_SHIPMENT_WEIGHT_EXCEEDS_PRICE &&
                                    EN_USPS_SHIPMENT_WEIGHT_EXCEEDS == 'yes':
                                        self::en_set_ltl_shipment($origin_zip_code);
                                        $shipment_type = EN_USPS_DECLARED_TRUE;
                                        $product_weight < EN_USPS_SHIPMENT_WEIGHT_EXCEEDS_PRICE ?
                                                        self::$en_request['shipment_type']['LTL_SMALL'][$origin_zip_code]['SMALL'] = EN_USPS_DECLARED_TRUE : "";
                                        break;
                                    case $product_weight < EN_USPS_SHIPMENT_WEIGHT_EXCEEDS_PRICE:
                                        $shipment_type = EN_USPS_DECLARED_TRUE;
                                        self::en_set_small_shipment($origin_zip_code);
                                        break;
                                    default:
                                }
                            }
                        }
                    }

                    self::$shipment_type = $shipment_type;
                    self::$origin_zip_code = $origin_zip_code;
                    add_filter('en_usps_reason_quotes_not_returned', [__CLASS__, 'en_usps_reason_quotes_not_returned'], 99, 1);

                    if ($shipment_type && strlen($origin_zip_code) > 0) {
                        self::$en_request['product_name'][$origin_zip_code][] = $product_quantity . " x " . $product_data->get_title();

                        self::$en_request['shipment_weight'][$origin_zip_code] = $shipment_weight;
                        self::$en_request['commdityDetails'][$origin_zip_code][] = $product_item;

                        if (isset(self::$en_request['productDetails'][$origin_zip_code]['isHazmatLineItem']) && self::$en_request['productDetails'][$origin_zip_code]['isHazmatLineItem'] == 'Y') {
                            $product_detail['isHazmatLineItem'] = 'Y';
                        }

                        self::$en_request['productDetails'][$origin_zip_code] = $product_detail;
                        self::$en_request['originAddress'][$origin_zip_code] = $origin_address;

                        // Product tags
                        $product_tags = get_the_terms($product['product_id'], 'product_tag');
                        if (!empty($product_tags)) {
                            $product_tag_names = array_map(function($tag) { return $tag->term_id; }, $product_tags);

                            if (isset(self::$en_request['product_tags'][$origin_zip_code])) {
                                self::$en_request['product_tags'][$origin_zip_code] = array_merge(self::$en_request['product_tags'][$origin_zip_code], $product_tag_names);
                            } else {
                                self::$en_request['product_tags'][$origin_zip_code] = $product_tag_names;
                            }
                        } else {
                            self::$en_request['product_tags'][$origin_zip_code] = [];
                        }

                        // Product quantities
                        if (isset(self::$en_request['product_quantities'][$origin_zip_code])) {
                            self::$en_request['product_quantities'][$origin_zip_code] += floatval($product['quantity']);
                        } else {
                            self::$en_request['product_quantities'][$origin_zip_code] = floatval($product['quantity']);
                        }

                        // Product prices
                        if (isset(self::$en_request['product_prices'][$origin_zip_code])) {
                            self::$en_request['product_prices'][$origin_zip_code] += floatval($product_data->get_price() * floatval($product['quantity']));
                        } else {
                            self::$en_request['product_prices'][$origin_zip_code] = floatval($product_data->get_price() * floatval($product['quantity']));
                        }
                    }
                }
            }

            return self::en_filter_shipment();
        }

        /**
         * Set images urls | Images for FDO
         * @param array type $en_fdo_image_urls
         * @return array type
         */
        static public function en_fdo_image_urls_merge($en_fdo_image_urls) {
            return array_merge(self::$en_fdo_image_urls, $en_fdo_image_urls);
        }

        /**
         * Get images urls | Images for FDO
         * @param array type $values
         * @param array type $product_data
         * @return array type
         */
        static public function en_fdo_image_urls($values, $product_data) {
            $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $product_data->get_id();
            $gallery_image_ids = $product_data->get_gallery_image_ids();
            foreach ($gallery_image_ids as $key => $image_id) {
                $gallery_image_ids[$key] = $image_id > 0 ? wp_get_attachment_url($image_id) : '';
            }

            $image_id = $product_data->get_image_id();
            self::$en_fdo_image_urls[$product_id] = [
                'product_id' => $product_id,
                'image_id' => $image_id > 0 ? wp_get_attachment_url($image_id) : '',
                'gallery_image_ids' => $gallery_image_ids
            ];

            add_filter('en_fdo_image_urls_merge', [__CLASS__, 'en_fdo_image_urls_merge'], 10, 1);
        }

        /**
         * Saving reasons to show proper error message on the cart or checkout page
         * When quotes are not returning
         * @param array $reasons
         * @return array
         */
        static public function en_usps_reason_quotes_not_returned($reasons) {
            $reasons = !self::$shipment_type ? array_merge($reasons, [EN_USPS_712]) : $reasons;
            return (!self::$origin_zip_code > 0) ? array_merge($reasons, [EN_USPS_713]) : $reasons;
        }

        /**
         * Filter shipment
         * @return array
         */
        static public function en_filter_shipment() {
            if (isset(self::$en_request['shipment_type']) && !empty(self::$en_request['shipment_type'])) {
                self::$en_request = array_merge(self::$en_request, self::$receiver_address);
                self::$en_request['instorPickupLocalDelEnable'] = self::$instore_pickup_local_delivery;

                self::$en_request['shipment_type'] = (isset(self::$en_request['LTL_FREIGHT'])) ?
                        self::$en_request['shipment_type']['LTL_FREIGHT'] :
                        self::$en_request['shipment_type']['LTL_SMALL'];
            }

            // Configure standard plugin with RAD addon
            self::$en_request = apply_filters("en_woo_addons_carrier_service_quotes_request", self::$en_request, EN_USPS_SHIPPING_NAME);

            // Configure standard plugin with SBS addon
            if (has_filter('enit_box_sizes_post_array_filter')) {
                self::$en_request = apply_filters('enit_box_sizes_post_array_filter_new_api', self::$en_request);
            }

            // Add the settings from quote page
            self::en_usps_quote_settings_detail();

            return self::$en_request;
        }

        /**
         * Set shipment is ltl in request
         * @param sring $origin_zip_code
         */
        static public function en_set_ltl_shipment($origin_zip_code) {
            self::$en_request['shipment_type']['LTL_FREIGHT'][$origin_zip_code]['LTL'] = self::$en_request['shipment_type']['LTL_SMALL'][$origin_zip_code]['LTL'] = EN_USPS_DECLARED_TRUE;
        }

        /**
         * Set shipment is small in request
         * @param string $origin_zip_code
         */
        static public function en_set_small_shipment($origin_zip_code) {
            self::$en_request['shipment_type']['LTL_FREIGHT'][$origin_zip_code]['SMALL'] = self::$en_request['shipment_type']['LTL_SMALL'][$origin_zip_code]['SMALL'] = EN_USPS_DECLARED_TRUE;
        }

        /**
         * Handle sender locations
         * @param array $location
         * @return array|false
         */
        static public function en_usps_sender_location_address($location) {
            $location['senderAddressLine'] = '';
            $location['origin_markup'] = isset($location['origin_markup']) ? $location['origin_markup'] : '';
            $selection_for_address = [
                'id' => 'id',
                'city' => 'senderCity',
                'state' => 'senderState',
                'zip' => 'senderZip',
                'country' => 'senderCountryCode',
                'location' => 'senderLocation',
                'origin_markup' => 'origin_markup',
                'senderAddressLine' => 'senderAddressLine',
            ];

            // Get result
            $sender_location_address = array_combine($selection_for_address, array_intersect_key($location, $selection_for_address));
            return self::en_is_instore_pickup_enabled($location, $sender_location_address);
        }

        /**
         * instore pickup enabled or not against warehouse|dropship
         * @param array $location
         * @param array $sender_location_address
         * @return array
         */
        static public function en_is_instore_pickup_enabled($location, $sender_location_address) {
            $phone_instore = $address = $enable_store_pickup = $enable_local_delivery = $miles_store_pickup = $miles_local_delivery = $suppress_local_delivery = $checkout_desc_store_pickup = $checkout_desc_local_delivery = $fee_local_delivery = '';
            $match_postal_store_pickup = $match_postal_local_delivery = $instore_pickup_local_delivery = [];

            $suscription_and_features = apply_filters(
                    "usps_plans_suscription_and_features", 'instore_pickup_local_delivery'
            );

            if (!is_array($suscription_and_features)) {
                extract($location);

                $instore_pickup_local_delivery['senderDescInStorePickup'] = $checkout_desc_store_pickup;
                $instore_pickup_local_delivery['senderDescLocalDelivery'] = $checkout_desc_local_delivery;
                $instore_pickup_local_delivery['suppressOtherRates'] = $suppress_local_delivery;
                $instore_pickup_local_delivery['feeLocalDelivery'] = $fee_local_delivery;
                $instore_pickup_local_delivery['address'] = $address;
                $instore_pickup_local_delivery['phone_instore'] = $phone_instore;

                $receiver_zip = (isset(self::$receiver_address['receiverZip'])) ?
                        self::$receiver_address['receiverZip'] : 0;

                if ($enable_store_pickup == 'on') {
                    self::$instore_pickup_local_delivery = 1;
                    $match_postal_store_pickup = strlen($match_postal_store_pickup) > 0 ?
                            explode(",", $match_postal_store_pickup) : [];

                    $instore_pickup_local_delivery['inStorePickup']['addressWithInMiles'] = $miles_store_pickup;

                    $instore_pickup_local_delivery['inStorePickup']['postalCodeMatch'] = (in_array($receiver_zip, $match_postal_store_pickup)) ? 1 : 0;
                }

                if ($enable_local_delivery == 'on') {
                    self::$instore_pickup_local_delivery = 1;
                    $match_postal_local_delivery = strlen($match_postal_local_delivery) > 0 ?
                            explode(",", $match_postal_local_delivery) : [];

                    $instore_pickup_local_delivery['localDelivery']['addressWithInMiles'] = $miles_local_delivery;
                    $instore_pickup_local_delivery['localDelivery']['suppressOtherRates'] = $suppress_local_delivery == 'on' ? 1 : 0;

                    $instore_pickup_local_delivery['localDelivery']['postalCodeMatch'] = (in_array($receiver_zip, $match_postal_local_delivery)) ? 1 : 0;
                }
                !empty($instore_pickup_local_delivery) ? $sender_location_address = array_merge($sender_location_address, $instore_pickup_local_delivery) : '';
            }

            return $sender_location_address;
        }

        /**
         * Handle receiver locations
         * @param array $location
         * @return array|false
         */
        static public function en_usps_receiver_location_address($receiver_address) {
            $selection_for_receiver_address = [
                'receiverZip' => 'zip',
                'receiverState' => 'state',
                'receiverCountryCode' => 'country',
                'receiverCity' => 'city',
            ];

            // Get result
            return array_combine($selection_for_receiver_address, array_intersect_key($receiver_address, $selection_for_receiver_address)
            );
        }

        /**
         * Quote settings
         * @param array $location
         * @return array|false
         */
        static public function en_usps_quote_settings_detail() {
            $delivery_estimate_option = $cutt_off_time = $fulfilment_offset_days = '';
            $active_services = $settings = $shipment_days = [];
            $feature_option = apply_filters("usps_plans_suscription_and_features", 'cutt_off_time');
            $en_settings = json_decode(EN_USPS_SET_QUOTE_SETTINGS, true);
            extract($en_settings);
            if (!is_array($feature_option) && ($delivery_estimate_option == 'delivery_days' || $delivery_estimate_option == 'delivery_date')) {
                $settings = [
                    'modifyShipmentDateTime' => strlen($cutt_off_time) > 0 && strlen($fulfilment_offset_days) > 0 && !empty($shipment_days) ? true : false,
                    'OrderCutoffTime' => $cutt_off_time,
                    'shipmentOffsetDays' => $fulfilment_offset_days,
                    'storeDateTime' => date('Y-m-d H:i:s'),
                    'shipmentWeekDays' => $shipment_days,
                ];
            }

            $domestic = EnUspsQuoteSettings::domestic_services();
            $international = EnUspsQuoteSettings::international_services();

            // Domestic services
            foreach ($domestic as $domestic_service_key => $domestic_service) {
                $id = (isset($domestic_service['id'])) ? $domestic_service['id'] : '';
                if (get_option($id) == 'yes') {
                    $name = (isset($domestic_service['name'])) ? $domestic_service['name'] : '';
                    $active_services['domestic'][] = $name;
                }
            }

            // International services
            foreach ($international as $international_service_key => $international_service) {
                $id = (isset($international_service['id'])) ? $international_service['id'] : '';
                if (get_option($id) == 'yes') {
                    $name = (isset($international_service['name'])) ? $international_service['name'] : '';
                    $active_services['international'][] = $name;
                }
            }

            $settings['activeServices'] = $active_services;
            $settings['rateTier'] = get_option('en_usps_rate_tier_dropdown');
            // Sbs optimization mode
            $settings['sbsMode'] = get_option('box_sizing_optimization_mode');
            self::$en_request = array_merge(self::$en_request, $settings);
        }

        /**
         * When minimum warehouse exist
         * @param string $location_id
         * @return array|false|string
         */
        static public function en_usps_get_location($location_id = '') {
            $en_where_clause = ['location' => 'warehouse'];
            // Micro Warehouse
            $location_array = [];
            $location_array['id'] = '';
            $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (stripos(implode($all_plugins), 'micro-warehouse-shipping.php')  || is_plugin_active_for_network('micro-warehouse-shipping-for-woocommerce/micro-warehouse-shipping.php')) {
                $locations_dropship = maybe_unserialize(get_post_meta(self::$post_id, '_dropship_location', true));
                $location_array['id'] = $locations_dropship;
                if(!empty($locations_dropship)) {
                    $en_where_clause = ['location' => 'dropship'];
                }
            }
            if(empty($location_array['id'])) {
                $location_id = strlen($location_id) > 0 ? maybe_unserialize($location_id) : $location_id;
            }else {
                if (stripos(implode($all_plugins), 'micro-warehouse-shipping.php')  || is_plugin_active_for_network('micro-warehouse-shipping-for-woocommerce/micro-warehouse-shipping.php')) {
                    $en_where_clause = $location_array;
                }
            }
            if (isset($location_id) && !empty($location_id)) {
                $en_where_clause = ['id' => $location_id];
            }
            $en_location = EnUspsWarehouse::get_data($en_where_clause);
            // Micro Warehouse
            if (stripos(implode($all_plugins), 'micro-warehouse-shipping.php')  || is_plugin_active_for_network('micro-warehouse-shipping-for-woocommerce/micro-warehouse-shipping.php')) {
                if (!empty($en_location) && is_array($en_location)) {
                    foreach ($en_location as $drops_index => $drops) {
                        if (!empty($locations_dropship) && is_array($locations_dropship) && !in_array($drops['id'], $locations_dropship)) {
                            unset($en_location[$drops_index]);
                        }
                    }
                }
            }
            if (!empty($en_location) && is_array($en_location)) {
                if (count($en_location) == 1) {
                    return self::en_usps_sender_location_address(reset($en_location));
                } else {
                    $en_access_level = 'MultiDistance';
                    // receiver address
                    $receiver_address = self::$receiver_address;

                    $receiver_address = self::en_usps_receiver_location_address($receiver_address);
                    $get_address = json_decode(
                            EnUspsDistance::get_address($en_location, $en_access_level, $receiver_address), true);

                    return (isset($get_address['origin_with_min_dist']) && !empty($get_address['origin_with_min_dist'])) ?
                            self::en_usps_sender_location_address($get_address['origin_with_min_dist']) : [];
                }
            }

            return [];
        }

        /**
         * Sanitize the value from array
         * @param string $index
         * @param dynamic $is_not_matched
         * @return dynamic mixed
         */
        static public function en_sanitize_package($index, $is_not_matched) {
            return (isset(self::$en_step_for_package[$index])) ? self::$en_step_for_package[$index] : $is_not_matched;
        }

        /**
         * is checkbox is checked or not against post id
         */
        static public function en_product_checkbox() {
            switch (self::$product_key_name) {
                case '_enable_dropship':
                    $enable_dropship = get_post_meta(self::$post_id, self::$product_key_name, true);
                    switch ($enable_dropship) {
                        case 'yes':
                            return 'dropship';
                        default:
                            return 'warehouse';
                    }

                    break;
                default:
                    return get_post_meta(self::$post_id, self::$product_key_name, true);
            }
        }

        /**
         * is checkbox is checked or not against post id
         */
        static public function en_product_dropdown() {
            switch (self::$product_key_name) {
                case '_dropship_location':
                    $enable_dropship = get_post_meta(self::$post_id, '_enable_dropship', true);
                    switch ($enable_dropship) {
                        case 'yes':
                            // Micro Warehouse
                            $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));
                            if (stripos(implode($all_plugins), 'micro-warehouse-shipping.php')  || is_plugin_active_for_network('micro-warehouse-shipping-for-woocommerce/micro-warehouse-shipping.php')) {
                                $locations_dropship = maybe_unserialize(get_post_meta(self::$post_id, '_dropship_location', true));
                                if(!empty($locations_dropship)) {
                                    self::$get_minimum_warehouse = self::en_usps_get_location();
                                    return self::$get_minimum_warehouse;
                                }
                            }
                            return self::en_usps_get_location(get_post_meta(self::$post_id, self::$product_key_name, true));
                        default:
                            self::$get_minimum_warehouse = self::en_usps_get_location();
                            return self::$get_minimum_warehouse;
                    }
                    break;
                default:
                    return get_post_meta(self::$post_id, self::$product_key_name, true);
            }
        }

        /**
         * Returns Product Tags
         */
        public static function usps_get_tag($terms)
        {
            if (!empty($terms) && !is_wp_error($terms)) {
                $nikname = self::usps_get_bins();

                foreach ($terms as $key => $term) {
                    $product_tag = trim(strtolower($term->name));
                    $matched_term = in_array($product_tag, $nikname);

                    if ($matched_term) {
                        return $term->name;
                    }
                }
            }

            return '';
        }

        /**
         * Returns Bins
         */
        public static function usps_get_bins()
        {
            $nikname = array();

            $args = array(
                'post_type' => 'box_sizing',
                'posts_per_page' => -1,
                'post_status' => 'any'
            );

            $posts_array = get_posts($args);

            if ($posts_array) {
                foreach ($posts_array as $post) {
                    $status = get_post_field('post_content', $post->ID);
                    if ($status == "Yes") { /* If box available */
                        $box_nickname = trim(strtolower(get_post_field('post_title', $post->ID)));
                        $hyphens = substr_count($box_nickname, '-');
                        $spaces = strrpos($box_nickname, " ");
                        (isset($box_nickname) && $hyphens > 0 && $spaces == FALSE) ? $nikname[] = $box_nickname : '';
                    }
                }
            }
            return $nikname;
        }

         /**
        * Returns product level markup
        */
        public static function en_get_product_level_markup($_product, $variation_id, $product_id, $quantity)
        {
            $product_level_markup = 0;
            
            if ($_product->get_type() == 'variation') {
                $product_level_markup = get_post_meta($variation_id, '_en_product_markup_variation', true);
                if(empty($product_level_markup) || $product_level_markup == 'get_parent'){
                    $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
                }
            } else {
                $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
            }
            
            if (empty($product_level_markup)) {
                $product_level_markup = get_post_meta($product_id, '_en_product_markup', true);
            }

            if (!empty($product_level_markup) && strpos($product_level_markup, '%') === false 
            && is_numeric($product_level_markup) && is_numeric($quantity))
            {
                $product_level_markup *= $quantity;
            } else if(!empty($product_level_markup) && strpos($product_level_markup, '%') > 0 && is_numeric($quantity)){
                $position = strpos($product_level_markup, '%');
                $first_str = substr($product_level_markup, $position);
                $arr = explode($first_str, $product_level_markup);
                $percentage_value = $arr[0];
                $product_price = $_product->get_price();
    
                if (!empty($product_price)) {
                    $product_level_markup = $percentage_value / 100 * ($product_price * $quantity);
                } else {
                    $product_level_markup = 0;
                }
            }
    
            return $product_level_markup;
        }

        /**
        * Applies shipping rules
        */
        public static function apply_shipping_rules($usps_small_package)
        {
            if (empty($usps_small_package)) return false;
    
            global $wpdb;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_usps_small_shipping_rules"; 
            $rules = $wpdb->get_results($qry, ARRAY_A);
            
            if (empty($rules)) return false;
           
            $is_rule_applied = false;
            foreach ($rules as $rule) {
                if (!$rule['is_active']) continue;
    
                $settings = isset($rule['settings']) ? json_decode($rule['settings'], true) : [];
                if (empty($settings)) continue;
    
                $is_rule_applied = self::apply_rule($settings, $usps_small_package);
                if ($is_rule_applied) break;
            }
    
            return $is_rule_applied;
        }
    
        /**
        * Returns applied rule status
        */
        public static function apply_rule($settings, $usps_small_package)
        {
            $is_rule_applied = false;
    
            $origin_addresses = isset($usps_small_package['originAddress']) ? array_keys($usps_small_package['originAddress']) : [];

            if ($settings['apply_to'] == 'cart') {
                $formatted_values = self::get_formatted_values($origin_addresses, $usps_small_package);
                $is_rule_applied = self::apply_rule_filters($settings, $formatted_values);
            } else {
                foreach ($origin_addresses as $key => $origin_id) {
                    $is_rule_applied = false;
                    $shipments = [];
                    $shipments[$key] = $origin_id;
    
                    $formatted_values = self::get_formatted_values($shipments, $usps_small_package);
                    $is_rule_applied = self::apply_rule_filters($settings, $formatted_values);

                    if ($is_rule_applied) break;
                }
            }
    
            return $is_rule_applied;
        }
    
        /**
        * Returns combined formatted values of shipments
        */
        public static function get_formatted_values($origin_addresses, $usps_small_package)
        {
            $formatted_values = ['weight' => 0, 'price' => 0, 'quantity' => 0, 'tags' => []];
    
            foreach ($origin_addresses as $origin) {
                $formatted_values['weight'] += floatval($usps_small_package['shipment_weight'][$origin]);
                $formatted_values['price'] += floatval($usps_small_package['product_prices'][$origin]);
                $formatted_values['quantity'] += floatval($usps_small_package['product_quantities'][$origin]);
                $formatted_values['tags'] = array_merge($formatted_values['tags'], $usps_small_package['product_tags'][$origin]);
            }
    
            return $formatted_values;
        }
    
        /**
        * Applies all filters and return status
        */
        public static function apply_rule_filters($settings, $formatted_values)
        {
            $is_filter_applied = false;
            $filters = ['weight', 'price', 'quantity'];

            // Check if any of the filter is checked
            $filters_checks = ['filter_by_weight', 'filter_by_price', 'filter_by_quantity', 'filter_by_product_tag'];
            $any_filter_checked = false;
            foreach ($filters_checks as $check) {
                if (isset($settings[$check]) && filter_var($settings[$check], FILTER_VALIDATE_BOOLEAN)) {
                    $any_filter_checked = true;
                    break;
                }
            }

            // If there is no filter check, then all rules will meet so rule will be treated as applied
            if (!$any_filter_checked) {
                return true;
            }
    
            foreach ($filters as $filter) {
                if (filter_var($settings['filter_by_' . $filter], FILTER_VALIDATE_BOOLEAN)) {
                    $is_filter_applied = $formatted_values[$filter] >= $settings['filter_by_' . $filter . '_from'];
                    if ($is_filter_applied && !empty($settings['filter_by_' . $filter . '_to'])) {
                        $is_filter_applied = $formatted_values[$filter] < $settings['filter_by_' . $filter . '_to'];
                    }
                }
    
                if ($is_filter_applied) break;
            }
    
            if (filter_var($settings['filter_by_product_tag'], FILTER_VALIDATE_BOOLEAN) && !$is_filter_applied) {
                $product_tags = $settings['filter_by_product_tag_value'];
                $tags_check = array_filter($product_tags, function ($tag) use ($formatted_values) {
                    return in_array($tag, $formatted_values['tags']);
                });
                $is_filter_applied = count($tags_check) > 0;
            }
    
            return $is_filter_applied;
        }
    }

}
