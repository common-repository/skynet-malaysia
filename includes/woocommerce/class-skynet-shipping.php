<?php

class Skynet_Shipping extends WC_Shipping_Method
{
    public function __construct($instance_id = 0)
    {
        $this->id = 'skynet';
        $this->instance_id        = absint($instance_id);
        $this->method_title       = __('Skynet', 'skynet');
        $this->method_description = __('To start creating Skynet consignments, please fill in your user credentials as shown in your Skynet user portal.', 'skynet');

        $this->init();
    }

    public function init()
    {
        $this->init_form_fields();
        $this->init_settings();

        $this->define_hooks();
    }

    function define_hooks()
    {
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'display_errors']);
    }

    public function init_form_fields()
    {
        $this->init_api_form_fields();
        $this->init_shipper_form_fields();
    }

    public function init_api_form_fields()
    {
        $fields['skynet_api'] = [
            'title'       => __('API', 'skynet'),
            'type'        => 'title',
            'description' => __('Configure your access towards the Skynet APIs by means of authentication.', 'skynet'),
        ];

        $fields['skynet_api_user_access_token'] = [
            'title'             => __('User Access Token', 'skynet'),
            'type'              => 'text',
            'description'       => __(
                'Go to <a href="https://skynet.com.my/api" target="_blank">Skynet APIs</a> page, generate and obtain your user access token.',
                'skynet'
            ),
            'custom_attributes' => [
                'required' => 'required',
                'maxlength' => '100'
            ],
        ];

        $this->form_fields += $fields;
    }

    public function init_shipper_form_fields()
    {
        $fields['shipper_details'] = [
            'title'       => __('Sender Details', 'skynet'),
            'type'        => 'title',
            'description' => __('<a id="skynet-default-store-address-link" class="button-secondary">Load Default Store Address</a>', 'skynet'),
        ];
        $fields['shipper_name'] = [
            'title'             => __('Company Name', 'skynet'),
            'type'              => 'text',
            'custom_attributes' => ['maxlength' => '100'],
        ];
        $fields['shipper_tel_no'] = [
            'title'             => __('Contact Number', 'skynet'),
            'type'              => 'tel',
            'custom_attributes' => ['required' => 'required', 'maxlength' => '20'],
        ];
        $fields['shipper_default_contact_person'] = [
            'title'             => __('Default Contact Person', 'skynet'),
            'type'              => 'text',
            'custom_attributes' => ['required' => 'required', 'maxlength' => '20'],
        ];
        $fields['shipper_address1'] = [
            'title'             => __('Address', 'skynet'),
            'type'              => 'text',
            'custom_attributes' => ['required' => 'required', 'maxlength' => '50'],
        ];
        $fields['shipper_address2'] = [
            'type'              => 'text',
            'custom_attributes' => ['maxlength' => '50'],
        ];
        $fields['shipper_city'] = [
            'title'             => __('City', 'skynet'),
            'type'              => 'text',
            'custom_attributes' => ['required' => 'required', 'maxlength' => '50'],
        ];
        $fields['shipper_state'] = [
            'title'             => __('State', 'skynet'),
            'type'              => 'text',
            'custom_attributes' => ['required' => 'required', 'maxlength' => '50'],
        ];
        $fields['shipper_postcode'] = [
            'title'             => __('Postcode', 'skynet'),
            'type'              => 'text',
            'custom_attributes' => ['required' => 'required', 'maxlength' => '50'],
        ];
        $fields['shipper_country'] = [
            'title'             => __('Country', 'skynet'),
            'type'              => 'text',
            'custom_attributes' => ['required' => 'required', 'maxlength' => '50'],
        ];

        $this->form_fields += $fields;
    }
}
