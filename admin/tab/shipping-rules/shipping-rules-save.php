<?php

namespace EnUspsShippingRulesAjaxReq;

if (!class_exists("EnUspsShippingRulesAjaxReq")) {

    class EnUspsShippingRulesAjaxReq
    {
        /**
         * Get shipping rules ajax request
         */
        public function __construct()
        {
            add_action('wp_ajax_nopriv_en_usps_save_shipping_rule', array($this, 'save_shipping_rule_usps_ajax'));
            add_action('wp_ajax_en_usps_save_shipping_rule', array($this, 'save_shipping_rule_usps_ajax'));

            add_action('wp_ajax_nopriv_en_usps_edit_shipping_rule', array($this, 'edit_shipping_rule_usps_ajax'));
            add_action('wp_ajax_en_usps_edit_shipping_rule', array($this, 'edit_shipping_rule_usps_ajax'));

            add_action('wp_ajax_nopriv_en_usps_delete_shipping_rule', array($this, 'delete_shipping_rule_usps_ajax'));
            add_action('wp_ajax_en_usps_delete_shipping_rule', array($this, 'delete_shipping_rule_usps_ajax'));

            add_action('wp_ajax_nopriv_en_usps_update_shipping_rule_status', array($this, 'update_shipping_rule_status_usps_ajax'));
            add_action('wp_ajax_en_usps_update_shipping_rule_status', array($this, 'update_shipping_rule_status_usps_ajax'));
        }

        /**
         * Save Shipping Rule Function
         * @global $wpdb
         */
        function save_shipping_rule_usps_ajax()
        {
            global $wpdb;

            $insert_qry = $update_qry = '';
            $error = false;
            $data = $_POST;
            $get_shipping_rule_id = (isset($data['rule_id']) && intval($data['rule_id'])) ? $data['rule_id'] : "";
            $last_id = $get_shipping_rule_id;
            $qry = "SELECT * FROM " . $wpdb->prefix . "eniture_usps_small_shipping_rules WHERE name = '" . $data['name'] . "'"; 
            $get_shipping_rule = $wpdb->get_results($qry);
            unset($data['action']);
            unset($data['rule_id']);
            
            if (!empty($get_shipping_rule_id)) {
                $data['settings'] = json_encode($data['settings']);
                $update_qry = $wpdb->update(
                    $wpdb->prefix . 'eniture_usps_small_shipping_rules', $data, array('id' => $get_shipping_rule_id)
                );

                $update_qry = (!empty($get_shipping_rule) && reset($get_shipping_rule)->id == $get_shipping_rule_id) ? 1 : $update_qry;
            } else {
                if (!empty($get_shipping_rule)) {
                    $error = true;
                } else {
                    $data['settings'] = json_encode($data['settings']);
                    $insert_qry = $wpdb->insert($wpdb->prefix . 'eniture_usps_small_shipping_rules', $data);
                    $last_id = $wpdb->insert_id;
                }
            }

            $shipping_rules_list = array('name' => $data["name"], 'type' => $data["type"], 'is_active' => $data["is_active"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $last_id, 'error' => $error);

            echo json_encode($shipping_rules_list);
            exit;
        }

        /**
         * Edit Shipping Rule Function
         * @global $wpdb
         */
        function edit_shipping_rule_usps_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['edit_id']) && intval($_POST['edit_id'])) ? $_POST['edit_id'] : "";
            $shipping_rules_list = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "eniture_usps_small_shipping_rules WHERE id=$get_shipping_rule_id"
            );
            $product_tags_markup = $this->get_product_tags_markup($shipping_rules_list);
            $data = ['rule_data' => reset($shipping_rules_list), 'product_tags_markup' => $product_tags_markup];

            echo json_encode($data);
            exit;
        }

        /**
         * Delete Shipping Rule Function
         * @global $wpdb
         */
        function delete_shipping_rule_usps_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['delete_id']) && intval($_POST['delete_id'])) ? $_POST['delete_id'] : "";
            $qry = $wpdb->delete($wpdb->prefix . 'eniture_usps_small_shipping_rules', array('id' => $get_shipping_rule_id));

            echo json_encode(['query' => $qry]);
            exit;
        }

        /**
         * Update Shipping Rule Status Function
         * @global $wpdb
         */
        function update_shipping_rule_status_usps_ajax()
        {
            global $wpdb;
            $get_shipping_rule_id = (isset($_POST['rule_id']) && intval($_POST['rule_id'])) ? $_POST['rule_id'] : "";
            $is_active = isset($_POST['is_active']) ? $_POST['is_active'] : "";
            $data = ['is_active' => $is_active];
            
            $update_qry = $wpdb->update(
                $wpdb->prefix . 'eniture_usps_small_shipping_rules', $data, array('id' => $get_shipping_rule_id)
            );

            echo json_encode(['id' => $get_shipping_rule_id, 'is_active' => $is_active, 'update_qry' => $update_qry]);
            exit;
        }

        function get_product_tags_markup($shipping_rules_list)
        {
            $tags_options = '';
            $shipping_rules_list = reset($shipping_rules_list);
            $tags_data = isset($shipping_rules_list->settings) ? json_decode($shipping_rules_list->settings, true) : [];
            $selected_tags_detials = $this->get_selected_tags_details($tags_data['filter_by_product_tag_value']);

            if (!empty($selected_tags_detials) && is_array($selected_tags_detials)) {
                foreach ($selected_tags_detials as $key => $tag) {
                    $tags_options .= "<option selected='selected' value='" . esc_attr($tag['term_taxonomy_id']) . "'>" . esc_html($tag['name']) . "</option>";
                }
            }
            
            if (empty($tags_data['filter_by_product_tag_value']) || !is_array($tags_data['filter_by_product_tag_value'])) {
                $tags_data['filter_by_product_tag_value'] = [];
            }

            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
            if (!empty($en_woo_product_tags) && is_array($tags_data['filter_by_product_tag_value'])) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (!in_array($tag->term_id, $tags_data['filter_by_product_tag_value'])) {
                        $tags_options .= "<option value='" . esc_attr($tag->term_taxonomy_id) . "'>" . esc_html($tag->name) . "</option>";
                    }
                }
            }

            return $tags_options;
        }

        function get_selected_tags_details($products_tags_arr)
        {
            if (empty($products_tags_arr) || !is_array($products_tags_arr)) {
                return [];
            }

            $tags_detail = [];
            $count = 0;
            $en_woo_product_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
            
            if (isset($en_woo_product_tags) && !empty($en_woo_product_tags)) {
                foreach ($en_woo_product_tags as $key => $tag) {
                    if (in_array($tag->term_taxonomy_id, $products_tags_arr)) {
                        $tags_detail[$count]['term_id'] = $tag->term_id;
                        $tags_detail[$count]['name'] = $tag->name;
                        $tags_detail[$count]['slug'] = $tag->slug;
                        $tags_detail[$count]['term_taxonomy_id'] = $tag->term_taxonomy_id;
                        $tags_detail[$count]['description'] = $tag->description;
                        $count++;
                    }
                }
            }

            return $tags_detail;
        }
    }
}
