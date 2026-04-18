<?php
if (!defined('ABSPATH')) {
    exit;
}

function gelikon_enqueue_assets() {
    wp_enqueue_style('gelikon-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap', [], null);
    wp_enqueue_style('gelikon-main', GELIKON_URI . '/assets/css/main.css', ['gelikon-fonts'], GELIKON_VERSION);

    wp_enqueue_script('gelikon-navigation', GELIKON_URI . '/assets/js/main.js', [], GELIKON_VERSION, true);

    wp_localize_script('gelikon-navigation', 'gelikonVars', [
        'menuLabelOpen'  => __('Открыть меню', 'gelikon'),
        'menuLabelClose' => __('Закрыть меню', 'gelikon'),
    ]);
}
add_action('wp_enqueue_scripts', 'gelikon_enqueue_assets');

function gelikon_inline_css_variables() {
    $vars = [
        '--gl-container'         => absint(get_theme_mod('gelikon_container_width', 1320)) . 'px',
        '--gl-radius'            => absint(get_theme_mod('gelikon_radius', 24)) . 'px',
        '--gl-radius-sm'         => absint(get_theme_mod('gelikon_radius_sm', 14)) . 'px',
        '--gl-btn-radius'        => absint(get_theme_mod('gelikon_button_radius', 999)) . 'px',
        '--gl-shadow'            => sanitize_text_field(get_theme_mod('gelikon_shadow', '0 12px 32px rgba(22, 34, 51, 0.08)')),
        '--gl-color-bg'          => sanitize_hex_color(get_theme_mod('gelikon_color_bg', '#f5f6f4')),
        '--gl-color-surface'     => sanitize_hex_color(get_theme_mod('gelikon_color_surface', '#ffffff')),
        '--gl-color-surface-alt' => sanitize_hex_color(get_theme_mod('gelikon_color_surface_alt', '#eef2ef')),
        '--gl-color-text'        => sanitize_hex_color(get_theme_mod('gelikon_color_text', '#1d232c')),
        '--gl-color-muted'       => sanitize_hex_color(get_theme_mod('gelikon_color_muted', '#69707d')),
        '--gl-color-line'        => sanitize_hex_color(get_theme_mod('gelikon_color_line', '#dde4dd')),
        '--gl-color-accent'      => sanitize_hex_color(get_theme_mod('gelikon_color_accent', '#2cbc63')),
        '--gl-color-accent-2'    => sanitize_hex_color(get_theme_mod('gelikon_color_accent_2', '#1ea751')),
        '--gl-color-dark'        => sanitize_hex_color(get_theme_mod('gelikon_color_dark', '#10161f')),
    ];

    $css = ':root {';
    foreach ($vars as $name => $value) {
        if (!empty($value)) {
            $css .= $name . ':' . $value . ';';
        }
    }
    $css .= '}';

    wp_add_inline_style('gelikon-main', $css);
}
add_action('wp_enqueue_scripts', 'gelikon_inline_css_variables', 20);
