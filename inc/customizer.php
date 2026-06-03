<?php
if (!defined('ABSPATH')) {
    exit;
}

function gelikon_customize_register($wp_customize) {
    $wp_customize->add_panel('gelikon_theme_options', [
        'title'       => __('Gelikon — параметры темы', 'gelikon'),
        'priority'    => 30,
        'description' => __('Основные цвета сайта и общие параметры брендинга.', 'gelikon'),
    ]);

    $wp_customize->add_section('gelikon_colors', [
        'title'       => __('Цвета сайта', 'gelikon'),
        'panel'       => 'gelikon_theme_options',
        'priority'    => 10,
        'description' => __('Настройте цвет кнопки покупки, акценты и цвет пунктов меню.', 'gelikon'),
    ]);

    $colors = [
        'gelikon_color_buy_button'       => ['Цвет кнопки «Купить»', '#12D457'],
        'gelikon_color_accent'           => ['Основной акцентный цвет', '#12D457'],
        'gelikon_color_accent_secondary' => ['Дополнительный акцентный цвет', '#1ea751'],
        'gelikon_color_menu'             => ['Цвет меню', '#9CA3AF'],
    ];

    foreach ($colors as $setting_id => $data) {
        [$label, $default] = $data;
        $wp_customize->add_setting($setting_id, [
            'default'           => $default,
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'refresh',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $setting_id, [
            'label'   => __($label, 'gelikon'),
            'section' => 'gelikon_colors',
        ]));
    }

    $wp_customize->add_section('gelikon_branding', [
        'title'    => __('Брендинг', 'gelikon'),
        'panel'    => 'gelikon_theme_options',
        'priority' => 30,
    ]);

    $branding_fields = [
        'gelikon_header_phone'           => ['Телефон в шапке', '+7 (800) 444-68-67'],
        'gelikon_header_phone_secondary' => ['Доп. телефон в шапке', '+7 (495) 604-48-43'],
        'gelikon_promo_badge'            => ['Текст бейджа товара', 'Хит'],
    ];

    foreach ($branding_fields as $setting_id => $field) {
        [$label, $default] = $field;
        $wp_customize->add_setting($setting_id, [
            'default'           => $default,
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control($setting_id, [
            'label'   => __($label, 'gelikon'),
            'section' => 'gelikon_branding',
            'type'    => 'text',
        ]);
    }
}
add_action('customize_register', 'gelikon_customize_register');