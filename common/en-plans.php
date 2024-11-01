<?php

/**
 * Identified subscription.
 */

namespace EnUspsPlans;

/**
 * Eniture plan.
 * Class EnUspsPlans
 * @package EnUspsPlans
 */
if (!class_exists('EnUspsPlans')) {

    class EnUspsPlans
    {
        /**
         * Hook for call.
         * EnUspsPlans constructor.
         */
        public function __construct()
        {
            add_filter('en_register_activation_hook', [$this, 'en_get_current_plan'], 10);
            register_activation_hook(EN_USPS_MAIN_FILE, [$this, 'en_get_current_plan'], 10, 1);
            add_filter('usps_plans_notification_link', [$this, 'en_notification'], 10, 1);
            add_filter('usps_plans_suscription_and_features', [$this, 'en_plans'], 10, 1);
            // Click here to update the plan
            add_action('wp_ajax_en_usps_get_current_plan', [$this, 'en_get_current_plan'], 10);
        }

        /**
         * Eniture subscription status
         */
        public function en_get_current_plan($network_wide = null)
        {
            if (is_multisite() && $network_wide) {

                foreach (get_sites(['fields' => 'ids']) as $blog_id) {

                    switch_to_blog($blog_id);
                    $pakg_price = $pakg_duration = $expiry_date = $plan_type = '';
                    $index = 'small-package-quotes-usps-edition/small-package-quotes-usps-edition.php';
                    $plugin_info = get_plugins();
                    $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : 0;
                    $plugin_dir_url = EN_USPS_DIR_FILE . 'en-hit-to-update-plan.php';
                    $post_data = array(
                        'platform' => 'wordpress',
                        'carrier' => '60',
                        'store_url' => EN_USPS_SERVER_NAME,
                        'webhook_url' => $plugin_dir_url,
                        'plugin_version' => $plugin_version,
                        'license_key' => get_option('usps_small_licence_key')
                    );

                    extract(json_decode(\EnUspsCurl\EnUspsCurl::en_usps_sent_http_request(EN_USPS_PLAN_HITTING_URL, $post_data, 'GET', 'Plan'), true));

                    $pakg_price == '0' ? $pakg_group = '0' : '';

                    // Get plan message
                    $this->en_filter_current_plan_name($pakg_group, $expiry_date);

                    update_option('en_usps_plan_number', $pakg_group);
                    update_option('en_usps_plan_expire_days', $pakg_duration);
                    update_option('en_usps_plan_expire_date', $expiry_date);
                    update_option('en_usps_store_type', $plan_type);
                    restore_current_blog();
                }
            }else {
                $pakg_price = $pakg_duration = $expiry_date = $plan_type = '';
                $index = 'small-package-quotes-usps-edition/small-package-quotes-usps-edition.php';
                $plugin_info = get_plugins();
                $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : 0;
                $plugin_dir_url = EN_USPS_DIR_FILE . 'en-hit-to-update-plan.php';
                $post_data = array(
                    'platform' => 'wordpress',
                    'carrier' => '60',
                    'store_url' => EN_USPS_SERVER_NAME,
                    'webhook_url' => $plugin_dir_url,
                    'plugin_version' => $plugin_version,
                    'license_key' => get_option('usps_small_licence_key')
                );

                extract(json_decode(\EnUspsCurl\EnUspsCurl::en_usps_sent_http_request(EN_USPS_PLAN_HITTING_URL, $post_data, 'GET', 'Plan'), true));

                $pakg_price == '0' ? $pakg_group = '0' : '';

                // Get plan message
                $this->en_filter_current_plan_name($pakg_group, $expiry_date);

                update_option('en_usps_plan_number', $pakg_group);
                update_option('en_usps_plan_expire_days', $pakg_duration);
                update_option('en_usps_plan_expire_date', $expiry_date);
                update_option('en_usps_store_type', $plan_type);
            }
        }

        /**
         * Eniture filter subscription plan name
         */
        public function en_filter_current_plan_name($pakg_group, $expiry_date)
        {
            $expiry_date .= EN_USPS_714;
            switch ($pakg_group) {
                case 3:
                    $plan_message = EN_USPS_703 . $expiry_date;
                    break;
                case 2:
                    $plan_message = EN_USPS_702 . $expiry_date;
                    break;
                case 1:
                    $plan_message = EN_USPS_701 . $expiry_date;
                    break;
                case 0:
                    $plan_message = EN_USPS_700 . $expiry_date;
                    break;
                default:
                    $plan_message = EN_USPS_704;
                    break;
            }

            update_option('en_usps_plan_message', "$plan_message .");
        }

        /**
         * Eniture plans
         * @param $feature
         * @return bool|mixed|string
         */
        public function en_plans($feature)
        {
            $package = EN_USPS_PLAN;
            $features = [
                'instore_pickup_local_delivery' => ['3'],
                'hazardous_material' => ['2', '3'],
                'multi_warehouse' => ['2', '3'],
                'transit_days' => ['3'],
                'cutt_off_time' => ['2', '3'],
                'delivery_estimate_option' => ['1', '2', '3'],
                'insurance' => ['2', '3']
            ];

            return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ?
                TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
        }

        /**
         * Plans notification link
         * @param array $plans
         * @return string
         */
        public function en_notification($plans)
        {
            $plan_to_upgrade = "";
            switch (current($plans)) {
                case 1:
                    $plan_to_upgrade = EN_USPS_715;
                    break;
                case 2:
                    $plan_to_upgrade = EN_USPS_705;
                    break;
                case 3:
                    $plan_to_upgrade = EN_USPS_706;
                    break;
            }

            return $plan_to_upgrade;
        }

    }

}
