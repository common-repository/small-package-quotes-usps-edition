<?php
/**
 * App install hook
 */
use EnUspsConfig\EnUspsConfig;
use EnUspsWarehouse\EnUspsWarehouse;

if (!function_exists('en_usps_installation')) {

    function en_usps_installation() {
        apply_filters('en_register_activation_hook', false);
    }

    register_activation_hook(EN_USPS_MAIN_FILE, 'en_usps_installation');
}

/**
 * App uninstall hook
 */
if (!function_exists('en_usps_uninstall')) {

    function en_usps_uninstall() {
        apply_filters('en_register_deactivation_hook', false);
    }

    register_deactivation_hook(EN_USPS_MAIN_FILE, 'en_usps_uninstall');
}

/**
 * App load admin side files of css and js hook
 */
if (!function_exists('en_usps_admin_enqueue_scripts')) {

    function en_usps_admin_enqueue_scripts() {
        wp_enqueue_script('EnUspsTagging', EN_USPS_DIR_FILE . '/admin/tab/location/assets/js/en-usps-tagging.js', [], '1.0.0');
        wp_localize_script('EnUspsTagging', 'script', [
            'pluginsUrl' => EN_USPS_PLUGIN_URL,
        ]);

        wp_enqueue_script('EnUspsAdminJs', EN_USPS_DIR_FILE . '/admin/assets/en-usps-admin.js', [], '1.0.4');
        wp_localize_script('EnUspsAdminJs', 'script', [
            'pluginsUrl' => EN_USPS_PLUGIN_URL,
        ]);

        wp_enqueue_script('EnWickedPicker', EN_USPS_DIR_FILE . '/admin/assets/en-wicked-picker.js', [], '1.0.0');
        wp_localize_script('EnWickedPicker', 'script', [
            'pluginsUrl' => EN_USPS_PLUGIN_URL,
        ]);

        wp_register_style('EnWickedPickerCss', EN_USPS_DIR_FILE . '/admin/assets/en-wicked-picker.css', [], '1.0.0');
        wp_enqueue_style('EnWickedPickerCss');

        wp_enqueue_script('EnUspsLocationScript', EN_USPS_DIR_FILE . '/admin/tab/location/assets/js/en-usps-location.js', [], '1.0.1');
        wp_localize_script('EnUspsLocationScript', 'script', array(
            'pluginsUrl' => EN_USPS_PLUGIN_URL,
        ));

        wp_register_style('EnUspsLocationStyle', EN_USPS_DIR_FILE . '/admin/tab/location/assets/css/en-usps-location.css', false, '1.0.1');
        wp_enqueue_style('EnUspsLocationStyle');

        wp_register_style('EnUspsAdminCss', EN_USPS_DIR_FILE . '/admin/assets/en-usps-admin.css', false, '1.0.3');
        wp_enqueue_style('EnUspsAdminCss');

        // Load scripts for json tree view
        wp_register_script('en_usps_json_tree_view_script', plugin_dir_url(__FILE__) . 'admin/tab/logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.0');
        wp_enqueue_script('en_usps_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);

        // Load styles for json tree view
        wp_enqueue_style('en_usps_json_tree_view_style');
        wp_register_style('en_usps_json_tree_view_style', plugin_dir_url(__FILE__) . 'admin/tab/logs/en-json-tree-view/en-jtv-style.css');

        // Shipping rules script and styles
        wp_enqueue_script('EnUspsShippingRulesStyle', plugin_dir_url(__FILE__) . 'admin/tab/shipping-rules/assets/js/shipping_rules.js', array(), '1.0.2');
        wp_localize_script('EnUspsShippingRulesStyle', 'script', array(
            'pluginsUrl' => plugins_url(),
        ));
        wp_register_style('EnUspsShippingRulesScript', plugin_dir_url(__FILE__) . 'admin/tab/shipping-rules/assets/css/shipping_rules.css', false, '1.0.1');
        wp_enqueue_style('EnUspsShippingRulesScript');
    }

    add_action('admin_enqueue_scripts', 'en_usps_admin_enqueue_scripts');
}

/**
 * App load front-end side files of css and js hook
 */
if (!function_exists('en_usps_frontend_enqueue_scripts')) {

    function en_usps_frontend_enqueue_scripts() {
        wp_enqueue_script('EnUspsFrontEnd', EN_USPS_DIR_FILE . '/admin/assets/en-usps-frontend.js', ['jquery'], '1.0.0');
        wp_localize_script('EnUspsFrontEnd', 'script', [
            'pluginsUrl' => EN_USPS_PLUGIN_URL,
        ]);
    }

    add_action('wp_enqueue_scripts', 'en_usps_frontend_enqueue_scripts');
}

/**
 * Load tab file
 * @param $settings
 * @return array
 */
if (!function_exists('en_usps_shipping_sections')) {

    function en_usps_shipping_sections($settings) {
        $settings[] = include('admin/tab/en-tab.php');
        return $settings;
    }

    add_filter('woocommerce_get_settings_pages', 'en_usps_shipping_sections', 10, 1);
}

/**
 * Show action links on plugins page
 * @param $actions
 * @param $plugin_file
 * @return array
 */
if (!function_exists('en_usps_freight_action_links')) {

    function en_usps_freight_action_links($actions, $plugin_file) {
        static $plugin;
        if (!isset($plugin)) {
            $plugin = EN_USPS_BASE_NAME;
        }

        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=usps_small">' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="' . EN_USPS_SUPPORT_URL . '" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }

        return $actions;
    }

    add_filter('plugin_action_links', 'en_usps_freight_action_links', 10, 2);
}

/**
 * globally script variable
 */
if (!function_exists('en_usps_admin_inline_js')) {

    function en_usps_admin_inline_js() {
        ?>
        <script>
            let EN_USPS_DIR_FILE
            = "<?php echo esc_js(EN_USPS_DIR_FILE); ?>";
        </script>
        <?php
    }

    add_action('admin_print_scripts', 'en_usps_admin_inline_js');
}

/**
 * Transportation insight action links
 * @staticvar $plugin
 * @param $actions
 * @param $plugin_file
 * @return array
 */
if (!function_exists('en_usps_admin_action_links')) {

    function en_usps_admin_action_links($actions, $plugin_file) {
        static $plugin;
        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);
        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=usps">' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="' . EN_USPS_SUPPORT_URL . '" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }
        return $actions;
    }

    add_filter('plugin_action_links_' . EN_USPS_BASE_NAME, 'en_usps_admin_action_links', 10, 2);
}

