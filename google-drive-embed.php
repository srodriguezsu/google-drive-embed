<?php
/*
Plugin Name: Google Drive Embed
Description: Insert Google Drive file embeds via Classic or Gutenberg editor.
Version: 1.0
Author: Your Name
*/

defined('ABSPATH') or die('No script kiddies please!');

// Register scripts for Classic Editor
function gde_classic_editor_assets() {
    wp_enqueue_script(
        'gde-editor',
        plugins_url('js/editor.js', __FILE__),
        array('jquery'),
        null,
        true
    );
}
add_action('admin_enqueue_scripts', 'gde_classic_editor_assets');

// Register Gutenberg block
function gde_register_block() {
    wp_register_script(
        'gde-block',
        plugins_url('block/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        null,
        true
    );
    register_block_type('gde/google-drive', array(
        'editor_script' => 'gde-block',
        'render_callback' => 'gde_render_callback',
        'attributes' => array(
            'link' => array('type' => 'string'),
            'title' => array('type' => 'string'),
        ),
    ));
}
add_action('init', 'gde_register_block');

// Render callback for frontend
function gde_render_callback($attributes) {
    $link = esc_url($attributes['link']);
    $title = esc_html($attributes['title']);
    $id = preg_match('/\/d\/([^\/]+)/', $link, $matches) ? $matches[1] : '';
    if (!$id) return '';

    $iframe = sprintf(
        '<div id="google-drive-container-%d"><h2>%s</h2><p><iframe src="https://drive.google.com/file/d/%s/preview" width="640" height="480" allow="autoplay"></iframe><br><a href="https://drive.google.com/file/d/%s/view" class="btn btn-primary" target="_blank" rel="noopener noreferrer"><br>Enlace a %s<br></a><br><br></p></div>',
        rand(1, 999),
        $title,
        $id,
        $id,
        $title
    );

    return $iframe;
}

function gde_embed_shortcode($atts) {
    $atts = shortcode_atts(array(
        'link' => '',
        'title' => 'Documento',
    ), $atts);

    $link = esc_url($atts['link']);
    $title = esc_html($atts['title']);

    if (preg_match('/\/d\/([^\/]+)/', $link, $matches)) {
        $id = $matches[1];
    } else {
        return '<p>Invalid Google Drive link</p>';
    }

    return sprintf(
        '<div id="google-drive-container-%d">
            <h2>%s</h2>
            <p>
                <iframe src="https://drive.google.com/file/d/%s/preview" width="640" height="480" allow="autoplay"></iframe><br>
                <a href="https://drive.google.com/file/d/%s/view" class="btn btn-primary" target="_blank" rel="noopener noreferrer"><br>
                Enlace a %s<br>
                </a><br><br>
            </p>
        </div>',
        rand(1, 999),
        $title,
        $id,
        $id,
        $title
    );
}
add_shortcode('gdrive_embed', 'gde_embed_shortcode');

add_filter('mce_external_plugins', function($plugins) {
    $plugins['gde_button'] = plugins_url('js/editor.js', __FILE__);
    return $plugins;
});

add_filter('mce_buttons', function($buttons) {
    array_push($buttons, 'gde_button');
    return $buttons;
});

