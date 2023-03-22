<?php
/**
 * Customer Proceed To PickUp email template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-proceed-to-pickup.php.
 *
 * @author      bzn.gr
 * @package     WooCommerce/Templates/Emails
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

?>

<?php do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php _e("Hi there,", 'woocommerce'); ?></p>
<p><?php _e("Your recent order is now ready for pickup! Here are the details:", 'woocommerce'); ?></p>

<?php

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

?>

<p><?php _e("Please proceed to our store to collect and pay for your order. Don't forget to bring your order number.", 'woocommerce'); ?></p>

<p><?php _e("We look forward to seeing you soon!", 'woocommerce'); ?></p>

<?php do_action('woocommerce_email_footer', $email); ?>
