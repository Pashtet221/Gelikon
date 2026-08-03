<?php
if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('WP_Customize_Control')) {
    /**
     * Product picker with an explicitly ordered list for the catalog default sort.
     */
    class Gelikon_Catalog_Order_Control extends WP_Customize_Control {
        public $type = 'gelikon_catalog_order';

        public function render_content() {
            $product_ids = function_exists('gelikon_get_manual_catalog_product_ids') ? gelikon_get_manual_catalog_product_ids() : [];
            $products    = get_posts([
                'post_type'              => 'product',
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'orderby'                => 'title',
                'order'                  => 'ASC',
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]);
            $product_titles = [];

            foreach ($products as $product) {
                $product_titles[$product->ID] = $product->post_title;
            }
            ?>
            <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php if ($this->description) : ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>

            <div class="gelikon-catalog-order">
                <label>
                    <span class="screen-reader-text"><?php esc_html_e('Добавить товар', 'gelikon'); ?></span>
                    <select class="gelikon-catalog-order__picker">
                        <option value=""><?php esc_html_e('Выберите товар…', 'gelikon'); ?></option>
                        <?php foreach ($products as $product) : ?>
                            <option value="<?php echo esc_attr($product->ID); ?>">
                                <?php echo esc_html($product->post_title . ' — #' . $product->ID); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="button" class="button gelikon-catalog-order__add"><?php esc_html_e('Добавить', 'gelikon'); ?></button>

                <ol class="gelikon-catalog-order__list">
                    <?php foreach ($product_ids as $product_id) : ?>
                        <?php $title = isset($product_titles[$product_id]) ? $product_titles[$product_id] : sprintf(__('Товар #%d', 'gelikon'), $product_id); ?>
                        <li data-product-id="<?php echo esc_attr($product_id); ?>">
                            <span class="gelikon-catalog-order__name"><?php echo esc_html($title . ' — #' . $product_id); ?></span>
                            <span class="gelikon-catalog-order__actions">
                                <button type="button" class="button-link" data-move="up" aria-label="<?php esc_attr_e('Поднять товар', 'gelikon'); ?>">↑</button>
                                <button type="button" class="button-link" data-move="down" aria-label="<?php esc_attr_e('Опустить товар', 'gelikon'); ?>">↓</button>
                                <button type="button" class="button-link-delete" data-remove aria-label="<?php esc_attr_e('Удалить товар из порядка', 'gelikon'); ?>">×</button>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <textarea class="gelikon-catalog-order__value" <?php $this->link(); ?>><?php echo esc_textarea($this->value()); ?></textarea>
                <p class="gelikon-catalog-order__empty"><?php esc_html_e('Список пока пуст — товары будут отсортированы по позиции WooCommerce и названию.', 'gelikon'); ?></p>
            </div>
            <?php
        }
    }
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
        'default'           => 'menu_order',
        'sanitize_callback' => 'gelikon_sanitize_catalog_orderby',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control('gelikon_catalog_default_orderby', [
        'label'       => __('Сортировка товаров по умолчанию', 'gelikon'),
        'description' => __('Выберите порядок товаров, который будет применяться в каталоге без выбора сортировки. Вариант «По умолчанию» показывает товары в ручном порядке из списка ID ниже, затем по позиции WooCommerce и названию.', 'gelikon'),
        'section'     => 'gelikon_catalog',
        'type'        => 'select',
        'choices'     => function_exists('gelikon_catalog_sorting_options') ? gelikon_catalog_sorting_options() : [
            'menu_order' => __('По умолчанию', 'gelikon'),
            'price_desc' => __('Сначала дороже', 'gelikon'),
        ],
    ]);


    $wp_customize->add_setting('gelikon_catalog_manual_product_ids', [
        'default'           => '',
        'sanitize_callback' => 'gelikon_sanitize_manual_catalog_product_ids',
        'transport'         => 'refresh',
    ]);

    $wp_customize->add_control(new Gelikon_Catalog_Order_Control($wp_customize, 'gelikon_catalog_manual_product_ids', [
        'label'       => __('Ручной порядок товаров', 'gelikon'),
        'description' => __('Добавьте товары и расположите их стрелками в нужном порядке. Они будут показаны первыми при сортировке «По умолчанию», остальные — после них по позиции WooCommerce и названию.', 'gelikon'),
        'section'     => 'gelikon_catalog',
    ]));


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

function gelikon_customize_catalog_order_assets() {
    wp_enqueue_style(
        'gelikon-customizer-catalog-order',
        GELIKON_URI . '/assets/css/customizer-catalog-order.css',
        [],
        GELIKON_VERSION
    );
    wp_enqueue_script(
        'gelikon-customizer-catalog-order',
        GELIKON_URI . '/assets/js/customizer-catalog-order.js',
        ['jquery', 'customize-controls'],
        GELIKON_VERSION,
        true
    );
}
add_action('customize_controls_enqueue_scripts', 'gelikon_customize_catalog_order_assets');

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
