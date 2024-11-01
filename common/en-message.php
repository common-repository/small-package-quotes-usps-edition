<?php

/**
 * All App Name messages
 */

namespace EnUspsMessage;

/**
 * Messages are relate to errors, warnings, headings
 * Class EnUspsMessage
 * @package EnUspsMessage
 */
if (!class_exists('EnUspsMessage')) {

    class EnUspsMessage
    {

        /**
         * Add all messages
         * EnUspsMessage constructor.
         */
        public function __construct()
        {
            if (!defined('EN_USPS_ROOT_URL')){
                define('EN_USPS_ROOT_URL', esc_url('https://eniture.com'));
            }
            define('EN_USPS_700', "You are currently on the Trial Plan. Your plan will be expire on ");
            define('EN_USPS_701', "You are currently on the Basic Plan. The plan renews on ");
            define('EN_USPS_702', "You are currently on the Standard Plan. The plan renews on ");
            define('EN_USPS_703', "You are currently on the Advanced Plan. The plan renews on ");
            define('EN_USPS_PLANS_URL', EN_USPS_ROOT_URL . '/woocommerce-usps-small-package-plugin/');
            define('EN_USPS_704', "Your currently plan subscription is inactive <a href='javascript:void(0)' data-action='en_usps_get_current_plan' onclick='en_update_plan(this);'>Click here</a> to check the subscription status. If the subscription status remains 
                inactive. Please activate your plan subscription from <a target='_blank' href='" . EN_USPS_PLANS_URL . "'>here</a>");

            define('EN_USPS_715', "<a target='_blank' class='en_plan_notification' href='" . EN_USPS_PLANS_URL . "'>
                        Basic Plan required
                    </a>");
            define('EN_USPS_705', "<a target='_blank' class='en_plan_notification' href='" . EN_USPS_PLANS_URL . "'>
                        Standard Plan required
                    </a>");
            define('EN_USPS_706', "<a target='_blank' class='en_plan_notification' href='" . EN_USPS_PLANS_URL . "'>
                        Advanced Plan required
                    </a>");
            define('EN_USPS_707', "Please verify credentials at connection settings panel.");
            define('EN_USPS_708', "Please enter valid US or Canada zip code.");
            define('EN_USPS_709', "Success! The test resulted in a successful connection.");
            define('EN_USPS_710', "Zip code already exists.");
            define('EN_USPS_711', "Connection settings are missing.");
            define('EN_USPS_712', "Shipping parameters are not correct.");
            define('EN_USPS_713', "Origin address is missing.");
            define('EN_USPS_714', ' <a href="javascript:void(0)" data-action="en_usps_get_current_plan" onclick="en_update_plan(this);">Click here</a> to refresh the plan');
        }

    }

}
