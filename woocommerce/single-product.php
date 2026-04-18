<?php
defined('ABSPATH') || exit;

get_header('shop');

if (!function_exists('gelikon_product_get_field')) {
	function gelikon_product_get_field($field_name, $default = '', $post_id = 0) {
		if (function_exists('get_field')) {
			$value = get_field($field_name, $post_id);
			if ($value !== null && $value !== false && $value !== '') {
				return $value;
			}
		}
		return $default;
	}
}

if (!function_exists('gelikon_product_image_url')) {
	function gelikon_product_image_url($image_field, $size = 'large') {
		if (empty($image_field)) {
			return '';
		}

		if (is_array($image_field)) {
			if (!empty($image_field['sizes'][$size])) {
				return $image_field['sizes'][$size];
			}
			if (!empty($image_field['url'])) {
				return $image_field['url'];
			}
			if (!empty($image_field['ID'])) {
				$image = wp_get_attachment_image_src((int) $image_field['ID'], $size);
				return !empty($image[0]) ? $image[0] : '';
			}
		}

		if (is_numeric($image_field)) {
			$image = wp_get_attachment_image_src((int) $image_field, $size);
			return !empty($image[0]) ? $image[0] : '';
		}

		if (is_string($image_field)) {
			return $image_field;
		}

		return '';
	}
}

while (have_posts()) :
	the_post();

	global $product;

	if (!$product || !is_a($product, 'WC_Product')) {
		$product = wc_get_product(get_the_ID());
	}

	$product_id = get_the_ID();

	$subtitle = gelikon_product_get_field('product_subtitle', '', $product_id);
	$top_note = gelikon_product_get_field('product_top_note', 'Боль в суставах или спорте', $product_id);

	$feature_1_text = gelikon_product_get_field('product_feature_1_text', 'Доставка за 1 день', $product_id);
	$feature_2_text = gelikon_product_get_field('product_feature_2_text', 'Гарантия 12 месяцев', $product_id);
	$feature_3_text = gelikon_product_get_field('product_feature_3_text', 'Поддержка клиентов', $product_id);

	$section_1_title = gelikon_product_get_field('product_section_1_title', 'Контроль давления без лишних устройств', $product_id);
	$section_1_text  = gelikon_product_get_field('product_section_1_text', '', $product_id);
	$section_1_image = gelikon_product_image_url(gelikon_product_get_field('product_section_1_image', '', $product_id), 'large');

	$section_2_title = gelikon_product_get_field('product_section_2_title', 'Полный контроль показателей здоровья', $product_id);
	$section_2_image = gelikon_product_image_url(gelikon_product_get_field('product_section_2_image', '', $product_id), 'large');
	$section_2_list  = gelikon_product_get_field('product_section_2_list', [], $product_id);
	$section_2_icons = gelikon_product_get_field('product_section_2_icons', [], $product_id);

	$section_3_title = gelikon_product_get_field('product_section_3_title', 'Стильный и продуманный дизайн', $product_id);
	$section_3_text  = gelikon_product_get_field('product_section_3_text', '', $product_id);
	$section_3_image = gelikon_product_image_url(gelikon_product_get_field('product_section_3_image', '', $product_id), 'large');

	$section_3_feature_1 = gelikon_product_get_field('product_section_3_feature_1', 'Доставка за 1 день', $product_id);
	$section_3_feature_2 = gelikon_product_get_field('product_section_3_feature_2', 'Гарантия качества', $product_id);
	$section_3_feature_3 = gelikon_product_get_field('product_section_3_feature_3', 'Поддержка клиентов', $product_id);

	$is_in_stock = $product && $product->is_in_stock();
	$mobile_button_text = $product && $product->is_type('simple') ? 'Купить' : 'В корзину';


    



// Мета-поля
$meta_row       = gelikon_product_get_field('product_meta_row', [], $product_id);
$highlights_raw = gelikon_product_get_field('product_highlights_list', [], $product_id);
$consultation   = gelikon_product_get_field('product_consultation', [], $product_id);



$meta_rating    = $product ? $product->get_average_rating() : '';
$meta_reviews   = $product ? $product->get_review_count() : 0;
$meta_questions = gelikon_get_product_questions_count($product_id);

$reviews        = gelikon_get_product_reviews($product_id);
$questions      = gelikon_get_product_questions($product_id);

$consultation   = gelikon_product_get_field('product_consultation', [], $product_id);
$consultation_text = !empty($consultation['text']) ? $consultation['text'] : '';
$consultation_url  = !empty($consultation['url']) ? $consultation['url'] : '';


$consultation_text = !empty($consultation['text']) ? $consultation['text'] : '';
$consultation_url  = !empty($consultation['url']) ? $consultation['url'] : '';

$highlights_left  = [];
$highlights_right = [];

if (!empty($highlights_raw) && is_array($highlights_raw)) {
	foreach ($highlights_raw as $item) {
		$text      = isset($item['text']) ? trim((string) $item['text']) : '';
		$column    = isset($item['column']) ? $item['column'] : 'left';
		$important = !empty($item['important']);

		if ($text === '') {
			continue;
		}

		$row = [
			'text'      => $text,
			'important' => $important,
		];

		if ($column === 'right') {
			$highlights_right[] = $row;
		} else {
			$highlights_left[] = $row;
		}
	}
}

?>

	<main id="primary" class="site-main gl-product-page">
		<div class="gl-container">

			<?php echo do_shortcode('[gelikon_breadcrumbs]'); ?>

			<section class="gl-product-hero gl-home-section">
				<div class="gl-product-hero__grid">
					
					<div class="gl-card gl-product-gallery">
						
						<?php echo gelikon_render_product_badges($product_id, 'single'); ?>
						
						<?php
						if (has_post_thumbnail()) {
							woocommerce_show_product_images();
						} else {
							echo wc_placeholder_img('full');
						}
						?>
					</div>

					<div class="gl-product-summary">
	<h1 class="product-title"><?php echo esc_html(get_the_title()); ?></h1>
						
						
						
	<?php if (!empty($subtitle)) : ?>
		<div class="gl-product-summary__subtitle">
			<?php echo esc_html($subtitle); ?>
		</div>
	<?php endif; ?>					
	

	<?php
	$has_product_meta_inline =
		$meta_rating ||
		($meta_reviews !== '' && $meta_reviews !== null) ||
		($meta_questions !== '' && $meta_questions !== null);
	?>

	<?php if ($has_product_meta_inline) : ?>
		<div class="gl-product-summary__meta-inline">
			<?php if ($meta_rating) : ?>
				
			
			
