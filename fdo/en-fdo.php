<?php

namespace EnUspsFdo;

if (!class_exists('EnUspsFdo')) {

    class EnUspsFdo {

        static public $en_fdo_meta_data;

        /**
         * arrange cart objects.
         * @param type $package
         * @return array
         */
        static public function en_cart_package($en_package, $key) {
            self::$en_fdo_meta_data['plugin_type'] = 'small';
            self::$en_fdo_meta_data['plugin_name'] = 'usps';
            $accessorials['residential'] = get_option('en_usps_residential_delivery') == 'yes' ? true : false;
            self::$en_fdo_meta_data['accessorials'] = $accessorials;

            (isset($en_package['commdityDetails']) && isset($en_package['commdityDetails'][$key])) ? self::en_package_items($en_package['commdityDetails'][$key]) : '';
            (isset($en_package['originAddress']) && isset($en_package['originAddress'][$key])) ? self::en_package_address($en_package['originAddress'][$key]) : '';

            return self::$en_fdo_meta_data;
        }

        /**
         * arrange items.
         * @param type $items
         */
        static public function en_package_items($items) {
            self::$en_fdo_meta_data['items'] = [];
            foreach ($items as $item_key => $item_data) {
                $productId = $productName = $piecesOfLineItem = $lineItemPrice = $lineItemWeight = $lineItemLength = $lineItemWidth = $lineItemHeight = $ptype = $productType = $productSku = $productClass = $attributes = $variantId = '';
                $isInsuranceActive = 0;
                $attributes = [];
                extract($item_data);

                $meta_data = [];
                if (!empty($attributes)) {
                    foreach ($attributes as $attr_key => $attr_value) {
                        $meta_data[] = [
                            'key' => $attr_key,
                            'value' => $attr_value,
                        ];
                    }
                }

                $productName = is_string($productName) && !empty($productName) ? str_replace(array("'", '"'), '', $productName) : '';

                $item = [
                    'id' => $productId,
                    'name' => $productName,
                    'quantity' => $piecesOfLineItem,
                    'price' => $lineItemPrice,
                    'weight' => $lineItemWeight,
                    'length' => $lineItemLength,
                    'width' => $lineItemWidth,
                    'height' => $lineItemHeight,
                    'type' => $ptype,
                    'product' => $productType,
                    'sku' => $productSku,
                    'attributes' => $attributes,
                    'shipping_class' => $productClass,
                    'variant_id' => $variantId,
                    'meta_data' => $meta_data,
                    'insurance' => $isInsuranceActive,
                ];

                // Hook for flexibility adding to package
                $item = apply_filters('en_fdo_package', $item, $item_data);
                self::$en_fdo_meta_data['items'][$item_key] = $item;
            }
        }

        /**
         * Get address.
         * @param array $address
         */
        static public function en_package_address($address) {
            $selection_for_address = [
                'id' => 'id',
                'senderCity' => 'city',
                'senderState' => 'state',
                'senderZip' => 'zip',
                'senderCountryCode' => 'country',
                'senderLocation' => 'location',
                'senderAddressLine' => 'address',
            ];

            // Get result
            $sender_location_address = array_combine($selection_for_address, array_intersect_key($address, $selection_for_address));
            self::$en_fdo_meta_data['address'] = $sender_location_address;
        }

    }

}
