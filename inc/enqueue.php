<?php
if (!defined('ABSPATH')) {
    exit;
}

function gelikon_enqueue_assets() {
    wp_enqueue_style('gelikon-fonts', 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap', [], null);
    wp_enqueue_style('gelikon-main', GELIKON_URI . '/assets/css/main.css', ['gelikon-fonts'], GELIKON_VERSION);

    wp_enqueue_script('gelikon-navigation', GELIKON_URI . '/assets/js/main.js', [], GELIKON_VERSION, true);

    $animated_page_templates = [
        'page-home-gelikon.php',
        'page-warranty-returns.php',
        'page-delivery-payment.php',
        'page-about-company.php',
        'page-contacts.php',
    ];

    if (is_page_template($animated_page_templates)) {
        wp_enqueue_style('gelikon-home-animations', GELIKON_URI . '/assets/css/animations.css', ['gelikon-main'], GELIKON_VERSION);
        wp_enqueue_script('gelikon-home-animations', GELIKON_URI . '/assets/js/scroll-reveal.js', [], GELIKON_VERSION, true);
    }

    wp_localize_script('gelikon-navigation', 'gelikonVars', [
        'menuLabelOpen'  => __('Открыть меню', 'gelikon'),
        'menuLabelClose' => __('Закрыть меню', 'gelikon'),
    ]);
}
add_action('wp_enqueue_scripts', 'gelikon_enqueue_assets');

function gelikon_inline_css_variables() {
    $buy_button_color = sanitize_hex_color(get_theme_mod('gelikon_color_buy_button', '#12D457'));
    $buy_button_color = $buy_button_color ?: '#12D457';

    $vars = [
        '--gl-color-buy-button' => $buy_button_color,
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

    $buy_button_selectors = [
        '.gl-product-card .button:not(.gl-product-card__button--disabled)',
        '.gl-product-card a.button:not(.gl-product-card__button--disabled)',
        '.gl-product-card__button:not(.gl-product-card__button--disabled)',
        'a.gl-product-card__button:not(.gl-product-card__button--disabled)',
        '.gl-product-mobile-bar__button .single_add_to_cart_button',
        '.gl-product-mobile-bar__button button.single_add_to_cart_button.button.alt',
        '.gl-product-desktop-bar__right .single_add_to_cart_button',
        '.gl-product-desktop-bar__right button.single_add_to_cart_button.button.alt',
        '.gl-product-buybox .single_add_to_cart_button',
        '.gl-product-buybox button.single_add_to_cart_button.button.alt',
        '.single_add_to_cart_button.is-in-cart',
    ];

    $buy_button_hover_selectors = [
        '.gl-product-card .button:not(.gl-product-card__button--disabled):hover',
        '.gl-product-card a.button:not(.gl-product-card__button--disabled):hover',
        '.gl-product-card__button:not(.gl-product-card__button--disabled):hover',
        'a.gl-product-card__button:not(.gl-product-card__button--disabled):hover',
        '.gl-product-mobile-bar__button .single_add_to_cart_button:hover',
        '.gl-product-mobile-bar__button button.single_add_to_cart_button.button.alt:hover',
        '.gl-product-desktop-bar__right .single_add_to_cart_button:hover',
        '.gl-product-desktop-bar__right button.single_add_to_cart_button.button.alt:hover',
        '.gl-product-buybox .single_add_to_cart_button:hover',
        '.gl-product-buybox button.single_add_to_cart_button.button.alt:hover',
    ];

    $buy_button_css = 'background:' . $buy_button_color . ' !important;';
    $buy_button_css .= 'border-color:' . $buy_button_color . ' !important;';

    $css .= implode(',', $buy_button_selectors) . '{' . $buy_button_css . '}';
    $css .= implode(',', $buy_button_hover_selectors) . '{' . $buy_button_css . '}';

    wp_add_inline_style('gelikon-main', $css);
}
add_action('wp_enqueue_scripts', 'gelikon_inline_css_variables', 20);
