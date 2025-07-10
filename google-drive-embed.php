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

    $iframe_src = $type === 'file'
        ? "https://drive.google.com/file/d/{$id}/preview"
        : "https://drive.google.com/embeddedfolderview?id={$id}#grid";

    $view_link = $type === 'file'
        ? "https://drive.google.com/file/d/{$id}/view"
        : "https://drive.google.com/drive/folders/{$id}";

    // Get user settings
    $show_title = get_option('gde_show_title', 'yes') === 'yes';
    $show_link = get_option('gde_show_link', 'yes') === 'yes';
    $radius = esc_attr(get_option('gde_iframe_radius', '0'));

    $output = "<div id=\"google-drive-container-{$container_id}\" class=\"google-drive-container\">";

    if ($show_title) {
        $output .= "<h2>" . esc_html($title) . "</h2>";
    }

    $output .= "<p>
        <iframe src=\"{$iframe_src}\" width=\"100%\" height=\"480\" frameborder=\"0\" allow=\"autoplay\" style=\"border-radius: {$radius};\"></iframe><br>";

    if ($show_link) {
        $output .= "<a href=\"{$view_link}\" class=\"btn btn-primary\" target=\"_blank\" rel=\"noopener noreferrer\"><br>Enlace a " . esc_html($title) . "<br></a><br>";
    }

    $output .= "</p></div>";

    return $output;
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


// 📘 Add admin page with usage instructions and settings

function gde_register_settings() {
    register_setting('gde_options_group', 'gde_show_title');
    register_setting('gde_options_group', 'gde_show_link');
    register_setting('gde_options_group', 'gde_iframe_radius');
}
add_action('admin_init', 'gde_register_settings');


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
    $show_title = get_option('gde_show_title', 'yes');
    $show_link = get_option('gde_show_link', 'yes');
    $radius = get_option('gde_iframe_radius', '0');

    ?>
    <div class="wrap">
        <h1>📄 Cómo usar el shortcode [insertar_drive]</h1>

        <form method="post" action="options.php">
            <?php settings_fields('gde_options_group'); ?>
            <h2>🎨 Opciones de Estilo</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Mostrar título</th>
                    <td>
                        <input type="checkbox" name="gde_show_title" value="yes" <?php checked($show_title, 'yes'); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mostrar enlace</th>
                    <td>
                        <input type="checkbox" name="gde_show_link" value="yes" <?php checked($show_link, 'yes'); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Borde redondeado del iframe</th>
                    <td>
                        <input type="text" name="gde_iframe_radius" value="<?php echo esc_attr($radius); ?>" placeholder="Ej: 8px, 0.5em, etc." />
                    </td>
                </tr>
            </table>
            <?php submit_button('Guardar cambios'); ?>
        </form>

        <hr>

        <h2>🧾 Instrucciones para usar el shortcode</h2>
        <p>Usa el shortcode <code>[insertar_drive]</code> en tus páginas o entradas:</p>

        <pre><code>[insertar_drive enlace="https://drive.google.com/drive/folders/ID" titulo="Mi Carpeta"]</code></pre>

        <h3>Ejemplos:</h3>
        <p><strong>Archivo:</strong></p>
        <pre><code>[insertar_drive enlace="https://drive.google.com/file/d/1AbcD...XYZ/preview" titulo="Mi Documento"]</code></pre>

        <p><strong>Carpeta:</strong></p>
        <pre><code>[insertar_drive enlace="https://drive.google.com/drive/folders/1XyzQ...LMN" titulo="Carpeta Pública"]</code></pre>

        <h3>¿Dónde usarlo?</h3>
        <ul>
            <li>Editor de WordPress (visual o código)</li>
            <li>Widgets HTML o de texto</li>
            <li>Editor Elementor (con bloque Shortcode)</li>
        </ul>

        <p>💡 Asegúrate de que los enlaces de Drive sean públicos o compartidos para que funcionen correctamente.</p>
    </div>
    <?php
}


add_action('elementor/widgets/register', function($widgets_manager) {
    require_once plugin_dir_path(__FILE__) . 'elementor-widget.php';
    $widgets_manager->register(new \Elementor\Widget_Google_Drive_Embed());
});
