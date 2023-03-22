<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email_Customer_Ready_To_Collect', false ) ) :

class WC_Email_Customer_Ready_To_Collect extends WC_Email {

    public function __construct() {
        $this->id               = 'customer_ready_to_collect';
        $this->customer_email   = true;
        $this->title            = __( 'Ready to Collect', 'woocommerce' );
        $this->description      = __( 'This is an order notification sent to customers when their order is ready to collect.', 'woocommerce' );
        $this->template_html    = 'emails/customer-ready-to-collect.php';
        $this->template_plain   = 'emails/plain/customer-ready-to-collect.php';
        $this->placeholders     = array(
            '{order_date}'   => '',
            '{order_number}' => '',
        );

        // Triggers for this email
        add_action( 'woocommerce_order_status_ready-to-collect_notification', array( $this, 'trigger' ), 10, 2 );

        parent::__construct();
    }

    public function trigger( $order_id, $order = false ) {
        $this->setup_locale();

        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if ( is_a( $order, 'WC_Order' ) ) {
            $this->object                         = $order;
            $this->recipient                      = $this->object->get_billing_email();
            $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
        }

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'         => $this,
        ) );
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'         => $this,
            ) );
        }
    
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'woocommerce' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable this email notification', 'woocommerce' ),
                    'default' => 'yes',
                ),
                'subject' => array(
                    'title'       => __( 'Subject', 'woocommerce' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => __( 'This controls the email subject line. Leave blank to use the default subject:', 'woocommerce' ) . ' <code>' . $this->get_default_subject() . '</code>',
                    'placeholder' => '',
                    'default'     => 'Ready to Collect Order',
                ),
                'heading' => array(
                    'title'       => __( 'Email Heading', 'woocommerce' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => __( 'This controls the main heading included in the email notification. Leave blank to use the default heading:', 'woocommerce' ) . ' <code>' . $this->get_default_heading() . '</code>',
                    'placeholder' => '',
                    'default'     => 'Ready to Collect Order',
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'woocommerce' ),
                    'type'        => 'select',
                    'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
                    'default'     => 'html',
                    'class'       => 'email_type wc-enhanced-select',
                    'options'     => $this->get_email_type_options(),
                    'desc_tip'    => true,
                ),
            );
        }
    }
    
    endif;
  
return new WC_Email_Customer_Ready_To_Collect();
