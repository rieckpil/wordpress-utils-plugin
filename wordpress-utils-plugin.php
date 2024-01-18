<?php
/*
Plugin Name: WordPress Utils Plugin
Description: Various utility functions for WordPress.
Version: 1.0
Author: Philip Riecks
*/

add_action('admin_enqueue_scripts', 'enqueue_functions_script');

function enqueue_functions_script() {
    wp_enqueue_script('wordpress-utils-plugin-js', plugin_dir_url(__FILE__) . 'admin/js/functions.js', array('jquery'), '1.0', true);
}

add_action('add_meta_boxes', 'wordcount_add_custom_box');

function wordcount_add_custom_box() {
    add_meta_box(
        'wordcount_sectionid',
        'Word Count',
        'wordcount_custom_box_html',
        'post',
        'side'
    );
}

function wordcount_custom_box_html($post) {
    echo '<div id="wordcount_value">0 words</div>';
    echo '<button type="button" onclick="wordcount_update()">Update Word Count</button>';
}
?>
