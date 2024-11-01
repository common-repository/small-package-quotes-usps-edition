<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shipping Rules Template
 */
if (!function_exists('shipping_rules_template')) {
    function shipping_rules_template($action = false)
    {
      ob_start();

      global $wpdb;
      $shipping_rules_list = $wpdb->get_results(
          "SELECT * FROM " . $wpdb->prefix . "eniture_usps_small_shipping_rules"
      );

      ?>
        <div>
          <table class="en_location_table" id="en_usps_shipping_rules_list">
            <!-- Table Headings -->
            <thead>
                  <tr>
                      <th>Rule Name</th>
                      <th>Type</th>
                      <th>Filters</th>
                      <th>Available</th>
                      <th>Action</th>
                  </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
              <?php
                if (count($shipping_rules_list) > 0) {
                  $count = 0;
                  foreach ($shipping_rules_list as $rule) {
                    $rule->settings = !empty($rule->settings) ? json_decode($rule->settings) : [];

                    ?>
                      <tr id="usps_sr_row_<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>" class="en_usps_sr_row">
                        <td><?php echo $rule->name; ?></td>
                        <td><?php echo $rule->type; ?></td>
                        <td><?php echo isset($rule->settings->filter_name) ? $rule->settings->filter_name : ''; ?></td>
                        <td>
                          <a href="#" class="usps_sr_status_link" data-id="<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>" data-status="<?php echo $rule->is_active; ?>"><?php echo $rule->is_active ? 'Yes' : 'No'; ?></a>
                        </td>
                        <td>
                          <!-- Edit rule link -->
                          <a href="#" class="usps_sr_edit_link" data-id="<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>">
                            <img src="<?php echo plugins_url(); ?>/small-package-quotes-ups-edition/warehouse-dropship/wild/assets/images/edit.png" title="Edit">
                          </a>
                          <!-- Delete rule link -->
                          <a href="#" class="usps_sr_delete_link" data-id="<?php echo (isset($rule->id)) ? esc_attr($rule->id) : ''; ?>">
                            <img src="<?php echo plugins_url(); ?>/small-package-quotes-ups-edition/warehouse-dropship/wild/assets/images/delete.png" title="Delete">
                          </a>
                        </td>
                      </tr>
                    <?php

                    $count++;
                  }
                } else {
                  ?>
                    <tr class="new_warehouse_add en_usps_empty_sr_row" data-id=0>
                      <td class="en_wd_warehouse_list_data" colspan="5" style="text-align: center;">
                        No data found!
                      </td>
                    </tr>
                  <?php
                }
              ?>
            </tbody>
          </table>
        </div>
      <?php

      if ($action) {
          $ob_get_clean = ob_get_clean();
          return $ob_get_clean;
      }
    }
}
?>

