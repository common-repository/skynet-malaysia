<?php

class Skynet_Settings
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.6
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.6
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.6
     *
     * @param      string $plugin_name The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        $this->define_hooks();
    }


    /**
     * Define hooks
     */
    protected function define_hooks()
    {
        add_filter('woocommerce_shipping_methods', [$this, 'add_shipping_method']);
        add_action('wp_ajax_skynet_load_default_address', [$this, 'ajax_load_default_address']);
        add_action('admin_print_footer_scripts', [$this, 'add_load_address_nonce']);
    }

    /**
     * Add woocommerce shipping method
     *
     * @param $methods
     *
     * @return mixed
     */
    public function add_shipping_method($methods)
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/woocommerce/class-skynet-shipping.php';

        $methods['skynet'] = Skynet_Shipping::class;
        return $methods;
    }

    public function ajax_load_default_address()
    {
        check_ajax_referer('skynet-load-address-nonce', 'nonce');

        try {
            // get state name if not empty
            $state_name = !empty(WC()->countries->get_base_state()) ? WC()->countries->states[WC()->countries->get_base_country()][WC()->countries->get_base_state()] : '';

            wp_send_json_success([
                'address1' => WC()->countries->get_base_address(),
                'address2' => WC()->countries->get_base_address_2(),
                'city' => WC()->countries->get_base_city(),
                'postcode' => WC()->countries->get_base_postcode(),
                'state' => $state_name,
                'country' => WC()->countries->countries[WC()->countries->get_base_country()],
            ]);
        } catch (Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage() ?? "Something went wrong. Could not load data from database.",
            ]);
        }

        wp_die();
    }

    public function add_load_address_nonce()
    {
        global $current_screen;

        if (!$current_screen || $current_screen->id !== 'woocommerce_page_wc-settings') {
            return;
        }

        $current_tab = isset($_REQUEST['tab']) ? wc_clean($_REQUEST['tab']) : 'general';
        if ($current_tab !== 'shipping') {
            return;
        }

        $current_section = isset($_REQUEST['section']) ? wc_clean($_REQUEST['section']) : '';
        if ($current_section !== 'skynet') {
            return;
        }

?>
        <script>
            var skynet_setting = {
                load_address_nonce: "<?php echo wp_create_nonce('skynet-load-address-nonce'); ?>"
            }
        </script>
<?php
    }
}
