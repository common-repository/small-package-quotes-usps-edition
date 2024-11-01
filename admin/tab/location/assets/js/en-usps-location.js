jQuery(document).ready(function () {

    // Origin terminal address
    jQuery(".en_street_address").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[a-z\d\-_,.\s]+$/i)) return false;
    });

    // Terminal phone number
    jQuery(".en_phone_number").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[0-9\d\-+\s]+$/i)) return false;
    });

    jQuery('.en_close_popup_location').on('click', function () {
        en_popup_location_overly_hide();
    });

    jQuery('#en_usps_location_zip').on('change', function (e) {
        let en_post_data = {
            'action': 'en_usps_get_location',
            'en_usps_location_zip': jQuery('#en_usps_location_zip').val()
        };

        let en_params = {
            en_ajax_loading_id: '#en_usps_location_zip,#en_location_city,#en_location_state,#en_location_country',
        };

        en_ajax_request(en_params, en_post_data, en_usps_get_location);
    });

    // When keyup on country and state.
    jQuery("#en_location_state,#en_location_country").keyup(function (e) {
        jQuery(this).val(this.value.toUpperCase());
    });

    jQuery('.en_usps_location_btn').on('click', function (e) {

        e.preventDefault();

        let validate = en_validate_input('.en_popup_location_form');

        if (validate === false) {
            jQuery('.en_popup_location_form').delay(200).animate({scrollTop: 0}, 300);
            return false;
        }

        var tab = get_parameter_by_usps('tab');

        let en_post_data = {
            'tab': tab,
            'action': 'en_usps_location_save_form_data',
            'en_post_data': jQuery(".en_popup_location_form input").serialize()
        };

        let en_params = {
            en_ajax_loading_msg_btn: '.en_usps_location_btn',
        };

        en_ajax_request(en_params, en_post_data, en_usps_location_save_form_data);
        jQuery('html, body').animate({
            scrollTop: jQuery(".subsubsub").offset().top
        }, 2000);
    });

    // Validations for origin and product level markup input fields
    jQuery("#en_wd_origin_markup, ._en_product_markup, #en_usps_handling_fee").bind("cut copy paste", function(e) {
        e.preventDefault();
    });
    
    jQuery("#en_wd_origin_markup, ._en_product_markup, #en_usps_handling_fee").keypress(function (e) {
        if (!String.fromCharCode(e.keyCode).match(/^[-0-9\d\.%\s]+$/i)) return false;
    });

    jQuery("#en_wd_origin_markup, ._en_product_markup, #en_usps_handling_fee").keydown(function (e) {
        if ((e.keyCode === 109 || e.keyCode === 189) && (jQuery(this).val().length>0) )  return false;
        if (e.keyCode === 53) if (e.shiftKey) if (jQuery(this).val().length == 0) return false; 
        
        if ((jQuery(this).val().indexOf('.') != -1) && (jQuery(this).val().substring(jQuery(this).val().indexOf('.'), jQuery(this).val().indexOf('.').length).length > 2)) {
            if (e.keyCode !== 8 && e.keyCode !== 46) { //exception
                e.preventDefault();
            }
        }
        // Allow: backspace, delete, tab, escape, enter and .
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190, 53, 189]) !== -1 ||
            // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
        if(jQuery(this).val().length > 7){
            e.preventDefault();
        }
    });

    jQuery("#en_wd_origin_markup, ._en_product_markup, #en_usps_handling_fee").keyup(function (e) {
        let val = jQuery(this).val();
        if (val.length && val.includes('%')) {
            jQuery(this).val(val.substring(0, val.indexOf('%') + 1));
        }
        
        if (val.split('.').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countDots = newval.substring(newval.indexOf('.') + 1).length;
            newval = newval.substring(0, val.length - countDots - 1);
            jQuery(this).val(newval);
        }

        if (val.split('%').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('%') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }
        
        if (val.split('-').length - 1 > 1) {
            var newval = val.substring(0, val.length - 1);
            var countPercentages = newval.substring(newval.indexOf('-') + 1).length;
            newval = newval.substring(0, val.length - countPercentages - 1);
            jQuery(this).val(newval);
        }
    });
});

/**
 * Only alpha allow
 */
if (typeof en_alpha_only != 'function') {
    function en_alpha_only(event) {
        var key = event.keyCode;
        return ((key >= 65 && key <= 90) || key == 8);
    }
}

/**
 * Round integer two number after decimal
 */
if (typeof en_round_two_digits_after_decimal != 'function') {
    function en_round_two_digits_after_decimal(el) {
        var v = parseFloat(el.value);
        el.value = (isNaN(v)) ? '' : v.toFixed(2);
    }
}

/**
 * Location data save in to db
 */
