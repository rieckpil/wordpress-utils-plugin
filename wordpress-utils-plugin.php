<?php
/*
Plugin Name: Alert on D Key
Description: Shows an alert box when the 'D' key is pressed.
Version: 1.0
Author: Philip Riecks
*/

add_action('admin_enqueue_scripts', 'alert_on_d_key_enqueue_script');

function alert_on_d_key_enqueue_script() {
    wp_enqueue_script('alert-on-d-key-js', plugin_dir_url(__FILE__) . 'admin/js/functions.js', array(), '1.0', true);
}
?>
