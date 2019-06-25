<?php
/**
 * Plugin Name: WC Carrier
 * Plugin URI: https://adroittechnosys.com/
 * Description: A Simple WC plugin that allows manage Carrier for shipping method.
 * Version: 1.0.0
 * Author: Keval Thacker
 * Author URI: https://adroittechnosys.com/
 * Text Domain: wc-carrier
 */
/* Denied direct access */
if (!defined('WPINC')) {
    die;
}
/* Check whether woocommerce is install or not */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    /* Custom Js nqueue for admin */
    function wc_carrier_admin_js()
    {
        global $wpdb;
        /* parameter initialize */
        $ids         = '';
        $carrier_ids = '';
        /* Fetch Exisitng Instances for shipping methods */
        $flat_rates  = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "woocommerce_shipping_zone_methods ");
        foreach ($flat_rates as $fr) {
            /* find type of method    */
            if ($fr->method_id == 'flat_rate' || $fr->method_id == 'free_shipping') {
                if ($ids == '') {
                    $ids = $fr->instance_id;
                } else {
                    $ids = $ids . ',' . $fr->instance_id;
                }
                if ($carrier_ids == '') {
                    if (get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true) == '') {
                        $carrier_ids = '-';
                    } else {
                        $carrier_ids = get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true);
                    }
                } else {
                    if (get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true) == '') {
                        $carrier_ids = $carrier_ids . ',-';
                    } else {
                        $carrier_ids = $carrier_ids . ',' . get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true);
                    }
                }
                
            }
        }
        echo '<script type="text/javascript">';
        echo 'var flat_rate_id ="' . $ids . '";';
        echo 'var carrier_ids ="' . $carrier_ids . '";';
        echo "</script>";
        
        wp_enqueue_script('wc_carrier_admin_js', plugin_dir_url(__FILE__) . 'js/wc-carrier-script.js', array(
            'jquery'
        ));
        wp_localize_script('wc_carrier_admin_js', 'wc_carrier', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
    add_action('admin_enqueue_scripts', 'wc_carrier_admin_js');
    
    /* Set carrier meta on order status changed to processing */
    add_action('woocommerce_order_status_changed', 'wc_carrier_order_processing', 10, 3);
    function wc_carrier_order_processing($order_id, $from_status, $to_status)
    {
        if ($to_status == 'processing') {
            $order = wc_get_order($order_id);
            // Iterating through order shipping items
            foreach ($order->get_items('shipping') as $item_id => $shipping_item_obj) {
                $shipping_method_id          = $shipping_item_obj->get_method_id(); // The method ID
                $shipping_method_instance_id = $shipping_item_obj->get_instance_id(); // The instance ID
                $option_name                 = 'woocommerce_shipping_' . $shipping_method_instance_id . '_carrier_id';
                if ($shipping_method_id == "flat_rate" || $shipping_method_id == "free_shipping") {
                    if (metadata_exists('post', $order_id, '_carrier_id')) {
                        update_post_meta($order_id, '_carrier_id', get_option($option_name, true));
                    } else {
                        add_post_meta($order_id, '_carrier_id', get_option($option_name, true));
                    }
                }
            }
        }
    }
    /* Ajax setup for c*/
    add_action('wp_ajax_nopriv_wc_carrier_shipping_methods_carrier_id', 'wc_carrier_shipping_methods_carrier_id_fn');
    add_action('wp_ajax_wc_carrier_shipping_methods_carrier_id', 'wc_carrier_shipping_methods_carrier_id_fn');
    function wc_carrier_shipping_methods_carrier_id_fn()
    {
        global $wpdb;
        $option_name = 'woocommerce_shipping_' . $_POST['instance_id'] . '_carrier_id';
        if (get_option($option_name) !== false) {
            // The option already exists, so we just update it.
            update_option($option_name, $_POST['cid']);
            
        } else {
            // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
            $deprecated = null;
            $autoload   = 'no';
            add_option($option_name, $_POST['cid'], $deprecated, $autoload);
        }
        $carrier_ids = '';
        $flat_rates  = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "woocommerce_shipping_zone_methods ");
        foreach ($flat_rates as $fr) {
            if ($fr->method_id == 'flat_rate' || $fr->method_id == 'free_shipping') {
                if ($carrier_ids == '') {
                    if (get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true) == '') {
                        $carrier_ids = '-';
                    } else {
                        $carrier_ids = get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true);
                    }
                } else {
                    if (get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true) == '') {
                        $carrier_ids = $carrier_ids . ',-';
                    } else {
                        $carrier_ids = $carrier_ids . ',' . get_option('woocommerce_shipping_' . $fr->instance_id . '_carrier_id', true);
                    }
                }
                
            }
        }
        echo $carrier_ids;
        die();
    }
}
?>