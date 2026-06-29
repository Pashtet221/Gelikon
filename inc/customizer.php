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


    $wp_customize->add_section('gelikon_catalog', [
        'title'       => __('Каталог', 'gelikon'),
        'panel'       => 'gelikon_theme_options',
        'priority'    => 35,
        'description' => __('Настройте порядок товаров, который покупатель видит без выбора сортировки.', 'gelikon'),
    ]);

    $wp_customize->add_setting('gelikon_catalog_default_orderby', [
        'default'           => 'price_desc',
        'sanitize_callback' => 'gelikon_sanitize_catalog_orderby',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gelikon_catalog_default_orderby', [
        'label'       => __('Сортировка товаров по умолчанию', 'gelikon'),
        'description' => __('Выберите порядок, который покупатель увидит без выбора сортировки. Сейчас по умолчанию включено «Сначала дороже».', 'gelikon'),
        'section'     => 'gelikon_catalog',
        'type'        => 'select',
        'choices'     => function_exists('gelikon_catalog_sorting_options') ? gelikon_catalog_sorting_options() : [
            'price_desc' => __('Сначала дороже', 'gelikon'),
            'manual_ids' => __('Ручной порядок по ID', 'gelikon'),
        ],
    ]);


    $wp_customize->add_setting('gelikon_catalog_manual_product_ids', [
        'default'           => '',
        'sanitize_callback' => 'gelikon_sanitize_catalog_manual_product_ids',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gelikon_catalog_manual_product_ids', [
        'label'       => __('Ручной порядок товаров по ID', 'gelikon'),
        'description' => __('Укажите ID товаров в нужном порядке через запятую, пробел или с новой строки. Например: 125, 98, 143. Этот список применяется, когда выше выбрана сортировка «Ручной порядок по ID». Остальные товары будут показаны после указанного списка.', 'gelikon'),
        'section'     => 'gelikon_catalog',
        'type'        => 'textarea',
    ]);


    $wp_customize->add_section('gelikon_legal_texts', [
        'title'       => __('Юридические тексты', 'gelikon'),
        'panel'       => 'gelikon_theme_options',
        'priority'    => 40,
        'description' => __('Тексты согласий выводятся в чекбоксах форм сайта. Можно вставлять HTML-ссылки на нужные документы.', 'gelikon'),
    ]);

    $wp_customize->add_setting('gelikon_personal_data_consent_text', [
        'default'           => function_exists('gelikon_personal_data_consent_default_text') ? gelikon_personal_data_consent_default_text() : '',
        'sanitize_callback' => 'gelikon_sanitize_legal_text',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gelikon_personal_data_consent_text', [
        'label'       => __('Текст чекбокса согласия', 'gelikon'),
        'description' => __('Например: Я ознакомлен(а) с <a href="/privacy-policy/">Политикой...</a> и принимаю условия <a href="/public-offer/">Публичной оферты</a>.', 'gelikon'),
        'section'     => 'gelikon_legal_texts',
        'type'        => 'textarea',
    ]);
}
add_action('customize_register', 'gelikon_customize_register');

function gelikon_sanitize_legal_text($value) {
    return wp_kses($value, [
        'a'      => [
            'href'   => true,
            'target' => true,
            'rel'    => true,
            'title'  => true,
        ],
        'br'     => [],
        'strong' => [],
        'b'      => [],
        'em'     => [],
        'i'      => [],
        'span'   => [
            'class' => true,
        ],
    ]);
}

function gelikon_sanitize_catalog_manual_product_ids($value) {
    $ids = preg_split('/[\s,;]+/', (string) $value);
    $ids = array_map('absint', is_array($ids) ? $ids : []);
    $ids = array_values(array_unique(array_filter($ids)));

    return implode(', ', $ids);
}