<!-- 			<button type="button" class="gl-product-summary__meta-link" data-gl-popup-open="reviews">
					<span class="gl-product-summary__rating">
						<span class="gl-product-summary__rating-star" aria-hidden="true">★</span>
						<span class="gl-product-summary__rating-value">
							<?php echo esc_html(number_format((float) $meta_rating, 1)); ?>
						</span>
					</span>
				</button> -->
			
			
			
			
			<button type="button" class="gl-product-summary__meta-link">
					<span class="gl-product-summary__rating">
						<span class="gl-product-summary__rating-star" aria-hidden="true">★</span>
						<span class="gl-product-summary__rating-value">
							4.3
						</span>
					</span>
				</button>
			
			
			<?php endif; ?>

			<?php if ($meta_reviews !== '' && $meta_reviews !== null) : ?>

			
			
			<!-- 				<button type="button" class="gl-product-summary__meta-link" data-gl-popup-open="reviews">
					<span class="gl-product-summary__meta-text gl-product-summary__meta-text--reviews">
						(<?php echo esc_html((int) $meta_reviews); ?>
						<?php echo esc_html(_n('отзыв', 'отзывов', (int) $meta_reviews, 'gelikon')); ?>)
					</span>
				</button> -->
			
			
			<button type="button" class="gl-product-summary__meta-link">
					<span class="gl-product-summary__meta-text gl-product-summary__meta-text--reviews">
						(254
						<?php echo esc_html(_n('отзыв', 'отзывов', (int) $meta_reviews, 'gelikon')); ?>)
					</span>
				</button>
			
			
			
			<?php endif; ?>

			<?php if ($meta_questions !== '' && $meta_questions !== null) : ?>
				<span class="gl-product-summary__meta-sep"></span>

			
			
<!-- 				<button type="button" class="gl-product-summary__meta-link" data-gl-popup-open="questions">
					<span class="gl-product-summary__meta-text">
						<?php echo esc_html((int) $meta_questions); ?>
						<?php echo esc_html(_n('вопрос', 'вопросов', (int) $meta_questions, 'gelikon')); ?>
					</span>
				</button> -->
			
			
			
			
			
			
			<?php endif; ?>
		</div>
	<?php endif; ?>

						
<style>
	.product-title {
       font-size: 24px;
	   line-height: 1.25;
       font-weight: 700;
	   margin: 0;
    }
	
	.gl-product-summary__meta-inline {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 8px;
	margin: 8px 0 14px;
	font-size: 15px;
	line-height: 1.2;
	font-weight: 500;
}

.gl-product-summary__meta-link {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 0;
	border: 0;
	background: transparent;
	cursor: pointer;
	color: inherit;
	font: inherit;
	text-decoration: none;
}

.gl-product-summary__rating {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	color: var(--gl-color-text);
	font-weight: 700;
}

.gl-product-summary__rating-star {
	font-size: 13px;
	line-height: 1;
	color: var(--gl-color-text);
	transform: translateY(-1px);
}

.gl-product-summary__rating-value {
	font-size: 15px;
	line-height: 1;
}

.gl-product-summary__meta-text {
	color: var(--gl-color-helper);
	font-size: 13px;
	line-height: 1.2;
}

.gl-product-summary__meta-text--reviews {
	text-decoration: underline;
	text-underline-offset: 2px;
}

.gl-product-summary__meta-sep {
	font-size: 13px;
	line-height: 1;
}

@media (max-width: 767px) {
	.gl-product-summary__meta-inline {
		margin: 8px 0 14px;
		font-size: 13px;
		gap: 6px;
	}

	.gl-product-summary__rating-value,
	.gl-product-summary__meta-text {
		font-size: 13px;
	}
}
</style>
						
						
						
	
<div class="gl-product-summary__excerpt-wrap">
	<div class="gl-product-summary__excerpt" id="gl-product-summary-excerpt">
		<?php
		global $product;

		if ($product) {
			$post_object = get_post($product->get_id());

			if ($post_object && !empty($post_object->post_content)) {
				echo apply_filters('the_content', $post_object->post_content);
			}
		}
		?>
	</div>

	<button class="gl-product-summary__more" type="button" data-scroll-to="#gl-product-details">
		Подробнее о модели →
	</button>
</div>

<style>
.gl-product-summary__excerpt-wrap {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

.gl-product-summary__excerpt {
	position: relative;
	max-height: 130px;
	overflow: hidden;
	color: var(--gl-color-text);
	font-size: 16px;
	line-height: 1.7;
}

.gl-product-summary__excerpt::after {
	content: "";
	position: absolute;
	left: 0;
	right: 0;
	bottom: 0;
	height: 60px;
	pointer-events: none;
	background: linear-gradient(
		to bottom,
		rgba(255, 255, 255, 0) 0%,
		rgba(255, 255, 255, 0.6) 40%,
		rgba(255, 255, 255, 1) 100%
	);
	transition: opacity 0.2s ease;
}

.gl-product-summary__excerpt.is-full::after {
	display: none;
}

.gl-product-summary__excerpt p {
	margin: 0 0 16px;
}

.gl-product-summary__excerpt p:last-child {
	margin-bottom: 0;
}

.gl-product-summary__excerpt ul,
.gl-product-summary__excerpt ol {
	margin: 0 0 16px 20px;
	padding: 0;
}

.gl-product-summary__excerpt li {
	margin-bottom: 6px;
}

.gl-product-summary__more {
	align-self: flex-start;
	padding: 0;
	border: 0;
	background: transparent;
	cursor: pointer;
	font-size: 15px;
	font-weight: 500;
	line-height: 1.4;
	color: var(--gl-color-helper);
	transition: color 0.2s ease, opacity 0.2s ease;
}

.gl-product-summary__more:hover {
	color: var(--gl-color-accent);
}

.gl-product-summary__excerpt-wrap:hover .gl-product-summary__excerpt::after {
	opacity: 0.85;
}

@media (max-width: 767px) {
	.gl-product-summary__excerpt {
		max-height: 100px;
		font-size: 15px;
		line-height: 1.6;
	}

	.gl-product-summary__excerpt::after {
		height: 46px;
	}

	.gl-product-summary__more {
		font-size: 13px;
	}
	.woocommerce-Price-currencySymbol{
		font-size: 18px;
	}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const wrap = document.querySelector('.gl-product-summary__excerpt-wrap');
	if (!wrap) return;

	const excerpt = wrap.querySelector('.gl-product-summary__excerpt');
	const button = wrap.querySelector('.gl-product-summary__more');

	if (excerpt && button) {
		const isOverflowing = excerpt.scrollHeight > excerpt.clientHeight + 5;

		if (!isOverflowing) {
			excerpt.classList.add('is-full');
			button.style.display = 'none';
		}
	}

	const buttons = document.querySelectorAll('[data-scroll-to]');

	buttons.forEach(function (btn) {
		btn.addEventListener('click', function () {
			const targetSelector = btn.getAttribute('data-scroll-to');
			if (!targetSelector) return;

			const target = document.querySelector(targetSelector);
			if (!target) return;

			const offset = 150;
			const top = target.getBoundingClientRect().top + window.pageYOffset - offset;

			window.scrollTo({
				top: top,
				behavior: 'smooth'
			});
		});
	});
});
</script>
						
						

						<div class="gl-card gl-product-buybox">
							
