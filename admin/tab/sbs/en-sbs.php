<?php

namespace EnSBS;

if (!class_exists('EnSBS')) {

    class EnSBS {

        public function __construct() {
            add_filter('en_woo_addons_box_sizing_flat_rate_text_fields_arr', array($this, 'en_woo_addons_box_sizing_flat_rate_text_fields_arr'), 10, 1);
        }

        public function en_woo_addons_box_sizing_flat_rate_text_fields_arr($box_sizes) {

            if (isset($_REQUEST['tab']) && ($_REQUEST['tab'] != 'usps_small')) {
                return $box_sizes;
                return array('fields' => $box_sizes);
            }

            $box_sizes_popup = array(
                "sm_box_sizing_nickname" => array(
                    "type" => "text",
                    "title" => "Nickname",
                    "label" => "Nickname",
                    "id" => "sm_box_sizing_nickname",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-12",
                    "data_type" => "string",
                    "data_length" => ''),
                // USPS flat rate
                "sm_box_size_type" => array(
                    "type" => "dropdown",
                    "title" => "Box Type",
                    "label" => "Box Type",
                    "id" => "sm_box_size_type",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-12",
                    "data_type" => "text",
                    "select_option" => array(
                        'upm_default' => 'Merchant defined box (default)',
                        'upm_express_box' => 'USPS Priority Mail Express Box',
                        'upm_box' => 'USPS Priority Mail Box',
                        'upm_large_flat_rate_box' => 'USPS Priority Mail Large Flat Rate Box',
                        'upm_medium_flat_rate_box' => 'USPS Priority Mail Medium Flat Rate Box',
                        'upm_small_flat_rate_box' => 'USPS Priority Mail Small Flat Rate Box',
                        'upm_padded_flat_rate_envelope' => 'USPS Priority Mail Padded Flat Rate Envelope',
                    ),
                    "data_length" => 'data-length="108"'),
                "sm_box_sizing_length" => array(
                    "type" => "text",
                    "title" => "Length (in)",
                    "label" => "Length (in)",
                    "id" => "sm_box_sizing_length",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-4",
                    "data_type" => "number",
                    "data_length" => 'data-length="108"'),
                "sm_box_sizing_width" => array(
                    "type" => "text",
                    "title" => "Width (in)",
                    "label" => "Width (in)",
                    "id" => "sm_box_sizing_width",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-4",
                    "data_type" => "number",
                    "data_length" => 'data-length="108"'),
                "sm_box_sizing_height" => array(
                    "type" => "text",
                    "title" => "Height (in)",
                    "label" => "Height (in)",
                    "id" => "sm_box_sizing_height",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-4",
                    "data_type" => "number",
                    "data_length" => 'data-length="108"'),
                "sm_box_sizing_max_weight" => array(
                    "type" => "text",
                    "title" => "Max Weight (LBS)",
                    "label" => "Max Weight (LBS)",
                    "id" => "sm_box_sizing_max_weight",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-4",
                    "data_type" => "number"
                ),
                "sm_box_sizing_weight" => array(
                    "type" => "text",
                    "title" => "Box Weight (LBS)",
                    "label" => "Box Weight (LBS)",
                    "id" => "sm_box_sizing_weight",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-4",
                    "data_type" => "number",
                    "data_length" => ''),
                // USPS flat rate
                "sm_box_sizing_fee" => array(
                    "type" => "text",
                    "title" => "Box Fee (e.g 1.75)",
                    "label" => "Box Fee (e.g 1.75)",
                    "id" => "sm_box_sizing_fee",
                    "class" => "form-control",
                    "placeholder" => "",
                    "position" => "col-md-4",
                    "data_type" => "number",
                    "data_length" => ''),
            );

            return $box_sizes_popup;
        }

    }

}