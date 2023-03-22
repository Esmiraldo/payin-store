<?php
/*
  Plugin Name: Pay in Store WooCommerce Payment Gatewayx
  Plugin URI: https://www.papaki.com
  Description: Provides a Pay in Store upon pick up Payment Gateway for Woocommerce.
  Version: 1.2.6
  Author: Papaki
  Author URI: https://www.papaki.com
  License: GPL-3.0+
  License URI: http://www.gnu.org/licenses/gpl-3.0.txt
  WC tested up to: 6.2.0
 */

 if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PayInStore {
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'), 0);
        add_filter('woocommerce_email_classes', array($this, 'add_customer_ready_to_collect_email'));
        add_filter('wc_order_statuses', array($this, 'add_custom_order_status_ready_to_collect'));
        add_filter('woocommerce_order_actions', array($this, 'add_custom_order_action'));
        add_action('woocommerce_order_action_send_ready_to_collect_email', array($this, 'process_custom_order_action'));
        register_activation_hook(__FILE__, array($this, 'copy_email_templates_on_activation'));
    }

    public function init() {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        require_once plugin_dir_path(__FILE__) . 'includes/class-pay-in-store-gateway.php';

        $pay_in_store_gateway = new PayInStoreGateway();
        $pay_in_store_gateway->init();
    }

    public function add_customer_ready_to_collect_email($email_classes) {
        $file_path = plugin_dir_path(__FILE__) . 'includes/class-wc-email-customer-ready-to-collect.php';

        if (file_exists($file_path)) {
            require_once $file_path;
            $email_classes['WC_Email_Customer_Ready_To_Collect'] = new WC_Email_Customer_Ready_To_Collect();
        } else {
            error_log('Pay in Store WooCommerce Payment Gateway: Unable to load email class file.');
        }

        return $email_classes;
    }

    public function add_custom_order_status_ready_to_collect($order_statuses) {
        if (is_array($order_statuses)) {
            $order_statuses['wc-ready-to-collect'] = _x('Ready to Collect', 'Order status', 'woocommerce');
        } else {
            error_log('Pay in Store WooCommerce Payment Gateway: Unable to add custom order status.');
        }

        return $order_statuses;
    }

    public function add_custom_order_action($actions) {
        if (is_array($actions)) {
            $actions['send_ready_to_collect_email'] = __('Send Ready to Collect Email', 'woocommerce');
        } else {
            error_log('Pay in Store WooCommerce Payment Gateway: Unable to add custom order action.');
        }

        return $actions;
    }

    public function process_custom_order_action($order) {
        $order->update_status('wc-ready-to-collect', __('Order is ready to collect', 'woocommerce'));
    }

    public function copy_email_templates_on_activation() {
        $src = plugin_dir_path(__FILE__) . 'templates/emails';
        $dst = plugin_dir_path(__FILE__) . '../woocommerce/templates/emails';

        if (!file_exists($dst)) {
            mkdir($dst, 0755, true);
        }

        $this->copy_directory($src, $dst);
    }

    public function copy_directory($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copy_directory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }
}

new PayInStore();