<!-- 							Статус наличия -->
							<?php echo gelikon_get_stock_status_html(); ?>

							<div class="gl-product-buybox__row">
								<div class="gl-product-buybox__price">
									<?php woocommerce_template_single_price(); ?>
								</div>

								<div class="gl-product-buybox__button">
									<?php woocommerce_template_single_add_to_cart(); ?>
								</div>
							</div>
						</div>

						<div class="gl-product-benefits">
							<div class="gl-card gl-product-benefit">
								<div class="gl-product-benefit__icon"></div>
								<div class="gl-product-benefit__text"><?php echo esc_html($feature_1_text); ?></div>
							</div>

							<div class="gl-card gl-product-benefit">
								<div class="gl-product-benefit__icon"></div>
								<div class="gl-product-benefit__text"><?php echo esc_html($feature_2_text); ?></div>
							</div>

							<div class="gl-card gl-product-benefit">
								<div class="gl-product-benefit__icon"></div>
								<div class="gl-product-benefit__text"><?php echo esc_html($feature_3_text); ?></div>
							</div>
						</div>
					</div>
				</div>
			</section>
			
			
			
			
			<div id="gl-product-details" class="gl-product-summary__description">
	<?php
	global $post;

	if (!empty($post->post_content)) {
		echo apply_filters('the_content', $post->post_content);
	}
	?>
</div>
			
			
			

			<?php if ($section_1_title || $section_1_text || $section_1_image) : ?>
				<section class="gl-home-section">
					<div class="gl-card gl-product-info-block gl-product-info-block--reverse-mobile">
						<div class="gl-product-info-block__media">
							<?php if ($section_1_image) : ?>
								<img src="<?php echo esc_url($section_1_image); ?>" alt="<?php echo esc_attr($section_1_title); ?>">
							<?php endif; ?>
						</div>

						<div class="gl-product-info-block__content">
							<?php if ($section_1_title) : ?>
								<h2><?php echo esc_html($section_1_title); ?></h2>
							<?php endif; ?>

							<?php if ($section_1_text) : ?>
								<div class="gl-product-info-block__text">
									<?php echo wpautop(wp_kses_post($section_1_text)); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php
			$has_section_2 = $section_2_title || !empty($section_2_list) || !empty($section_2_icons) || $section_2_image;
			if ($has_section_2) :
			?>
				<section class="gl-home-section">
					<div class="gl-card gl-product-health-block">
						<div class="gl-product-health-block__content">
							<?php if ($section_2_title) : ?>
								<h2><?php echo esc_html($section_2_title); ?></h2>
							<?php endif; ?>

							<?php if (!empty($section_2_list) && is_array($section_2_list)) : ?>
								<ul class="gl-product-health-list">
									<?php foreach ($section_2_list as $item) :
										$text = is_array($item) && !empty($item['text']) ? $item['text'] : '';
										if (!$text) {
											continue;
										}
										?>
										<li><?php echo esc_html($text); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if (!empty($section_2_icons) && is_array($section_2_icons)) : ?>
								<div class="gl-product-health-icons">
									<?php foreach ($section_2_icons as $item) :
										$title = is_array($item) && !empty($item['title']) ? $item['title'] : '';
										$icon  = is_array($item) ? gelikon_product_image_url($item['icon'] ?? '', 'medium') : '';
										if (!$title) {
											continue;
										}
										?>
										<div class="gl-card gl-product-health-icons__item">
											<?php if ($icon) : ?>
												<img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($title); ?>">
											<?php endif; ?>
											<span><?php echo esc_html($title); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="gl-product-health-block__media">
							<?php if ($section_2_image) : ?>
								<img src="<?php echo esc_url($section_2_image); ?>" alt="<?php echo esc_attr($section_2_title); ?>">
							<?php endif; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php if ($section_3_title || $section_3_text || $section_3_image) : ?>
				<section class="gl-home-section">
					<div class="gl-card gl-product-info-block">
						<div class="gl-product-info-block__media">
							<?php if ($section_3_image) : ?>
								<img src="<?php echo esc_url($section_3_image); ?>" alt="<?php echo esc_attr($section_3_title); ?>">
							<?php endif; ?>
						</div>

						<div class="gl-product-info-block__content">
							<?php if ($section_3_title) : ?>
								<h2><?php echo esc_html($section_3_title); ?></h2>
							<?php endif; ?>

							<?php if ($section_3_text) : ?>
								<div class="gl-product-info-block__text">
									<?php echo wpautop(wp_kses_post($section_3_text)); ?>
								</div>
							<?php endif; ?>

							<div class="gl-product-benefits gl-product-benefits--inline">
								<div class="gl-card gl-product-benefit">
									<div class="gl-product-benefit__icon"></div>
									<div class="gl-product-benefit__text"><?php echo esc_html($section_3_feature_1); ?></div>
								</div>

								<div class="gl-card gl-product-benefit">
									<div class="gl-product-benefit__icon"></div>
									<div class="gl-product-benefit__text"><?php echo esc_html($section_3_feature_2); ?></div>
								</div>

								<div class="gl-card gl-product-benefit">
									<div class="gl-product-benefit__icon"></div>
									<div class="gl-product-benefit__text"><?php echo esc_html($section_3_feature_3); ?></div>
								</div>
							</div>
						</div>
					</div>
				</section>
			<?php endif; ?>

			<?php
			$related_products = wc_get_related_products($product_id, 4);
			if (!empty($related_products)) :
				?>
				<section class="gl-home-section">
					<div class="gl-section-head gl-section-head--between">
						<h2><?php esc_html_e('Похожие товары', 'gelikon'); ?></h2>
						<a class="gl-section-link" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
							<?php esc_html_e('В каталог', 'gelikon'); ?>
						</a>
					</div>

					<ul class="products columns-4">
						<?php
						foreach ($related_products as $related_product_id) {
							$post_object = get_post($related_product_id);

							if (!$post_object) {
								continue;
							}

							$GLOBALS['post'] = $post_object;
							setup_postdata($post_object);

							wc_get_template_part('content', 'product');
						}
						wp_reset_postdata();
						?>
					</ul>
				</section>
			<?php endif; ?>

		</div>

		
		
<!-- Мобильный sticky bar -->
<?php if ($product && $is_in_stock) : ?>
	<div class="gl-product-mobile-bar">
		<div class="gl-product-mobile-bar__inner">
			<div class="gl-product-mobile-bar__price">
				<?php echo wp_kses_post($product->get_price_html()); ?>
			</div>

			<div class="gl-product-mobile-bar__button">
				<form class="cart" method="post" enctype="multipart/form-data">
					<input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
					<button type="submit" class="button alt single_add_to_cart_button">
						<?php echo esc_html($mobile_button_text); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
<?php endif; ?>


