<?php

/**
 * Version compactibility.
 */

namespace EnUspsVersionCompact;

/**
 * Get valid php dericated function.
 * Class EnUspsVersionCompact
 * @package EnUspsVersionCompact
 */
if (!class_exists('EnUspsVersionCompact')) {

    class EnUspsVersionCompact
    {
        static public $php_version = PHP_VERSION;

        /**
         * @param array $data
         * @param string $key
         * @return array
         */
        static public function en_array_column($data, $key)
        {
            $old_version = self::$php_version <= 5.4;
            $columns = (!$old_version && function_exists("array_column")) ? array_column($data, $key) : [];
            $arr_length = count($data);
            if (empty($arr_length) || !$old_version)
                return $columns;
            $index_arr = array_fill(0, $arr_length, $key);
            $columns = array_map(function ($data, $index) {
                $column = is_object($data) ? $data->$index : $data[$index];
                return $column;
            }, $data, $index_arr);
            return $columns;
        }

    }

}