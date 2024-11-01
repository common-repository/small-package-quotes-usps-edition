<?php

/**
 * App Name load classes.
 */

namespace EnUspsLoad;

use EnSBS\EnSBS;
use EnUspsCsvExport\EnUspsCsvExport;
use EnUspsOrderWidget\EnUspsOrderWidget;
use EnUspsConfig\EnUspsConfig;
use EnUspsCreateLTLClass\EnUspsCreateLTLClass;
use EnUspsLocationAjax\EnUspsLocationAjax;
use EnUspsMessage\EnUspsMessage;
use EnUspsOrderRates\EnUspsOrderRates;
use EnUspsOrderScript\EnUspsOrderScript;
use EnUspsPlans\EnUspsPlans;
use EnUspsWarehouse\EnUspsWarehouse;
use EnUspsTestConnection\EnUspsTestConnection;
use EnUspsShippingRulesAjaxReq\EnUspsShippingRulesAjaxReq;

/**
 * Load classes.
 * Class EnUspsLoad
 * @package EnUspsLoad
 */
if (!class_exists('EnUspsLoad')) {

    class EnUspsLoad
    {
        /**
         * Load classes of App Name plugin
         */
        static public function Load()
        {
            new EnUspsMessage();
            new EnUspsPlans();
            EnUspsConfig::do_config();
            new \WC_EnUspsShippingRates();
            if (is_admin()) {
                new EnUspsWarehouse();
                new EnUspsTestConnection();
                new EnUspsLocationAjax();
                new EnUspsOrderRates();
                new EnUspsOrderScript();
                !class_exists('EnOrderWidget') ? new EnUspsOrderWidget() : '';
                !class_exists('EnCsvExport') ? new EnUspsCsvExport() : '';
                new EnSBS();
                new EnUspsShippingRulesAjaxReq();
            }
        }
    }
}