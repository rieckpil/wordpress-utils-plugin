<?php
/*
 *
 * @package   WordPress_Utils_Plugin
 * @author    rieckpil <blog@rieckpil.de>
 * @copyright 2024
 * @license   MIT
 *
 * Plugin Name: WordPress Utils Plugin
 * Description: Various utility functions for WordPress.
 * Version: 1.8.0
 * Author: Philip Riecks
 */

add_action('admin_enqueue_scripts', 'enqueue_functions_script');

function enqueue_functions_script()
{
    wp_enqueue_script('wordpress-utils-plugin-js', plugin_dir_url(__FILE__) . 'admin/js/functions.js', array('jquery'), '1.0', true);
}

add_action('add_meta_boxes', 'wordcount_add_custom_box');

function wordcount_add_custom_box()
{
    add_meta_box(
        'wordcount_sectionid',
        'Word Count',
        'wordcount_custom_box_html',
        'post',
        'side'
    );
}

function wordcount_custom_box_html($post)
{
    echo '<div id="wordcount_value">0 words</div>';
    echo '<button type="button" onclick="wordcount_update()">Update Word Count</button>';
}

add_action('admin_menu', 'wordpress_utils_plugin_menu');

function wordpress_utils_plugin_menu()
{
    add_menu_page('WordPress Utils Plugin Settings', 'WordPress Utils Plugin', 'manage_options', 'wordpress-utils-plugin-settings', 'wordpress_utils_plugin_settings_page', null, 99);
}

function wordpress_utils_plugin_settings_page()
{
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

function wordpress_utils_plugin_page_settings()
{
    register_setting('wordpress-utils-plugin-options', 'openai_api_key');
    add_settings_section('wordpress_utils_plugin_section', '', null, 'wordpress-utils-plugin-options');
    add_settings_field('openai_api_key', 'API Key', 'openai_api_key_callback', 'wordpress-utils-plugin-options', 'wordpress_utils_plugin_section');
}

function openai_api_key_callback()
{
    $api_key = get_option('openai_api_key');
    $display_key = !empty($api_key) ? '****' : ''; // Display **** if key is set
    echo '<input type="text" name="openai_api_key" value="' . esc_attr($display_key) . '" />';
    echo '<p class="description">Enter your OpenAI API key.</p>';
}

add_action('add_meta_boxes', 'openai_add_custom_box');

function openai_enqueue_admin_scripts()
{
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');
}

add_action('admin_enqueue_scripts', 'openai_enqueue_admin_scripts');

function openai_add_custom_box()
{
    add_meta_box('openai_sectionid', 'OpenAI Generated Preview', 'openai_custom_box_html', 'post');
}

function openai_custom_box_html($post)
{
    // Check for existing preview text
    $post_id = $post->ID;
    $post_content = get_post_field('post_content', $post_id);
    $post_url = get_permalink($post_id);

    if (get_post_status($post->ID) == 'draft') {
        echo 'No preview text for drafts.';
        return 'No preview text for drafts.';
    }

    if (empty($post_content)) {
        echo 'No content to generate preview text.';
        return 'No content to generate preview text.';
    }

    $twitter_prompt = "Creating engaging Twitter content to summarize the following blog post content. Include some emojis. The audience are Spring developer that want to learn about testing spring boot applications. the url (' . $post_url . ') must be included at the end of the tweet. Each tweet must have a max of 280 characters not more, use maximum three hashtags. Include line breaks in the tweets. Create three different tweet variations:\n" . $post_content;
    $newsletter_prompt = 'Please summarize the following technical blog article for my newsletter. The newsletter email should have around 400-600 words and summarize the technical blog article, including a CTA at the end of the email to encourage visiting the article at the given URL (' . $post_url . '). Make sure to include some bullet points, as well as text and line breaks, to make it easy for the reader to grasp. The newsletter ends with "Joyful testing, Philip".  The audience consists of Spring Boot Java developers who need help with testing their Spring Boot applications. They signed up for the blog to receive help, best practices, and testing recipes. Please include a compelling email title (including one suiting emoji) and a description of 100-120 characters. \n' . $post_content;

    $social_platforms = [
        ['twitter', $twitter_prompt],
        ['newsletter', $newsletter_prompt]
    ];

    foreach ($social_platforms as $social_platform) {
        $social_platform_name = $social_platform[0];
        $social_platform_prompt = $social_platform[1];


        $preview_text = get_post_meta($post->ID, 'openai_'. $social_platform_name . '_preview_text', true);

        if (!$preview_text) {
            $preview_text = openai_generate_preview_text($social_platform_prompt);

            if ($preview_text) {
                update_post_meta($post->ID, 'openai_'. $social_platform_name . '_preview_text', $preview_text);
            }
        }

        echo '<h3>' . ucfirst($social_platform_name) . '</h3>';
        echo '<textarea id="openai_' . $social_platform_name . '_preview_text" style="width:100%;" rows="4">' . esc_textarea($preview_text) . '</textarea>';
        echo '<i id="copy_' . $social_platform_name . '_to_clipboard" class="fas fa-clipboard" style="cursor: pointer; margin-left: 10px;"></i>';
        echo '<script>
            document.getElementById("copy_'  . $social_platform_name . '_to_clipboard").addEventListener("click", function() {               
                var copyText = document.getElementById("openai_' . $social_platform_name . '_preview_text");
                navigator.clipboard.writeText(copyText.innerHTML);
            });
          </script>';

        echo '<i id="delete_' . $social_platform_name . '_preview_text" class="fas fa-trash" style="cursor: pointer; margin-left: 10px;"></i>';
        // Add script for AJAX call
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#delete_<?php echo  $social_platform_name  ?>_preview_text').click(function () {
                    var data = {
                        'action': 'delete_<?php echo $social_platform_name ?>_preview_text',
                        'post_id': '<?php echo $post->ID; ?>'
                    };

                    $.post(ajaxurl, data, function (response) {
                        $('#openai_<?php echo  $social_platform_name  ?>_preview_text').val('');
                    });
                });
            });
        </script>
        <?php

    }
}

add_action('wp_ajax_delete_twitter_preview_text', 'delete_twitter_preview_text_callback');
add_action('wp_ajax_delete_newsletter_preview_text', 'delete_newsletter_preview_text_callback');

function delete_twitter_preview_text_callback() {
    $post_id = intval($_POST['post_id']);
    delete_post_meta($post_id, 'openai_twitter_preview_text');

    echo 'Twitter preview text deleted';
    wp_die(); // This is required to terminate immediately and return a proper response
}

function delete_newsletter_preview_text_callback() {
    $post_id = intval($_POST['post_id']);
    delete_post_meta($post_id, 'openai_newsletter_preview_text');

    echo 'Newsletter preview text deleted';
    wp_die(); // This is required to terminate immediately and return a proper response
}

function openai_generate_preview_text($prompt) {
    $api_key = get_option('openai_api_key');

    if (empty($api_key)) {
        echo 'Please first configure the OpenAI API key in the settings.';
        return;
    }

    $api_url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'model' => 'gpt-4-1106-preview',
        'temperature' => 0.7  // Adjust creativity/variability
    ];

    $response = wp_remote_post($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode($data),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 90
    ]);


    if (is_wp_error($response)) {
        // Handle error
        return 'Error: ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    return $data->choices[0]->message->content ?? 'Error: No response from API';
}
?>
