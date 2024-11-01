<?php
if (!class_exists('EnLocation')) {

    class EnLocation
    {

        static public $plan_required = '';
        static public $disabled_plan = '';

        /**
         * Location fields
         * @return array
         */
        static public function en_location_data()
        {
            // Load function for plans implementation of instore pickup and local delivery
            self::en_get_instore_pickup_plan_status();
            $plan_required = self::$plan_required;
            $disabled_plan = self::$disabled_plan;

            $en_location_data = [
                'en_location_id' => [
                    'type' => 'en_input_hidden',
                    'id' => 'en_location_id',
                    'name' => 'id',
                    'append' => ' data-optional="1" ',
                ],
                'en_location_type' => [
                    'type' => 'en_input_hidden',
                    'id' => 'en_location_type',
                    'name' => 'location',
                    'append' => ' data-optional="1" ',
                ],
                'nickname' => [
                    'type' => 'en_input_field',
                    'name' => 'nickname',
                    'placeholder' => 'Nickname',
                    'id' => 'en_location_nickname',
                    'label' => 'Nickname',
                    'class' => 'en_location_input_field',
                    'append' => ' data-optional="1" ',
                    'frontend' => 'show',
                    'position' => 10,
                    'append_after' => '<span class="en_location_error"></span>',
                ],
                'address' => [
                    'type' => 'en_input_field',
                    'name' => 'address',
                    'placeholder' => '320 W. Lanier Ave, Ste 200',
                    'id' => 'en_location_address',
                    'label' => 'Street Address',
                    'class' => 'en_street_address en_location_input_field',
                    'append' => ' data-optional="1" ',
                    'position' => 11,
                    'append_after' => '<span class="en_location_error"></span>',
                ],
                'zip' => [
                    'type' => 'en_input_field',
                    'name' => 'zip',
                    'placeholder' => '30214',
                    'id' => 'en_usps_location_zip',
                    'label' => 'Zip',
                    'class' => 'en_location_input_field',
                    'append' => ' maxlength="7" ',
                    'frontend' => 'show',
                    'position' => 40,
                    'append_after' => '<span class="en_location_error"></span>',
                ],
                'city' => [
                    'type' => 'en_input_field',
                    'name' => 'city',
                    'placeholder' => 'Fayetteville',
                    'id' => 'en_location_city',
                    'label' => 'City',
                    'class' => 'en_location_input_field',
                    'append' => '',
                    'frontend' => 'show',
                    'position' => 20,
                    'append_after' => '<span class="en_location_error"></span>',
                ],
                'state' => [
                    'type' => 'en_input_field',
                    'name' => 'state',
                    'placeholder' => 'GA',
                    'id' => 'en_location_state',
                    'label' => 'State',
                    'class' => 'en_location_input_field',
                    'append' => ' maxlength="2" en_alpha_only(event)',
                    'frontend' => 'show',
                    'position' => 30,
                    'append_after' => '<span class="en_location_error"></span>',
                ],
                'country' => [
                    'type' => 'en_input_field',
                    'name' => 'country',
                    'placeholder' => 'US',
                    'id' => 'en_location_country',
                    'label' => 'Country',
                    'class' => 'en_location_input_field',
                    'append' => ' maxlength="2" en_alpha_only(event)',
                    'frontend' => 'show',
                    'position' => 50,
                    'append_after' => '<span class="en_location_error"></span>',
                ],
                'en_wd_origin_markup' => [
                    'type' => 'en_input_field',
                    'name' => 'en_wd_origin_markup',
                    'placeholder' => 'e.g Currency 1.00 or percentage 5%',
                    'id' => 'en_wd_origin_markup',
                    'label' => 'Handling Fee / Markup',
                    'class' => 'en_location_input_field numericonly',
                    'append' => ' maxlength="8" data-optional="1"',
                    'position' => 60,
                    'append_after' => '<span class="en_location_error"></span>',
                ],
                'en_in_store_pickup' => [
                    'type' => 'en_heading',
                    'id' => 'en_popup_location_heading',
                    'label' => 'In-store pick up',
                    'class' => 'en_popup_location_heading',
                    'append' => ' data-optional="1" ',
                    'append_after' => $plan_required,
                ],
                'en_enable_in_store_pickup' => [
                    'type' => 'en_checkbox',
                    'name' => 'enable_store_pickup',
                    'id' => 'en_enable_in_store_pickup',
                    'label' => 'Enable in-store pick up',
                    'title' => 'Enable in-store pick up',
                    'class' => 'en_location_checkout_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" ',
                ],
                'en_in_store_pickup_miles' => [
                    'type' => 'en_input_field',
                    'name' => 'miles_store_pickup',
                    'id' => 'in_store_pickup_miles',
                    'label' => 'Offer if address is within (miles):',
                    'class' => 'en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" onchange="en_round_two_digits_after_decimal(this)" ',
                ],
                'en_in_store_pickup_postal_code' => [
                    'type' => 'en_input_field',
                    'name' => 'match_postal_store_pickup',
                    'id' => 'en_in_store_pickup_postal_code',
                    'label' => 'Offer if postal code matches:',
                    'class' => 'en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" data-role="tagsinput" ',
                ],
                'en_in_store_pickup_description' => [
                    'type' => 'en_input_field',
                    'name' => 'checkout_desc_store_pickup',
                    'id' => 'en_in_store_pickup_description',
                    'label' => 'Checkout description:',
                    'class' => 'en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" placeholder="In-store pick up" ',
                ],
                'en_in_store_pickup_phone' => [
                    'type' => 'en_input_field',
                    'name' => 'phone_instore',
                    'id' => 'en_in_store_pickup_phone',
                    'label' => 'Phone number:',
                    'class' => 'en_phone_number en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" placeholder="404-369-0680" ',
                ],
                'en_local_delivery' => [
                    'type' => 'en_heading',
                    'id' => 'en_popup_location_heading',
                    'label' => 'Local Delivery',
                    'class' => 'en_popup_location_heading',
                    'append' => '',
                    'append_after' => $plan_required,
                ],
                'en_enable_local_delivery' => [
                    'type' => 'en_checkbox',
                    'name' => 'enable_local_delivery',
                    'id' => 'en_enable_local_delivery',
                    'label' => 'Enable local delivery',
                    'title' => 'Enable local delivery',
                    'class' => 'en_location_checkout_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" ',
                ],
                'en_local_delivery_miles' => [
                    'type' => 'en_input_field',
                    'name' => 'miles_local_delivery',
                    'id' => 'local_delivery_miles',
                    'label' => 'Offer if address is within (miles):',
                    'class' => 'en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" onchange="en_round_two_digits_after_decimal(this)" ',
                ],
                'en_local_delivery_postal_code' => [
                    'type' => 'en_input_field',
                    'name' => 'match_postal_local_delivery',
                    'id' => 'en_local_delivery_postal_code',
                    'label' => 'Offer if postal code matches:',
                    'class' => 'en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => '  data-optional="1" data-role="tagsinput" ',
                ],
                'en_local_delivery_description' => [
                    'type' => 'en_input_field',
                    'name' => 'checkout_desc_local_delivery',
                    'id' => 'en_local_delivery_description',
                    'label' => 'Checkout description:',
                    'class' => 'en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" placeholder="Local delivery" ',
                ],
                'en_local_delivery_fee' => [
                    'type' => 'en_input_field',
                    'name' => 'fee_local_delivery',
                    'id' => 'en_local_delivery_fee',
                    'label' => 'Local delivery fee:',
                    'class' => 'en_location_input_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" onchange="en_round_two_digits_after_decimal(this)" ',
                ],
                'en_suppress_other_rates' => [
                    'type' => 'en_checkbox',
                    'name' => 'suppress_local_delivery',
                    'id' => 'en_suppress_other_rates',
                    'label' => 'Suppress other rates <span class="suppress-span" title="This setting only suppresses rates that would otherwise be returned by the Eniture Technology products.">[?]</span>',
                    'title' => 'Suppress other rates',
                    'class' => 'en_location_checkout_field ' . esc_attr($disabled_plan),
                    'append' => ' data-optional="1" ',
                ]
            ];

//      We can use hook for add new location field from other plugin add-on
            return apply_filters('en_usps_add_location', $en_location_data);
        }

        /**
         * Make a table row in location frontend
         * @param array $en_location_list
         * @param array $en_data
         * @return mixed
         */
        static public function en_arrange_location_table_row($en_location_list, $en_data, $location_bol, $disabled_plan = '')
        {
            ob_start();
            foreach ($en_location_list as $key => $location) {
                $en_location_id = (isset($location['id'])) ? $location['id'] : '';
                $en_flipped_data = array_flip($en_data);

                $location['en_wd_origin_markup'] = isset($location['origin_markup']) ? $location['origin_markup'] : ''; 

                $en_intersected_data = array_intersect_key($location, $en_flipped_data);
                $en_sorted_location = array_merge($en_flipped_data, $en_intersected_data);
                $append_class = $key === 0 ? '' : $disabled_plan;

                echo '<tr class="' . esc_attr($append_class) . '" id="en_location_row_id_' . esc_attr($en_location_id). '">';
                echo self::en_arrange_table_data('td', $en_sorted_location);

                echo "<td class='en_location_db_data'>" . wp_json_encode($location) . "</td>";
                echo "<td class='en_location_custom_data'>" . wp_json_encode(\EnLocation::en_location_data()) . "</td>";

                echo '<td class="en_location_icons">';
                echo '<a href="javascript(0)" onclick="return en_usps_location_edit(event, this,' . $location_bol . ')"> <img src = "' . EN_USPS_DIR_FILE . '/admin/tab/location/assets/images/edit.png" title = "Edit" ></a>';
                echo '<a href="javascript(0)" onclick="return en_usps_location_delete(event, this,' . $location_bol . ' , ' . esc_attr($en_location_id) . ')"> <img  src = "' . EN_USPS_DIR_FILE . '/admin/tab/location/assets/images/delete.png" title = "Delete" ></a>';
                echo '</td>';

                echo '</tr>';
            }

            return ob_get_clean();
        }

        /**
         * Load html for location popup
         */
        static public function en_load()
        {
            $en_location_data = self::en_location_data();
            ?>

            <!-- Confirmation message when you delete dropship or warehouse -->
            <div class="confirmation_location_delete en_popup_location_overly">
                <div class="en_popup_location_form en_hide_popup_location">
                    <a class="en_close_popup_location" href="#"><?php _e('×', 'eniture-technology'); ?></a>

                    <h2 class="en_confirmation_warning">
                        <?php _e('Warning!', 'eniture-technology'); ?>
                    </h2>
                    <p class="en_confirmation_message">
                        <?php _e('If you delete this location, then location settings will be disabled against products
                        (if any).', 'eniture-technology'); ?>
                    </p>
                    <div class="en_confirmation_buttons">
                        <a href="#"
                           class="button-primary en_location_cancel_delete"><?php _e('Cancel', 'eniture-technology'); ?></a>
                        <a href="#"
                           class="button-primary en_location_confirm_delete"><?php _e('OK', 'eniture-technology'); ?></a>
                    </div>
                </div>
            </div>

            <?php
            echo '<div class="en_popup_location_overly">';
            echo '<div class="en_popup_location_form en_hide_popup_location">';
            echo '<h2 id="en_popup_location_heading">Warehouse</h2>';
            echo '<a class="en_close_popup_location" href="#">×</a>';

            echo '</form>';

            // Popup form to show error messages class|div
            echo '<div class="en_location_error_message"><strong>Error!</strong> <span> </span></div>';

            echo '<form method="post" id="en_location_form_reset_me">';

            foreach ($en_location_data as $key => $value) {
                $id = $placeholder = $type = $label = $class = $append = $append_after = $name = $title = '';
                extract($value);
                echo '<div class="en_popup_location_input_field">';

                switch ($type) {
                    case 'en_input_field':
                        echo "<label for='" . esc_attr($id) . "'>" . esc_attr($label) . "</label>";
                        echo "<input type='text' $append title='" . esc_attr($label) . "' name='" . esc_attr($name) . "' placeholder='" . esc_attr($placeholder) . "' id='" . esc_attr($id) . "' class='" . esc_attr($class) . "'>";
                        echo force_balance_tags($append_after);
                        break;

                    case 'en_input_hidden':
                        echo "<input type='hidden' $append name='" . esc_attr($name) . "' id='" . esc_attr($id) . "'>";
                        break;

                    case 'en_heading':
                        echo "<h2 class='" . esc_attr($class) . " en_float_left'>" . esc_attr($label) . "</h2>";
                        echo "<span class='en_instore_pickup_notification'>".force_balance_tags($append_after)." </span>";
                        break;

                    case 'en_checkbox':
                        echo "<label for='" . esc_attr($id) . "'>" . __($label) . "</label>";
                        echo "<div class='" . esc_attr($class) . "'>";
                        echo "<input type='checkbox' $append name='" . esc_attr($name) . "' id='" . esc_attr($id) . "' title='" . esc_attr($title) . "'>";
                        echo '</div>';
                        break;
                }

                echo '</div>';
            }

            echo '<input type="submit" value="Save" class="en_usps_location_btn button-primary">';
            echo '</form>';
            echo '</div>';
            echo '</div>';

            echo \EnUspsWarehouseTemplate\EnUspsWarehouseTemplate::en_load();
            echo \EnUspsDropshipTemplate\EnUspsDropshipTemplate::en_load();
        }

        /**
         * Convert array to string for table using
         * @param string $index
         * @param array $data
         * @return string
         */
        static public function en_arrange_table_data($index, $data)
        {
            return "<$index> " . implode(" <$index> ", $data) . " </$index>";
        }

        /**
         * search detail for existance location
         * @param string $en_location_type
         * @return array
         */
        static public function en_location_filter_data($en_location_type)
        {
            $en_location_data = \EnLocation::en_location_data();
            $en_location_filtered_data = [];
            foreach ($en_location_data as $key => $fields) {
                if (isset(
                        $fields['frontend'], $fields['label'], $fields['name'], $fields['position']) &&
                    $fields['frontend'] === 'show' &&
                    (($key === 'nickname' && $en_location_type === 'dropship') ||
                        $key != 'nickname')) {
                    $en_location_filtered_data['en_heading'][$fields['position']] = $fields['label'];
                    $en_location_filtered_data['en_data'][$fields['position']] = $fields['name'];
                }
            }
            return $en_location_filtered_data;
        }

        /**
         * Get plan for use multi warehouse
         */
        static public function en_get_instore_pickup_plan_status()
        {
            if (isset($_REQUEST['tab'])) {
                $instore_pickup = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_suscription_and_features", 'instore_pickup_local_delivery');
                if (is_array($instore_pickup) && count($instore_pickup) > 0) {
                    self::$plan_required = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_notification_link", $instore_pickup);
                    self::$disabled_plan = 'en_disabled_plan';
                }
            }
        }
    }
}


