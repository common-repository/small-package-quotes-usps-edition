<?php
/**
 * App Name guard.
 */

namespace EnUspsGuard;

use EnUspsLoad\EnUspsLoad;

/**
 * Versions compatibility.
 * Class EnUspsGuard
 * @package EnUspsGuard
 */
if (!class_exists('EnUspsGuard')) {

    class EnUspsGuard
    {

        private $plugin_name;
        private $php_version;
        private $wp_version;
        private $wc_version;
        private $errors;
        private $warnings;

        /**
         * @param string $plugin_name
         * @param string $php_version
         * @param string $wp_version
         * @param string|null $wc_version
         * @return void
         */
        private function __construct($plugin_name, $php_version, $wp_version, $wc_version)
        {
            $this->plugin_name = $plugin_name;
            $this->php_version = $php_version;
            $this->wp_version = $wp_version;
            $this->wc_version = $wc_version;

            // Hook admin_notices always since errors can be added lately
            add_action('admin_notices', array($this, 'en_show_for_notices'));
        }

        /**
         * @param string $plugin_name
         * @param string $php_version
         * @param string $wp_version
         * @param string|null $wc_version
         * @return void
         */
        public static function en_check_prerequisites($plugin_name, $php_version, $wp_version, $wc_version)
        {
            $instance = new self($plugin_name, $php_version, $wp_version, $wc_version);
            return $instance->en_check_for_prerequisites();
        }

        /**
         * Show notices
         */
        public function en_show_for_notices()
        {
            $this->en_show_notices($this->errors, 'error');
            $this->en_show_notices($this->warnings, 'warning');
        }

        /**
         * Check for errors.
         */
        public function en_check_woocommerce_for_version()
        {
            $wc_version = defined('WC_VERSION') ? WC_VERSION : null;

            if (!isset($wc_version) || version_compare($wc_version, $this->wc_version, '<')) {
                $this->errors[] = 'You are running an outdated WooCommerce version".(isset($wc_version) ? " ".$wc_version : null).".
                 {plugin_name} requires WooCommerce {wc_version}+.
                 Consider updating to a modern WooCommerce version.';
                return;
            }
        }

        /**
         * @return void
         */
        public function en_check_for_prerequisites()
        {
            global $wp_version;
            $this->errors = [];
            $this->warnings = [];

            if (version_compare($phpv = PHP_VERSION, $this->php_version, '<')) {
                $this->errors[] = "You are running an outdated PHP version {$phpv}. 
                 {plugin_name} requires PHP {php_version}+. 
                 Contact your hosting support to switch to a newer PHP version.";
            }

            if (isset($wp_version) && version_compare($wp_version, $this->wp_version, '<')) {
                $this->errors[] = "You are running an outdated WordPress version {$wp_version}.
                 {plugin_name} is tested with WordPress {wp_version}+.
                 Consider updating to a modern WordPress version.";
            }

            if (isset($this->wc_version)) {
                if (!self::is_woocommerce_active()) {
                    $this->errors[] = 'WooCommerce is not active. 
                     {plugin_name} requires WooCommerce to be installed and activated.';
                } else {
                    if (defined('WC_VERSION') || did_action('woocommerce_loaded')) {
                        $this->en_check_woocommerce_for_version();
                    } else {
                        add_action('woocommerce_loaded', array($this, 'en_check_woocommerce_for_version'));
                    }
                }
            }

            if (empty($this->errors)) {
                // Start to load all classes
                EnUspsLoad::Load();
            }

            return $this->errors;
        }

        /**
         * Version compatibility errors list
         * @param array $errors
         * @return array
         */
        public function en_version_compatibility($errors)
        {
            return array_merge($errors, $this->errors);
        }

        /**
         * Show notices
         * @param array $notices
         * @param $kind
         */
        public function en_show_notices($notices, $kind)
        {
            if ($notices) {
                foreach ($notices as $en_dismiss_id => $notice):
                    ?>
                    <?php
                    $en_dismiss_class = null;
                    $en_dismiss_attr = null;
                    if (is_string($en_dismiss_id) && !empty($en_dismiss_id)) {
                        $en_dismiss_class = 'is-dismissible';
                        $en_dismiss_attr = 'data-dismissible=' . esc_html($en_dismiss_id);
                    }
                    ?>
                    <div class="notice notice-<?php echo esc_html($kind) ?> <?php echo esc_attr($en_dismiss_class) ?>"
                        <?php echo $en_dismiss_attr ?>
                    >
                        <?php
                        $notice = strtr($notice, array(
                            '{plugin_name}' => $this->plugin_name,
                            '{php_version}' => $this->php_version,
                            '{wp_version}' => $this->wp_version,
                            '{wc_version}' => $this->wc_version,
                        ));
                        ?>
                        <p><?php echo esc_html($notice) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php
            }
        }

        /**
         * Condition check WooCommerce is active or not.
         * @return bool
         */
        public static function is_woocommerce_active()
        {
            static $active_plugins;

            if (!isset($active_plugins)) {
                $active_plugins = (array)get_option('active_plugins', []);
                if (is_multisite()) {
                    $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', []));
                }
            }

            return
                in_array('woocommerce/woocommerce.php', $active_plugins) ||
                array_key_exists('woocommerce/woocommerce.php', $active_plugins);
        }

    }

}