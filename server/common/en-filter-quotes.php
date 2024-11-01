<?php

/**
 * Filter rates.
 */

namespace EnUspsFilterQuotes;

use EnUspsVersionCompact\EnUspsVersionCompact;

/**
 * Rates according selected rating method.
 * Class EnUspsFilterQuotes
 * @package EnUspsFilterQuotes
 */
if (!class_exists('EnUspsFilterQuotes')) {

    class EnUspsFilterQuotes
    {
        static public $quotes;
        static public $quote_settings;
        static public $total_carriers;

        /**
         * Get random id for quote
         * @return string
         */
        static public function rand_string()
        {
            $alphabets = 'abcdefghijklmnopqrstuvwxyz';
            return substr(str_shuffle(str_repeat($alphabets, mt_rand(1, 10))), 1, 10);
        }

    }

}