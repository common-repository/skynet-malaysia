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
class Skynet_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.6
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.6
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	protected $headers = [
		'Content-Type'              => 'application/json',
		'access_token'                => '',
	];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.6
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->define_hooks();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.6
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Skynet_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Skynet_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/skynet-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.6
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Skynet_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Skynet_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/skynet-admin.js', array('jquery'), $this->version, false);
	}

	public function define_hooks()
	{
		add_action('init', [$this, 'start_session'], 1);

		add_action('plugins_loaded', [$this, 'check_woocommerce_activated']);

		add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

		add_action('add_meta_boxes', [$this, 'skynet_add_meta_box']);
		add_action('wp_ajax_skynet_print_awb_sticker', [$this, 'ajax_print_awb_sticker']);
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

	public function skynet_add_meta_box()
	{
		$screen = get_current_screen();

		if ($screen->action != 'add') {
			add_meta_box(
				'skynet_printawb_meta_box',             // Unique ID
				'Print Skynet Airwaybill',      		// Box title
				[$this, 'render_printawb_meta_box'],	// Content callback, must be of type callable
				'shop_order',							// Screen to show
				'side',									// screen context (part of screen on where to show)
				'high'									// priority
			);
		}
	}

	public function render_printawb_meta_box($post)
	{
		include_once plugin_dir_path(__FILE__) . 'partials/skynet-print-awb-meta-box.php';
	}

	public function ajax_print_awb_sticker()
	{
		try {
			check_ajax_referer('skynet-print-awb-nonce', 'skynet-print-awb-meta-box-nonce');

			$request_data = wc_clean($_REQUEST);
			
			$order_id = $request_data['order_id'];

			$order = wc_get_order($order_id);
			if (!$order) {
				throw new InvalidArgumentException(__('Invalid order', 'skynet'));
			}

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

			$consignee_first_name =
				!empty($request_data['_shipping_first_name']) ? $request_data['_shipping_first_name'] : $order->get_shipping_first_name();

			$consignee_last_name =
				!empty($request_data['_shipping_last_name']) ? $request_data['_shipping_last_name'] : $order->get_shipping_last_name();

			$consignee_name = $consignee_first_name . " " . $consignee_last_name;

			$consignee_address_1 =
				!empty($request_data['_shipping_address_1']) ? $request_data['_shipping_address_1'] : $order->get_shipping_address_1();

			$consignee_addresss_2 =
				!empty($request_data['_shipping_address_2']) ? $request_data['_shipping_address_2'] : $order->get_shipping_address_2();

			if(isset($request_data['_shipping_address_3']))
			{
				$consignee_addresss_3 =
					!empty($request_data['_shipping_address_3']) ? $request_data['_shipping_address_3'] : $order->get_shipping_address_3();
			}

			$consignee_city =
				!empty($request_data['_shipping_city']) ? $request_data['_shipping_city'] : $order->get_shipping_city();

			$consignee_state =
				!empty($request_data['_shipping_state']) ? $request_data['_shipping_state'] : $order->get_shipping_state();

			$consignee_postcode =
				!empty($request_data['_shipping_postcode']) ? $request_data['_shipping_postcode'] : $order->get_shipping_postcode();

			$consignee_tel_no =
				!empty($request_data['_shipping_phone']) ? $request_data['_shipping_phone'] : $order->get_shipping_phone();

			$shipping_company =
				!empty($request_data['_shipping_company']) ? $request_data['_shipping_company'] : $order->get_shipping_company();
	
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
				throw new Exception("Please fill up the Shipping details (".$error_message.") in Order #".$order_id." details section before printing Skynet sticker.", 001);
			
			$body = [];
			date_default_timezone_set("Asia/Kuala_Lumpur");
			if ($request_data['reprint'] == 1) {
				global $wpdb;

				$db_result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."wc_skynet_printed_sticker WHERE `order_id` = '$order_id' LIMIT 1");

				$body = [
					"access_token"	=> $settings['skynet_api_user_access_token'],
					"printFormat"	=> "Sticker",
					"Mode"			=> "Reprint",
					"RunningNumber"	=> "5e97bd6b59e6e",
					"ReturnType"	=> "PDF",
					"Data" => [
						[
							"AWBNum" => $db_result->awbnumber,
							"CreatedBy"	=> $settings['shipper_default_contact_person']."-WEBWOO",
						]
					]
				];
			} else {
				$body = [
					"access_token"	=> $settings['skynet_api_user_access_token'],
					"printFormat"	=> "Sticker",
					"Mode"			=> "Print",
					"RunningNumber"	=> "5e97bd6b59e6e",
					"ReturnType"	=> "PDF",
					"Data" => [
						[
							"shipperRef"		=> isset($request_data['skynet_shipment_shipperref']) && !empty($request_data['skynet_shipment_shipperref']) ? $request_data['skynet_shipment_shipperref'] : "",
							"ShipmentType"		=> $request_data['skynet_shipment_type'],
							"Pieces"			=> $request_data['skynet_shipment_pieces'],
							"Weight"			=> $request_data['skynet_shipment_weight'],
							"Contents"			=> $request_data['skynet_shipment_contents'],
							"CurrencyCode"		=> "RM",
							"Value"				=> "0",
							"ShippingDate"		=> date("d-M-y h:ia"),
							"ShipperName"		=> $settings['shipper_name'],
							"ShipperAdd1"		=> $settings['shipper_address1'],
							"ShipperAdd2"		=> $settings['shipper_address2'],
							"ShipperAdd3"		=> $settings['shipper_city'],
							"ShipperAdd4"		=> $settings['shipper_state'],
							"ShipperTelNo"		=> $settings['shipper_tel_no'],
							"ShipperPostCode"	=> $settings['shipper_postcode'],
							"ConsigneeName"		=> $consignee_name,
							"ConsigneeAdd1"		=> $consignee_address_1,
							"ConsigneeAdd2"		=> isset($consignee_addresss_3) ? !empty($consignee_addresss_2) ? $consignee_addresss_2 : $consignee_addresss_3 : $consignee_addresss_2,
							"ConsigneeAdd3"		=> $consignee_city,
							"ConsigneeAdd4"		=> $consignee_state,
							"ConsigneePostCode"	=> $consignee_postcode,
							"ConsigneeTelNo"	=> $consignee_tel_no,
							"CODType"			=> "",
							"CODAmount"			=> "",
							"DepartmentCode"	=> "",
							"ContactPerson"		=> $settings['shipper_default_contact_person'],
							"CustomerAttn"		=> $shipping_company,
							"OriginStn"			=> "",
							"DestStn"			=> "",
							"CreatedBy"			=> $settings['shipper_default_contact_person']."-WEBWOO",
							"CreatedByStn"		=> "",
							"Length"			=> $request_data['skynet_shipment_length'],
							"Width"				=> $request_data['skynet_shipment_width'],
							"Height"			=> $request_data['skynet_shipment_height'],
							"VolWt"				=> $request_data['skynet_shipment_volumn'],
							"ReprintText"		=> ""
						]
					]
				];
			}

			// if($_SERVER['HTTPS'] == "on")
			// {
				// $response = wp_remote_post(SKYNET_API_URL1 . 'Skynet/ECommerce/PrintAWBInternalAPIMultiplePage', [
					// 'headers'     	=> ['Content-Type' => 'application/json',],
					// 'body'        	=> json_encode($body),
				// ]);
			// }
			// else
			// {
				// $response = wp_remote_post(SKYNET_API_URL2 . 'Skynet/ECommerce/PrintAWBInternalAPIMultiplePage', [
					// 'headers'     	=> ['Content-Type' => 'application/json',],
					// 'body'        	=> json_encode($body),
				// ]);
			// }
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

						$wpdb->insert(
							$wpdb->prefix.'wc_skynet_printed_sticker',
							array(
								'order_id'		=> (int)$order_id,
								'awbnumber'		=> esc_html($response_result['AWBNo']),
								'created_on'	=> date("Y-m-d H:i:s"),
							)
						);
					}

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
	
}
