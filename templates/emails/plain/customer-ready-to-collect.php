<?php
/**
 * Customer Proceed To PickUp Plain Email Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/customer-proceed-to-pickup.php.
 *
 * @author      BZNGR
 * @package     WooCommerce/Templates/Emails/Plain
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

echo "= " . esc_html($email_heading) . " =\n\n";

echo esc_html_e("Hi there,", 'woocommerce');
echo "\n\n";
echo esc_html_e("Your recent order on {site_title} is now ready for pickup! Here are the details:", 'woocommerce');
echo "\n\n";

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

echo "\n----------------------------------------\n\n";

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

echo "\n----------------------------------------\n\n";

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

echo "\n\n";

echo esc_html_e("Please proceed to our store to collect and pay for your order. Don't forget to bring your order number with you for a smoother pickup experience.", 'woocommerce');

echo "\n\n";

echo esc_html_e("We look forward to seeing you soon!", 'woocommerce');

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";