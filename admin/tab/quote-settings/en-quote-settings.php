<?php

/**
 * Quote settings detail array.
 */

namespace EnUspsQuoteSettings;

if (!class_exists('EnUspsQuoteSettings')) {

    class EnUspsQuoteSettings {

        /**
         * Domestic services
         * @return array
         */
        static public function domestic_services() {
            return
                    [
                        [
                            'id' => 'PRIORITY_MAIL',
                            'request' => 'Priority Mail',
                            'name' => 'Priority Mail',
                        ],
                        [
                            'id' => 'PRIORITY_MAIL_EXPRESS',
                            'request' => 'Priority Mail Express',
                            'name' => 'Priority Mail Express',
                        ],
                        [
                            'id' => 'PRIORITY_MAIL_FLAT_RATE',
                            'request' => 'Priority Mail Flat Rate',
                            'name' => 'Priority Mail Flat Rate',
                        ],
                        [
                            'id' => 'GROUND_ADVANTAGE',
                            'request' => 'Ground Advantage',
                            'name' => 'Ground Advantage',
                        ]
            ];
        }

        /**
         * International services
         * @return array
         */
        static public function international_services() {
            return
                    [
                        [
                            'id' => 'PRIORITY_MAIL_INTERNATIONAL_EXPRESS',
                            'request' => 'Priority Mail International Express',
                            'name' => 'Priority Mail International Express',
                        ],
                        [
                            'id' => 'PRIORITY_MAIL_INTERNATIONAL',
                            'request' => 'Priority Mail International',
                            'name' => 'Priority Mail International',
                        ],
                        [
                            'id' => 'FIRST_CLASS_PACKAGE_INTERNATIONAL_SERVICE',
                            'request' => 'First-Class Package International Service',
                            'name' => 'First-Class Package International Service',
                        ]
            ];
        }

        /**
         * Quote Settings Services
         * @return array
         */
        static public function services() {
            $alphabets = 'abcdefghijklmnopqrstuvwxyz';
            $domestic = self::domestic_services();
            $international = self::international_services();
            $services = [];
            foreach ($domestic as $key => $service) {

                // Domestic checkbox
                $id = $name = '';
                extract($service);
                $indexing = 'en_usps_checkbox_' . $id;
                $services[$indexing] = [
                    'name' => __($name, 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'id' => $id,
                    'class' => 'en_usps_domestic_service en_usps_service_checkbox',
                ];

                // International checkbox
                $international_service = (isset($international[$key])) ? $international[$key] : [];
                if (!empty($international_service)) {
                    $international_id = $international_service['id'];
                    $international_name = $international_service['name'];
                    $indexing = 'en_usps_checkbox_' . $international_id;
                    $services[$indexing] = [
                        'name' => __($international_name, 'woocommerce-settings-usps'),
                        'type' => 'checkbox',
                        'id' => $international_id,
                        'class' => 'en_usps_international_service en_usps_service_checkbox',
                    ];
                } else {
                    $rand_string = substr(str_shuffle(str_repeat($alphabets, mt_rand(1, 10))), 1, 5);
                    $services[$rand_string] = [
                        'name' => __('', 'woocommerce-settings-usps'),
                        'type' => 'checkbox',
                        'id' => $rand_string,
                        'class' => 'en_usps_international_service hidden en_usps_service_hide',
                    ];
                }

                // Domestic markup
                $indexing = 'en_usps_markup_' . $id;
                $services[$indexing] = [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'id' => $indexing,
                    'class' => 'en_usps_domestic_service en_usps_service_markup',
                    'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-usps'),
                    'placeholder' => 'Markup',
                ];

                // International markup
                if (!empty($international_service)) {
                    $indexing = 'en_usps_markup_' . $international_id;
                    $services[$indexing] = [
                        'name' => __('', 'woocommerce-settings-usps'),
                        'type' => 'text',
                        'id' => $indexing,
                        'class' => 'en_usps_international_service en_usps_service_markup',
                        'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-usps'),
                        'placeholder' => 'Markup',
                    ];
                } else {
                    $rand_string = substr(str_shuffle(str_repeat($alphabets, mt_rand(1, 10))), 1, 10);
                    $services[$rand_string] = [
                        'name' => __('', 'woocommerce-settings-usps'),
                        'type' => 'text',
                        'id' => $rand_string,
                        'class' => 'en_usps_service_hide en_usps_international_service en_usps_service_markup hidden',
                    ];
                }
            }

            $services['shipping_methods_do_not_sort_by_price'] = [
                'name' => __("Don't sort shipping methods by price", 'woocommerce-settings-usps'),
                'type' => 'checkbox',
                'id' => 'shipping_methods_do_not_sort_by_price',
                'desc' => 'By default, the plugin will sort all shipping methods by price in ascending order.',
            ];


            /**
             * ==================================================================
             * Standard box sizes notification
             * ==================================================================
             */
            $services['avaibility_box_sizing'] = [
                'name' => __('Use my standard box sizes ', 'woocommerce-settings-usps'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='" . EN_USPS_SBS_URL . "'>here</a> to add the Standard Box Sizes module. (<a target='_blank' href='https://eniture.com/woocommerce-standard-box-sizes/#documentation'>Learn more</a>)",
                'id' => 'en_quote_settings_availability_sbs_usps'
            ];

            return $services;
        }

        /**
         * Hazardous material settings
         * @return array
         */
        static public function hazardous_material() {
            $option = $message = '';
            if (isset($_REQUEST['tab'])) {
                $feature_option = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_suscription_and_features", 'hazardous_material');
                if (is_array($feature_option)) {
                    $option = 'en_usps_disabled';
                    $message = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_notification_link", $feature_option);
                }
            }

            return [
                'hazardous_material_settings' => [
                    'name' => __('Hazardous material settings', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => $message,
                    'id' => 'hazardous_material_settings'
                ],
                'en_usps_hazardous_material_settings' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'class' => $option,
                    'desc' => 'Only quote ground service for hazardous materials shipments.',
                    'id' => 'en_usps_hazardous_material_settings'
                ],
                'en_usps_hazardous_material_settings_ground_fee' => [
                    'name' => __('Ground Hazardous Material Fee ', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => $option,
                    'desc' => 'Enter an amount, e.g 20. or Leave blank to disable.',
                    'id' => 'en_usps_hazardous_material_settings_ground_fee'
                ],
                'en_usps_hazardous_material_settings_international_fee' => [
                    'name' => __('Air Hazardous Material Fee ', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => $option,
                    'desc' => 'Enter an amount, e.g 20. or Leave blank to disable.',
                    'id' => 'en_usps_hazardous_material_settings_international_fee'
                ],
            ];
        }

        /**
         * Delivery estimate options
         * @return array
         */
        static public function delivery_estimate_option() {
            $option = $message = '';
            if (isset($_REQUEST['tab'])) {
                $feature_option = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_suscription_and_features", 'delivery_estimate_option');
                if (is_array($feature_option)) {
                    $option = 'en_usps_disabled';
                    $message = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_notification_link", $feature_option);
                }
            }

            return [
                'delivery_estimate_options' => [
                    'name' => __('Delivery Estimate Options', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => $message,
                    'id' => 'delivery_estimate_options'
                ],
                'en_delivery_estimate_options_usps' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'radio',
                    'class' => $option,
                    'default' => "dont_show_estimates",
                    'options' => [
                        'dont_show_estimates' => __("Don't display delivery estimates.", 'woocommerce-settings-usps'),
                        'delivery_days' => __('Display estimated number of days until delivery.', 'woocommerce-settings-usps'),
                        'delivery_date' => __('Display estimated delivery date.', 'woocommerce-settings-usps'),
                    ],
                    'id' => 'en_delivery_estimate_options_usps'
                ],
            ];
        }

        /**
         * Transit days
         * @return array
         */
        static public function transit_days() {
            $option = $message = '';
            if (isset($_REQUEST['tab'])) {
                $feature_option = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_suscription_and_features", 'transit_days');
                if (is_array($feature_option)) {
                    $option = 'en_usps_disabled';
                    $message = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_notification_link", $feature_option);
                }
            }

            return [
                'ground_transit' => [
                    'name' => __('Ground transit time restriction', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => $message,
                    'id' => 'ground_transit'
                ],
                'en_usps_transit_days' => [
                    'name' => __('Enter the number of transit days to restrict ground service to. Leave blank to disable this feature.', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => $option,
                    'id' => 'en_usps_transit_days'
                ],
                'en_usps_transit_day_options' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'radio',
                    'class' => $option,
                    'options' => [
                        'transitDays' => __('Restrict the carriers in transit days metric.', 'woocommerce-settings-usps'),
                        'CalenderDaysInTransit' => __('Restrict by calendar days in transit.', 'woocommerce-settings-usps'),
                    ],
                    'id' => 'en_usps_transit_day_options'
                ],
            ];
        }

        /**
         * Cutt off time
         * @return array
         */
        static public function cutt_off_time() {
            $option = $message = '';
            if (isset($_REQUEST['tab'])) {
                $feature_option = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_suscription_and_features", 'cutt_off_time');
                if (is_array($feature_option)) {
                    $option = 'en_usps_disabled';
                    $message = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_notification_link", $feature_option);
                }
            }

            return [
                'cutt_off_time_and_ship_date_offset' => [
                    'name' => __('Cut Off Time & Ship Date Offset', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => 'hidden',
                    'desc' => $message,
                    'id' => 'cutt_off_time_and_ship_date_offset'
                ],
                'en_usps_cutt_off_time' => [
                    'name' => __('Order Cut Off Time', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => $option,
                    'placeholder' => '--:-- --',
                    'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                    'id' => 'en_usps_cutt_off_time'
                ],
                'en_usps_fulfilment_offset_days' => [
                    'name' => __('Fulfilment Offset Days', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'class' => $option,
                    'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                    'id' => 'en_usps_fulfilment_offset_days'
                ],
                'en_usps_all_shipment' => [
                    'name' => __("What days do you ship orders?", 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'desc' => 'Select All',
                    'id' => 'en_usps_all_shipment',
                    'class' => 'en_usps_all_shipment ' . $option,
                ],
                'en_usps_monday_shipment' => [
                    'name' => __("", 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'desc' => 'Monday',
                    'id' => 'en_usps_monday_shipment',
                    'class' => 'en_usps_shipment_day ' . $option,
                ],
                'en_usps_tuesday_shipment' => [
                    'name' => __("", 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'desc' => 'Tuesday',
                    'id' => 'en_usps_tuesday_shipment',
                    'class' => 'en_usps_shipment_day ' . $option,
                ],
                'en_usps_wednesday_shipment' => [
                    'name' => __("", 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'desc' => 'Wednesday',
                    'id' => 'en_usps_wednesday_shipment',
                    'class' => 'en_usps_shipment_day ' . $option,
                ],
                'en_usps_thursday_shipment' => [
                    'name' => __("", 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'desc' => 'Thursday',
                    'id' => 'en_usps_thursday_shipment',
                    'class' => 'en_usps_shipment_day ' . $option,
                ],
                'en_usps_friday_shipment' => [
                    'name' => __("", 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'desc' => 'Friday',
                    'id' => 'en_usps_friday_shipment',
                    'class' => 'en_usps_shipment_day ' . $option,
                ],
            ];
        }

        static public function Load() {
            $services = self::services();
            $settings_start = [
                'en_quote_settings_start_usps' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'title',
                    'id' => 'en_quote_settings_usps',
                ],
                'en_usps_rate_tier_title' => [
                    'name' => __('Rate tier', 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'id' => 'en_usps_rate_tier_title',
                    'class' => 'en_usps_service_heading',
                ],
                'en_usps_rate_tier_dropdown' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'select',
                    'default' => 'retail',
                    'desc' => __('', 'woocommerce-settings-usps'),
                    'id' => 'en_usps_rate_tier_dropdown',
                    'options' => [
                        'retail' => __('Retail (at Post Office)', 'Retail (at Post Office)'),
                        'commercialBase' => __('Commercial Base', 'Commercial Base'),
                        'commercialPlus' => __('Commercial Plus', 'Commercial Plus'),
                    ],
                    'class' => ''
                ],
                'en_usps_domestic_heading' => [
                    'name' => __('Domestic Services', 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'id' => 'en_usps_domestic_heading',
                    'class' => 'en_usps_domestic_service en_usps_service_heading',
                ],
                'en_usps_international_heading' => [
                    'name' => __('International Services', 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'id' => 'en_usps_international_heading',
                    'class' => 'en_usps_international_service en_usps_service_heading',
                ],
                'en_usps_domestic_selective' => [
                    'name' => __('Select All', 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'id' => 'en_usps_domestic_selective',
                    'class' => 'en_usps_domestic_service en_usps_service_all_select',
                ],
                'en_usps_international_selective' => [
                    'name' => __('Select All', 'woocommerce-settings-usps'),
                    'type' => 'checkbox',
                    'id' => 'en_usps_international_selective',
                    'class' => 'en_usps_international_service en_usps_service_all_select',
                ],
            ];

            $settings_body = [

                'en_usps_handling_fee' => [
                    'name' => __('Handling Fee / Markup ', 'woocommerce-settings-usps'),
                    'type' => 'text',
                    'desc' => 'Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.',
                    'id' => 'en_usps_handling_fee'
                ],
                'en_usps_shipping_logs' => [
                    'name' => __("Enable Logs  ", 'woocommerce-settings-freightview'),
                    'type' => 'checkbox',
                    'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                    'id' => 'en_usps_shipping_logs'
                ],
                'en_usps_allow_other_plugin_quotes' => [
                    'name' => __('Show WooCommerce Shipping Options ', 'woocommerce-settings-usps'),
                    'type' => 'select',
                    'default' => 'yes',
                    'desc' => __('Enabled options on WooCommerce Shipping page are included in quote results.', 'woocommerce-settings-usps'),
                    'id' => 'en_usps_allow_other_plugin_quotes',
                    'options' => [
                        'yes' => __('YES', 'YES'),
                        'no' => __('NO', 'NO'),
                    ]
                ],
                /**
                 * ==================================================================
                 * When plugin fail return to rate
                 * ==================================================================
                 */
                'en_quote_settings_clear_both_usps' => [
                    'title' => __('', 'woocommerce'),
                    'name' => __('', 'woocommerce-settings-usps'),
                    'desc' => '',
                    'id' => 'en_quote_settings_clear_both_usps',
                    'css' => '',
                    'type' => 'title',
                ],
                'en_quote_settings_unable_retrieve_shipping_usps' => [
                    'name' => __('Checkout options if the plugin fails to return a rate ', 'woocommerce-settings-usps'),
                    'type' => 'title',
                    'desc' => '<span> When the plugin is unable to retrieve shipping quotes and no other shipping options are provided by an alternative source: </span>',
                    'id' => 'en_quote_settings_unable_retrieve_shipping_usps',
                ],
                'en_usps_unable_retrieve_shipping' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'radio',
                    'id' => 'en_usps_unable_retrieve_shipping',
                    'default' => 'allow',
                    'options' => [
                        'allow' => __('Allow user to continue to check out and display this message', 'woocommerce-settings-usps'),
                        'prevent' => __('Prevent user from checking out and display this message', 'woocommerce-settings-usps'),
                    ]
                ],
                'en_usps_checkout_error_message' => [
                    'name' => __('', 'woocommerce-settings-usps'),
                    'type' => 'textarea',
                    'desc' => 'Enter a maximum of 250 characters.',
                    'id' => 'en_usps_checkout_error_message'
                ],
                'en_quote_settings_end_usps' => [
                    'type' => 'sectionend',
                    'id' => 'en_quote_settings_end_usps'
                ],
            ];

            $settings = $settings_start + $services + $settings_body;

            return $settings;
        }

    }

}
