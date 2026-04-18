<?php
/**
 * Template Name: Главная Gelikon
 * Template Post Type: page
 */

get_header();


if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('gelikon_home_get_field')) {
    function gelikon_home_get_field($field_name, $default = '', $post_id = 0) {
        if (function_exists('get_field')) {
            $value = get_field($field_name, $post_id);
            if ($value !== null && $value !== false && $value !== '') {
                return $value;
            }
        }

        return $default;
    }
}

if (!function_exists('gelikon_home_image_url')) {
    function gelikon_home_image_url($image_field) {
        if (empty($image_field)) {
            return '';
        }

        if (is_array($image_field)) {
            if (!empty($image_field['url'])) {
                return $image_field['url'];
            }

            if (!empty($image_field['sizes']['large'])) {
                return $image_field['sizes']['large'];
            }

            if (!empty($image_field['ID'])) {
                $image = wp_get_attachment_image_src((int) $image_field['ID'], 'large');
                return !empty($image[0]) ? $image[0] : '';
            }
        }

        if (is_numeric($image_field)) {
            $image = wp_get_attachment_image_src((int) $image_field, 'large');
            return !empty($image[0]) ? $image[0] : '';
        }

        if (is_string($image_field)) {
            return $image_field;
        }

        return '';
    }
}

if (!function_exists('gelikon_home_link_url')) {
    function gelikon_home_link_url($link_field, $default = '') {
        if (empty($link_field)) {
            return $default;
        }

        if (is_array($link_field) && !empty($link_field['url'])) {
            return $link_field['url'];
        }

        if (is_string($link_field)) {
            return $link_field;
        }

        return $default;
    }
}

$page_id = (int) get_queried_object_id();

$default_banners = [
    1 => [
        'title'       => 'Умные-часы для контроля здоровья',
        'text'        => 'Контроль давления, пульса и ЭКГ в режиме реального времени.',
        'button_text' => 'Смотреть',
        'link'        => home_url('/shop/'),
        'image_url'   => '',
    ],
    2 => [
        'title'       => 'Смарт-кольца',
        'text'        => 'Миниатюрный помощник на каждый день.',
        'button_text' => 'Смотреть',
        'link'        => home_url('/shop/'),
        'image_url'   => '',
    ],
    3 => [
        'title'       => 'Смарт-очки',
        'text'        => '',
        'button_text' => 'Смотреть',
        'link'        => home_url('/shop/'),
        'image_url'   => '',
    ],
    4 => [
        'title'       => '4G Wi‑Fi Роутеры',
        'text'        => '',
        'button_text' => 'Смотреть',
        'link'        => home_url('/shop/'),
        'image_url'   => '',
    ],
];

$banners = [];
for ($i = 1; $i <= 4; $i++) {
    $banners[$i] = [
        'title'       => gelikon_home_get_field("home_banner_{$i}_title", $default_banners[$i]['title'], $page_id),
        'text'        => gelikon_home_get_field("home_banner_{$i}_text", $default_banners[$i]['text'], $page_id),
        'button_text' => gelikon_home_get_field("home_banner_{$i}_button_text", $default_banners[$i]['button_text'], $page_id),
        'link'        => gelikon_home_link_url(gelikon_home_get_field("home_banner_{$i}_link", $default_banners[$i]['link'], $page_id), $default_banners[$i]['link']),
        'image_url'   => gelikon_home_image_url(gelikon_home_get_field("home_banner_{$i}_image", '', $page_id)),
    ];
}

$products_title = gelikon_home_get_field('home_products_title', 'Популярные товары', $page_id);
$trust_title    = gelikon_home_get_field('home_trust_title', 'Почему выбирают Gelikon', $page_id);
$blog_title     = gelikon_home_get_field('home_blog_title', 'Блог', $page_id);
$blog_link_text = gelikon_home_get_field('home_blog_link_text', 'Смотреть все статьи', $page_id);
$reviews_title  = gelikon_home_get_field('home_reviews_title', 'Отзывы', $page_id);

