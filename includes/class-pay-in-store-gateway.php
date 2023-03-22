<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PayInStoreGateway
{
    public function init()
    {
        require_once plugin_dir_path(__FILE__) . 'class-wc-gateway-pay-in-store.php';
        add_filter('woocommerce_payment_gateways', array($this, 'add_pay_in_store_gateway'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function add_pay_in_store_gateway($methods)
    {
        $methods[] = 'WC_Gateway_PayInStore';
        return $methods;
    }
    public function enqueue_admin_assets()
    {
        wp_enqueue_style(
            'pay-in-store-gateway-admin',
            plugin_dir_url(dirname(__FILE__)) . 'assets/admin.css',
            array(),
            '1.0.0'
        );
    }

}