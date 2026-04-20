<?php
if (!defined('ABSPATH')) {
    exit;
}

function gelikon_header_phones() {
    $primary   = trim((string) get_theme_mod('gelikon_header_phone', '+7 (800) 444-68-67'));
    $secondary = trim((string) get_theme_mod('gelikon_header_phone_secondary', '+7 (495) 604-48-43'));

    if (!$primary && !$secondary) {
        return;
    }

    echo '<div class="gl-header__phones">';

    foreach ([$primary, $secondary] as $phone) {
        if (!$phone) {
            continue;
        }
        $href = preg_replace('/[^\d\+]/', '', $phone);
        echo '<a href="tel:' . esc_attr($href) . '">' . esc_html($phone) . '</a>';
    }

    echo '</div>';
}

function gelikon_site_logo() {
    if (has_custom_logo()) {
        the_custom_logo();
        return;
    }

    echo '<a class="gl-logo__text" href="' . esc_url(home_url('/')) . '">' . esc_html(get_bloginfo('name')) . '</a>';
}