/**
 * Transportation insight method in woo method list
 * @param $methods
 * @return string
 */
if (!function_exists('en_usps_add_shipping_app')) {

    function en_usps_add_shipping_app($methods) {
        $methods['usps'] = 'EnUspsShippingRates';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'en_usps_add_shipping_app', 10, 1);
}
/**
 * The message show when no rates will display on the cart page
 */
if (!function_exists('en_none_shipping_rates')) {

    function en_none_shipping_rates() {
        $en_eniture_shipment = apply_filters('en_eniture_shipment', []);
        if (isset($en_eniture_shipment['LTL'])) {
            return esc_html("<div><p>There are no shipping methods available. 
                    Please double check your address, or contact us if you need any help.</p></div>");
        }
    }

    add_filter('woocommerce_cart_no_shipping_available_html', 'en_none_shipping_rates');
}

/**
 * Transportation insight plan status
 * @param array $plan_status
 * @return array
 */
if (!function_exists('en_usps_plan_status')) {

    function en_usps_plan_status($plan_status) {

        $features = ['hazardous_material', 'insurance'];
        foreach($features as $key => $feature){
            $response = apply_filters("usps_plans_suscription_and_features", $feature);
            if (is_array($response)) {
                $plan_required = '1';
                $feature_status = EN_USPS_NAME . ': Upgrade to Standard Plan to enable.';
            }else{
                $plan_required = '0';
                $feature_status = EN_USPS_NAME . ': Enabled.';
            }

            $plan_status[$feature]['usps'][] = 'usps';
            $plan_status[$feature]['plan_required'][] = $plan_required;
            $plan_status[$feature]['status'][] = $feature_status;
        }

        return $plan_status;
    }

    add_filter('en_app_common_plan_status', 'en_usps_plan_status', 10, 1);
}
/**
 * The message show when no rates will display on the cart page
 */
if (!function_exists('en_app_load_restricted_duplicate_classes')) {

    function en_app_load_restricted_duplicate_classes() {
        new \EnUspsProductDetail\EnUspsProductDetail();
    }

    en_app_load_restricted_duplicate_classes();
}

/**
 * Hide third party shipping rates
 * @param mixed $available_methods
 * @return mixed
 */
if (!function_exists('en_usps_hide_shipping')) {

    function en_usps_hide_shipping($available_methods) {
        $en_eniture_shipment = apply_filters('en_eniture_shipment', []);
        $en_shipping_applications = apply_filters('en_shipping_applications', []);
        $eniture_old_plugins = get_option('EN_Plugins');
        $eniture_old_plugins = $eniture_old_plugins ? json_decode($eniture_old_plugins, true) : [];
        $en_eniture_apps = array_merge($en_shipping_applications, $eniture_old_plugins);

        if (get_option('en_usps_allow_other_plugin_quotes') == 'no' && count($available_methods) > 0) {
            $rates_available = false;
            foreach ($available_methods as $value) {
                if ($value->method_id == 'usps') {
                    $rates_available = true;
                    break;
                }
            }

            // add missing methods
            $other_plugins = ['ltl_shipping_method', 'daylight', 'tql', 'unishepper_small', 'usps'];
            $en_eniture_apps = array_merge($en_eniture_apps, $other_plugins);

            if ($rates_available) {
                foreach ($available_methods as $index => $method) {
                    if (!in_array($method->method_id, $en_eniture_apps)) {
                        unset($available_methods[$index]);
                    }
                }
            }
        }

        return $available_methods;
    }

    add_filter('woocommerce_package_rates', 'en_usps_hide_shipping', 99, 1);
}

/**
 * Eniture save app name
 * @param array $en_applications
 * @return array
 */
if (!function_exists('en_usps_shipping_applications')) {

    function en_usps_shipping_applications($en_applications) {
        return array_merge($en_applications, ['usps']);
    }

    add_filter('en_shipping_applications', 'en_usps_shipping_applications', 10, 1);
}

/**
 * Eniture admin notices
 */
if (!function_exists('en_usps_admin_notices')) {

    function en_usps_admin_notices() {
        $en_usps_tabs = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
        if (isset($en_usps_tabs) && ($en_usps_tabs == "usps_small")) {
            echo '<div class="notice notice-success is-dismissible"> <p>' . EN_USPS_PLAN_MESSAGE . '</p> </div>';
        }
    }

    add_filter('admin_notices', 'en_usps_admin_notices');
}

/**
 * Custom error message.
 * @param string $message
 * @return string|void
 */
if (!function_exists('en_usps_error_message')) {

    function en_usps_error_message($message) {
        $en_eniture_shipment = apply_filters('en_eniture_shipment', []);
        $reasons = apply_filters('en_usps_reason_quotes_not_returned', []);

        if (isset($en_eniture_shipment['SPQ']) || !empty($reasons)) {
            $en_settings = json_decode(EN_USPS_SET_QUOTE_SETTINGS, true);
            $message = (isset($en_settings['custom_error_message'])) ? $en_settings['custom_error_message'] : '';
            $custom_error_enabled = (isset($en_settings['custom_error_enabled'])) ? $en_settings['custom_error_enabled'] : '';

            switch ($custom_error_enabled) {
                case 'prevent':
                    remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
                    break;
                case 'allow':
                    add_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
                    break;
                default:
                    $message = '<div><p>There are no shipping methods available. Please double check your address, or contact us if you need any help.</p></div>';
                    break;
            }

            $message = !empty($reasons) ? implode(", ", $reasons) : $message;
        }

        return __($message);
    }

    add_filter('woocommerce_cart_no_shipping_available_html', 'en_usps_error_message', 999, 1);
    add_filter('woocommerce_no_shipping_available_html', 'en_usps_error_message', 999, 1);
}
// Create plugin option
if (!function_exists('en_usps_create_option')) {

    function en_usps_create_option() {
        $eniture_plugins = get_option('EN_Plugins');
        if (!$eniture_plugins) {
            add_option('EN_Plugins', wp_json_encode(array('EnUspsShippingRates')));
        } else {
            $plugins_array = json_decode($eniture_plugins, true);
            if (!in_array('EnUspsShippingRates', $plugins_array)) {
                array_push($plugins_array, 'EnUspsShippingRates');
                update_option('EN_Plugins', json_encode($plugins_array));
            }
        }
    }

    en_usps_create_option();
}

/**
 * Remove plugin option
 */
if(!function_exists('en_usps_small_deactivate_plugin')) {
    function en_usps_small_deactivate_plugin()
    {
        $eniture_plugins = get_option('EN_Plugins');
        $plugins_array = json_decode($eniture_plugins, true);
        $plugins_array = !empty($plugins_array) && is_array($plugins_array) ? $plugins_array : array();
        $key = array_search('EnUspsShippingRates', $plugins_array);
        if ($key !== false) {
            unset($plugins_array[$key]);
        }
        update_option('EN_Plugins', json_encode($plugins_array));
    }
    register_deactivation_hook(EN_USPS_MAIN_FILE, 'en_usps_small_deactivate_plugin');
}

/**
 * Filter For CSV Import
 */
if (!function_exists('en_import_dropship_location_csv')) {

    /**
     * Import drop ship location CSV
     * @param $data
     * @param $this
     * @return array
     */
    function en_import_dropship_location_csv($data, $parseData) {
        $en_product_freight_class = $en_product_freight_class_variation = '';
        $en_dropship_location = $locations = [];
        foreach ($data['meta_data'] as $key => $metaData) {
            $location = explode(',', trim($metaData['value']));
            switch ($metaData['key']) {
                // Update new columns
                case '_product_freight_class':
                    $en_product_freight_class = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_product_freight_class_variation':
                    $en_product_freight_class_variation = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_nickname':
                    $locations[0] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_zip_code':
                    $locations[1] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_city':
                    $locations[2] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_state':
                    $locations[3] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_country':
                    $locations[4] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location':
                    $en_dropship_location = $location;
            }
        }

        // Update new columns
        if (strlen($en_product_freight_class) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight',
                'value' => $en_product_freight_class,
            ];
        }

        // Update new columns
        if (strlen($en_product_freight_class_variation) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight_variation',
                'value' => $en_product_freight_class_variation,
            ];
        }

        if (!empty($locations) || !empty($en_dropship_location)) {
            if (isset($locations[0]) && is_array($locations[0])) {
                foreach ($locations[0] as $key => $location_arr) {
                    $metaValue = [];
                    if (isset($locations[0][$key], $locations[1][$key], $locations[2][$key], $locations[3][$key])) {
                        $metaValue[0] = $locations[0][$key];
                        $metaValue[1] = $locations[1][$key];
                        $metaValue[2] = $locations[2][$key];
                        $metaValue[3] = $locations[3][$key];
                        $metaValue[4] = $locations[4][$key];
                        $dsId[] = en_serialize_dropship($metaValue);
                    }
                }
            } else {
                $dsId[] = en_serialize_dropship($en_dropship_location);
            }

            $sereializedLocations = maybe_serialize($dsId);
            $data['meta_data'][] = [
                'key' => '_dropship_location',
                'value' => $sereializedLocations,
            ];
        }
        return $data;
    }

    add_filter('woocommerce_product_importer_parsed_data', 'en_import_dropship_location_csv', '99', '2');
}