$trust_items = gelikon_home_get_field('home_trust_items', [], $page_id);
if (empty($trust_items) || !is_array($trust_items)) {
    $trust_items = [
        [
            'title' => 'Официальная гарантия',
            'text'  => 'Прозрачные условия покупки, сервисная поддержка и понятная документация.',
        ],
        [
            'title' => 'Быстрая доставка',
            'text'  => 'Отправка по России и СНГ, аккуратная упаковка и трекинг заказа.',
        ],
        [
            'title' => 'Проверенные товары',
            'text'  => 'Подбираем устройства под реальные сценарии: здоровье, связь, дом и работа.',
        ],
    ];
}

$review_items = gelikon_home_get_field('home_reviews', [], $page_id);
if (empty($review_items) || !is_array($review_items)) {
    $review_items = [
        [
            'name' => 'Андрей',
            'text' => 'Заказ пришёл быстро, упаковка аккуратная, устройство сразу готово к работе.',
        ],
        [
            'name' => 'Марина',
            'text' => 'Понравился стиль сайта и подробные карточки товара. Всё понятно без лишней воды.',
        ],
        [
            'name' => 'Игорь',
            'text' => 'Выбирали роутер для офиса — помогли подобрать модель под задачу и бюджет.',
        ],
    ];
}

$selected_products = gelikon_home_get_field('home_popular_products', [], $page_id);
$products = [];

if (function_exists('wc_get_products')) {
    if (!empty($selected_products) && is_array($selected_products)) {
        $product_ids = [];

        foreach ($selected_products as $selected_product) {
            if (is_object($selected_product) && isset($selected_product->ID)) {
                $product_ids[] = (int) $selected_product->ID;
            } elseif (is_numeric($selected_product)) {
                $product_ids[] = (int) $selected_product;
            }
        }

        if (!empty($product_ids)) {
            $products = wc_get_products([
                'status'  => 'publish',
                'limit'   => count($product_ids),
                'include' => $product_ids,
                'orderby' => 'post__in',
            ]);
        }
    }

    if (empty($products)) {
        $products = wc_get_products([
            'status'   => 'publish',
            'limit'    => 4,
            'featured' => true,
        ]);
    }

    if (empty($products)) {
        $products = wc_get_products([
            'status' => 'publish',
            'limit'  => 4,
        ]);
    }
}

$blog_query = new WP_Query([
    'post_type'           => 'post',
    'posts_per_page'      => 3,
    'ignore_sticky_posts' => true,
]);
?>
<main id="primary" class="site-main gl-homepage">
    <div class="gl-container">
      
		
		
		
<?php
$home_banners = function_exists('get_field') ? get_field('home_banners', $page_id) : [];
?>

<?php if (!empty($home_banners) && is_array($home_banners)) : ?>
<section class="gl-home-banners" aria-label="<?php esc_attr_e('Направления каталога', 'gelikon'); ?>">
	<?php foreach ($home_banners as $banner) :
		$title       = !empty($banner['title']) ? $banner['title'] : '';
		$text        = !empty($banner['description']) ? $banner['description'] : '';
		$button_text = !empty($banner['button_text']) ? $banner['button_text'] : '';
		$link        = !empty($banner['link']['url']) ? $banner['link']['url'] : '#';
		$link_target = !empty($banner['link']['target']) ? $banner['link']['target'] : '_self';
		$image_url   = '';

		if (!empty($banner['image'])) {
			$image_url = gelikon_home_image_url($banner['image']);
		}

		$size = !empty($banner['size']) ? $banner['size'] : 'small';
		$size_class = $size === 'large' ? 'gl-home-banner--large' : 'gl-home-banner--small';
		?>
		<a class="gl-card gl-home-banner <?php echo esc_attr($size_class); ?>" href="<?php echo esc_url($link); ?>" target="<?php echo esc_attr($link_target); ?>">
			<div class="gl-home-banner__content <?php echo $size === 'large' ? 'gl-home-banner__content--large' : 'gl-home-banner__content--small'; ?>">
				<?php if ($size === 'large') : ?>
					<h2><?php echo esc_html($title); ?></h2>
				<?php else : ?>
					<h2><?php echo esc_html($title); ?></h2>
				<?php endif; ?>

				<?php if (!empty($text)) : ?>
					<p><?php echo esc_html($text); ?></p>
				<?php endif; ?>

				<?php if (!empty($button_text) && $size === 'large') : ?>
					<span class="gl-home-banner__action"><?php echo esc_html($button_text); ?></span>
				<?php endif; ?>
			</div>

			<div class="gl-home-banner__media">
				<?php if ($image_url) : ?>
					<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
				<?php else : ?>
					<div class="gl-home-banner__placeholder"></div>
				<?php endif; ?>
			</div>
		</a>
	<?php endforeach; ?>
