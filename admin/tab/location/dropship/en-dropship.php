<?php

namespace EnUspsDropshipTemplate;

use EnUspsWarehouse\EnUspsWarehouse;

if (!class_exists('EnUspsDropshipTemplate')) {

    class EnUspsDropshipTemplate
    {

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
         * Warehouse template
         * @return false|string
         */
        static public function en_load()
        {
            $en_heading = $en_data = [];
            $en_dropship_list = EnUspsWarehouse::get_data(['location' => 'dropship']);

            extract(\EnLocation::en_location_filter_data('dropship'));

            ksort($en_heading);
            ksort($en_data);

            ob_start();
            ?> <!-- Close PHP-->

            <div class="en_location_dropship_main_div">

            <h1><?php _e('Drop ships', 'eniture-technology'); ?></h1>
            <button onclick="en_show_popup_location(false)"
                    class="button-primary"><?php _e('Add', 'eniture-technology'); ?></button>
            <p><?php _e("Locations that inventory specific items that are drop shipped to the destination. Use the product's settings
                    page to identify it as a drop shipped item and its associated drop ship location. Orders that include drop
                    shipped items will display a single figure for the shipping rate estimate that is equal to the sum of the
                    cheapest option of each shipment required to fulfill the order.", 'eniture-technology'); ?></p>

            <table class="en_location_table en_location_dropship_table">
            <thead>
            <tr>
                <?php echo force_balance_tags(\EnLocation::en_arrange_table_data('th', $en_heading)); ?>
                <th><?php _e('Action', 'eniture-technology'); ?></th>
            </tr>
            </thead>

            <?php
            // Start PHP

            echo force_balance_tags(\EnLocation::en_arrange_location_table_row($en_dropship_list, $en_data, 0));

            echo '</table>';

            echo '</div>';

            return ob_get_clean();
        }

    }

}
