<?php
if (!defined('ABSPATH')) {
    exit;
}

function gelikon_wc_wrapper_before() {
    echo '<main id="primary" class="site-main"><div class="gl-container gl-shop">';
}
add_action('woocommerce_before_main_content', 'gelikon_wc_wrapper_before', 5);

function gelikon_wc_wrapper_after() {
    echo '</div></main>';
}
add_action('woocommerce_after_main_content', 'gelikon_wc_wrapper_after', 50);

remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

function gelikon_woocommerce_enqueue_fragments() {
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 'gelikon_woocommerce_enqueue_fragments');

function gelikon_loop_columns() {
    return 3;
}
add_filter('loop_shop_columns', 'gelikon_loop_columns');

function gelikon_products_per_page() {
    return 9;
}
add_filter('loop_shop_per_page', 'gelikon_products_per_page', 20);

function gelikon_sale_flash_text($html) {
    $badge = get_theme_mod('gelikon_promo_badge', 'Хит');
    return '<span class="onsale">' . esc_html($badge) . '</span>';
}
add_filter('woocommerce_sale_flash', 'gelikon_sale_flash_text');

function gelikon_customize_checkout_fields($fields) {
    if (isset($fields['billing'])) {
        $fields['billing']['billing_first_name']['label'] = 'ФИО';
        $fields['billing']['billing_first_name']['placeholder'] = 'Иванов Иван Иванович';
        $fields['billing']['billing_first_name']['priority'] = 10;
        $fields['billing']['billing_first_name']['required'] = true;

        unset($fields['billing']['billing_last_name']);
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_country']);
        unset($fields['billing']['billing_state']);
        unset($fields['billing']['billing_postcode']);
        unset($fields['billing']['billing_city']);
        unset($fields['billing']['billing_address_1']);
        unset($fields['billing']['billing_address_2']);

        $fields['billing']['billing_phone']['label'] = 'Телефон';
        $fields['billing']['billing_phone']['required'] = true;
        $fields['billing']['billing_phone']['priority'] = 20;

        $fields['billing']['billing_email']['label'] = 'Email';
        $fields['billing']['billing_email']['required'] = false;
        $fields['billing']['billing_email']['priority'] = 30;
    }

    if (isset($fields['shipping'])) {
        unset($fields['shipping']['shipping_first_name']);
        unset($fields['shipping']['shipping_last_name']);
        unset($fields['shipping']['shipping_company']);
        unset($fields['shipping']['shipping_country']);
        unset($fields['shipping']['shipping_state']);
        unset($fields['shipping']['shipping_postcode']);

        $fields['shipping']['shipping_city']['label'] = 'Город';
        $fields['shipping']['shipping_city']['priority'] = 10;
        $fields['shipping']['shipping_address_1']['label'] = 'Улица и дом';
        $fields['shipping']['shipping_address_1']['priority'] = 20;
        $fields['shipping']['shipping_address_2']['label'] = 'Квартира';
        $fields['shipping']['shipping_address_2']['required'] = false;
        $fields['shipping']['shipping_address_2']['priority'] = 30;
    }

    if (isset($fields['order']['order_comments'])) {
        $fields['order']['order_comments']['label'] = 'Комментарий к заказу';
        $fields['order']['order_comments']['placeholder'] = 'Например, удобное время для звонка';
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'gelikon_customize_checkout_fields');

function gelikon_validate_checkout_email_requirement() {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    $payment_method = isset($_POST['payment_method']) ? wc_clean(wp_unslash($_POST['payment_method'])) : '';
    $email = isset($_POST['billing_email']) ? trim(wp_unslash($_POST['billing_email'])) : '';
    $requires_email = in_array($payment_method, ['yookassa', 'cloudpayments', 'tinkoff', 'online_payment', 'installments'], true);

    if ($requires_email && empty($email)) {
        wc_add_notice('Укажите Email для онлайн-оплаты или рассрочки.', 'error');
    }
}
add_action('woocommerce_checkout_process', 'gelikon_validate_checkout_email_requirement');

function gelikon_checkout_ui_texts($translated, $text, $domain) {
    if ($domain !== 'woocommerce') {
        return $translated;
    }

    if ($text === 'Have a coupon? %s') {
        return 'Есть промокод? %s';
    }

    if ($text === 'Click here to enter your code') {
        return 'Ввести';
    }

    return $translated;
}
add_filter('gettext', 'gelikon_checkout_ui_texts', 10, 3);

function gelikon_adjust_shipping_rate_labels($label, $method) {
    $min_free = 3000;
    $subtotal = WC()->cart ? (float) WC()->cart->get_subtotal() : 0;

    if ($subtotal >= $min_free) {
        return $method->get_label() . ': Бесплатно';
    }

    return $label;
}
add_filter('woocommerce_cart_shipping_method_full_label', 'gelikon_adjust_shipping_rate_labels', 10, 2);

function gelikon_enqueue_checkout_script() {
    if (!is_checkout()) {
        return;
    }

    wp_enqueue_script(
        'gelikon-checkout',
        GELIKON_URI . '/assets/js/checkout.js',
        ['jquery', 'wc-checkout'],
        GELIKON_VERSION,
        true
    );

    wp_localize_script('gelikon-checkout', 'gelikonCheckout', [
        'officeAddress' => 'Москва, 2-й Хорошёвский проезд, 7с14',
    ]);
}
add_action('wp_enqueue_scripts', 'gelikon_enqueue_checkout_script');