</section>
<?php endif; ?>

<style>
	.gl-home-banners {
	display: grid;
	grid-template-columns: repeat(12, minmax(0, 1fr));
	gap: 24px;
}

.gl-home-banner {
	display: grid;
	align-items: center;
	min-height: 280px;
	padding: 34px;
	border-radius: 28px;
	background: #f3f5f7;
	text-decoration: none;
	color: inherit;
	overflow: hidden;
}

.gl-home-banner--large {
	grid-column: span 8;
	grid-template-columns: minmax(280px, 1fr) minmax(260px, 420px);
	min-height: 300px;
}

.gl-home-banner--small {
	grid-column: span 4;
	grid-template-columns: 1fr;
	grid-template-rows: auto 1fr;
	text-align: center;
	min-height: 300px;
}

.gl-home-banner__content {
	position: relative;
	z-index: 2;
	text-align: left;
}

.gl-home-banner__content--large h2 {
	margin: 0 0 16px;
	font-size: clamp(34px, 3.2vw, 64px);
	line-height: .95;
	letter-spacing: -0.04em;
	color: #171d2a;
}

.gl-home-banner__content--small h3 {
	margin: 0 0 12px;
	font-size: clamp(24px, 2vw, 36px);
	line-height: 1.05;
	letter-spacing: -0.03em;
	color: #171d2a;
}

.gl-home-banner__content p {
	margin: 0;
	font-size: 16px;
	line-height: 1.45;
	color: #2e3440;
	max-width: 360px;
}

.gl-home-banner--small .gl-home-banner__content p {
	max-width: 100%;
	margin: 0 auto;
}

.gl-home-banner__action {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 48px;
	padding: 0 22px;
	margin-top: 24px;
	border-radius: 999px;
	background: #fff;
	color: #171d2a;
	font-size: 15px;
	font-weight: 600;
	line-height: 1;
	box-shadow: 0 4px 14px rgba(0,0,0,.05);
}

.gl-home-banner__media {
	display: flex;
	align-items: center;
	justify-content: center;
	height: 100%;
}

.gl-home-banner__media img {
	display: block;
	width: 100%;
	height: 100%;
	max-height: 360px;
	object-fit: contain;
	object-position: center;
}

.gl-home-banner--small .gl-home-banner__media img {
	max-height: 200px;
}

