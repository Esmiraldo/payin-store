<?php

class WC_Gateway_PayInStore extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id = 'payinstore';
        $this->icon = apply_filters('woocommerce_payinstore_icon', '');
        $this->method_title = __('Pay in Store', 'woocommerce');
        $this->method_description = __('Have your customers pay with cash (or by other means) in store upon  pickup.', 'woocommerce');
        $this->has_fields = false;

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        // Get settings
        $this->title = $this->get_option('title');
        $image_url = $this->get_option('image_url');
        if ($image_url) {
            $this->description = '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($this->description) . '" />' . $this->description = $this->get_option('description');
        } else {
            $this->description = $this->get_option('description');
        }
        $this->instructions = $this->get_option('instructions', $this->description);
        $this->enable_for_methods = $this->get_option('enable_for_methods', array());
        $this->enable_for_virtual = $this->get_option('enable_for_virtual', 'yes') === 'yes' ? true : false;

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_thankyou_payinstore', array($this, 'thankyou_page'));
        add_action('woocommerce_admin_field_schedule_table', array($this, 'generate_schedule_table_html'));
        add_action('woocommerce_update_options_payment_gateways_payinstore', array($this, 'save_custom_fields'));
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        // send email
        add_filter('woocommerce_order_actions', array($this, 'add_order_action'));
        add_action('woocommerce_order_action_send_ready_to_collect_email', array($this, 'process_order_action'));


    }
    
    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $shipping_methods = array();

        if (is_admin()) {
            foreach (WC()->shipping()->load_shipping_methods() as $method) {
                $shipping_methods[$method->id] = $method->get_method_title();
            }
        }

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable Pay in Store', 'woocommerce'),
                'label' => __('Enable Pay in Store', 'woocommerce'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ),
            'title' => array(
                'title' => __('Title', 'woocommerce'),
                'type' => 'text',
                'description' => __('Payment method description that the customer will see on your checkout.', 'woocommerce'),
                'default' => __('Pay in Store', 'woocommerce'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your website.', 'woocommerce'),
                'default' => __('Pay with cash upon delivery.', 'woocommerce'),
                'desc_tip' => true,
            ),
            'image_url' => array(
                'title' => __('Description Image URL', 'woocommerce'),
                'type' => 'url',
                'description' => __('Enter the URL to the custom image that will be displayed for this payment method description. Leave blank to disable.'),
                'default' => '',
                'desc_tip' => true,
            ),
            'instructions' => array(
                'title' => __('Instructions', 'woocommerce'),
                'type' => 'textarea',
                'description' => __('Instructions that will be added to the thank you page.', 'woocommerce'),
                'default' => __('Pay with cash upon pickup.', 'woocommerce'),
                'desc_tip' => true,
            ),
            'enable_for_methods' => array(
                'title' => __('Enable for shipping methods', 'woocommerce'),
                'type' => 'multiselect',
                'class' => 'wc-enhanced-select',
                'css' => 'width: 450px;',
                'default' => '',
                'description' => __('If Pay in Store is only available for certain shipping methods, set it up here. Leave blank to enable for all methods.', 'woocommerce'),
                'options' => $shipping_methods,
                'desc_tip' => true,
            ),
            'enable_for_virtual' => array(
                'title' => __('Accept for virtual orders', 'woocommerce'),
                'label' => __('Accept Pay in Store if the order is virtual', 'woocommerce'),
                'type' => 'checkbox',
                'default' => 'yes'
            ),
            'order_processing_time' => array(
                'title' => __('Order Processing Time', 'woocommerce'),
                'type' => 'text',
                'description' => __('Enter the order processing time (e.g. "24-48 hours").', 'woocommerce'),
                'default' => '',
            ),
            'store_schedule' => array(
                'title' => __('Store Schedule', 'woocommerce'),
                'type' => 'schedule_table',
            ),
            'store_address' => array(
                'title' => __('Store Address', 'woocommerce'),
                'type' => 'text',
                'description' => __('Enter the store address where customers can pick up their orders. e.g. Leoforos Vasilissis Sofias 17, Athina 10671 or 40.748817,-73.985428', 'woocommerce'),
                'default' => '',
                'desc_tip' => true,
            ),
            'store_maps_enabled' => array(
                'title' => __('Enable Google Maps URL', 'woocommerce'),
                'label' => __('Enable Google Maps URL', 'woocommerce'),
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no'
            ),

        );
    }
    public function generate_schedule_table_html()
    {
        $days = array(
            'monday' => __('Monday', 'woocommerce'),
            'tuesday' => __('Tuesday', 'woocommerce'),
            'wednesday' => __('Wednesday', 'woocommerce'),
            'thursday' => __('Thursday', 'woocommerce'),
            'friday' => __('Friday', 'woocommerce'),
            'saturday' => __('Saturday', 'woocommerce'),
            'sunday' => __('Sunday', 'woocommerce'),
        );

        ob_start();

        require_once plugin_dir_path(__FILE__) . 'template-pay-in-store-schedule.php';

        return ob_get_clean();
    }

	public function get_formatted_store_schedule()
	{
		$days = array(
			'monday' => __('Monday', 'woocommerce'),
			'tuesday' => __('Tuesday', 'woocommerce'),
			'wednesday' => __('Wednesday', 'woocommerce'),
			'thursday' => __('Thursday', 'woocommerce'),
			'friday' => __('Friday', 'woocommerce'),
			'saturday' => __('Saturday', 'woocommerce'),
			'sunday' => __('Sunday', 'woocommerce'),
		);

		$schedule = array();
		foreach ($days as $key => $label) {
			if ($this->get_option($key . '_open') === 'yes') {
				$hours = esc_attr($this->get_option($key . '_hours'));
				$schedule[] = esc_html($label) . ': ' . esc_html($hours);
			}
		}

		return implode('<br>', $schedule);
	}


    /**
     * Check If The Gateway Is Available For Use
     *
     * @return bool
     */
    public function is_available()
    {
        $order = null;
        $needs_shipping = false;

        if (WC()->cart && WC()->cart->needs_shipping()) {
            $needs_shipping = true;
        } elseif (is_page(wc_get_page_id('checkout')) && 0 < get_query_var('order-pay')) {
            $order_id = absint(get_query_var('order-pay'));
            $order = wc_get_order($order_id);
            // deprecated sizeof replaced with  count
            if (0 < count($order->get_items())) {
                foreach ($order->get_items() as $item) {
                    $_product = $item->get_product();
                    if ($_product && $_product->needs_shipping()) {
                        $needs_shipping = true;
                        break;
                    }
                }
            }
        }

        $needs_shipping = apply_filters('woocommerce_cart_needs_shipping', $needs_shipping);

        // Virtual order, with virtual disabled
        if (!$this->enable_for_virtual && !$needs_shipping) {
            return false;
        }

        if (!empty($this->enable_for_methods) && $needs_shipping) {
            $chosen_shipping_methods_session = WC()->session->get('chosen_shipping_methods');
            if (isset($chosen_shipping_methods_session[0])) {
                $chosen_shipping_methods = array($chosen_shipping_methods_session[0]);
            } else {
                $chosen_shipping_methods = array();
            }

            if (0 < count(array_intersect($chosen_shipping_methods, $this->enable_for_methods))) {
                return true;
            }

            return false;
        }

        return parent::is_available();
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status('on-hold', __('Awaiting pay in store payment', 'woocommerce'));

        // Reduce stock levels
        $order->reduce_order_stock();

        // Remove cart
        WC()->cart->empty_cart();

        // Return thankyou redirect
        return array(
            'result' => 'success',
            'redirect' => esc_url($this->get_return_url($order))
        );
    }

    /**
     * Output for the order received page.
     */
	public function thankyou_page()
	{
		if ($this->instructions) {
			echo wpautop(wptexturize(esc_html($this->instructions)));
		}
		$order_processing_time = $this->get_option('order_processing_time');
		if (!empty($order_processing_time)) {
			echo '<h2>' . esc_html(__('Order Processing Time', 'woocommerce')) . '</h2>';
			echo wpautop(wptexturize(esc_html($order_processing_time)));
		}

		
		$store_address = $this->get_option('store_address');
		echo '<h2>' . esc_html(__('Store Opening Hours', 'woocommerce')) . '</h2>';
		echo wpautop(wptexturize($this->get_formatted_store_schedule()));
		if ($this->get_option('store_maps_enabled') === 'yes') {
			$google_maps_url = "https://www.google.com/maps?q=" . urlencode(esc_url($store_address));
			echo '<a href="' . esc_url($google_maps_url) . '" target="_blank" class="button">' . esc_html(__('Find Store in Google Maps', 'woocommerce')) . '</a>';
		} else {
			echo '<p>' . esc_html(__('You can pick your order from this location ', 'woocommerce')) . esc_html($store_address) .'</p>';
		}

	}

    /**
     * Add content to the WC emails.
     *
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
	public function email_instructions($order, $sent_to_admin, $plain_text = false)
	{
		if ($this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status('on-hold')) {
			echo wpautop(wptexturize($this->instructions)) . PHP_EOL;

			$order_processing_time = $this->get_option('order_processing_time');
			if (!empty($order_processing_time)) {
				echo '<h2>' . esc_html__('Order Processing Time', 'woocommerce') . '</h2>';
				echo wpautop(wptexturize($order_processing_time)) . PHP_EOL;
			}
			echo '<h2>' . esc_html__('Store Opening Hours', 'woocommerce') . '</h2>';
			echo wpautop(wptexturize($this->get_formatted_store_schedule()));
			$store_address = $this->get_option('store_address');
			if ($this->get_option('store_maps_enabled') === 'yes') {
				$google_maps_url = "https://www.google.com/maps?q=" . urlencode($store_address);
				echo '<a href="' . esc_url($google_maps_url) . '" target="_blank" class="button">' . esc_html__('Find Store in Google Maps', 'woocommerce') . '</a>';
			} else {
				echo '<p>' . esc_html__('You can pick your order from this location', 'woocommerce') . esc_html($store_address) .'</p>';
			}
		}
	}

    
    

    public function process_admin_options()
    {
        parent::process_admin_options();
    }
    public function save_custom_fields()
    {
        $days = array(
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        );

        foreach ($days as $day) {
            $this->settings[$day . '_open'] = isset($_POST[$this->plugin_id . $this->id . '_settings'][$day . '_open']) ? 'yes' : 'no';
            $this->settings[$day . '_hours'] = isset($_POST[$this->plugin_id . $this->id . '_settings'][$day . '_hours']) ? sanitize_text_field($_POST[$this->plugin_id . $this->id . '_settings'][$day . '_hours']) : '';
        }
        update_option($this->plugin_id . $this->id . '_settings', $this->settings);
    }
    public function send_ready_to_collect_email($order_id) {
        $order = wc_get_order($order_id);
        $mailer = WC()->mailer();
        $email = $mailer->emails['WC_Email_Customer_Ready_To_Collect'];
        $email->trigger($order_id);
    }
    public function add_order_action($actions) {
        $actions['send_ready_to_collect_email'] = __('Send Ready to Collect Email', 'woocommerce');
        return $actions;
    }
    
    public function process_order_action($order) {
        $this->send_ready_to_collect_email($order->get_id());
    }
    



}