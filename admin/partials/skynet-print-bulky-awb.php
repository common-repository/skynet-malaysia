<?php
global $wp;
$current_url="//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
if (str_contains($current_url, 'post-new.php') && str_contains($current_url, 'skynet_order_bulky='))
{

$order = $_REQUEST["skynet_order_bulky"];

$order_ids = explode(',',$order);

$order_int= [];
foreach($order_ids as $row)
{
    $order_int[] = (int)$row;
};

// var_dump("test",$order_int);die();
global $post;
global $wpdb;

?>
<script>
    
		// jQuery("h1[class='wp-heading-inline']").text("Print Bulky Skynet Airwaybills");
        jQuery("h1[class='wp-heading-inline']").css("display", "none");
        jQuery("div[id='titlediv']").css("display", "none");
        jQuery("div[id='postbox-container-1']").css("display", "none");
        var width = "100%";

        // jQuery("input[name='screen_columns']").val("1");

        document.getElementById("skynet-print-bulky-awb").style.width = "1000px";
        
        // jQuery(document).on("change", "input[name='screen_columns']", function (e) {

        //     var page_column = jQuery(this).val();
             
        //     if(page_column == 2)
        //     {
        //         width = "135%";
        //     }else{
        //         width = "100%";
        //     }
            
        //     document.getElementById("skynet-print-bulky-awb").style.width = width;
        // });
        
</script>
<p>
    <table cellpadding="0" cellspacing="0" style="width: 100%;border: 1px solid black"  class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th style="width: 10px;">#</th>
            <th style="width: 7%;">Order No</th>
            <th>Shipping Adress</th>
            <th>Details</th>
            <th>Shipment Type</th>
            <th>No. of Boxes</th>
            <th>Weight (Kg)</th>
            <th>Volumetric Weight (Kg)</th>
            
        </tr>
        </thead>
        <!-- // foreach($order_int as $key => $order_id)
        // {
        //     $order = wc_get_order( $order_id );
        //     // var_dump($order->get_billing_first_name());die();
        //     echo '<tr>';
        //     echo '<td>', $key+1 ,'</td>';
        //     echo '<td>', $order->get_id(),'</td>';
        //     echo '<td>', $key+1,'</td>';
        //     echo '<td>', $key+1,'</td>';
        //     echo '<td>', $key+1,'</td>';
        //     echo '<td>', $key+1,'</td>';
        //     echo '<td>', $key+1,'</td>';
        //     echo '</tr>';
        // } -->
        <tbody>
        <?php
        $skynet_print_order_id = [];
        $count = 0;
        foreach($order_int as $key => $order_id)
        {
            $result = $wpdb->get_results("SELECT `order_id` FROM ".$wpdb->prefix."wc_skynet_printed_sticker WHERE `order_id` = '$order_id'");

            $order = wc_get_order( $order_id );
            $shipperref = $order->get_id();
            $content = [];
            foreach ( $order->get_items() as $item_id => $item ) {
                $content[] = $item->get_name();
            }
            $item = $wpdb->get_results("SELECT `order_item_id`,`order_item_name`,`order_id` FROM ".$wpdb->prefix."woocommerce_order_items WHERE `order_id` = '$order_id'");
            $item_meta = $wpdb->get_results("SELECT `meta_id`,`order_item_id`,`meta_key`,`meta_value` FROM ".$wpdb->prefix."woocommerce_order_itemmeta WHERE `meta_key` = '_qty'");

            $total = [];
            $product = [];
            foreach($item as $items){
                
                foreach($item_meta as $meta)
                {
                    
                    
                    if($items->order_item_id == $meta->order_item_id)
                    {
                        $weight = $wpdb->get_results("SELECT `meta_id`,`order_item_id`,`meta_key`,`meta_value` FROM ".$wpdb->prefix."woocommerce_order_itemmeta WHERE `meta_key` = '_product_id' and `order_item_id` = '$meta->order_item_id'");
                        $first = $weight[0];
                        $get_weight = $wpdb->get_results("SELECT `meta_id`,`post_id`,`meta_key`,`meta_value` FROM ".$wpdb->prefix."postmeta WHERE `meta_key` = '_weight' and `post_id` = '$first->meta_value'");
                        $second = $get_weight[0];
                        
                        $total[] = floatval($second->meta_value) * floatval($meta->meta_value);
                        $product[] = $items->order_item_name;
                        // var_dump($second->meta_value,$meta->meta_value,floatval($second->meta_value) * floatval($meta->meta_value));
                    }
                }
            }
            if(!empty($total)){
                $total = array_sum($total);
                $product = join(' , ',$product);
            }else{
                $total = 0.10;
                $product = '';
            }
            if(!$result)
            {
            $count++;
            $skynet_print_order_id[] = $order->get_id();
            $url=  admin_url().'post.php?post='.$order->get_id().'&action=edit';
            
        // wp_redirect($url);
        ?>
            <tr>
                <td><?php echo $count ?></td>
                <td><a href="<?php echo $url ?>"><?php echo $order->get_id() ?></a></td>
                <td>
                    <?php echo $order->get_shipping_first_name(); ?> <?php echo $order->get_shipping_last_name(); ?><br>
                    <?php echo $order->get_shipping_company(); ?> <br>
                    <?php echo $order->get_shipping_address_1(); ?>,<?php echo $order->get_shipping_address_2(); ?>,
                    <?php echo $order->get_shipping_city(); ?>,<?php echo $order->get_shipping_state(); ?>,<?php echo $order->get_shipping_postcode(); ?>,<?php echo $order->get_shipping_country(); ?>
                </td>
                <td>
                    <label for="skynet_shipment_shipperref">Shipper Ref</label>
                    <input id="skynet_shipment_shipperref_<?php echo $order->get_id() ?>" name="skynet_shipment_shipperref_<?php echo $order->get_id() ?>" type="text" maxlength="30" style="width:100%; margin-bottom:5px;" value="Order#<?php echo $order->get_id(); ?>">
                    <label for="skynet_shipment_contents">Contents</label><br />
                    <input id="skynet_shipment_contents_<?php echo $order->get_id() ?>" name="skynet_shipment_contents_<?php echo $order->get_id() ?>" type="text" maxlength="50" style="width:100%;" value="<?php echo implode(",",$content) ?>">
                </td>
                <td>
                    <select id="skynet_shipment_type_<?php echo $order->get_id() ?>" name="skynet_shipment_type_<?php echo $order->get_id() ?>" onchange="shipmentType(this.value)" style="width:100%; margin-bottom:5px;">
                        <option value="" disabled>Please select</option>
                        <option value="DOCUMENT">Document</option>
                        <option value="PARCEL" selected>Parcel</option>
                    </select>
                </td>
                <td>
                    <input id="skynet_shipment_pieces_<?php echo $order->get_id() ?>" name="skynet_shipment_pieces_<?php echo $order->get_id() ?>" type="number" min="1" step="1" value="1" style="width:100%; margin-bottom:5px;">
                </td>
                <td>
                    <input id="skynet_shipment_weight_<?php echo $order->get_id() ?>" name="skynet_shipment_weight_<?php echo $order->get_id() ?>" type="number" min="0.10" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="<?php echo $total ?>">
                </td>
                <td>

                    <label for="skynet_shipment_length">Length (cm)</label>
                    <input id="skynet_shipment_length_<?php echo $order->get_id() ?>" name="skynet_shipment_length_<?php echo $order->get_id() ?>" class="volumetric" data-get_order_id="<?php echo $order->get_id() ?>" type="number" min="0.10" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0">

                    <label for="skynet_shipment_width_">Width (cm)</label>
                    <input id="skynet_shipment_width_<?php echo $order->get_id() ?>" name="skynet_shipment_width_<?php echo $order->get_id() ?>" class="volumetric" data-get_order_id="<?php echo $order->get_id() ?>" type="number" min="0.10" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0">

                    <label for="skynet_shipment_height_">Height (cm)</label>
                    <input id="skynet_shipment_height_<?php echo $order->get_id() ?>" name="skynet_shipment_height_<?php echo $order->get_id() ?>" class="volumetric" data-get_order_id="<?php echo $order->get_id() ?>" type="number" min="0.10" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0">
                    <label for="skynet_volumetric_weight">Volumetric Weight (Kg)</label>
                    <input id="skynet_volumetric_weight_<?php echo $order->get_id() ?>" name="skynet_volumetric_weight_<?php echo $order->get_id() ?>" class="volumetric" data-get_order_id="<?php echo $order->get_id() ?>" type="number" min="0.10" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0">
                </td>
            </tr>
        <?php
            }
        }
        ?>
            <tr>
                <td>
                <input id="print_get_order_id" name="print_get_order_id" type="hidden" value="<?php echo implode(",",$skynet_print_order_id); ?>">

                </td>
            </tr>
        </tbody>
    </table>
</p>
 <hr>
<?php 
if(!empty($skynet_print_order_id))
{ 
?>
<p>
    <div style="text-align: right !important;">
    <button type="button"  class="button button-primary " name="btn_skynet_print_bulky_awb" id="btn_skynet_print_bulky_awb" value="Save">Print Airwaybill</button>
    <!-- <button type="button"  class="button button-danger " name="btn_skynet_cancel_bulky_awb" id="btn_skynet_cancel_bulky_awb" value="Cancel">Cancel</button> -->
    <!-- <button name="btn_skynet_print_bulky_awb" id="btn_skynet_print_bulky_awb" type="button" class="button button-primary" style="width:20%;">Print Airwaybill</button> -->
    </div>
</p>
<?php
}
woocommerce_wp_hidden_input([
    'id'    => 'skynet-print-awb-meta-box-nonce',
    'value' => wp_create_nonce('skynet-print-awb-nonce'),
]);
}
?>