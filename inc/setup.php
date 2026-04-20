<?php
if (!defined('ABSPATH')) {
    exit;
}

function gelikon_theme_setup() {
    load_theme_textdomain('gelikon', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    add_theme_support('custom-background', [
        'default-color' => 'f6f7f5',
    ]);

    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');

    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    register_nav_menus([
        'primary' => __('Основное меню', 'gelikon'),
        'footer'  => __('Меню в подвале', 'gelikon'),
    ]);

    add_image_size('gelikon-card', 900, 700, true);
    add_image_size('gelikon-hero', 1600, 900, true);
}
add_action('after_setup_theme', 'gelikon_theme_setup');

function gelikon_content_width() {
    $GLOBALS['content_width'] = apply_filters('gelikon_content_width', 1320);
}
add_action('after_setup_theme', 'gelikon_content_width', 0);

function gelikon_register_sidebars() {
    register_sidebar([
        'name'          => __('Sidebar', 'gelikon'),
        'id'            => 'sidebar-1',
        'description'   => __('Основной сайдбар.', 'gelikon'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'gelikon_register_sidebars');
