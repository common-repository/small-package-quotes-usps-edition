<?php
/**
 * App Name tabs.
 */

use EnUspsConnectionSettings\EnUspsConnectionSettings;
use EnUspsCarriers\EnUspsCarriers;

if (!class_exists('EnUspsTab')) {
    /**
     * Tabs show on admin side.
     * Class EnUspsTab
     */
    class EnUspsTab extends WC_Settings_Page
    {
        /**
         * Hook for call.
         */
        public function en_load()
        {
            $this->id = 'usps_small';
            add_filter('woocommerce_settings_tabs_array', [$this, 'add_settings_tab'], 50);
            add_action('woocommerce_sections_' . $this->id, [$this, 'output_sections']);
            add_action('woocommerce_settings_' . $this->id, [$this, 'output']);
            add_action('woocommerce_settings_save_' . $this->id, [$this, 'save']);
        }

        /**
         * Setting Tab For Woocommerce
         * @param $settings_tabs
         * @return string
         */
        public function add_settings_tab($settings_tabs)
        {
            $settings_tabs[$this->id] = __('USPS', 'woocommerce-settings-usps');
            return $settings_tabs;
        }

        /**
         * Setting Sections
         * @return array
         */
        public function get_sections()
        {
            $sections = array(
                '' => __('Connection Settings', 'woocommerce-settings-usps'),
                'section-2' => __('Quote Settings', 'woocommerce-settings-usps'),
                'section-3' => __('Warehouses', 'woocommerce-settings-usps'),
                'shipping-rules' => __('Shipping Rules', 'woocommerce-settings-usps'),
                // fdo va
                'section-4' => __('FreightDesk Online', 'woocommerce-settings-usps'),
                'section-5' => __('Validate Addresses', 'woocommerce-settings-usps'),
                'section-6' => __('User Guide', 'woocommerce-settings-usps'),
            );

            // Logs data
            $enable_logs = get_option('en_usps_shipping_logs');
            if ($enable_logs == 'yes') {
                $sections['en-logs'] = 'Logs';
            }

            $sections = apply_filters('en_usps_add_sections', $sections);
            $sections = apply_filters('en_woo_addons_sections', $sections, EN_USPS_SHIPPING_NAME);
            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }


        /**
         * Display all pages on wc settings tabs
         * @param $section
         * @return array
         */
        public function get_settings($section = null)
        {
            ob_start();
            switch ($section) {

                case 'section-2' :
                    $settings = \EnUspsQuoteSettings\EnUspsQuoteSettings::Load();
                    break;

                case 'section-3':
                    EnLocation::en_load();
                    $settings = [];
                    break;
                // fdo va
                case 'section-4' :
                    \EnUspsFreightdeskOnline\EnUspsFreightdeskOnline::en_load();
                    $settings = [];
                    break;

                case 'section-5' :
                    \EnUspsValidateAddress\EnUspsValidateAddress::en_load();
                    $settings = [];
                    break;
                case 'section-6' :
                    \EnUspsUserGuide\EnUspsUserGuide::en_load();
                    $settings = [];
                    break;

                case 'shipping-rules' :
                    include_once('shipping-rules/shipping-rules-template.php');
                    $settings = [];
                    break;

                case 'en-logs' :
                    $this->shipping_logs_section();
                    $settings = [];
                    break;

                default:
                    $settings = EnUspsConnectionSettings::en_load();
                    break;
            }

            $settings = apply_filters('en_usps_add_settings', $settings, $section);
            $settings = apply_filters('en_woo_addons_settings', $settings, $section, EN_USPS_SHIPPING_NAME);
            $settings = $this->avaibility_addon($settings);
            return apply_filters('woocommerce-settings-usps', $settings, $section);
        }

        /**
         * RAD addon activated or not
         * @param array type $settings
         * @return array type
         */
        function avaibility_addon($settings)
        {
            if (!function_exists('is_plugin_active')) {
                require_once(EN_USPS_ABSPATH . '/wp-admin/includes/plugin.php');
            }

            if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
                unset($settings['avaibility_lift_gate']);
                unset($settings['avaibility_auto_residential']);
            }

            if (is_plugin_active('standard-box-sizes/standard-box-sizes.php') || is_plugin_active('standard-box-sizes/en-standard-box-sizes.php')) {
                unset($settings['avaibility_box_sizing']);
            }

            return $settings;
        }

        /**
         * WooCommerce Settings Tabs
         * @global $current_section
         */
        public function output()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);
            WC_Admin_Settings::output_fields($settings);
        }

        /**
         * Woocommerce Save Settings
         * @global $current_section
         */
        public function save()
        {
            global $current_section;
            $settings = $this->get_settings($current_section);
            if (isset($_POST['en_usps_cutt_off_time']) && strlen($_POST['en_usps_cutt_off_time']) > 0) {
                $_POST['en_usps_cutt_off_time'] = $this->get_time_in_24_hours(sanitize_text_field($_POST['en_usps_cutt_off_time']));
            }
            WC_Admin_Settings::save_fields($settings);
        }

        /**
         * Change time format.
         * @param $timeStr
         * @return false|string
         */
        public function get_time_in_24_hours($time_str)
        {
            $cutOffTime = explode(' ', $time_str);
            $hours = $cutOffTime[0];
            $separator = $cutOffTime[1];
            $minutes = $cutOffTime[2];
            $meridiem = $cutOffTime[3];
            $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
            return date("H:i", strtotime($cutOffTime));
        }

        /**
         * Shipping Logs Section
        */
        public function shipping_logs_section()
        {
            include_once plugin_dir_path(__FILE__) . 'logs/en-logs.php';
        }
    }

    $en_tab = new EnUspsTab();
    return $en_tab->en_load();
}