<!-- ПК sticky bar -->
<?php if ($product && $is_in_stock) : ?>
	<div class="gl-product-desktop-bar" id="glProductDesktopBar" aria-hidden="true">
		<div class="gl-product-desktop-bar__inner gl-container">
			<div class="gl-product-desktop-bar__left">

			</div>

			<div class="gl-product-desktop-bar__right">
				<div class="gl-product-desktop-bar__price">
					<?php echo wp_kses_post($product->get_price_html()); ?>
				</div>
				
				<form class="cart" method="post" enctype="multipart/form-data">
					<input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>">
					<button type="submit" class="button alt single_add_to_cart_button">
						<?php echo esc_html($mobile_button_text); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
<?php endif; ?>

<style>
/* =========================
   MOBILE STICKY BUY BAR
========================= */
.gl-product-mobile-bar {
	position: fixed;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 999;
	margin: 0;
	padding: 0;
}

.gl-product-mobile-bar__inner {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	padding: 10px 12px;
	margin: 0;
	background: #fff;
	border: 1px solid rgba(16, 24, 40, 0.06);
	border-radius: 16px 16px 0 0;
    box-shadow: 0 -2px 6px rgba(16, 24, 40, 0.18), 0 -1px 0 rgba(16, 24, 40, 0.1);
}

.gl-product-mobile-bar__price {
	flex: 1 1 auto;
	min-width: 0;
	text-align: end;
}

.gl-product-mobile-bar__price .price {
	margin: 0;
	line-height: 1;
}

.gl-product-mobile-bar__price .amount,
.gl-product-mobile-bar__price bdi {
	font-size: 22px;
	font-weight: 700;
	line-height: 1;
	letter-spacing: -0.03em;
	color: var(--gl-color-heading);
}

.gl-product-mobile-bar__button {
	flex: 0 0 auto;
}

.gl-product-mobile-bar__button form.cart {
	margin: 0;
}

.gl-product-mobile-bar__button .quantity {
	display: none !important;
}

.gl-product-mobile-bar__button .single_add_to_cart_button,
.gl-product-mobile-bar__button button.single_add_to_cart_button.button.alt {
	min-width: 132px;
	min-height: 48px;
	padding: 10px 20px;
	border: 0;
	border-radius: 999px;
	background: #12D457;
	color: #fff;
	font-size: 16px;
	font-weight: 700;
	line-height: 1;
	box-shadow: none;
}
	
	
	
/* =========================
   DESKTOP STICKY BUY BAR
========================= */
.gl-product-desktop-bar {
	position: fixed;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 998;
	padding: 12px 0;
	background: rgba(255, 255, 255, 0.92);
	backdrop-filter: blur(12px);
	-webkit-backdrop-filter: blur(12px);
	border-top: 1px solid rgba(0, 0, 0, 0.08);
	box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.06);
	opacity: 0;
	visibility: hidden;
	transform: translateY(100%);
	pointer-events: none;
	transition: opacity .25s ease, visibility .25s ease, transform .25s ease;
	border-radius: 14px 14px 0 0;
	box-shadow: 0 -2px 6px rgba(16, 24, 40, 0.18), 0 -1px 0 rgba(16, 24, 40, 0.1);
}

.gl-product-desktop-bar.is-visible {
	opacity: 1;
	visibility: visible;
	transform: translateY(0);
	pointer-events: auto;
}

.gl-product-desktop-bar__inner {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 20px;
}

.gl-product-desktop-bar__left {
	display: flex;
	align-items: center;
	gap: 18px;
	min-width: 0;
	flex: 1 1 auto;
}

.gl-product-desktop-bar__consultation {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 54px;
	padding: 12px 26px;
	border: 2px solid #12D457;
	border-radius: 999px;
	background: transparent;
	color: #12D457;
	font-size: 16px;
	font-weight: 600;
	line-height: 1;
	text-decoration: none;
	white-space: nowrap;
	transition: background-color .2s ease, color .2s ease, transform .2s ease;
}

.gl-product-desktop-bar__consultation:hover {
	background: rgba(18, 212, 87, 0.06);
	transform: translateY(-1px);
	color: #12D457;
}

.gl-product-desktop-bar__price {
	flex: 0 0 auto;
}

.gl-product-desktop-bar__price .price {
	margin: 0;
	line-height: 1;
}

.gl-product-desktop-bar__price .amount,
.gl-product-desktop-bar__price bdi {
	font-size: 28px;
	font-weight: 700;
	line-height: 1;
	letter-spacing: -0.03em;
	color: var(--gl-color-heading);
}

.gl-product-desktop-bar__right {
	flex: 0 0 auto;
	display: flex;
    align-items: center;
    gap: 25px;
}

.gl-product-desktop-bar__right form.cart {
	margin: 0;
}

.gl-product-desktop-bar__right .quantity {
	display: none !important;
}

.gl-product-desktop-bar__right .single_add_to_cart_button,
.gl-product-desktop-bar__right button.single_add_to_cart_button.button.alt {
	min-width: 180px;
	min-height: 54px;
	padding: 12px 26px;
	border: 0;
	border-radius: 999px;
	background: #12D457;
	color: #fff;
	font-size: 16px;
	font-weight: 700;
	line-height: 1;
	box-shadow: none;
	transition: transform .2s ease, filter .2s ease;
}

.gl-product-desktop-bar__right .single_add_to_cart_button:hover,
.gl-product-desktop-bar__right button.single_add_to_cart_button.button.alt:hover {
	transform: translateY(-1px);
	filter: brightness(.96);
}

/* =========================
   RESPONSIVE
========================= */
@media (min-width: 768px) {
	.gl-product-mobile-bar {
		display: none !important;
	}
}

@media (max-width: 767px) {
	.gl-product-desktop-bar {
		display: none !important;
	}

	.gl-product-mobile-bar__inner {
		padding: 10px 10px;
		border-radius: 14px 14px 0 0;
		gap: 10px;
	}

	.gl-product-mobile-bar__button .single_add_to_cart_button,
	.gl-product-mobile-bar__button button.single_add_to_cart_button.button.alt {
		min-width: 120px;
		min-height: 44px;
		padding: 10px 16px;
		font-size: 15px;
	}
}
</style>
		
<script>
document.addEventListener('DOMContentLoaded', function () {
	const desktopBar = document.getElementById('glProductDesktopBar');
	const buyBox = document.querySelector('.gl-product-buybox');

	if (!desktopBar || !buyBox) return;

	function updateDesktopBar() {
		if (window.innerWidth <= 767) {
			desktopBar.classList.remove('is-visible');
			desktopBar.setAttribute('aria-hidden', 'true');
			return;
		}

		const rect = buyBox.getBoundingClientRect();
		const buyBoxOutOfView = rect.bottom < 0 || rect.top < -120;

		if (buyBoxOutOfView) {
			desktopBar.classList.add('is-visible');
			desktopBar.setAttribute('aria-hidden', 'false');
		} else {
			desktopBar.classList.remove('is-visible');
			desktopBar.setAttribute('aria-hidden', 'true');
		}
	}

	updateDesktopBar();

	window.addEventListener('scroll', updateDesktopBar, { passive: true });
	window.addEventListener('resize', updateDesktopBar);
});
</script>
		
		
		
	</main>

<style>
	/* SINGLE PRODUCT */
