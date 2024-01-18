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

add_action('admin_menu', 'wordpress_utils_plugin_menu');

function wordpress_utils_plugin_menu() {
    add_menu_page('WordPress Utils Plugin Settings', 'WordPress Utils Plugin', 'manage_options', 'wordpress-utils-plugin-settings', 'wordpress_utils_plugin_settings_page', null, 99);
}

function wordpress_utils_plugin_settings_page() {
    ?>
    <div class="wrap">
        <h1>WordPress Plugin Utils Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wordpress-utils-plugin-options');
            do_settings_sections('wordpress-utils-plugin-options');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'wordpress_utils_plugin_page_settings');

function wordpress_utils_plugin_page_settings() {
    register_setting('wordpress-utils-plugin-options', 'openai_api_key');
    add_settings_section('wordpress_utils_plugin_section', '', null, 'wordpress-utils-plugin-options');
    add_settings_field('openai_api_key', 'API Key', 'openai_api_key_callback', 'wordpress-utils-plugin-options', 'wordpress_utils_plugin_section');
}

function openai_api_key_callback() {
    $api_key = get_option('openai_api_key');
    $display_key = $api_key ? str_repeat('*', strlen($api_key) - 4) . substr($api_key, -4) : '';

    echo '<input type="text" name="openai_api_key" value="' . esc_attr($display_key) . '" />';
    echo '<p class="description">Enter your OpenAI API key.</p>';
}

?>
