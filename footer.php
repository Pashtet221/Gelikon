<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Получить ссылку на страницу по slug.
 */
if (!function_exists('gelikon_get_page_url_by_path')) {
    function gelikon_get_page_url_by_path($path) {
        $page = get_page_by_path($path);
        return $page ? get_permalink($page->ID) : '#';
    }
}

/**
 * Популярные модели для футера.
 * Ожидается ACF true/false поле у товара: show_in_footer
 */
$footer_products = [];

$footer_products_query = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => 'show_in_footer',
            'value'   => '1',
            'compare' => '='
        ]
    ]
]);

if ($footer_products_query->have_posts()) {
    foreach ($footer_products_query->posts as $product_post) {
        $wc_product = wc_get_product($product_post->ID);
        if ($wc_product) {
            $footer_products[] = $wc_product;
        }
    }
}
wp_reset_postdata();

$privacy_url = gelikon_get_page_url_by_path('privacy-policy');
$terms_url   = gelikon_get_page_url_by_path('user-agreement');
$cookies_url = gelikon_get_page_url_by_path('cookies');
?>


<footer class="gl-footer">
    <div class="gl-container">
        <div class="gl-footer__grid">

            <div class="gl-footer__col gl-footer__col--brand">
                <div class="gl-footer__brand">
                    <?php gelikon_site_logo(); ?>
                </div>

                <div class="gl-footer__phones">
                    <a class="gl-footer__phone-main" href="tel:88004446867">8 (800) 444-68-67</a>
                    <p class="gl-footer__phone-note"><?php esc_html_e('Звонок бесплатный', 'gelikon'); ?></p>
                </div>
            </div>

            <div class="gl-footer__col">
                <h3><?php esc_html_e('Категории товаров', 'gelikon'); ?></h3>

                <?php
                wp_nav_menu([
                    'theme_location' => 'footer_categories',
                    'container'      => false,
                    'menu_class'     => 'gl-footer__menu',
                    'fallback_cb'    => false,
                ]);
                ?>
            </div>

            <div class="gl-footer__col gl-footer__col--products">
                <h3><?php esc_html_e('Популярные модели', 'gelikon'); ?></h3>

               <?php if (!empty($footer_products)) : ?>
    <ul class="gl-footer__menu gl-footer__products">
        <?php foreach ($footer_products as $footer_product) : ?>
            <?php
            $product_id = $footer_product->get_id();
            $thumb_html = $footer_product->get_image('thumbnail', [
                'class'   => 'gl-footer__product-thumb-img',
                'loading' => 'lazy',
            ]);
            ?>
            <li class="gl-footer__product-item">
                <article class="gl-footer__product-card">
                    <a class="gl-footer__product-link" href="<?php echo esc_url(get_permalink($product_id)); ?>">
                        <span class="gl-footer__product-thumb">
                            <?php
                            $product_badge = '';
                            if ($footer_product->is_on_sale()) {
                                $product_badge = esc_html__('Хит', 'gelikon');
                            } elseif ($footer_product->is_featured()) {
                                $product_badge = esc_html__('Новинка', 'gelikon');
                            }
                            ?>
                            <?php if ($product_badge) : ?>
                                <span class="gl-footer__product-badge"><?php echo esc_html($product_badge); ?></span>
                            <?php endif; ?>
                        <?php
                        if ($thumb_html) {
                            echo $thumb_html;
                        } else {
                            echo wc_placeholder_img('thumbnail', ['class' => 'gl-footer__product-thumb-img']);
                        }
                        ?>
                        </span>

                        <span class="gl-footer__product-content">
                            <span class="gl-footer__product-name">
                                <?php echo esc_html($footer_product->get_name()); ?>
                            </span>
                            <?php if ($footer_product->get_price_html()) : ?>
                                <span class="gl-footer__product-price"><?php echo wp_kses_post($footer_product->get_price_html()); ?></span>
                            <?php endif; ?>
                        </span>
                    </a>

                    <a class="gl-footer__product-button" href="<?php echo esc_url(get_permalink($product_id)); ?>">
                        <?php esc_html_e('Купить', 'gelikon'); ?>
                    </a>
                </article>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
                    <div class="gl-footer__empty">
                        <?php esc_html_e('Пока нет выбранных моделей.', 'gelikon'); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="gl-footer__col">
                <h3><?php esc_html_e('Информация', 'gelikon'); ?></h3>

                <?php
                wp_nav_menu([
                    'theme_location' => 'footer_info',
                    'container'      => false,
                    'menu_class'     => 'gl-footer__menu',
                    'fallback_cb'    => false,
                ]);
                ?>
            </div>

        </div>

        <div class="gl-footer__bottom">
            <div class="gl-footer__copyright">
                © <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>
                <span class="gl-footer__slogan"><?php echo esc_html(get_bloginfo('description')); ?></span>
            </div>

            <div class="gl-footer__legal">
                <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">
	                 Политика конфиденциальности
                </a>
                <a href="<?php echo esc_url(home_url('/user-agreement/')); ?>">
                    Пользовательское соглашение
                </a>
                <a href="<?php echo esc_url(home_url('/cookies/')); ?>">
                    Cookies
                </a>
            </div>
        </div>
    </div>