.gl-product-page {
	padding: 28px 0 96px;
	background: #ffff;
}

.gl-product-page .woocommerce-breadcrumb {
	margin: 0 0 24px;
	font-size: 13px;
	color: var(--gl-color-text);
}

.gl-product-page .woocommerce-breadcrumb a {
	color: inherit;
	text-decoration: none;
}

.gl-product-hero {
	margin-bottom: 28px;
}

.gl-product-hero__grid {
	display: grid;
	grid-template-columns: minmax(360px, 0.92fr) minmax(0, 1.08fr);
	gap: 32px;
	align-items: start;
}

.gl-product-gallery {
	padding: 28px;
	background: #fff;
	border-radius: 30px;
	overflow: hidden;
}

.gl-product-gallery .woocommerce-product-gallery {
	margin: 0 !important;
}

.gl-product-gallery .woocommerce-product-gallery__wrapper {
	height: 100%;
}

.gl-product-gallery .woocommerce-product-gallery__image {
	margin: 0 !important;
}

.gl-product-gallery .woocommerce-product-gallery__image a,
.gl-product-gallery .woocommerce-product-gallery__image img {
	display: block;
	width: 100%;
	height: 550px;
	object-fit: cover;
}

.gl-product-summary {
	padding-top: 6px;
}
	
	
@media (max-width: 767px) {
	.gl-product-gallery .woocommerce-product-gallery__image a,
	.gl-product-gallery .woocommerce-product-gallery__image img {
		height: 320px;
	}
}


.gl-product-summary__subtitle {
	margin: 16px 0 16px;
	font-size: 17px;
	line-height: 1.5;
	font-weight: 500;
	color: var(--gl-color-subtitle);
}

.gl-product-summary__description {
	margin: 0 0 24px;
	font-size: 16px;
	line-height: 1.65;
	color: var(--gl-color-text);
}

.gl-product-summary__description p {
	margin: 0 0 12px;
}

.gl-product-buybox {
	padding: 18px 18px 18px 20px;
	margin-bottom: 18px;
	background: #ffffff;
	border: 1px solid #e6e9e7;
	border-radius: 22px;
}

.gl-product-buybox__row {
	display: flex;
	align-items: center;
	gap: 18px;
	justify-content: space-between;
}

.gl-product-buybox__price {
	flex: 1 1 auto;
	min-width: 0;
}

.gl-product-buybox__price .price {
	display: block;
	margin: 0;
	color: var(--gl-color-heading);
	font-weight: 700;
	line-height: 1;
}

.gl-product-buybox__price .amount,
.gl-product-buybox__price bdi {
	font-size: 34px;
	line-height: 1;
	font-weight: 700;
	letter-spacing: -0.04em;
	color: var(--gl-color-heading);
}

.gl-product-buybox__button {
	flex: 0 0 auto;
}

.gl-product-buybox form.cart {
	display: flex;
	align-items: center;
	gap: 12px;
	margin: 0;
}

.gl-product-buybox .quantity {
	display: none !important;
}

.gl-product-buybox .single_add_to_cart_button,
.gl-product-buybox button.single_add_to_cart_button.button.alt {
	min-width: 180px;
min-height: 54px;
padding: 12px 26px;
border: 0;
border-radius: 999px;
background: #12D457;
color: #fff;
font-size: 16px;
font-weight: 700;
line-height: 1;
box-shadow: none;
transition: transform .2s ease, filter .2s ease;
}

.gl-product-buybox .single_add_to_cart_button:hover,
.gl-product-buybox button.single_add_to_cart_button.button.alt:hover {
	transform: translateY(-1px);
	filter: brightness(.96);
}

.gl-product-benefits {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 14px;
}

.gl-product-benefits--inline {
	margin-top: 26px;
}

.gl-product-benefit {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 18px 16px;
	min-height: 96px;
	background: #fff;
	border: 1px solid #e8ece8;
	border-radius: 20px;
}

.gl-product-benefit__icon {
	width: 36px;
	height: 36px;
	border-radius: 50%;
	background: rgba(34, 197, 94, 0.10);
	flex: 0 0 36px;
	position: relative;
}

.gl-product-benefit__icon::before {
	content: "";
	position: absolute;
	inset: 9px;
	border: 2px solid var(--gl-color-accent);
	border-radius: 50%;
}

.gl-product-benefit__text {
	font-size: 15px;
	line-height: 1.35;
	color: var(--gl-color-text);
}

.gl-product-info-block,
.gl-product-health-block {
	display: grid;
	grid-template-columns: minmax(360px, 0.92fr) minmax(0, 1.08fr);
	gap: 34px;
	padding: 24px;
	align-items: center;
	background: #f8f9f8;
	border-radius: 28px;
}

.gl-product-info-block__media img,
.gl-product-health-block__media img {
	display: block;
	width: 100%;
	height: auto;
	object-fit: cover;
	border-radius: 22px;
}

.gl-product-info-block__content h2,
.gl-product-health-block__content h2 {
	margin: 0 0 20px;
	font-size: clamp(28px, 2.8vw, 50px);
	line-height: 1.03;
	letter-spacing: -0.04em;
	color: var(--gl-color-text);
}

.gl-product-info-block__text,
.gl-product-health-block__content {
	font-size: 15px;
	line-height: 1.5;
	color: var(--gl-color-text);
}

.gl-product-info-block__text p {
	margin: 0 0 14px;
}

.gl-product-health-block {
	grid-template-columns: minmax(0, 1fr) minmax(380px, 0.92fr);
}

.gl-product-health-list {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 14px 34px;
	margin: 0 0 28px;
	padding: 0;
	list-style: none;
}

.gl-product-health-list li {
	position: relative;
	padding-left: 18px;
	font-size: 15px;
	line-height: 1.5;
	color: var(--gl-color-text);
}

.gl-product-health-list li::before {
	content: "";
	position: absolute;
	top: 11px;
	left: 0;
	width: 7px;
	height: 7px;
	border-radius: 50%;
	background: var(--gl-color-accent);
}

.gl-product-health-icons {
	display: grid;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	gap: 12px;
}

.gl-product-health-icons__item {
	padding: 16px 12px;
	text-align: center;
	min-height: 114px;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 12px;
	background: #fff;
	border: 1px solid #e8ece8;
	border-radius: 18px;
}

.gl-product-health-icons__item img {
	width: 34px;
	height: 34px;
	object-fit: contain;
}

.gl-product-health-icons__item span {
	font-size: 13px;
	line-height: 1.35;
	color: var(--gl-color-text);
}

.gl-product-page ul.products.columns-4 {
	display: grid;
	grid-template-columns: repeat(4, minmax(0, 1fr));
	gap: 20px;
	margin: 0;
	padding: 0;
	list-style: none;
}

.gl-product-page ul.products.columns-4 li.product {
	width: auto !important;
	float: none !important;
	margin: 0 !important;
}
	
.gl-home-section{
	margin: 50px 0;
}



