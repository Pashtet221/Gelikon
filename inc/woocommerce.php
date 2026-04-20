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
