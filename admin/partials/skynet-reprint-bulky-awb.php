<?php
global $wp;

$current_url="//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
if (str_contains($current_url, 'post-new.php') && str_contains($current_url, 'skynet_order_bulky='))
{

$odr_id = $_REQUEST["skynet_order_bulky"];

$order_ids = explode(',',$odr_id);
$order_int= [];
foreach($order_ids as $row)
{
    $order_int[] = (int)$row;
};

// var_dump("test",$odr_id);die();
global $post;
global $wpdb;


?>
<input id="order_id" name="order_id" type="hidden" value="<?php echo $odr_id; ?>" readonly>
<!-- <link rel="stylesheet" href="http://cdn.datatables.net/1.10.2/css/jquery.dataTables.min.css"></style>
<script type="text/javascript" src="http://cdn.datatables.net/1.10.2/js/jquery.dataTables.min.js"></script> -->
<script>
        
		// jQuery("h1[class='wp-heading-inline']").text("Print Bulky Skynet Airwaybills");
        jQuery("h1[class='wp-heading-inline']").css("display", "none");
        jQuery("div[id='titlediv']").css("display", "none");
        jQuery("div[id='postbox-container-1']").css("display", "none");
        // var width = "100%";
        // jQuery("input[name='screen_columns']").val("1");
        
        document.getElementById("skynet-reprint-bulky-awb").style.width = "1000px";

        // jQuery(document).on("change", "input[name='screen_columns']", function (e) {
        //     var page_column = jQuery(this).val();
             
        //     if(page_column == 2)
        //     {
        //         width = "135%";
        //     }else{
        //         width = "100%";
        //     }
            
        //     document.getElementById("skynet-reprint-bulky-awb").style.width = width;
        // });
        
  
</script>
<?php ?>
<p>
    
    
    <table id="reprint" cellpadding="0" cellspacing="0" style="width: 100%;border: 1px solid black"  class="wp-list-table widefat fixed striped posts">
        <thead>
        <tr>
            <th style="width: 10px;">#</th>
            <th>Airwaybill No.</th>
            <th>Order No.</th>
            <th>Contents</th>
            <th>Recipient Name</th>
            <th>status</th>
            <th>Requested on</th>
        </tr>
        </thead>
        <tbody>
        
        </tbody>
    </table>
</p>
 <hr>
<p>
    <div style="text-align: right !important;">
    <button type="button"  class="button button-primary " name="btn_skynet_reprint_bulky_awb" id="btn_skynet_reprint_bulky_awb" value="Save">Reprint Airwaybill</button>
    <!-- <button type="button"  class="button button-danger " name="btn_skynet_cancel_bulky_awb" id="btn_skynet_cancel_bulky_awb" value="Cancel">Cancel</button> -->
    <!-- <button name="btn_skynet_print_bulky_awb" id="btn_skynet_print_bulky_awb" type="button" class="button button-primary" style="width:20%;">Print Airwaybill</button> -->
    </div>
</p>

<?php
woocommerce_wp_hidden_input([
    'id'    => 'skynet-print-awb-meta-box-nonce',
    'value' => wp_create_nonce('skynet-print-awb-nonce'),
]);
}
?>