if (typeof en_usps_location_save_form_data != 'function') {
    function en_usps_location_save_form_data(params, response) {

        let data = JSON.parse(response);

        if (en_is_var_exist('severity', data) && data['severity'] == 'success') {

            jQuery(data['target_location']).replaceWith(data['html']);
            en_popup_location_overly_hide();

        } else if (en_is_var_exist('severity', data) && data['severity'] == 'error') {
            jQuery('.en_popup_location_form').delay(200).animate({scrollTop: 0}, 300);
            jQuery('.en_location_error_message span').text(data['message']);
            en_show_errors('.en_location_error_message');
        }

        en_location_notification(data);
    }
}

/**
 * Get response from api when we sent zip code
 */
if (typeof en_usps_get_location != 'function') {
    function en_usps_get_location(params, response) {
        let data = JSON.parse(response);

        let en_selecter_city_address_input_location = jQuery('#en_location_city');
        let en_selecter_city_address_dropdown_option = jQuery('.en_multi_city_change');
        let en_selecter_location_city = jQuery('#en_location_city');

        switch (true) {
            case (data.country === 'US' || data.country === 'CA'):
                switch (true) {
                    case (data.en_postcode_localities == 1):

                        en_selecter_city_address_input_location.closest('div').hide();
                        en_selecter_city_address_dropdown_option.closest('div').show();

                        if (jQuery('.en_multi_city_change').length > 0) {
                            jQuery('.en_multi_city_change').closest('div').replaceWith(data.city_option);
                        } else {
                            jQuery('#en_location_city').closest('div').after(data.city_option);
                        }

                        jQuery('.en_multi_city_change').change(function () {
                            en_save_city(this);
                        });

                        en_selecter_location_city.val(data.first_city);

                        break;
                    default:

                        en_selecter_city_address_dropdown_option.closest('div').hide();
                        en_selecter_city_address_input_location.closest('div').show();
                        en_selecter_location_city.val(data.city);
                }

                jQuery('#en_location_state').val(data.state);
                jQuery('#en_location_country').val(data.country);
                break;
            case (data.severity === 'error'):
                jQuery('.en_popup_location_form').delay(200).animate({scrollTop: 0}, 300);
                jQuery('.en_location_error_message span').text(data.message);
                en_show_errors('.en_location_error_message');
                break;
            default:
                jQuery('.en_popup_location_form').delay(200).animate({scrollTop: 0}, 300);
                jQuery('.en_location_error_message span').text('Please enter US zip code.');
                en_show_errors('.en_location_error_message');
        }
    }
}

/**
 * Location popup location form reset
 * @param enClassId
 */
if (typeof en_popup_location_reset != 'function') {
    function en_popup_location_reset() {
        jQuery('.en_location_error').text('');
        jQuery('#en_location_form_reset_me')[0].reset();
        // jQuery('.bootstrap-tagsinput').tagsinput('removeAll');
        jQuery(jQuery(".bootstrap-tagsinput").find("span[data-role=remove]")).trigger("click");
        jQuery('#en_location_city').closest('div').show();
        jQuery('.en_multi_city_change').closest('div').hide();
        jQuery('.en_popup_location_form').delay(200).animate({scrollTop: 0}, 300);
        jQuery('.en_location_error_message').hide();
    }
}

/**
 * Show errors when we get adresss on change zip code in warehouses tab
 * @param enClassId
 */
if (typeof en_show_errors != 'function') {
    function en_show_errors(en_class_id) {
        jQuery(en_class_id).show('slow');
        setTimeout(function () {
            jQuery(en_class_id).hide('slow');
        }, 5000);
    }
}

/**
 * Filter City option
 */
if (typeof en_save_city != 'function') {
    function en_save_city(e) {
        let city = jQuery(e).val();
        jQuery('#en_location_city').val(city);
    }
}

/**
 * When location row deleted
 */
if (typeof en_action_location_deleted != 'function') {
    function en_action_location_deleted(params, response) {
        let data = JSON.parse(response);
        jQuery(data['target_location']).html(data['html']);
        en_location_notification(data);
        en_popup_confirmation_location_delete_hide();
    }
}

/**
 * Location add btn click
 */
if (typeof en_show_popup_location != 'function') {
    function en_show_popup_location(en_location_type) {

        // First reset the location popup form
        en_popup_location_reset();

        jQuery('#en_location_id').val('');

        if (en_location_type) {
            jQuery('#en_location_nickname').closest('.en_popup_location_input_field').hide();
            jQuery('#en_location_type').val('warehouse');
            jQuery('#en_popup_location_heading').text('Warehouse');
        } else {
            jQuery('#en_location_nickname').closest('.en_popup_location_input_field').show();
            jQuery('#en_location_type').val('dropship');
            jQuery('#en_popup_location_heading').text('Drop ship');
        }
        en_popup_location_overly_show();
    }
}