/**
 * Serialize drop ship
 * @param $metaValue
 * @return string
 * @global $wpdb
 */
if (!function_exists('en_serialize_dropship')) {

    function en_serialize_dropship($metaValue) {
        global $wpdb;
        $dropship = (array) reset($wpdb->get_results(
                                "SELECT id
                        FROM " . $wpdb->prefix . "warehouse WHERE nickname='$metaValue[0]' AND zip='$metaValue[1]' AND city='$metaValue[2]' AND state='$metaValue[3]' AND country='$metaValue[4]'"
        ));

        $dropship = array_map('intval', $dropship);

        if (empty($dropship['id'])) {
            $data = en_csv_import_dropship_data($metaValue);
            $wpdb->insert(
                    $wpdb->prefix . 'warehouse', $data
            );

            $dsId = $wpdb->insert_id;
        } else {
            $dsId = $dropship['id'];
        }

        return $dsId;
    }

}

/**
 * Filtered Data Array
 * @param $metaValue
 * @return array
 */
if (!function_exists('en_csv_import_dropship_data')) {

    function en_csv_import_dropship_data($metaValue) {
        return array(
            'city' => $metaValue[2],
            'state' => $metaValue[3],
            'zip' => $metaValue[1],
            'country' => $metaValue[4],
            'location' => 'dropship',
            'nickname' => (isset($metaValue[0])) ? $metaValue[0] : "",
        );
    }

}