@media (max-width: 1199px) {
	.gl-product-hero__grid,
	.gl-product-info-block,
	.gl-product-health-block {
		grid-template-columns: 1fr;
	}

	.gl-product-benefits,
	.gl-product-health-icons,
	.gl-product-page ul.products.columns-4 {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
}

@media (max-width: 767px) {
	.gl-product-page {
		padding: 16px 0 16px;
	}

	.gl-product-page .woocommerce-breadcrumb {
		font-size: 12px;
		margin-bottom: 14px;
	}

	.gl-product-gallery,
	.gl-product-info-block,
	.gl-product-health-block {
		padding: 16px;
		border-radius: 22px;
	}

	.gl-product-hero__grid {
		gap: 18px;
	}

	.gl-product-summary {
		padding-top: 0;
	}

	.gl-product-summary__subtitle {
		margin: 12px 0 12px;
		font-size: 15px;
		line-height: 1.45;
	}

	.gl-product-summary__description {
		font-size: 15px;
		line-height: 1.6;
		margin-bottom: 18px;
		max-width: none;
	}

	.gl-product-buybox {
		display: none;
	}

	.gl-product-buybox__row {
		flex-direction: column;
		align-items: stretch;
	}

	.gl-product-benefits,
	.gl-product-health-list,
	.gl-product-health-icons,
	.gl-product-page ul.products.columns-4 {
		grid-template-columns: 1fr;
	}

	.gl-product-info-block--reverse-mobile .gl-product-info-block__media {
		order: -1;
	}

	.gl-product-mobile-bar {
		display: block;
	}
}
	
	
	
/* Миниатюры галереи */
.gl-product-gallery .woocommerce-product-gallery {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.gl-product-gallery .woocommerce-product-gallery__wrapper {
    margin: 0;
}

.gl-product-gallery .flex-control-thumbs {
    display: flex !important;
    flex-wrap: wrap;
    gap: 12px;
    margin: 0 !important;
    padding: 0 !important;
    list-style: none !important;
}

.gl-product-gallery .flex-control-thumbs li {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
    float: none !important;
    width: 84px !important;
    flex: 0 0 84px;
}

.gl-product-gallery .flex-control-thumbs li::marker {
    content: none;
}

.gl-product-gallery .flex-control-thumbs img {
    display: block;
    width: 100% !important;
    height: 84px !important;
    object-fit: cover;
    border-radius: 16px;
    border: 1px solid #e3e7e5;
    background: #fff;
    padding: 4px;
    opacity: 1 !important;
    cursor: pointer;
    transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
}

.gl-product-gallery .flex-control-thumbs img:hover,
.gl-product-gallery .flex-control-thumbs img.flex-active {
    border-color: var(--gl-color-accent);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
    transform: translateY(-1px);
}

@media (max-width: 767px) {
    .gl-product-gallery .flex-control-thumbs {
        gap: 8px;
    }

    .gl-product-gallery .flex-control-thumbs li {
        width: 68px !important;
        flex: 0 0 68px;
    }

    .gl-product-gallery .flex-control-thumbs img {
        height: 68px !important;
        border-radius: 12px;
    }
}
</style>







<!-- Попап отзывов -->
<div class="gl-product-popup" id="gelikon-product-popup" hidden>
	<div class="gl-product-popup__overlay" data-gl-popup-close></div>

	<div class="gl-product-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="gl-product-popup-title">
		<button type="button" class="gl-product-popup__close" aria-label="Закрыть" data-gl-popup-close>
			×
		</button>

		<div class="gl-product-popup__head">
			<h3 class="gl-product-popup__title" id="gl-product-popup-title">
				Отзывы и вопросы
			</h3>

			<div class="gl-product-popup__tabs">
				<button type="button" class="gl-product-popup__tab is-active" data-gl-tab="reviews">Отзывы</button>
				<button type="button" class="gl-product-popup__tab" data-gl-tab="questions">Вопросы</button>
			</div>
		</div>

		<div class="gl-product-popup__body">
			<div class="gl-product-popup__panel is-active" data-gl-panel="reviews">
				<div class="gl-product-popup__section">
					<div class="gl-product-popup__section-top">
						<strong>Рейтинг товара:</strong>
						<span><?php echo esc_html(number_format((float) $meta_rating, 1)); ?> / 5</span>
					</div>

					<?php if (!empty($reviews)) : ?>
						<ul class="gl-product-comments">
							<?php foreach ($reviews as $comment) : ?>
								<?php $rating = get_comment_meta($comment->comment_ID, 'rating', true); ?>
								<li class="gl-product-comments__item">
									<div class="gl-product-comments__top">
										<strong><?php echo esc_html($comment->comment_author); ?></strong>
										<?php if ($rating) : ?>
											<span class="gl-product-comments__rating"><?php echo esc_html($rating); ?>/5</span>
										<?php endif; ?>
									</div>
									<div class="gl-product-comments__date">
										<?php echo esc_html(get_comment_date('', $comment)); ?>
									</div>
									<div class="gl-product-comments__text">
										<?php echo wp_kses_post(wpautop($comment->comment_content)); ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p>Пока отзывов нет.</p>
					<?php endif; ?>
				</div>

				<div class="gl-product-popup__section">
					<h4>Оставить отзыв</h4>

					<?php
					comment_form([
	'title_reply'          => '',
	'title_reply_before'   => '',
	'title_reply_after'    => '',
	'comment_notes_before' => '',
	'comment_notes_after'  => '',
	'label_submit'         => 'Отправить отзыв',
	'class_submit'         => 'gl-product-form__submit',
	'fields' => [
		'author' =>
			'<p class="comment-form-author">
				<label for="author">Имя <span class="required">*</span></label>
				<input id="author" name="author" type="text" autocomplete="name" required>
			</p>',
		'email' =>
			'<p class="comment-form-email">
				<label for="email">Email <span class="required">*</span></label>
				<input id="email" name="email" type="email" autocomplete="email" required>
			</p>',
	],
	'comment_field' =>
		'<p class="comment-form-rating">
			<label for="rating">Оценка</label>
			<select name="rating" id="rating" required>
				<option value="">Выберите оценку</option>
				<option value="5">5</option>
				<option value="4">4</option>
				<option value="3">3</option>
				<option value="2">2</option>
				<option value="1">1</option>
			</select>
		</p>
		<p class="comment-form-comment">
			<label for="comment">Ваш отзыв</label>
			<textarea id="comment" name="comment" cols="45" rows="6" required></textarea>
		</p>',
], $product_id);
					?>
				</div>
			</div>

			<div class="gl-product-popup__panel" data-gl-panel="questions">
				<div class="gl-product-popup__section">
					<?php if (!empty($questions)) : ?>
						<ul class="gl-product-comments">
							<?php foreach ($questions as $comment) : ?>
								<li class="gl-product-comments__item">
									<div class="gl-product-comments__top">
										<strong><?php echo esc_html($comment->comment_author); ?></strong>
									</div>
									<div class="gl-product-comments__date">
										<?php echo esc_html(get_comment_date('', $comment)); ?>
									</div>
									<div class="gl-product-comments__text">
										<?php echo wp_kses_post(wpautop($comment->comment_content)); ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p>Пока вопросов нет.</p>
					<?php endif; ?>
				</div>

				<div class="gl-product-popup__section">
					<h4>Задать вопрос</h4>

					<form class="gl-product-form" method="post" action="">
						<p class="gl-product-form__field">
							<label for="gelikon_question_author">Имя</label>
							<input type="text" id="gelikon_question_author" name="author" required>
						</p>

						<p class="gl-product-form__field">
							<label for="gelikon_question_email">Email</label>
							<input type="email" id="gelikon_question_email" name="email" required>
						</p>

						<p class="gl-product-form__field">
							<label for="gelikon_question_comment">Ваш вопрос</label>
							<textarea id="gelikon_question_comment" name="comment" rows="6" required></textarea>
						</p>

						<input type="hidden" name="comment_post_ID" value="<?php echo esc_attr($product_id); ?>">
						<?php wp_nonce_field('gelikon_product_question', 'gelikon_question_nonce'); ?>

						<button type="submit" name="gelikon_submit_product_question" class="gl-product-form__submit">
							Отправить вопрос
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const popup = document.getElementById('gelikon-product-popup');
	if (!popup) return;

	const openButtons = document.querySelectorAll('[data-gl-popup-open]');
	const closeButtons = popup.querySelectorAll('[data-gl-popup-close]');
	const tabs = popup.querySelectorAll('[data-gl-tab]');
	const panels = popup.querySelectorAll('[data-gl-panel]');

	function openPopup(tabName) {
		popup.hidden = false;
		document.documentElement.classList.add('gl-popup-open');
		document.body.classList.add('gl-popup-open');

		if (tabName) {
			switchTab(tabName);
		}
	}

	function closePopup() {
		popup.hidden = true;
		document.documentElement.classList.remove('gl-popup-open');
		document.body.classList.remove('gl-popup-open');
	}

	function switchTab(tabName) {
		tabs.forEach(tab => {
			tab.classList.toggle('is-active', tab.dataset.glTab === tabName);
		});

		panels.forEach(panel => {
			panel.classList.toggle('is-active', panel.dataset.glPanel === tabName);
		});
	}

	openButtons.forEach(button => {
		button.addEventListener('click', function () {
			openPopup(this.dataset.glPopupOpen || 'reviews');
		});
	});

	closeButtons.forEach(button => {
		button.addEventListener('click', function () {
			closePopup();
		});
	});

	tabs.forEach(tab => {
		tab.addEventListener('click', function () {
			switchTab(this.dataset.glTab);
		});
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && !popup.hidden) {
			closePopup();
		}
	});

	if (window.location.hash === '#gelikon-product-popup') {
		openPopup('questions');
	}
});
</script>

