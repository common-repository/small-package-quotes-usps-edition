<?php
/**
 * Get rates on frontend side.
 */

namespace EnUspsOrderScript;

/**
 * Script for send ajax request.
 * Class EnUspsOrderScript
 * @package EnUspsOrderScript
 */
if (!class_exists('EnUspsOrderScript')) {

    class EnUspsOrderScript
    {

        /**
         * Hook for load script.
         * EnUspsOrderScript constructor.
         */
        public function __construct()
        {
            add_action('admin_print_scripts', [$this, 'en_usps_order_script'], 50);
        }

        public function en_usps_order_script()
        {
            global $post;
            ?>
            <script type="text/javascript">

                jQuery(document).ready(function () {
                    admin_order_shipping_method();
                });
                // Change in shipping options on order page
                if (typeof admin_order_shipping_method != 'function') {
                    function admin_order_shipping_method() {
                        jQuery(document).ajaxComplete(function (event, xhr, settings) {
                            jQuery(".woocommerce_order_items #order_shipping_line_items .shipping_method").on('change', function (event) {
                                event.stopPropagation();
                                event.stopImmediatePropagation();
                                let target = jQuery(this).val();
                                let window_fn = window[target];
                                if (typeof window_fn === 'function') {
                                    target + "()";
                                }
                            });
                        });
                    }
                }

                // Show error message when you are getting quotes
                if (typeof admin_order_shipping_errors != 'function') {
                    function admin_order_shipping_errors(errors) {
                        jQuery.each(errors, function (ind, error) {
                            jQuery('.woocommerce_order_items').before('<div id="message" class="error inline en_admin_order_quotes_messages"><p><strong>' + error + '</p></strong></div>');
                        });
                    }
                }

                /**
                 * Call generic app related ajax call
                 */
                function usps() {
                    let data =
                        {
                            'order_id': <?php echo isset($post->ID) ? esc_js($post->ID) : 0; ?>,
                            'bill_zip': jQuery("#_billing_postcode").val(),
                            'ship_zip': jQuery("#_shipping_postcode").val(),
                            'action': 'en_usps_admin_order_quotes'
                        };
                    jQuery.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: data,
                        datatype: "json",
                        beforeSend: function () {
                            jQuery('.en_admin_order_quotes_messages').remove();
                            jQuery('.woocommerce_order_items').before('<div class="en_admin_order_page_waiting_bar"></div>');
                        },
                        success: function (response) {
                            jQuery('.en_admin_order_page_waiting_bar').remove();
                            response = JSON.parse(response);
                            (typeof response['errors'] != 'undefined') ? admin_order_shipping_errors(response['errors']) : "";
                            if (typeof response['cost'] != "undefined" && typeof response['label'] != "undefined") {
                                jQuery('.shipping_method_name').val(response['label']);
                                jQuery('input[name*="shipping_cost"]').val(response['cost']);
                                jQuery(".save-action").trigger("click");
                            }
                        },
                        error: function (request, status, error) {
                            console.log(request.responseText);
                        }
                    });
                }
            </script>
            <?php
        }

    }

}