// Define reference
if (!function_exists('en_usps_plugin')) {

    function en_usps_plugin($plugins) {
        $plugins['spq'] = (isset($plugins['spq'])) ? array_merge($plugins['lfq'], ['usps' => 'EnUspsShippingRates']) : ['usps' => 'EnUspsShippingRates'];
        return $plugins;
    }

    add_filter('en_plugins', 'en_usps_plugin');
}

/**
 * Update warehouse table
 */
if (!function_exists('en_usps_update_warehouse_db')) {

    function en_usps_update_warehouse_db() {
        global $wpdb;
        $warehouse_table = $wpdb->prefix . "warehouse";
        $warehouse_address = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'phone_instore'");
        if (!(isset($warehouse_address->Field) && $warehouse_address->Field == 'phone_instore')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN address VARCHAR(255) NOT NULL", $warehouse_table));
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN phone_instore VARCHAR(255) NOT NULL", $warehouse_table));
        }

        $usps_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
        if (!(isset($usps_origin_markup->Field) && $usps_origin_markup->Field == 'origin_markup')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(255) NOT NULL", $warehouse_table));
        }
    }

    en_usps_update_warehouse_db();
    EnUspsWarehouse::create_usps_small_shipping_rules_table();
}

add_action('upgrader_process_complete', 'en_usps_update_warehouse_db', 10);

