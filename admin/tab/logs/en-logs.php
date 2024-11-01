<script type="text/javascript">
    jQuery(function() {
        jQuery('a').on('click', function(e) {
            const class_name = this.className;
            const show_class_name = class_name.includes('show') ? class_name.replace('show', 'hide') : class_name.replace('hide', 'show');

            if (class_name.includes('show') || class_name.includes('hide')) {
                e.preventDefault();
            }

            jQuery('.' + class_name).hide();
            jQuery('.' + show_class_name).show();
        })
    });
</script>

<style>
.hide {
    display: none;
}
.dims_space {
    padding-top: 0 !important;
}
.packaging_actions {
    display: flex;
    justify-content: space-evenly;
    align-items: center;
}
.empty_packaging_actions {
    text-align: center !important;
}
</style>

<?php

use EnUspsConfig\EnUspsConfig;

if (!class_exists('EnUspsSpqLogs')) {
    class EnUspsSpqLogs
    {
        public function __construct()
        {
            $this->en_logs();
        }

        // Logs request
        public function en_logs()
        {
            $obj_classs = new \EnUspsCurl\EnUspsCurl();
            $data = array(
                'serverName' => EnUspsConfig::en_get_server_name(),
                'licenseKey' => get_option('usps_small_licence_key'),
                'lastLogs' => '25',
                'carrierName' => 'usps'
            );

            require_once('en-json-tree-view/en-jtv.php');
            require_once('en-packaging-details/en-packaging-details.php');

            $url = EN_USPS_ROOT_URL_QUOTES . '/request-log/index.php';
            $logs = $obj_classs->en_usps_sent_http_request($url, $data, 'POST');
            $logs = (!empty($logs) && is_string($logs) && strlen($logs) > 0) ? json_decode($logs, true) : [];
            
            echo '<table class="en_logs">';
            
            if (isset($logs['severity'], $logs['data']) && $logs['severity'] == 'SUCCESS') {
                echo '<tr>';
                echo '<th>Log ID</th>';
                echo '<th>Request Time</th>';
                echo '<th>Response Time</th>';
                echo '<th>Latency</th>';
                echo '<th>Items</th>';
                echo '<th>DIMs (L x W x H)</th>';
                echo '<th>Qty</th>';
                echo '<th>Sender Address</th>';
                echo '<th>Receiver Address</th>';
                echo '<th>Response</th>';
                echo '</tr>';
                
                foreach ($logs['data'] as $key => $shipment) {
                    if (empty($shipment) || !is_array($shipment)) continue;

                    echo '<tr>';

                    $request = $response = $carrier = $status = '';
                    $receiverLineAddress = $receiverCity = $receiverState = $receiverZip = $receiverCountryCode = '';
                    extract($shipment);
                    $request = is_string($request) && strlen($request) > 0 ? json_decode($request, true) : [];
                    if (empty($request) || !is_array($request)) continue;

                    extract($request);
                    
                    $formatted_info = $hidden_formatted_info = [];
                    $formattedSenderAddress = $formattedItems = $formattedDims = $formattedQty = '';
                    $hiddenSenderAddress = $hiddenItems = $hiddenDims = $hiddenQty = '';
                    $itemsCount = 0;

                    if (empty($request['originAddress'])) {
                        continue;
                    }

                    foreach ($request['originAddress'] as $orgId => $value) {
                        $formatted_info[$orgId]['origin'] = $value;
                        $formatted_info[$orgId]['items'] = $request['commdityDetails'][$orgId];

                        $orgItems = count($request['commdityDetails'][$orgId]);

                        if ($itemsCount < 6) {
                            $hidden_formatted_info[$orgId]['origin'] = $value;

                            if ($itemsCount + $orgItems > 5) {
                                $sliceLength = 5 - $itemsCount;
                                $hidden_formatted_info[$orgId]['items'] = array_slice($request['commdityDetails'][$orgId], 0, $sliceLength);
                            } else {
                                $hidden_formatted_info[$orgId]['items'] = $request['commdityDetails'][$orgId];
                            }

                            $itemsCount += $orgItems;
                        }
                    }

                    // Formatting for showing all items on show more items button click
                    foreach ($formatted_info as $key => $value) {
                        $formattedSenderAddress .= '<p>' . $value['origin']['senderCity'] . ', ' . $value['origin']['senderState'] . ' ' . $value['origin']['senderZip'] . ' ' . $value['origin']['senderCountryCode'] . '</p>';

                        foreach ($value['items'] as $item) {
                            $formattedItems .= '<p>' . $item['productName'] . '</p>';
                            $formattedDims .= '<p>' . $item['lineItemLength'] . ' X ' . $item['lineItemWidth'] . ' X ' . $item['lineItemHeight'] . '</p>';
                            $formattedQty .= '<p>' . $item['piecesOfLineItem'] . '</p>';
                        }
                    }

                    // Formatting for showing items on hide more items button click
                    foreach ($hidden_formatted_info as $key => $value) {
                        $hiddenSenderAddress .= '<p>' . $value['origin']['senderCity'] . ', ' . $value['origin']['senderState'] . ' ' . $value['origin']['senderZip'] . ' ' . $value['origin']['senderCountryCode'] . '</p>';

                        foreach ($value['items'] as $item) {
                            $hiddenItems .= '<p>' . $item['productName'] . '</p>';
                            $hiddenDims .= '<p>' . $item['lineItemLength'] . ' X ' . $item['lineItemWidth'] . ' X ' . $item['lineItemHeight'] . '</p>';
                            $hiddenQty .= '<p>' . $item['piecesOfLineItem'] . '</p>';
                        }
                    }

                    $receiverLineAddress = $receiverCity = $receiverState = $receiverZip = $receiverCountryCode = '';
                    extract($request);
                    $en_fdo_meta_data = (isset($request['en_fdo_meta_data'])) ? $request['en_fdo_meta_data'] : [];

                    $address = [];
                    extract($en_fdo_meta_data);
                    $en_address = $address;
                    $class_name = 'usps-small-log-' . $key . rand(1, 100);

                    // Sender address
                    $address = $city = $state = $zip = $country = '';
                    extract($en_address);
                    $en_sender = strlen(trim($address) > 0) ? "$address, " : '';
                    $en_sender .= "$city, $state $zip $country";

                    // Receiver address
                    $en_receiver = strlen(trim($receiverLineAddress) > 0) ? "$receiverLineAddress, " : '';
                    $en_receiver .= "$receiverCity, $receiverState $receiverZip $receiverCountryCode";

                    $carrier = ucfirst($carrier);
                    $status = ucfirst($status);
                    $log_id = $shipment['id'];
                    $request_time = $this->setTimeZone($request_time);
                    $response_time = $this->setTimeZone($response_time);
                    $latency = strtotime($response_time) - strtotime($request_time);
                    $temp_resp = json_decode($response);

                    // Handle packaging details
                    $bin_response = [];
                    $products = [];
                    foreach ($request['originAddress'] as $zip_code => $value) {
                        if (!empty($temp_resp->$zip_code->binPackagingData)) {
                            $bin_response[] = $temp_resp->$zip_code->binPackagingData;
                            $products[] = !empty($product_name[$zip_code]) ? array_values($product_name[$zip_code]) : [];
                        }
                    }

                    if (!empty($bin_response)) {
                        $bin_packaging = new \EnUspsSpqLogsPackaging();
                        $products = array_merge(...$products);
                        $bin_packaging_markup = $bin_packaging->en_get_bins_packed($bin_response, $products, $request);
                    }

                    echo "<td>$log_id</td>";
                    echo "<td>$request_time</td>";
                    echo "<td>$response_time</td>";
                    echo "<td>$latency</td>";
                    
                    $name = 'show-' . $class_name;
                    if ($itemsCount > 5) {
                        echo "<td class='items_space $name'>$hiddenItems <a href='#' class='$name'>Show more items</a> </td>";
                    } else {
                        echo "<td class='items_space $name'>$hiddenItems</td>";
                    }
                    
                    echo "<td class='dims_space $name'>$hiddenDims</td>";
                    echo "<td class='$name'>$hiddenQty</td>";
                    echo "<td class='$name'>$hiddenSenderAddress</td>";
                    
                    $name = 'hide-' . $class_name;
                    echo "<td class='hide items_space $name'>$formattedItems <a href='#' class='$name'>Hide more items</a> </td>";
                    echo "<td class='hide dims_space $name'>$formattedDims</td>";
                    echo "<td class='hide $name'>$formattedQty</td>";
                    echo "<td class='hide $name'>$formattedSenderAddress</td>";

                    echo "<td>$en_receiver</td>";

                    if (!empty($bin_response) && strtolower($status)  != 'error') {
                        $action_links = '<td class="packaging_actions"><a href = "#en_jtv_showing_res" class="response" onclick=\'en_jtv_res_detail(' . $response . ')\'>' . $status . '</a>';

                        $box_icon = plugins_url('en-packaging-details/box-icon.png', __FILE__);
                        $packaging_link = ' <span> | </span> <a title="View packaging details" href="#en_jtv_showing_res" class="response" onclick=\'en_packging_details(' . json_encode($bin_packaging_markup) . ')\' ><img src=' . $box_icon . ' /></a>';
                        
                        $action_links .= $packaging_link;
                        $action_links .= '</td>';
                    } else {
                        $action_links = '<td class="empty_packaging_actions"><a href = "#en_jtv_showing_res" class="response" onclick=\'en_jtv_res_detail(' . $response . ')\'>' . $status . '</a></td>';
                    }

                    echo $action_links;
                    echo '</tr>';
                }
            } else {
                echo '<div class="user_guide">';
                echo '<p>Logs are not available.</p>';
                echo '</div>';
            }

            echo '<table>';
        }

        public function setTimeZone($date_time)
        {
            $time_zone = wp_timezone_string();
            if (empty($time_zone)) {
                return $date_time;
            }

            $converted_date_time = new DateTime($date_time, new DateTimeZone($time_zone));

            return $converted_date_time->format('m/d/Y h:i:s');
        }
    }

    new EnUspsSpqLogs();
}