<?php 
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<tr valign="top">
    <th scope="row" class="titledesc">
        <label><?php esc_html_e('Store Schedule', 'woocommerce'); ?></label>
    </th>
    <td class="forminp" id="store_schedule">
        <div class="papaki-bzn-style">
            <table class="widefat wc_input_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Day', 'woocommerce'); ?></th>
                        <th><?php esc_html_e('Open', 'woocommerce'); ?></th>
                        <th><?php esc_html_e('Opening Hours', 'woocommerce'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($days as $key => $label) :
                        $open_option = $this->get_option($key . '_open');
                        $hours_option = $this->get_option($key . '_hours');
                    ?>
                        <tr>
                            <td><?php echo esc_html($label); ?></td>
                            <td><input type="checkbox" name="<?php echo esc_attr($this->plugin_id . $this->id . '_settings[' . $key . '_open]'); ?>" id="<?php echo esc_attr($key . '_open'); ?>" <?php checked($open_option, 'yes'); ?>></td>
                            <td><input type="text" name="<?php echo esc_attr($this->plugin_id . $this->id . '_settings[' . $key . '_hours]'); ?>" id="<?php echo esc_attr($key . '_hours'); ?>" style="width: 100%;" value="<?php echo esc_attr($hours_option); ?>" /></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </td>
</tr>
