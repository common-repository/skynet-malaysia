<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.skynet.com.my
 * @since      1.0.6
 *
 * @package    Skynet
 * @subpackage Skynet/admin
 */

use ParagonIE\Sodium\Core\Curve25519\Ge\P2;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Skynet
 * @subpackage Skynet/admin
 * @author     skynetmalaysia <malaysiaskynet@gmail.com>
 */

class Skynet_Bulky
{
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 *
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->define_hooks();
	}

	/**
	 * Define hooks
	 */
	protected function define_hooks() {

		add_action('init', [$this, 'start_session'], 1);
		add_action('plugins_loaded', [$this, 'check_woocommerce_activated']);

        add_action('add_meta_boxes', [ $this, 'add_shipment_order_data_meta_box' ] );

		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'add_download_pdf_bulk_actions' ], 20 );
		add_filter( 'handle_bulk_actions-edit-shop_order', [ $this, 'handle_download_pdf_bulk_actions' ], 10, 3 );

		add_action('init', [$this, 'start_session'], 1);

		add_action('wp_ajax_skynet_reprint_table', [$this, 'ajax_reprint_table']);
		add_action('wp_ajax_skynet_print_awb', [$this, 'ajax_print_awb_sticker']);

		add_action('wp_ajax_skynet_print_new_bulky_awb_sticker', [$this, 'ajax_print_new_bulky_sticker']);
	}

	public function start_session()
	{
		if (wp_doing_cron()) {
			return;
		}
		/*
		if (!session_id()) {
			session_start();
		}
		*/
	}

	public function check_woocommerce_activated()
	{
		if (defined('WC_VERSION')) {
			return;
		}

		add_action('admin_notices', [$this, 'notice_woocommerce_required']);
	}

	public function notice_woocommerce_required()
	{
?>
		<div class="notice notice-error">
			<p>
				<?php _e('Skynet requires WooCommerce to be installed and activated!', $this->plugin_name); ?>
			</p>
		</div>
<?php
	}

    /**
	 * Add sender meta box
	 */
	public function add_shipment_order_data_meta_box() {
		$screen = $current_url="//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		// var_dump($screen);die();
		if (str_contains($current_url, 'post-new.php') && str_contains($current_url, 'skynet_order_bulky='))
		{
			add_meta_box(
				'skynet-print-bulky-awb',
				'Print Bulky Skynet Airwaybills (NEW)',
				[ $this, 'render_print_actions_meta_box' ],
				'shop_order',
				'normal',
				'high'
			);

			add_meta_box(
				'skynet-reprint-bulky-awb',
				'Reprint Existing Skynet Airwaybills',
				[ $this, 'render_reprint_actions_meta_box' ],
				'shop_order',
				'normal',
				'high'
			);

			add_action( 'add_meta_boxes', [$this,'remove_shop_order_meta_boxe'], 90 );
			add_action('admin_head',  [$this,'Hide_WooCommerce_Breadcrumb']);
		}
	}

	function remove_shop_order_meta_boxe() {
		remove_meta_box('postcustom', 'shop_order', 'normal');
		remove_meta_box('woocommerce-order-downloads', 'shop_order', 'normal');
		remove_meta_box('woocommerce-order-data', 'shop_order', 'normal');
		remove_meta_box('woocommerce-order-items', 'shop_order', 'normal');
		remove_meta_box('woocommerce-order-notes', 'shop_order', 'normal');
		remove_meta_box('postcustom', 'shop_order', 'normal');
		remove_meta_box('titlediv', 'shop_order', 'normal');
		remove_meta_box('postbox-container-1', 'post', 'normal');
		remove_meta_box('woocommerce-order-actions', 'shop_order', 'normal');
		
		
	}

	function Hide_WooCommerce_Breadcrumb() {
		echo '<style>
			.woocommerce-layout__header {
				display: none;
			}
			.woocommerce-layout__activity-panel-tabs {
				display: none;
			}
			.woocommerce-layout__header-breadcrumbs {
				display: none;
			}
			.woocommerce-embed-page .woocommerce-layout__primary{
				display: none;
			}
			.woocommerce-embed-page #screen-meta, .woocommerce-embed-page #screen-meta-links{top:0;}
			</style>';
	}

    public function render_print_actions_meta_box($post) {
		include_once plugin_dir_path(__FILE__) . 'partials/skynet-print-bulky-awb.php';
	}

	public function render_reprint_actions_meta_box($post) {
		include_once plugin_dir_path(__FILE__) . 'partials/skynet-reprint-bulky-awb.php';
	}

    public function add_download_pdf_bulk_actions( $actions ) {

		$actions['write_downloads'] = __( 'Print Bulk Skynet Airwaybill', 'skynet-malaysia' );

		return $actions;
	}

    /**
	 * Handle create shipment order bulk actions
	 *
	 * @param $redirect_url
	 * @param $action
	 * @param $order_ids
	 *
	 * @return string
	 */
	public function handle_download_pdf_bulk_actions( $redirect_url, $action, $order_ids ) {
		
		if ( $action !== 'write_downloads' )
            return $redirect_to; // Exit

		
		$order_ids = implode(',',$order_ids); 
		
		return add_query_arg( [
			'skynet_order_bulky' => $order_ids,
		], 
		admin_url( 'post-new.php?post_type=shop_order' ) );

	}

    public function ajax_print_awb_sticker($order_id)
	{
		try {

			
			check_ajax_referer('skynet-print-awb-nonce', 'skynet-print-awb-meta-box-nonce');

            $settings = array_merge([
				'skynet_api_user_access_token' 		=> '',
				'shipper_name' 						=> '',
				'shipper_tel_no' 					=> '',
				'shipper_default_contact_person'	=> '',
				'shipper_address1' 					=> '',
				'shipper_address2' 					=> '',
				'shipper_city' 						=> '',
				'shipper_state' 					=> '',
				'shipper_postcode' 					=> '',
				'shipper_country' 					=> '',
			], get_option('woocommerce_skynet_settings', []));

				global $wpdb;

				$order = $_REQUEST['order_id'];
				$order_ids = array_map('intval', explode(",", $order));
				
                foreach($order_ids as $row){
					$db_result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."wc_skynet_printed_sticker WHERE `order_id` =$row ");
                    $dataItem	= array();
					// var_export($db_result[0]->awbnumber);die();
			        $dataItem["AWBNum"]				= $db_result[0]->awbnumber;
                    $dataItem["CreatedBy"]			= $settings['shipper_default_contact_person']."-WEBWOO";
                    
                    $dataItems[]					= $dataItem;
                }
				
				$body = [
					"access_token"	=> $settings['skynet_api_user_access_token'],
					"printFormat"	=> "Sticker",
					"Mode"			=> "Reprint",
					"RunningNumber"	=> "5e97bd6b59e6e",
					"ReturnType"	=> "PDF",
					"Data" => 
						$dataItems
					
				];
				// var_export($body);die();
			$response = wp_remote_post(SKYNET_API_URL . 'Skynet/ECommerce/PrintAWBInternalAPIMultiplePage', [
				'headers'     	=> ['Content-Type' => 'application/json',],
				'body'        	=> json_encode($body),
			]);

			if (is_wp_error($response)) {
				throw new Exception($response->get_error_message(), $response->get_error_code());
			} else {
				$response_result  = json_decode(wp_remote_retrieve_body($response), true);
				if ($response_result["code"] == 200) {

						global $wpdb;

						// $wpdb->insert(
						// 	$wpdb->prefix.'wc_skynet_printed_sticker',
						// 	array(
						// 		'order_id'		=> (int)$order_id,
						// 		'awbnumber'		=> esc_html($response_result['AWBNo']),
						// 		'created_on'	=> date("Y-m-d H:i:s"),
						// 	)
						// );

					wp_send_json_success([
						'pdfUrl' 	=> esc_url($response_result["printAWB"]),
						'awbnumber' => esc_js($response_result['AWBNo']),
					]);
				} else {
					throw new Exception($response_result["message"], $response_result["code"]);
				}
			}
		} catch (Exception $exception) {
			wp_send_json_error([
				'code' 		=> esc_js($exception->getCode() ?? 001),
				'message' 	=> esc_js($exception->getMessage() ?? "Could not load data from API."),
			]);
		}

		wp_die();
	}

	public function ajax_reprint_table()
	{ 
		
		try {
			
			$settings = array_merge([
				'skynet_api_user_access_token' 		=> '',
				'shipper_name' 						=> '',
				'shipper_tel_no' 					=> '',
				'shipper_default_contact_person'	=> '',
				'shipper_address1' 					=> '',
				'shipper_address2' 					=> '',
				'shipper_city' 						=> '',
				'shipper_state' 					=> '',
				'shipper_postcode' 					=> '',
				'shipper_country' 					=> '',
			], get_option('woocommerce_skynet_settings', []));

			global $wpdb;
			// var_dump("masuk",$_REQUEST['order_id']);die();
			check_ajax_referer('skynet-print-awb-nonce', 'skynet-print-awb-meta-box-nonce');
			$order = $_REQUEST['order_id'];
			
			$order_ids = array_map('intval', explode(",", $order));
			// var_dump("masuk",$order_ids,$order);die();
			$eawb = [];
			$get_order = [];
			$get_wp_order = [];
			// $get_order_detail = [];
			$count = 0;
			foreach($order_ids as $key => $row)
			{
				$result = $wpdb->get_results("SELECT `order_id`,`awbnumber` FROM ".$wpdb->prefix."wc_skynet_printed_sticker WHERE `order_id` = '$row' ");

				$get_eawb = $result[$count]->awbnumber;
				$get_order_id = $result[$count];
				// var_dump($result[$key]->order_id);
				if($get_eawb){
					$eawb[] = $get_eawb;
					$get_order[] = $get_order_id;
					$get_wp_order[] = $get_order_id->order_id;
					// $get_order_detail[] =  wc_get_order($get_order_id->order_id);
				}
					
			};
			$product_name = [];
			foreach($get_wp_order as $items)
			{
				$pro_name = [];
				$itemss = wc_get_order($items);
				foreach($itemss->get_items() as $item_id => $item)
				{
					$pro_name[] = $item->get_name();	
				}
				$pro_name = implode(",",$pro_name);
				$product_name[] = array('item'=>$pro_name,'order_id'=>$items,'receipt_name'=>$itemss->get_shipping_first_name()." ".$itemss->get_shipping_last_name(),'dated'=>$itemss->get_date_created());
				
			}
			// var_dump($product_name,$get_wp_order);die();
			$dataItem = array();

			// foreach($eawb as $row) {
			// 	$dataItem	= array();
			// 	$dataItem["dtAWBNos"]			= $row;
			// 	$dataItems[]					= $dataItem;
			// }
			
			// $body = [
			// 		"access_token"	=> 'C4447448SND5F0S484DSNB3FASN5B2E07A83094',
			// 		"functionstr"	=> "getTrackingData",
			// 		"items" => $dataItems,
			// 	];
			
			// $response = wp_remote_post(SKYNET_API_URL . 'sn/WebNetworkTrack', [
			// 	'headers'     	=> ['Content-Type' => 'application/json',],
			// 	'body'        	=> json_encode($body),
			// ]);
			foreach($eawb as $row) {
				$dataItem	= array();
				$dataItem["awbnumber"]			= $row;
				$dataItems[]					= $dataItem;
			}
			$body = [
					"access_token"	=> '778f58ceSNb63fSN4372SN871bSN15cba73aff7c',
					"awbs" => $dataItems,
				];
			$response = wp_remote_post(SKYNET_API_URL . 'sn/pub/AWBTracking/', [
				'headers'     	=> ['Content-Type' => 'application/json',],
				'body'        	=> json_encode($body),
			]);

			if (is_wp_error($response)) {
				throw new Exception($response->get_error_message(), $response->get_error_code());
			} else {
				$response_result  = json_decode(wp_remote_retrieve_body($response), true);
				if ($response_result) {
					$data=[];
					foreach($eawb as $rows) {
						$i = 0;
						foreach($response_result as $result)
						{
							if(($result['AWBNumber'] == $rows) && ($i == 0))
							{
								$data[] = $result;
								$i++;
							}
						}
					}
					wp_send_json_success([
						'awbnumber' => $data,
						'order' => $get_order,
						'product_name' => $product_name,
						// 'order_detail' => $get_order_detail,
						// 'status' => esc_js($response_result['Description']),
					]);
				} else {
					throw new Exception($response_result["message"], $response_result["code"]);
				}
				
			}
		} catch (Exception $exception) {
			wp_send_json_error([
				'code' 		=> esc_js($exception->getCode() ?? 001),
				'message' 	=> esc_js($exception->getMessage() ?? "Could not load data from API."),
			]);
		}
		
	}

	public function ajax_print_new_bulky_sticker()
	{
		try {

			check_ajax_referer('skynet-print-awb-nonce', 'skynet-print-awb-meta-box-nonce');
			$settings = array_merge([
				'skynet_api_user_access_token' 		=> '',
				'shipper_name' 						=> '',
				'shipper_tel_no' 					=> '',
				'shipper_default_contact_person'	=> '',
				'shipper_address1' 					=> '',
				'shipper_address2' 					=> '',
				'shipper_city' 						=> '',
				'shipper_state' 					=> '',
				'shipper_postcode' 					=> '',
				'shipper_country' 					=> '',
			], get_option('woocommerce_skynet_settings', []));

			global $wpdb;
			$request_data = wc_clean($_REQUEST);
			$order_id = $request_data['print_get_order_id'];
			$order_id = explode(",",$order_id);
			
			$data = [];
			foreach($order_id as $key=>$row){

				$order = wc_get_order($row);
				if (!$order) {
					throw new InvalidArgumentException(__('Invalid order', 'skynet'));
				}

				$consignee_first_name = $order->get_shipping_first_name();

				$consignee_last_name = $order->get_shipping_last_name();

				$consignee_name = $consignee_first_name . " " . $consignee_last_name;

				$consignee_address_1 = $order->get_shipping_address_1();

				$consignee_addresss_2 = $order->get_shipping_address_2();

				$consignee_city = $order->get_shipping_city();

				$consignee_state = $order->get_shipping_state();

				$consignee_postcode = $order->get_shipping_postcode();

				$consignee_tel_no = $order->get_shipping_phone();

				$shipping_company = $order->get_shipping_company();

				$error_message = "";
				
				if(empty($consignee_name))
					$error_message .= "Name";
				if(empty($consignee_address_1))
					$error_message .= $error_message != "" ? ", Address line 1" : "Address line 1";
				if(empty($consignee_city))
					$error_message .= $error_message != "" ? ", City" : "City";
				if(empty($consignee_state))
					$error_message .= $error_message != "" ? ", State" : "State";
				if(empty($consignee_postcode))
					$error_message .= $error_message != "" ? ", Postcode" : "Postcode";
				if(empty($consignee_tel_no))
					$error_message .= $error_message != "" ? ", Phone" : "Phone";
				
				if($error_message != "")
					throw new Exception("Please fill up the Shipping details (".$error_message.") in Order #".$row." details section before printing Skynet sticker.", 001);
				
				

				$db_result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."wc_skynet_printed_sticker WHERE `order_id` = '$row' LIMIT 1");

				// $dataItem	= array();
				date_default_timezone_set("Asia/Kuala_Lumpur");
				$dataItem["shipperRef"]			= isset($request_data['skynet_shipment_shipperref_'.$row]) && !empty($request_data['skynet_shipment_shipperref_'.$row]) ? $request_data['skynet_shipment_shipperref_'.$row] : "";
				$dataItem["ShipmentType"]		= $request_data['skynet_shipment_type_'.$row];
				$dataItem["Pieces"]				= $request_data['skynet_shipment_pieces_'.$row];
				$dataItem["Weight"]				= $request_data['skynet_shipment_weight_'.$row];
				$dataItem["Contents"]			= $request_data['skynet_shipment_contents_'.$row];
				$dataItem["CurrencyCode"]		= "RM";
				$dataItem["Value"]				= "0";
				$dataItem["ShippingDate"]		= date("d-M-y h:ia");
				$dataItem["ShipperName"]		= $settings['shipper_name'];
				$dataItem["ShipperAdd1"]		= $settings['shipper_address1'];
				$dataItem["ShipperAdd2"]		= $settings['shipper_address2'];
				$dataItem["ShipperAdd3"]		= $settings['shipper_city'];
				$dataItem["ShipperAdd4"]		= $settings['shipper_state'];
				$dataItem["ShipperTelNo"]		= $settings['shipper_tel_no'];
				$dataItem["ShipperPostCode"]	= $settings['shipper_postcode'];
				$dataItem["ConsigneeName"]		= $consignee_name;
				$dataItem["ConsigneeAdd1"]		= $consignee_address_1;
				$dataItem["ConsigneeAdd2"]		= isset($consignee_addresss_3) ? !empty($consignee_addresss_2) ? $consignee_addresss_2 : $consignee_addresss_3 : $consignee_addresss_2;
				$dataItem["ConsigneeAdd3"]		= $consignee_city;
				$dataItem["ConsigneeAdd4"]		= $consignee_state;
				$dataItem["ConsigneePostCode"]	= $consignee_postcode;
				$dataItem["ConsigneeTelNo"]		= $consignee_tel_no;
				$dataItem["CODType"]			= "";
				$dataItem["CODAmount"]			= "";
				$dataItem["DepartmentCode"]		= "";
				$dataItem["ContactPerson"]		= $settings['shipper_default_contact_person'];
				$dataItem["CustomerAttn"]		= $shipping_company;
				$dataItem["OriginStn"]			= "";
				$dataItem["DestStn"]			= "";
				$dataItem["CreatedBy"]			= $settings['shipper_default_contact_person']."-WEBWOO";
				$dataItem["CreatedByStn"]		= "";
				$dataItem["Length"]				= (isset($request_data['skynet_shipment_length_'.$row]) && $request_data['skynet_shipment_length_'.$row] != 0) ? $request_data['skynet_shipment_length_'.$row] : '0';
				$dataItem["Width"]				= (isset($request_data['skynet_shipment_width_'.$row]) && $request_data['skynet_shipment_width_'.$row] != 0) ? $request_data['skynet_shipment_width_'.$row] : '0';
				$dataItem["Height"]				= (isset($request_data['skynet_shipment_height_'.$row]) && $request_data['skynet_shipment_height_'.$row] != 0) ? $request_data['skynet_shipment_height_'.$row] : '0';
				$dataItem["VolWt"]				= (isset($request_data['skynet_volumetric_weight_'.$row]) && $request_data['skynet_volumetric_weight_'.$row] != 0) ? $request_data['skynet_volumetric_weight_'.$row] : '0';
				$dataItem["ReprintText"]		= "";
				
				$dataItems[]				= $dataItem;
			}
			$body = [];

			$body = [
					"access_token"	=> $settings['skynet_api_user_access_token'],
					"printFormat"	=> "Sticker",
					"Mode"			=> "Print",
					"RunningNumber"	=> "5e97bd6b59e6e",
					"ReturnType"	=> "PDF",
					"Data" => $dataItems
					
				];
			
			$response = wp_remote_post(SKYNET_API_URL . 'Skynet/ECommerce/PrintAWBInternalAPIMultiplePage', [
				'headers'     	=> ['Content-Type' => 'application/json',],
				'body'        	=> json_encode($body),
			]);
			
			if (is_wp_error($response)) {
				throw new Exception($response->get_error_message(), $response->get_error_code());
			} else {
				$response_result  = json_decode(wp_remote_retrieve_body($response), true);
				
				if ($response_result["code"] == 200) {

					if ($request_data['reprint'] == 0) {
						global $wpdb;

						$eawb = explode(",",$response_result['AWBNo']);
						$order_id = explode(",",$request_data['print_get_order_id']);
						foreach($eawb as $key => $eawbs)
						{
							foreach($order_id as $key2=>$row)
							{
								if($key == $key2){
									$wpdb->insert(
										$wpdb->prefix.'wc_skynet_printed_sticker',
										array(
											'order_id'		=> (int)$row,
											'awbnumber'		=> esc_html($eawbs),
											'created_on'	=> date("Y-m-d H:i:s"),
										)
									);
								}	
							}
						}
						
					}

					wp_send_json_success([
						'pdfUrl' 	=> esc_url($response_result["printAWB"]),
						'awbnumber' => esc_js($response_result['AWBNo']),
					]);
				} else {
					throw new Exception($response_result["message"], $response_result["code"]);
				}
			}

		}catch (Exception $exception) {
			wp_send_json_error([
				'code' 		=> esc_js($exception->getCode() ?? 001),
				'message' 	=> esc_js($exception->getMessage() ?? "Could not load data from API."),
			]);
		}
	}
}