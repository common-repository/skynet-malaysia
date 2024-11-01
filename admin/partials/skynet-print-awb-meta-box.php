<?php

global $post;
global $wpdb;

$order = wc_get_order($post);
$order_id = $order->get_id();
$result = $wpdb->get_row("SELECT `order_id`,`awbnumber` FROM ".$wpdb->prefix."wc_skynet_printed_sticker WHERE `order_id` = '$order_id'");
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
// var_dump("masauk",array_sum($total));die();
if (!$result) {
?>
<script>
	function myReload(){
		$box = jQuery("#skynet_printawb_meta_box");
		$box.block();
		$box.load(location.href + " #skynet_printawb_meta_box");
		// $box.unblock();
	}
	
</script>
	<p>
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td class="form-field skynet_shipment_weight_field" width="52%">
					
					<button name="btn_refress" id="btn_refress" type="button" class="button button-primary" onclick="myReload()" style="width:100%;">Get Latest Weight</button>
				</td>
				
			</tr>
			<tr>
				<td class="form-field skynet_shipment_weight_field" width="52%">
					<label for="skynet_shipment_weight">Weight (Kg)</label>
					<input id="skynet_shipment_weight" name="skynet_shipment_weight" type="number" min="0.10" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="<?php echo $total ?>">
				</td>
				<td class="form-field skynet_shipment_pieces_field">
					<label for="skynet_shipment_pieces">No. of Boxes</label>
					<input id="skynet_shipment_pieces" name="skynet_shipment_pieces" type="number" min="1" step="1" value="1" style="width:100%; margin-bottom:5px;">
				</td>
			</tr>
			<tr>
				<td class="form-field skynet_shipment_type_field">
					<label for="skynet_shipment_type">Shipment Type</label>
					<select id="skynet_shipment_type" name="skynet_shipment_type" onchange="shipmentType(this.value)" style="width:100%; margin-bottom:5px;">
						<option value="" disabled>Please select</option>
						<option value="DOCUMENT">Document</option>
						<option value="PARCEL" selected>Parcel</option>
					</select>
				</td>
				<td class="form-field skynet_shipment_shipperref_field">
					<label for="skynet_shipment_shipperref">Shipper Ref</label>
					<input id="skynet_shipment_shipperref" name="skynet_shipment_shipperref" type="text" maxlength="30" style="width:100%; margin-bottom:5px;" value="Order#<?php echo $order->get_id() ?>">
				</td>
			</tr>
			<tr>
				<td class="form-field skynet_shipment_length_field" width="52%">
					<label for="skynet_shipment_length">Length (cm)</label>
					<input onclick="calculate_weight()" id="skynet_shipment_length" name="skynet_shipment_length" type="number" min="0" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0">
				</td>
				<td class="form-field skynet_shipment_width_field" width="52%">
					<label for="skynet_shipment_width">Width (cm)</label>
					<input onclick="calculate_weight()" id="skynet_shipment_width" name="skynet_shipment_width" type="number" min="0" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0">
				</td>
			</tr>
			<tr>
				<td class="form-field skynet_shipment_height_field" width="52%">
					<label for="skynet_shipment_height">Height (cm)</label>
					<input onclick="calculate_weight()" id="skynet_shipment_height" name="skynet_shipment_height" type="number" min="0" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0">
				</td>
				<td class="form-field skynet_shipment_volumn_field" width="52%">
					<label for="skynet_shipment_volumn">Volumetric Weight</label>
					<input onclick="calculate_weight()" id="skynet_shipment_volumn" name="skynet_shipment_volumn" type="number" min="0" step=".01" onkeypress="return event.charCode >= 46 && event.charCode <= 57" style="width:95%; margin-bottom:5px;" value="0" readonly>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="skynet_shipment_contents">Contents</label><br />
					<input id="skynet_shipment_contents" name="skynet_shipment_contents" type="text" maxlength="50" style="width:100%;" value="<?php echo $product ?>">
				</td>
			</tr>
		</table>
	</p>
    <hr>
    <p>
		<button name="btn_skynet_print_awb" id="btn_skynet_print_awb" type="button" class="button" style="width:100%;">Print Airwaybill</button>
	</p>
<?php } else {
?>
    <p>
		<label><b>Airwaybill Number&nbsp;:&nbsp;</b><?php echo $result->awbnumber ?></label>
	</p>
    <hr>
    <p>
        <button name="btn_skynet_reprint_awb" id="btn_skynet_reprint_awb" type="button" class="button" style="width:100%;">Reprint Airwaybill</button>
    </p>
<?php }
woocommerce_wp_hidden_input([
    'id'    => 'skynet-print-awb-meta-box-nonce',
    'value' => wp_create_nonce('skynet-print-awb-nonce'),
]);