<!-- Shipping rules html markup -->
<div class="en_usps_shipping_rules_setting_section">
  <br />
  <!-- Add rule button -->
  <div class="en_sr_add_btn">
    <a href="#" title="Add Rule" class="button-primary" 
    id="en_sr_add_shipping_rule_btn">Add Rule</a>
  </div>

  <div class="updated sr_created">
      <p><strong>Success!</strong> Shipping rule is added successfully.</p>
  </div>
  <div class="updated sr_deleted">
    <p><strong>Success!</strong> Shipping rule is deleted successfully.</p>
  </div>
  <div class="updated sr_updated">
      <p><strong>Success!</strong> Shipping rule is updated successfully.</p>
  </div>

  <!-- Shipping rules data table -->
  <?php shipping_rules_template(); ?>

  <!-- WP Form Close -->
  </form>

  <!-- Add popup for new rule -->
  <div class="en_popup_sr_overlay">
      <div class="en_popup_sr_form">
        <div class="en_popup_sr_heading">
          <h2>Add Rule</h2>
          <a class="en_close_sr_popup" href="#">&times;</a>
        </div>
        <div class="en_popup_sr_content">
          <div class="already_exist sr_already_exist">
            <strong>Error!</strong> Shipping rule with this name already exists.
          </div>

          <form id="add_shipping_rule_usps" role="form">
              <input type="hidden" name="edit_sr_form_id" value="" id="edit_sr_form_id">
              <!-- Rule name -->
              <div class="en_sr_form_control">
                  <label for="en_sr_rule_name">Rule Name <span style="color: red;">*</span></label>
                  <input type="text" title="Rule Name" name="en_sr_rule_name" id="en_sr_rule_name" maxlength="50">
                  <span class="en_sr_err"></span>
              </div>

              <!-- Type -->
              <div class="en_sr_form_control">
                  <label for="rule_type">Type</label>
                  <select name="rule_type" id="rule_type">
                    <option value="Hide Methods">Hide Methods</option>
                  </select>
                  <span class="en_sr_err"></span>
              </div>

              <!-- Apply to -->
              <div class="en_sr_form_control">
                  <label for="apply_to">Apply to:</label>
                  <div>
                    <input type="radio" name="apply_to" value="cart" checked> <span>Cart</span>
                  </div>
                  <div><p></p></div>
                  <div>
                    <input type="radio" name="apply_to" value="shipment"> <span>Shipment</span>
                  </div>
                  <span class="en_sr_err"></span>
              </div>

              <!-- Filter name -->
              <div class="en_sr_form_control">
                  <label for="en_sr_filter_name">Filter Name</label>
                  <input type="text" title="Filter Name" name="en_sr_filter_name" id="en_sr_filter_name" data-optional="1" maxlength="50">
                  <span class="en_sr_err"></span>
              </div>

              <!-- Filter by Weight -->
              <div class="en_sr_form_control">
                <div>
                  <label for="filter_by_weight">
                    <input type="checkbox" title="Filter by weight" name="filter_by_weight" id="filter_by_weight"> Filter by weight
                  </label>
                </div>
                <span class="en_sr_err"></span>
              </div>
              <div class="group_sr_form_control">
                <div class="en_sr_form_control">
                  <label for="en_sr_weight_from">From</label>
                  <input type="text" title="From" name="en_sr_weight_from" id="en_sr_weight_from" data-optional="1" maxlength="10">
                  <span class="en_sr_err"></span>
                </div>
                <div class="en_sr_form_control">
                  <label for="en_sr_weight_to">To</label>
                  <input type="text" title="To" name="en_sr_weight_to" id="en_sr_weight_to" data-optional="1" maxlength="10">
                  <span class="en_sr_err"></span>
                </div>
              </div>

              <!-- Filter by Price -->
              <div class="en_sr_form_control">
                <div>
                  <label for="en_sr_filter_price">
                    <input type="checkbox" title="Filter by price" name="en_sr_filter_price" id="en_sr_filter_price"> Filter by price
                  </label>
                </div>
                <span class="en_sr_err"></span>
              </div>
              <div class="group_sr_form_control">
                <div class="en_sr_form_control">
                  <label for="en_sr_price_from">From</label>
                  <input type="text" title="From" name="en_sr_price_from" id="en_sr_price_from" data-optional="1" maxlength="20">
                  <span class="en_sr_err"></span>
                </div>
                <div class="en_sr_form_control">
                  <label for="en_sr_price_to">To</label>
                  <input type="text" title="To" name="en_sr_price_to" id="en_sr_price_to" data-optional="1" maxlength="20">
                  <span class="en_sr_err"></span>
                </div>
              </div>

              <!-- Filter by Quantity -->
              <div class="en_sr_form_control">
                <div>
                  <label for="filter_by_quantity">
                    <input type="checkbox" title="Filter by quantity" name="filter_by_quantity" id="filter_by_quantity"> Filter by quantity
                  </label>
                </div>
                <span class="en_sr_err"></span>
              </div>
              <div class="group_sr_form_control">
                <div class="en_sr_form_control">
                  <label for="en_sr_quantity_from">From</label>
                  <input type="text" title="From" name="en_sr_quantity_from" id="en_sr_quantity_from" data-optional="1" maxlength="20">
                  <span class="en_sr_err"></span>
                </div>
                <div class="en_sr_form_control">
                  <label for="en_sr_quantity_to">To</label>
                  <input type="text" title="To" name="en_sr_quantity_to" id="en_sr_quantity_to" data-optional="1" maxlength="20">
                  <span class="en_sr_err"></span>
                </div>
              </div>

              <!-- Filter by product tag -->
              <div class="en_sr_form_control">
                <div>
                  <label for="filter_by_product_tag">
                    <input type="checkbox" title="Filter by product tag" name="filter_by_product_tag" id="filter_by_product_tag"> Filter by product tag
                  </label>
                </div>
                <span class="en_sr_err"></span>
              </div>
              <div class="en_sr_form_control">
                <select id="en_sr_product_tags_list" multiple="multiple" data-attribute="en_product_tag_filter_value"
                        name="en_product_tag_filter_value"
                        title="Filter by product tag"
                        data-optional="1"
                        class="chosen_select en_product_tag_filter_value"
                        style="width: 100% !important;"
                        >
                    <?php
                      $en_products_tags = get_tags( array( 'taxonomy' => 'product_tag' ) );
                      if (isset($en_products_tags) && !empty($en_products_tags)) {
                          foreach ($en_products_tags as $key => $tag) {
                              echo "<option value='" . esc_attr($tag->term_taxonomy_id) . "'>" . esc_html($tag->name) . "</option>";
                          }
                      }
                    ?>
                </select>
                <span class="en_sr_err"></span>
              </div>

              <!-- Available checkbox -->
              <div class="en_sr_form_control">
                <div>
                  <label for="en_sr_avialable">
                    <input type="checkbox" title="Available" name="en_sr_avialable" id="en_sr_avialable" checked> Available
                  </label>
                </div>
                <span class="en_sr_err"></span>
              </div>

              <!-- Form submit button -->
              <div class="save_sr_form">
                  <input type="submit" value="Save" class="button-primary save_shipping_rule_form">
              </div>
          </form>
        </div>
      </div>
  </div>