<style>
	.gl-product-highlights__meta-link{
	padding: 0;
	border: 0;
	background: transparent;
	cursor: pointer;
	font: inherit;
	color: inherit;
}

.gl-product-highlights__meta-link:hover .gl-product-highlights__meta-item{
	color: var(--gl-color-accent-2);
}

.gl-product-highlights__actions{
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	margin-top: 14px;
}

.gl-product-highlights__button--ghost{
	background: transparent;
	border: 1px solid var(--gl-color-line);
	color: var(--gl-color-text);
}

.gl-product-popup{
	position: fixed;
	inset: 0;
	z-index: 9999;
}

.gl-product-popup__overlay{
	position: absolute;
	inset: 0;
	background: rgba(17, 24, 39, .52);
}

.gl-product-popup__dialog{
	position: relative;
	z-index: 2;
	width: min(960px, calc(100% - 32px));
	max-height: calc(100vh - 40px);
	margin: 20px auto;
	background: #fff;
	border-radius: 28px;
	overflow: hidden;
	box-shadow: 0 30px 80px rgba(0,0,0,.18);
	display: flex;
	flex-direction: column;
}

.gl-product-popup__close{
	position: absolute;
	top: 14px;
	right: 14px;
	width: 42px;
	height: 42px;
	border: 0;
	border-radius: 50%;
	background: #f4f6f8;
	font-size: 28px;
	line-height: 1;
	cursor: pointer;
	z-index: 3;
}

.gl-product-popup__head{
	padding: 28px 32px 18px;
	border-bottom: 1px solid #edf1f4;
}

.gl-product-popup__title{
	margin: 0 0 18px;
	font-size: 28px;
	line-height: 1.1;
}

.gl-product-popup__tabs{
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
}

.gl-product-popup__tab{
	padding: 10px 16px;
	border: 1px solid #d8e1e8;
	border-radius: 999px;
	background: transparent;
	cursor: pointer;
	font: inherit;
	font-weight: 600;
}

.gl-product-popup__tab.is-active{
	border-color: var(--gl-color-accent);
	color: var(--gl-color-accent);
}

.gl-product-popup__body{
	padding: 24px 32px 32px;
	overflow: auto;
}

.gl-product-popup__panel{
	display: none;
}

.gl-product-popup__panel.is-active{
	display: block;
}

.gl-product-popup__section + .gl-product-popup__section{
	margin-top: 28px;
	padding-top: 28px;
	border-top: 1px solid #edf1f4;
}

.gl-product-popup__section-top{
	display: flex;
	align-items: center;
	gap: 10px;
	margin-bottom: 18px;
	font-size: 18px;
}

.gl-product-comments{
	list-style: none;
	margin: 0;
	padding: 0;
	display: grid;
	gap: 16px;
}

.gl-product-comments__item{
	padding: 18px 20px;
	border: 1px solid #e8edf1;
	border-radius: 18px;
	background: #fafcfd;
}

.gl-product-comments__top{
	display: flex;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 6px;
}

.gl-product-comments__date{
	font-size: 13px;
	color: var(--gl-color-helper);
	margin-bottom: 10px;
}

.gl-product-comments__rating{
	color: var(--gl-color-accent-2);
	font-weight: 700;
}

.gl-product-form__field,
.comment-form-author,
.comment-form-email,
.comment-form-rating,
.comment-form-comment{
	margin: 0 0 14px;
}

.gl-product-form label,
#review_form label{
	display: block;
	margin-bottom: 6px;
	font-weight: 600;
}

.gl-product-form input,
.gl-product-form textarea,
#review_form input,
#review_form textarea,
#review_form select{
	width: 100%;
	min-height: 48px;
	padding: 12px 14px;
	border: 1px solid #dbe4ea;
	border-radius: 14px;
	background: #fff;
	font: inherit;
}

.gl-product-form textarea,
#review_form textarea{
	min-height: 140px;
	resize: vertical;
}

.gl-product-form__submit,
#review_form .submit{
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 48px;
	padding: 0 22px;
	border: 0;
	border-radius: 999px;
	background: var(--gl-color-accent);
	color: #fff;
	font-weight: 700;
	cursor: pointer;
}

