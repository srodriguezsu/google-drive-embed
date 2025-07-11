<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) return;

class Widget_Google_Drive_Embed extends Widget_Base {
    public function get_name() {
        return 'google_drive_embed';
    }

    public function get_title() {
        return 'Google Drive Embed';
    }

    public function get_icon() {
        return 'eicon-folder';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        $this->start_controls_section('content_section', [
            'label' => __('Contenido', 'plugin-name'),
        ]);

        $this->add_control('enlace', [
            'label' => __('Enlace de Google Drive', 'plugin-name'),
            'type' => Controls_Manager::TEXT,
        ]);

        $this->add_control('titulo', [
            'label' => __('Título', 'plugin-name'),
            'type' => Controls_Manager::TEXT,
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $enlace = $this->get_settings_for_display('enlace');
        $titulo = $this->get_settings_for_display('titulo');

        if (function_exists('gde_parse_drive_link')) {
            $parsed = gde_parse_drive_link($enlace);
            echo gde_render_embed_html($parsed['id'], $titulo, $parsed['type']);
        } else {
            echo '<p>Error: plugin base no disponible.</p>';
        }
    }
}
