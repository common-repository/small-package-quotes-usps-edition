<?php

namespace EnUspsWarehouseTemplate;

use EnUspsWarehouse\EnUspsWarehouse;

if (!class_exists('EnUspsWarehouseTemplate')) {

    class EnUspsWarehouseTemplate {

        static public $plan_required = '';
        static public $disabled_plan = '';
        static public $en_warehouse_list = [];

        /**
         * Warehouse template
         * @return false|string
         */
        static public function en_load() {
            $en_heading = $en_data = [];
            self::$en_warehouse_list = EnUspsWarehouse::get_data(['location' => 'warehouse']);

            // Load function for plans implementation of multi warehouse
            self::en_get_multi_warehouse_plan_status();

            extract(\EnLocation::en_location_filter_data('warehouse'));

            ksort($en_heading);
            ksort($en_data);

            ob_start();
            ?> <!-- Close PHP -->

            <div class="en_location_warehouse_main_div">

                <div class="en_location_success_message">
                    <strong><?php _e('Success!', 'eniture-technology'); ?> </strong><span></span></div>

                <h1><?php _e('Warehouses', 'eniture-technology'); ?></h1>
                <button onclick="en_show_popup_location(true)"
                        class="button-primary <?php echo esc_attr(self::$disabled_plan); ?>"><?php _e('Add', 'eniture-technology'); ?>
                </button>
                <?php echo force_balance_tags(self::$plan_required); ?>

                <p><?php _e('Warehouses that inventory all products not otherwise identified as drop shipped items. The warehouse with the
            lowest shipping cost to the destination is used for quoting purposes.', 'eniture-technology'); ?></p>

                <table class="en_location_table en_location_warehouse_table">
                    <thead>
                        <tr>
                            <?php echo force_balance_tags(\EnLocation::en_arrange_table_data('th', $en_heading)); ?>
                            <th><?php _e('Action', 'eniture-technology'); ?></th>
                        </tr>
                    </thead>

                    <?php
                    // Start PHP

                    echo force_balance_tags(\EnLocation::en_arrange_location_table_row(self::$en_warehouse_list, $en_data, true, self::$disabled_plan));

                    echo '</table>';

                    echo '</div>';

                    return ob_get_clean();
                }

                /**
                 * Get plan for use multi warehouse
                 */
                static public function en_get_multi_warehouse_plan_status() {
                    if (isset($_REQUEST['tab'])) {
                        $multi_warehouse = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_suscription_and_features", 'multi_warehouse');
                        if (is_array($multi_warehouse) && count(self::$en_warehouse_list) > 0) {
                            self::$plan_required = apply_filters(sanitize_text_field($_REQUEST['tab']) . "_plans_notification_link", $multi_warehouse);
                            self::$disabled_plan = 'en_disabled_plan';
                        }
                    }
                }

            }

        }