.gl-home-banner__placeholder {
	width: 100%;
	height: 220px;
	border-radius: 20px;
	background: linear-gradient(180deg, #eef2f1 0%, #e7ecea 100%);
}

@media (max-width: 1199px) {
	.gl-home-banner--large {
		grid-column: span 12;
	}

	.gl-home-banner--small {
		grid-column: span 6;
	}
}

@media (max-width: 767px) {
	.gl-home-banners {
		grid-template-columns: 1fr;
		gap: 16px;
	}

	.gl-home-banner,
	.gl-home-banner--large,
	.gl-home-banner--small {
		grid-column: auto;
		grid-template-columns: 1fr;
		min-height: auto;
		padding: 24px;
	}

	.gl-home-banner__content--large h2 {
		font-size: 34px;
	}

	.gl-home-banner__content--small h3 {
		font-size: 28px;
	}

	.gl-home-banner__media {
		margin-top: 18px;
	}

	.gl-home-banner__media img {
		max-height: 240px;
	}
}
</style>
		
		
		
		
		
		
		
		
<!-- 		Слайдер товаров -->
<?php if (!empty($products)) : ?>
	<section class="gl-home-products gl-home-section">
		<div class="gl-section-head gl-section-head--between">
			<h2><?php echo esc_html($products_title); ?></h2>
			<a class="gl-section-link" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>">
				<?php esc_html_e('В каталог', 'gelikon'); ?>
			</a>
		</div>

		<div class="gl-home-products-slider swiper">
			<div class="swiper-wrapper">
				<?php foreach ($products as $slider_product) :
					$post_object = get_post($slider_product->get_id());
					if (!$post_object) {
						continue;
					}

					$GLOBALS['post'] = $post_object;
					setup_postdata($post_object);
					$GLOBALS['product'] = wc_get_product($post_object->ID);
					?>
					<div class="swiper-slide">
						<ul class="gl-home-products-slider__list">
							<?php wc_get_template_part('content', 'product'); ?>
						</ul>
					</div>
				<?php endforeach; ?>
				<?php wp_reset_postdata(); ?>
			</div>

			<div class="gl-home-products-slider__footer">
				<div class="gl-home-products-slider__pagination"></div>

				<div class="gl-home-products-slider__nav">
					<button type="button" class="gl-home-products-slider__prev" aria-label="Назад">‹</button>
					<button type="button" class="gl-home-products-slider__next" aria-label="Вперёд">›</button>
				</div>
			</div>
		</div>
	</section>
<?php endif; ?>
		
<style>
	/* =========================
   Home products slider
========================= */

.gl-home-products-slider {
	position: relative;
	overflow: hidden;
}

.gl-home-products-slider .swiper-slide {
	height: auto;
}

.gl-home-products-slider__list {
	margin: 0;
	padding: 0;
	list-style: none;
}

.gl-home-products-slider__footer {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	margin-top: 22px;
}

.gl-home-products-slider__nav {
	display: flex;
	align-items: center;
	gap: 10px;
}

.gl-home-products-slider__prev,
.gl-home-products-slider__next {
	width: 44px;
	height: 44px;
	border: 1px solid #dbe2dd;
	border-radius: 999px;
	background: #fff;
	cursor: pointer;
	font-size: 22px;
	line-height: 1;
	transition: .2s ease;
}

.gl-home-products-slider__prev:hover,
.gl-home-products-slider__next:hover {
	background: var(--gl-color-accent);
	border-color: var(--gl-color-accent);
	color: #fff;
}

.gl-home-products-slider__pagination {
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 8px;
}

.gl-home-products-slider__pagination .swiper-pagination-bullet {
	width: 10px;
	height: 10px;
	margin: 0 !important;
	border-radius: 999px;
	background: #cfd6d1;
	opacity: 1;
	transition: width .2s ease, background-color .2s ease;
}

.gl-home-products-slider__pagination .swiper-pagination-bullet-active {
	width: 26px;
	background: var(--gl-color-accent);
}

@media (max-width: 767px) {
	.gl-home-products-slider__footer {
		flex-direction: column;
		align-items: center;
	}

	.gl-home-products-slider__nav {
		display: none;
	}
}
</style>
		
		
		
		
		
<!--     Секция Доверия -->
<section class="gl-home-trust gl-home-section gl-card">
    <div class="gl-section-head">
        <h2><?php echo esc_html($trust_title); ?></h2>
    </div>

    <div class="gl-trust-grid">
        <?php foreach ($trust_items as $trust_item) :
            $trust_item_title = is_array($trust_item) && !empty($trust_item['title']) ? $trust_item['title'] : '';
            $trust_item_text  = is_array($trust_item) && !empty($trust_item['text']) ? $trust_item['text'] : '';
            if (!$trust_item_title && !$trust_item_text) {
                continue;
            }
            ?>
            <article class="gl-trust-item">
                <div class="gl-trust-item__icon" aria-hidden="true"></div>
                <?php if ($trust_item_title) : ?>
                    <h3><?php echo esc_html($trust_item_title); ?></h3>
                <?php endif; ?>
                <?php if ($trust_item_text) : ?>
                    <p><?php echo esc_html($trust_item_text); ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="gl-trust-payments">
        <div class="gl-trust-payments__head">
            <h3 class="gl-trust-payments__title"><?php esc_html_e('Способы оплаты', 'gelikon'); ?></h3>
            <p class="gl-trust-payments__text"><?php esc_html_e('Поддерживаем популярные способы онлайн-оплаты', 'gelikon'); ?></p>
        </div>

        <div class="gl-trust-payments__list" aria-label="<?php esc_attr_e('Поддерживаемые способы оплаты', 'gelikon'); ?>">
            <div class="gl-trust-payments__logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/mir.png'); ?>" alt="МИР">
            </div>
            <div class="gl-trust-payments__logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/visa.jpg'); ?>" alt="Visa">
            </div>
            <div class="gl-trust-payments__logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/mastercard.png'); ?>" alt="Mastercard">
            </div>
            <div class="gl-trust-payments__logo">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/payments/sbp.png'); ?>" alt="СБП">
            </div>
        </div>
    </div>
</section>
		
<style>
	.gl-trust-payments {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #e6ebe7;
}

.gl-trust-payments__head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 18px;
}

