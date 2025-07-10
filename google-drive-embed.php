<?php
/*
Plugin Name: Insertar Google Drive
Description: Inserta archivos o carpetas de Google Drive al editor de WordPress.
Version: 1.2.2
Author: Sebastian Rodriguez
*/

defined('ABSPATH') or exit;

// 🔧 Util: Extract Google Drive ID and type
function gde_parse_drive_link($link) {
    $link = trim(esc_url_raw($link));

    if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
        return ['type' => 'file', 'id' => $matches[1]];
    }

    if (preg_match('/\/drive\/folders\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
        return ['type' => 'folder', 'id' => $matches[1]];
    }

    return ['type' => 'invalid', 'id' => null];
}

// 🎨 Util: Render embed HTML
function gde_render_embed_html($id, $title, $type, $container_id = null) {
    if (!$id || $type === 'invalid') {
        return '<p><strong>Error:</strong> Enlace no válido.</p>';
    }

    $container_id = $container_id ?? rand(1, 999);

    if ($type === 'file') {
        $iframe_src = "https://drive.google.com/file/d/{$id}/preview";
        $view_link = "https://drive.google.com/file/d/{$id}/view";
    } else {
        $iframe_src = "https://drive.google.com/embeddedfolderview?id={$id}#grid";
        $view_link = "https://drive.google.com/drive/folders/{$id}";
    }

    return sprintf(
        '<div id="google-drive-container-%d">
            <h2>%s</h2>
            <p>
                <iframe src="%s" width="100%%" height="480" frameborder="0" allow="autoplay"></iframe><br>
                <a href="%s" class="btn btn-primary" target="_blank" rel="noopener noreferrer"><br>
                Enlace a %s<br>
                </a><br>
            </p>
        </div>',
        $container_id,
        esc_html($title),
        $iframe_src,
        $view_link,
        esc_html($title)
    );
}

//
// 🔌 Classic Editor Integration
//
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

add_filter('mce_external_plugins', function ($plugins) {
    $plugins['gde_button'] = plugins_url('js/editor.js', __FILE__);
    return $plugins;
});
add_filter('mce_buttons', function ($buttons) {
    array_push($buttons, 'gde_button');
    return $buttons;
});

//
// 🧱 Gutenberg Block Registration
//
function gde_register_block() {
    wp_register_script(
        'gde-block',
        plugins_url('block/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . 'block/block.js')
    );

    // Register block editor styles
    wp_register_style(
        'gde-block-editor',
        plugins_url('block/editor.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'block/editor.css')
    );

    register_block_type('gde/google-drive', array(
        'editor_script' => 'gde-block',
        'editor_style' => 'gde-block-editor',
        'render_callback' => 'gde_render_callback',
        'attributes' => array(
            'link' => array('type' => 'string'),
            'title' => array('type' => 'string'),
        ),
    ));
}
add_action('init', 'gde_register_block');

function gde_render_callback($attributes) {
    $parsed = gde_parse_drive_link($attributes['link']);
    return gde_render_embed_html($parsed['id'], $attributes['title'], $parsed['type']);
}

//
// 🔢 Shortcode Support
//
function gde_embed_shortcode($atts) {
    $atts = shortcode_atts(array(
        'enlace' => '',
        'titulo' => 'Documento',
    ), $atts);

    $parsed = gde_parse_drive_link($atts['enlace']);
    return gde_render_embed_html($parsed['id'], $atts['titulo'], $parsed['type']);
}
add_shortcode('insertar_drive', 'gde_embed_shortcode');


// 📘 Add admin page with usage instructions
function gde_add_admin_menu() {
    add_menu_page(
        'Insertar Google Drive',
        'Google Drive Embed',
        'manage_options',
        'insertar-google-drive',
        'gde_render_admin_page',
        'dashicons-media-document',
        90
    );
}
add_action('admin_menu', 'gde_add_admin_menu');

function gde_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>📄 Cómo usar el shortcode [insertar_drive]</h1>
        <p>Este plugin te permite insertar archivos o carpetas de Google Drive fácilmente en tus entradas o páginas usando el siguiente shortcode:</p>

        <pre><code>[insertar_drive enlace="https://drive.google.com/drive/folders/ID" titulo="Mi Carpeta"]</code></pre>

        <h2>🔹 Parámetros:</h2>
        <ul>
            <li><strong>enlace</strong>: URL de un archivo o carpeta de Google Drive.</li>
            <li><strong>titulo</strong>: Título que se mostrará encima del visor.</li>
        </ul>

        <h2>✅ Ejemplos:</h2>

        <p><strong>Insertar un archivo:</strong></p>
        <pre><code>[insertar_drive enlace="https://drive.google.com/file/d/1AbcD...XYZ/preview" titulo="Mi Documento"]</code></pre>

        <p><strong>Insertar una carpeta:</strong></p>
        <pre><code>[insertar_drive enlace="https://drive.google.com/drive/folders/1XyzQ...LMN" titulo="Carpeta Pública"]</code></pre>

        <h2>📌 Dónde usarlo:</h2>
        <p>Puedes pegar el shortcode directamente en:</p>
        <ul>
            <li>Editor de entradas o páginas de WordPress (modo visual o HTML)</li>
            <li>Un widget de texto o HTML</li>
            <li>Un bloque “Shortcode” en Elementor o Gutenberg</li>
        </ul>

        <p>💡 Recuerda que el enlace de Google Drive debe tener permisos públicos o compartidos para que sea visible.</p>
    </div>
    <?php
}