/**
 * Location edit btn click
 */
if (typeof en_usps_location_edit != 'function') {
    function en_usps_location_edit(e, data, en_location_type) {
        e.preventDefault();
        en_show_popup_location(en_location_type);

        let en_location_db_data = jQuery(data).closest('tr').find('.en_location_db_data').text();
        let en_location_db_data_parsed = JSON.parse(en_location_db_data);

        let en_location_custom_data = jQuery(data).closest('tr').find('.en_location_custom_data').text();
        let en_location_custom_data_parsed = JSON.parse(en_location_custom_data);

        jQuery.each(en_location_custom_data_parsed, function (index, item) {
            let en_item_id = typeof item['id'] !== undefined ? item['id'] : '';
            let en_item_name = typeof item['name'] !== undefined ? item['name'] : '';
            let en_item_type = typeof item['type'] !== undefined ? item['type'] : '';
            let en_item_get_value = typeof en_location_db_data_parsed[en_item_name] !== undefined ? en_location_db_data_parsed[en_item_name] : '';

            switch (en_item_type) {
                case "en_input_field":
                    if (index == 'en_local_delivery_postal_code' || index == 'en_in_store_pickup_postal_code') {
                        jQuery("#" + en_item_id).tagsinput('add', en_item_get_value);
                    } else {
                        jQuery("#" + en_item_id).val(en_item_get_value);
                    }

                    break;

                case "en_input_hidden":
                    jQuery("#" + en_item_id).val(en_item_get_value);
                    break;

                case "en_checkbox":
                    en_item_get_value == 'on' || en_item_get_value == '1' ?
                        jQuery("#" + en_item_id).prop("checked", true) :
                        jQuery("#" + en_item_id).prop("checked", false);
                    break;
            }
        });
    }
}

/**
 * Get url detail
 */
if (typeof get_parameter_by_usps != 'function') {
    function get_parameter_by_usps(name) {
        name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
}

/**
 * Location row delete confirmation
 */
if (typeof en_usps_location_delete != 'function') {
    function en_usps_location_delete(e, data, en_location_type, en_location_id) {
        e.preventDefault();
        en_popup_confirmation_location_delete_show();

        jQuery('.en_location_cancel_delete').on('click', function () {
            en_popup_confirmation_location_delete_hide();
        });

        jQuery('.en_location_confirm_delete').on('click', function () {
            en_location_confirm_delete(data, en_location_type, en_location_id);
        });
    }
}

/**
 * Location row delete
 */
if (typeof en_location_confirm_delete != 'function') {
    function en_location_confirm_delete(data, en_location_type, en_location_id) {

        var tab = get_parameter_by_usps('tab');
        let en_post_data = {
            'tab': tab,
            'action': 'en_usps_location_delete_row',
            'en_location_id': en_location_id,
            'en_location_type': (en_location_type) ? 'warehouse' : 'dropship'
        };

        let en_params = {
            en_ajax_loading_msg_ok_btn: '.en_location_confirm_delete',
        };

        en_ajax_request(en_params, en_post_data, en_action_location_deleted);
    }
}
/**
 * Location notification
 */
if (typeof en_location_notification != 'function') {
    function en_location_notification(data) {
        if (data['message'].length > 0) {
            jQuery('.en_popup_location_form').delay(200).animate({scrollTop: 0}, 300);
            jQuery('.en_location_success_message span').text(data['message']);
            en_show_errors('.en_location_success_message');
        }
    }
}

/**
 * Location popup hide
 */
if (typeof en_popup_location_overly_hide != 'function') {
    function en_popup_location_overly_hide() {
        jQuery('.en_popup_location_overly').css({'opacity': 0, 'visibility': 'hidden'});
    }
}

/**
 * Location popup show
 */
if (typeof en_popup_location_overly_show != 'function') {
    function en_popup_location_overly_show() {
        jQuery('.en_popup_location_overly').css({'opacity': 1, 'visibility': 'visible'});
    }
}

/**
 * Location popup hide
 */
if (typeof en_popup_confirmation_location_delete_hide != 'function') {
    function en_popup_confirmation_location_delete_hide() {
        jQuery('.confirmation_location_delete').css({'opacity': 0, 'visibility': 'hidden'});
    }
}

/**
 * Location popup show
 */
if (typeof en_popup_confirmation_location_delete_show != 'function') {
    function en_popup_confirmation_location_delete_show() {
        jQuery('.confirmation_location_delete').css({'opacity': 1, 'visibility': 'visible'});
    }
}