html.gl-popup-open,
body.gl-popup-open{
	overflow: hidden;
}

@media (max-width: 767px){
	.gl-product-popup__dialog{
		width: calc(100% - 16px);
		margin: 8px auto;
		max-height: calc(100vh - 16px);
		border-radius: 20px;
	}

	.gl-product-popup__head{
		padding: 20px 20px 16px;
	}

	.gl-product-popup__body{
		padding: 18px 20px 24px;
	}

	.gl-product-popup__title{
		font-size: 24px;
	}
}
	
	
	
	
/* =========================
   Форма отзыва в popup
   ========================= */

.gl-product-popup #review_form,
.gl-product-popup #respond,
.gl-product-popup .comment-respond,
.gl-product-popup .comment-form {
	width: 100%;
}

.gl-product-popup #respond{
	margin: 0;
	padding: 0;
}

.gl-product-popup .comment-reply-title{
	display: none;
}

.gl-product-popup .comment-form{
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 18px 20px;
	align-items: start;
	margin: 0;
}

.gl-product-popup .comment-form > p{
	margin: 0;
}

/* Заголовок блока */
.gl-product-popup .gl-review-form-title,
.gl-product-popup .comment-form-title{
	grid-column: 1 / -1;
	margin: 0 0 6px;
	font-size: 30px;
	line-height: 1.1;
	font-weight: 800;
	color: var(--gl-color-heading);
}

/* Лейблы */
.gl-product-popup .comment-form label{
	display: block;
	margin: 0 0 8px;
	font-size: 15px;
	line-height: 1.3;
	font-weight: 600;
	color: var(--gl-color-heading);
}

.gl-product-popup .comment-form .required{
	color: #21c45d;
	text-decoration: none;
	border: 0;
}


/* Текст отзыва */
.gl-product-popup .comment-form-comment{
	grid-column: 1 / -1;
}

.gl-product-popup .comment-form-comment textarea{
	width: 100%;
	min-height: 150px;
	padding: 16px 18px;
	border: 1px solid #dbe3ea;
	border-radius: 18px;
	background: #fff;
	font: inherit;
	font-size: 16px;
	line-height: 1.5;
	color: var(--gl-color-heading);
	resize: vertical;
	box-sizing: border-box;
	transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.gl-product-popup .comment-form-comment textarea::placeholder{
	color: var(--gl-color-helper);
}

/* Поля имя / email / сайт */
.gl-product-popup .comment-form-author,
.gl-product-popup .comment-form-email,
.gl-product-popup .comment-form-url{
	min-width: 0;
}

.gl-product-popup .comment-form-author input,
.gl-product-popup .comment-form-email input,
.gl-product-popup .comment-form-url input{
	width: 100%;
	height: 54px;
	padding: 0 16px;
	border: 1px solid #dbe3ea;
	border-radius: 14px;
	background: #fff;
	font: inherit;
	font-size: 16px;
	line-height: 1.2;
	color: var(--gl-color-heading);
	box-sizing: border-box;
	transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.gl-product-popup .comment-form textarea:focus,
.gl-product-popup .comment-form input:focus,
.gl-product-popup .comment-form select:focus{
	outline: none;
	border-color: rgba(33, 196, 93, 0.65);
	box-shadow: 0 0 0 4px rgba(33, 196, 93, 0.10);
	background: #fff;
}

/* чекбокс */
.gl-product-popup .comment-form-cookies-consent{
	grid-column: 1 / -1;
	display: flex;
	align-items: flex-start;
	gap: 12px;
	padding: 2px 0 0;
	font-size: 13px;
	line-height: 1.45;
	color: var(--gl-color-text);
}

.gl-product-popup .comment-form-cookies-consent input[type="checkbox"]{
	flex: 0 0 auto;
	width: 18px;
	height: 18px;
	margin: 2px 0 0;
	accent-color: var(--gl-color-accent);
}

.gl-product-popup .comment-form-cookies-consent label{
	margin: 0;
	font-size: 13px;
	line-height: 1.45;
	font-weight: 400;
	color: var(--gl-color-helper);
	cursor: pointer;
}

/* submit */
.gl-product-popup .form-submit{
	grid-column: 1 / -1;
	margin-top: 4px;
}

.gl-product-popup .form-submit .submit,
.gl-product-popup .form-submit input[type="submit"],
.gl-product-popup .gl-product-form__submit{
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 54px;
	padding: 0 28px;
	border: 0;
	border-radius: 999px;
	background: var(--gl-color-accent);
	color: #fff;
	font-size: 17px;
	line-height: 1;
	font-weight: 700;
	cursor: pointer;
	box-shadow: none;
	transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
}

.gl-product-popup .form-submit .submit:hover,
.gl-product-popup .form-submit input[type="submit"]:hover,
.gl-product-popup .gl-product-form__submit:hover{
	background: #1db954;
	transform: translateY(-1px);
	box-shadow: 0 10px 24px rgba(34, 197, 94, 0.22);
}

.gl-product-popup .form-submit .submit:active,
.gl-product-popup .form-submit input[type="submit"]:active,
.gl-product-popup .gl-product-form__submit:active{
	transform: translateY(0);
}

/* скрытый select рейтинга */
.gl-product-popup .comment-form-rating select{
	display: none !important;
}

/* вспомогательные тексты WP */
.gl-product-popup .logged-in-as,
.gl-product-popup .comment-notes,
.gl-product-popup .form-allowed-tags{
	grid-column: 1 / -1;
	margin: 0;
	font-size: 13px;
	line-height: 1.45;
	color: var(--gl-color-helper);
}

/* ошибки */
.gl-product-popup .comment-form .woocommerce-error,
.gl-product-popup .comment-form .error,
.gl-product-popup .comment-form .form-error{
	grid-column: 1 / -1;
	padding: 14px 16px;
	border-radius: 14px;
	background: #fff4f4;
	border: 1px solid #fecaca;
	color: #b91c1c;
	font-size: 13px;
}

/* адаптив */
@media (max-width: 767px){
	.gl-product-popup .comment-form{
		grid-template-columns: 1fr;
		gap: 14px;
	}

	.gl-product-popup .comment-form-author,
	.gl-product-popup .comment-form-email,
	.gl-product-popup .comment-form-url,
	.gl-product-popup .comment-form-comment,
	.gl-product-popup .comment-form-rating,
	.gl-product-popup .comment-form-cookies-consent,
	.gl-product-popup .form-submit{
		grid-column: 1 / -1;
	}

	.gl-product-popup .comment-form-rating .stars a{
		width: 34px;
		height: 34px;
		margin-right: 4px;
	}

	.gl-product-popup .form-submit .submit,
	.gl-product-popup .form-submit input[type="submit"],
	.gl-product-popup .gl-product-form__submit{
		width: 100%;
	}
}
	
.gl-product-popup .comment-form-rating .stars span{
	display: inline-flex;
	align-items: center;
	gap: 6px;
}
</style>

<?php
endwhile;

get_footer('shop');