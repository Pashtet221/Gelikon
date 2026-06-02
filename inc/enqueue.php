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
        '--gl-color-buy-button' => sanitize_hex_color(get_theme_mod('gelikon_color_buy_button', '#12D457')),
        '--gl-color-accent'     => sanitize_hex_color(get_theme_mod('gelikon_color_accent', '#12D457')),
        '--gl-color-accent-2'   => sanitize_hex_color(get_theme_mod('gelikon_color_accent_secondary', '#1ea751')),
        '--gl-color-menu'       => sanitize_hex_color(get_theme_mod('gelikon_color_menu', '#9CA3AF')),
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