</footer>

<style>
	.gl-footer {
    margin-top: 56px;
    padding: 48px 0 24px;
    background: #171d2a;
    color: #d7dce3;
}

.gl-footer a {
    color: inherit;
    text-decoration: none;
    transition: opacity .2s ease;
}

.gl-footer a:hover {
    opacity: .8;
}

.gl-footer__grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1.3fr 1fr;
    gap: 32px;
    padding-bottom: 28px;
    border-bottom: 1px solid rgba(255,255,255,.08);
}

.gl-footer__brand img,
.gl-footer__brand svg {
    max-width: 180px;
    height: auto;
}

.gl-footer__text {
    margin-top: 16px;
    font-size: 15px;
    line-height: 1.7;
    color: #aeb6c2;
}

.gl-footer__phones {
    margin-top: 16px;
}

.gl-footer__phone-main {
    display: inline-block;
    margin-bottom: 4px;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
}

.gl-footer__phone-note {
    margin: 0;
    font-size: 12px;
    line-height: 1.4;
    color: #aeb6c2;
}

.gl-footer__col h3 {
    margin: 0 0 16px;
    font-size: 18px;
    line-height: 1.3;
    font-weight: 700;
    color: #fff;
}

.gl-footer__menu {
    margin: 0;
    padding: 0;
    list-style: none;
    line-height: 1.7;
}

.gl-footer__menu li + li {
    margin-top: 12px;
}

.gl-footer__menu a {
    font-size: 15px;
    line-height: 1.5;
    color: #c7ced8;
    padding: 4px 0;
}

.gl-footer__empty {
    font-size: 14px;
    color: #97a1ae;
}

.gl-footer__bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding-top: 26px;
    padding-bottom: 4px;
}

.gl-footer__copyright {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 14px;
    color: #96a0ad;
}

.gl-footer__slogan {
    font-size: 13px;
    color: #b8c0ca;
}

.gl-footer__legal {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
}

.gl-footer__legal a {
    font-size: 14px;
    color: #b8c0ca;
}

@media (max-width: 991px) {
    .gl-footer__grid {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    .gl-footer__bottom {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 767px) {
    .gl-footer {
        padding: 36px 0 20px;
    }

    .gl-footer__grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .gl-footer__legal {
        gap: 12px;
        flex-direction: column;
    }
}
	
	
.gl-footer__products {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 12px;
}

.gl-footer__product-item {
    margin: 0;
}

.gl-footer__product-card {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 10px;
    border-radius: 14px;
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.08);
}

.gl-footer__product-link {
    display: grid;
    grid-template-columns: 72px 1fr;
    gap: 10px;
    text-decoration: none;
    color: inherit;
    min-width: 0;
}

.gl-footer__product-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 72px;
    height: 72px;
    flex: 0 0 72px;
    border-radius: 14px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    overflow: hidden;
}

.gl-footer__product-thumb-img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.gl-footer__product-badge {
    position: absolute;
    top: 6px;
    left: 6px;
    z-index: 2;
    padding: 4px 8px;
    border-radius: 999px;
    background: var(--gl-color-accent);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    line-height: 1;
}

.gl-footer__product-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
}

.gl-footer__product-name {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    line-height: 1.35;
    color: #c7ced8;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.gl-footer__product-price {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
}

.gl-footer__product-price del {
    margin-right: 6px;
    font-size: 12px;
    color: #97a1ae;
}

.gl-footer__product-price ins {
    text-decoration: none;
}

.gl-footer__product-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 8px 12px;
    border-radius: 999px;
    background: var(--gl-color-accent);
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: filter .2s ease;
}

.gl-footer__product-button:hover {
    color: #fff;
    filter: brightness(.95);
}

.gl-footer__product-link:hover .gl-footer__product-name {
    color: #ffffff;
}

@media (max-width: 767px) {
    .gl-footer__col--products {
        order: 4;
    }

    .gl-footer__products {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .gl-footer__product-link {
        grid-template-columns: 64px minmax(0, 1fr);
    }

    .gl-footer__product-thumb {
        width: 64px;
        height: 64px;
        flex-basis: 64px;
        border-radius: 12px;
    }

    .gl-footer__product-name {
        font-size: 14px;
    }

    .gl-footer__bottom {
        padding-top: 22px;
    }
}
</style>


<?php wp_footer(); ?>
</body>
</html>