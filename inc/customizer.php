<?php
if (!defined('ABSPATH')) {
    exit;
}

function gelikon_customize_register($wp_customize) {
    $wp_customize->add_panel('gelikon_theme_options', [
        'title'       => __('Gelikon — параметры темы', 'gelikon'),
        'priority'    => 30,
        'description' => __('Цвета, размеры, радиусы и общие параметры интерфейса.', 'gelikon'),
    ]);

    $wp_customize->add_section('gelikon_colors', [
        'title'    => __('Цветовая система', 'gelikon'),
        'panel'    => 'gelikon_theme_options',
        'priority' => 10,
    ]);

    $colors = [
        'gelikon_color_bg'          => ['Фон сайта', '#f5f6f4'],
        'gelikon_color_surface'     => ['Белые карточки', '#ffffff'],
        'gelikon_color_surface_alt' => ['Светлый дополнительный фон', '#eef2ef'],
        'gelikon_color_text'        => ['Основной текст', '#1d232c'],
        'gelikon_color_muted'       => ['Вторичный текст', '#69707d'],
        'gelikon_color_line'        => ['Линии и бордеры', '#dde4dd'],
        'gelikon_color_accent'      => ['Основной акцент', '#2cbc63'],
        'gelikon_color_accent_2'    => ['Темный акцент', '#1ea751'],
        'gelikon_color_dark'        => ['Темный фон / заголовки', '#10161f'],
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

    $wp_customize->add_section('gelikon_layout', [
        'title'    => __('Размеры и геометрия', 'gelikon'),
        'panel'    => 'gelikon_theme_options',
        'priority' => 20,
    ]);

    $numbers = [
        'gelikon_container_width' => ['Ширина контейнера', 1320, 1080, 1600],
        'gelikon_radius'          => ['Большой радиус карточек', 24, 0, 60],
        'gelikon_radius_sm'       => ['Малый радиус', 14, 0, 40],
        'gelikon_button_radius'   => ['Радиус кнопок', 999, 0, 999],
    ];

    foreach ($numbers as $setting_id => $data) {
        [$label, $default, $min, $max] = $data;
        $wp_customize->add_setting($setting_id, [
            'default'           => $default,
            'sanitize_callback' => 'absint',
        ]);

        $wp_customize->add_control($setting_id, [
            'label'       => __($label, 'gelikon'),
            'section'     => 'gelikon_layout',
            'type'        => 'number',
            'input_attrs' => [
                'min'  => $min,
                'max'  => $max,
                'step' => 1,
            ],
        ]);
    }

    $wp_customize->add_setting('gelikon_shadow', [
        'default'           => '0 12px 32px rgba(22, 34, 51, 0.08)',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('gelikon_shadow', [
        'label'       => __('Тень карточек (CSS value)', 'gelikon'),
        'section'     => 'gelikon_layout',
        'type'        => 'text',
        'description' => __('Например: 0 12px 32px rgba(22,34,51,.08)', 'gelikon'),
    ]);

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