.gl-trust-payments__title {
    margin: 0;
    font-size: 18px;
    line-height: 1.2;
    font-weight: 700;
    color: #171d2a;
}

.gl-trust-payments__text {
    margin: 0;
    font-size: 14px;
    line-height: 1.45;
    color: #6f7782;
}

.gl-trust-payments__list {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.gl-trust-payments__logo {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 72px;
    padding: 14px 18px;
    border-radius: 18px;
    background: #fff;
    border: 1px solid #e4e9e5;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.03);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.gl-trust-payments__logo:hover {
    transform: translateY(-2px);
    border-color: #d8e1db;
    box-shadow: 0 10px 22px rgba(0, 0, 0, 0.05);
}

.gl-trust-payments__logo img {
    display: block;
    width: auto;
    height: auto;
    min-height: 50px;
    max-height: 50px;
    object-fit: contain;
}

@media (max-width: 991px) {
    .gl-trust-payments__head {
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .gl-trust-payments__list {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 767px) {
    .gl-trust-payments {
        margin-top: 24px;
        padding-top: 18px;
    }

    .gl-trust-payments__title {
        font-size: 16px;
    }

    .gl-trust-payments__text {
        font-size: 13px;
    }

    .gl-trust-payments__list {
        gap: 10px;
    }

    .gl-trust-payments__logo {
        min-height: 62px;
        padding: 12px 14px;
        border-radius: 14px;
    }

    .gl-trust-payments__logo img {
        max-width: 76px;
        max-height: 24px;
    }
}
</style>
		
		
		
		
<!-- 		Блог -->
        <?php if ($blog_query->have_posts()) : ?>
            <section class="gl-home-blog gl-home-section">
                <div class="gl-section-head gl-section-head--between">
                    <h2><?php echo esc_html($blog_title); ?></h2>
                    <a class="gl-section-link" href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: get_post_type_archive_link('post')); ?>"><?php echo esc_html($blog_link_text); ?></a>
                </div>
                <div class="gl-posts-grid">
                    <?php while ($blog_query->have_posts()) : $blog_query->the_post(); ?>
                        <article <?php post_class('gl-card gl-post-card'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <a class="gl-post-card__thumb" href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('gelikon-card'); ?>
                                </a>
                            <?php endif; ?>
                            <div class="gl-post-card__content">
                                <div class="gl-post-card__meta"><?php echo esc_html(get_the_date()); ?></div>
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?></p>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </section>
        <?php endif; ?>

        
		
		
		
		<?php
$reviews_query = new WP_Query([
	'post_type'      => 'gelikon_review',
	'post_status'    => 'publish',
	'posts_per_page' => 10,
	'orderby'        => 'date',
	'order'          => 'DESC',
]);
?>

<?php if ($reviews_query->have_posts()) : ?>
	<section class="gl-home-reviews gl-home-section">
		<div class="gl-section-head gl-section-head--between">
			<h2><?php echo esc_html($reviews_title); ?></h2>

			<div class="gl-reviews-slider__nav">
				<button type="button" class="gl-reviews-slider__prev" aria-label="Назад">‹</button>
				<button type="button" class="gl-reviews-slider__next" aria-label="Вперёд">›</button>
			</div>
		</div>

		<div class="gl-reviews-slider swiper">
			<div class="swiper-wrapper">
				<?php while ($reviews_query->have_posts()) : $reviews_query->the_post(); ?>
					<?php
					$review_text    = function_exists('get_field') ? get_field('review_text') : '';
					$review_name    = function_exists('get_field') ? get_field('review_name') : '';
					$review_product = function_exists('get_field') ? get_field('review_product') : '';
					$review_photo   = function_exists('get_field') ? get_field('review_photo') : '';

					$product_title = '';
					if (is_object($review_product) && !empty($review_product->post_title)) {
						$product_title = $review_product->post_title;
					} elseif (is_numeric($review_product)) {
						$product_title = get_the_title((int) $review_product);
					}

					$photo_url = '';
					if (is_array($review_photo) && !empty($review_photo['sizes']['thumbnail'])) {
						$photo_url = $review_photo['sizes']['thumbnail'];
					} elseif (is_array($review_photo) && !empty($review_photo['url'])) {
						$photo_url = $review_photo['url'];
					} elseif (is_numeric($review_photo)) {
						$img = wp_get_attachment_image_src((int) $review_photo, 'thumbnail');
						$photo_url = !empty($img[0]) ? $img[0] : '';
					}
					?>
					<div class="swiper-slide">
						<article class="gl-card gl-review-card">
							<div class="gl-review-card__rating" aria-label="5 stars">★★★★★</div>

							<?php if ($review_text) : ?>
								<div class="gl-review-card__text">
									<?php echo wp_kses_post($review_text); ?>
								</div>
							<?php endif; ?>

							<div class="gl-review-card__footer">
								<?php if ($photo_url) : ?>
									<div class="gl-review-card__photo">
										<img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($review_name ?: 'Отзыв'); ?>">
									</div>
								<?php endif; ?>

								<div class="gl-review-card__meta">
									<?php if ($review_name) : ?>
										<div class="gl-review-card__author"><?php echo esc_html($review_name); ?></div>
									<?php endif; ?>

									<?php if ($product_title) : ?>
										<div class="gl-review-card__product"><?php echo esc_html($product_title); ?></div>
									<?php endif; ?>
								</div>
							</div>
						</article>
					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<div class="gl-reviews-slider__pagination"></div>
		</div>
	</section>
<?php endif; ?>
		
		
<style>
	.gl-home-reviews {
	position: relative;
}

.gl-reviews-slider {
	overflow: hidden;
}

.gl-reviews-slider .swiper-slide {
	height: auto;
}

.gl-review-card {
	height: 100%;
	padding: 24px;
	border-radius: 24px;
	background: #fff;
	border: 1px solid #e7ebe8;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	min-height: 280px;
}

.gl-review-card__rating {
	margin-bottom: 16px;
	font-size: 18px;
	letter-spacing: 2px;
	color: var(--gl-color-accent);
}

.gl-review-card__text {
	font-size: 16px;
	line-height: 1.65;
	color: var(--gl-text, #20242a);
	margin-bottom: 22px;
}

.gl-review-card__footer {
	display: flex;
	align-items: center;
	gap: 14px;
	margin-top: auto;
}

.gl-review-card__photo {
	width: 56px;
	height: 56px;
	border-radius: 50%;
	overflow: hidden;
	flex: 0 0 56px;
	background: #f3f5f7;
}

.gl-review-card__photo img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.gl-review-card__meta {
	min-width: 0;
}

.gl-review-card__author {
	font-size: 16px;
	font-weight: 600;
	line-height: 1.3;
	color: #171d2a;
}

.gl-review-card__product {
	margin-top: 4px;
	font-size: 14px;
	line-height: 1.35;
	color: #7c838c;
}

.gl-reviews-slider__nav {
	display: flex;
	align-items: center;
	gap: 12px;
}

.gl-reviews-slider__prev,
.gl-reviews-slider__next {
	width: 44px;
	height: 44px;
	border: 1px solid #dbe2dd;
	border-radius: 999px;
	background: #fff;
	cursor: pointer;
	font-size: 22px;
	line-height: 1;
	transition: .2s ease;
}

.gl-reviews-slider__prev:hover,
.gl-reviews-slider__next:hover {
	background: var(--gl-color-accent);
	border-color: var(--gl-color-accent);
	color: #fff;
}

.gl-reviews-slider__pagination {
	margin-top: 18px;
	text-align: center;
}

@media (max-width: 767px) {
	.gl-review-card {
		padding: 20px;
		min-height: 240px;
		border-radius: 20px;
	}

	.gl-review-card__text {
		font-size: 15px;
		line-height: 1.6;
	}

	.gl-reviews-slider__nav {
		display: none;
	}
}
	

.gl-reviews-slider__pagination {
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 10px;
	margin-top: 22px;
}

.gl-reviews-slider__pagination .swiper-pagination-bullet {
	width: 10px;
	height: 10px;
	margin: 0 !important;
	border-radius: 999px;
	background: var(--gl-border, #cfd6d1);
	opacity: 1;
	transition: width .2s ease, background-color .2s ease, transform .2s ease;
}

.gl-reviews-slider__pagination .swiper-pagination-bullet-active {
	width: 28px;
	background: var(--gl-accent, var(--gl-color-accent));
}
</style>
		
		
		
		
		
		
		
		
<!-- Префутер		 -->
<section class="gl-prefooter">
    <div class="gl-container">
        <div class="gl-card gl-prefooter__inner">
            <div class="gl-prefooter__content">
                <h2 class="gl-prefooter__title">
                    <?php esc_html_e('Попробуйте инновационные продукты от российского бренда', 'gelikon'); ?>
                </h2>

                <a class="gl-btn gl-prefooter__button" href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>">
                    <?php esc_html_e('Перейти в каталог', 'gelikon'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
	.gl-prefooter {
    margin-top: 56px;
    margin-bottom: 0;
}

.gl-prefooter__inner {
    padding: 40px 48px;
    border-radius: 32px;
    background:
        radial-gradient(circle at right top, rgba(34, 197, 94, 0.12), transparent 35%),
        linear-gradient(135deg, #171d2a 0%, #1f2937 100%);
    overflow: hidden;
}

.gl-prefooter__content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}

.gl-prefooter__title {
    margin: 0;
    max-width: 760px;
    font-size: clamp(30px, 3vw, 52px);
    line-height: 1.02;
    letter-spacing: -0.04em;
    color: #fff;
}

.gl-prefooter__button,
a.gl-prefooter__button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 58px;
    padding: 14px 28px;
    border-radius: 999px;
    background: var(--gl-color-accent);
    color: #fff;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    transition: transform .2s ease, filter .2s ease;
}

.gl-prefooter__button:hover,
a.gl-prefooter__button:hover {
    color: #fff;
    transform: translateY(-1px);
    filter: brightness(.96);
}

@media (max-width: 991px) {
    .gl-prefooter__inner {
        padding: 32px 28px;
        border-radius: 26px;
    }

    .gl-prefooter__content {
        flex-direction: column;
        align-items: flex-start;
    }

    .gl-prefooter__title {
        max-width: none;
    }
}

@media (max-width: 767px) {
    .gl-prefooter {
        margin-top: 40px;
    }

    .gl-prefooter__inner {
        padding: 24px 20px;
        border-radius: 22px;
    }

    .gl-prefooter__title {
        font-size: 28px;
        line-height: 1.04;
    }

    .gl-prefooter__button,
    a.gl-prefooter__button {
        width: 100%;
    }
}
</style>
		
		
		
    </div>
</main>
<?php get_footer(); ?>