// fdo va
add_action('wp_ajax_nopriv_usps_fd', 'usps_fd_api');
add_action('wp_ajax_usps_fd', 'usps_fd_api');
/**
 * UPS AJAX Request
 */
function usps_fd_api()
{
    $store_name =  EnUspsConfig::en_get_server_name();
    $company_id = $_POST['company_id'];
    $data = [
        'plateform'  => 'wp',
        'store_name' => $store_name,
        'company_id' => $company_id,
        'fd_section' => 'tab=usps_small&section=section-4',
    ];
    if (is_array($data) && count($data) > 0) {
        if($_POST['disconnect'] != 'disconnect') {
            $url =  'https://freightdesk.online/validate-company';
        }else {
            $url = 'https://freightdesk.online/disconnect-woo-connection';
        }
        $response = wp_remote_post($url, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $data,
            ]
        );
        $response = wp_remote_retrieve_body($response);
    }
    if($_POST['disconnect'] == 'disconnect') {
        $result = json_decode($response);
        if ($result->status == 'SUCCESS') {
            update_option('en_fdo_company_id_status', 0);
        }
    }
    echo $response;
    exit();
}
add_action('rest_api_init', 'en_rest_api_init_status_usps');
function en_rest_api_init_status_usps()
{
    register_rest_route('fdo-company-id', '/update-status', array(
        'methods' => 'POST',
        'callback' => 'en_usps_fdo_data_status',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update FDO coupon data
 * @param array $request
 * @return array|void
 */
function en_usps_fdo_data_status(WP_REST_Request $request)
{
    $status_data = $request->get_body();
    $status_data_decoded = json_decode($status_data);
    if (isset($status_data_decoded->connection_status)) {
        update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
        update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
    }
    return true;
}

/**
 * To export order 
 */
if (!function_exists('en_export_order_on_order_place')) {
    
    function en_export_order_on_order_place() 
    {
        new \EnUspsOrderExport\EnUspsOrderExport();
    }

    en_export_order_on_order_place();
}