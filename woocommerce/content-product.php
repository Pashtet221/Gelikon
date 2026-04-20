<?php
defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
	return;
}

$product_id   = $product->get_id();
$product_url  = get_permalink($product_id);
$product_name = $product->get_name();
$price_html   = $product->get_price_html();
$is_featured  = $product->is_featured();
$is_on_sale   = $product->is_on_sale();
$is_in_stock  = $product->is_in_stock();
$product_type = $product->get_type();

$badge = '';
if ($is_on_sale) {
	$badge = 'Sale';
} elseif ($is_featured) {
	$badge = 'Хит';
}

$image_html = $product->get_image('woocommerce_thumbnail', [
	'class'   => 'gl-product-card__image',
	'loading' => 'lazy',
]);

$add_to_cart_url  = $product->add_to_cart_url();
$add_to_cart_desc = $product->add_to_cart_description();
$is_catalog_view  = function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy());
$primary_cta_text = $is_catalog_view ? __('В корзину', 'gelikon') : __('Купить', 'gelikon');
?>
<li <?php wc_product_class('gl-product-card', $product); ?>>
	<div class="gl-product-card__inner">

		<a class="gl-product-card__link" href="<?php echo esc_url($product_url); ?>">
			<div class="gl-product-card__media">
				<?php echo gelikon_render_product_badges($product_id, 'card'); ?>

				<?php if ($image_html) : ?>
					<?php echo $image_html; ?>
				<?php else : ?>
					<?php echo wc_placeholder_img('woocommerce_thumbnail', ['class' => 'gl-product-card__image']); ?>
				<?php endif; ?>
			</div>

			<div class="gl-product-card__content">
				<h3 class="gl-product-card__title"><?php echo esc_html($product_name); ?></h3>

				<div class="gl-product-card__meta">
					<span class="gl-product-card__stock <?php echo $is_in_stock ? 'is-in-stock' : 'is-out-of-stock'; ?>">
						<?php echo $is_in_stock ? esc_html__('В наличии', 'gelikon') : esc_html__('Нет в наличии', 'gelikon'); ?>
					</span>
				</div>
			</div>
		</a>

		<div class="gl-product-card__purchase">
			<?php if ($price_html) : ?>
				<div class="gl-product-card__price">
					<?php echo wp_kses_post($price_html); ?>
				</div>
			<?php endif; ?>

			<div class="gl-product-card__actions">
				<?php if ($is_in_stock) : ?>

					<?php if ($product->is_type('simple')) : ?>
						<a
							href="<?php echo esc_url($add_to_cart_url); ?>"
							data-quantity="1"
							class="button product_type_<?php echo esc_attr($product_type); ?> add_to_cart_button ajax_add_to_cart gl-product-card__button"
							data-product_id="<?php echo esc_attr($product_id); ?>"
							data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
							aria-label="<?php echo esc_attr($add_to_cart_desc); ?>"
							rel="nofollow"
						>
							<?php echo esc_html($primary_cta_text); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url($product_url); ?>" class="gl-product-card__button">
							<?php echo esc_html($primary_cta_text); ?>
						</a>
					<?php endif; ?>

				<?php else : ?>
					<a href="<?php echo esc_url($product_url); ?>" class="gl-product-card__button gl-product-card__button--disabled">
						<?php esc_html_e('Подробнее', 'gelikon'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

	</div>
</li>

<style>
/* =========================
   Product card — Gelikon
========================= */

.gl-product-card {
	margin: 0 !important;
	padding: 0;
	width: auto !important;
	float: none !important;
	list-style: none !important;
	height: 100%;
}

.gl-product-card__inner {
	display: flex;
	flex-direction: column;
	height: 100%;
	padding: 18px;
	background: #fff;
	border: 1px solid #e5ebe7;
	border-radius: 28px;
	transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.gl-product-card__inner:hover {
	transform: translateY(-3px);
	box-shadow: 0 18px 40px rgba(0, 0, 0, 0.05);
	border-color: #dce4df;
}

.gl-product-card__link {
	display: flex;
	flex-direction: column;
	flex: 1 1 auto;
	text-decoration: none;
	color: inherit;
	min-height: 0;
}

.gl-product-card__media {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	min-height: 250px;
	padding: 0;
	margin-bottom: 18px;
	background: #f4f7f6;
	border-radius: 22px;
	overflow: hidden;
	flex-shrink: 0;
}

.gl-product-card__image,
.gl-product-card__media img {
	display: block;
	width: 100%;
	height: 210px;
	object-fit: contain;
	object-position: center;
	background: #fff;
}

.gl-product-card__badge {
	position: absolute;
	top: 12px;
	left: 12px;
	z-index: 2;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 7px 12px;
	border-radius: 999px;
	background: var(--gl-color-accent);
	color: #fff;
	font-size: 12px;
	font-weight: 600;
	line-height: 1;
}

.gl-product-card__content {
	display: flex;
	flex-direction: column;
	flex: 1 1 auto;
	min-height: 0;
}

.gl-product-card__title {
	margin: 0 0 14px;
	font-size: 16px;
	line-height: 1.22;
	font-weight: 700;
	letter-spacing: -0.03em;
	color: #171d2a;

	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
	overflow: hidden;

	min-height: calc(1.22em * 2);
}

.gl-product-card__price {
	margin-bottom: 0;
	font-size: 18px;
	line-height: 1.1;
	font-weight: 700;
	color: #171d2a;
	flex-shrink: 0;
}

.gl-product-card__price .amount,
.gl-product-card__price bdi {
	font-size: 24px;
	font-weight: 700;
	letter-spacing: -0.03em;
	color: #171d2a;
}

.gl-product-card__price del {
	margin-right: 8px;
	font-size: 14px;
	font-weight: 400;
	color: #9aa3ad;
}

.gl-product-card__price ins {
	text-decoration: none;
}

.gl-product-card__meta {
	font-size: 14px;
	line-height: 1.4;
	flex-shrink: 0;
}

.gl-product-card__stock.is-in-stock {
	color: #12D457;
	font-weight: 500;
}

.gl-product-card__stock.is-out-of-stock {
	color: var(--gl-color-helper);
	font-weight: 500;
}

.gl-product-card__actions {
	margin-top: 0;
	flex-shrink: 0;
}

.gl-product-card__purchase {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-top: 16px;
	flex-wrap: wrap;
}

.gl-product-card__button,
a.gl-product-card__button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: auto;
	min-height: 38px;
	padding: 8px 16px;
	border: 1.5px solid #22C55E;
	border-radius: 7px;
	background: transparent;
	color: #22C55E;
	text-decoration: none;
	font-size: 14px;
	font-weight: 600;
	line-height: 1;
	cursor: pointer;
	white-space: nowrap;
	transition: transform .2s ease, color .2s ease, background-color .2s ease, border-color .2s ease;
}

.gl-product-card__button:hover,
a.gl-product-card__button:hover {
	color: #fff;
	background: #16A34A;
	border-color: #16A34A;
	transform: translateY(-1px);
}

.gl-product-card__button--disabled {
	background: #cfd6d1;
	border-color: #cfd6d1;
	color: #fff;
	pointer-events: none;
}

.woocommerce ul.products li.product .button {
	display: flex;
}
	
.woocommerce ul.products li.product a img{
	margin: 0;	
}

@media (max-width: 767px) {
	.gl-product-card__inner {
		padding: 14px;
		border-radius: 22px;
	}

	.gl-product-card__media {
		min-height: 190px;
		padding: 0;
		border-radius: 18px;
	}

	.gl-product-card__image,
	.gl-product-card__media img {
		height: 160px;
	}

	.gl-product-card__title {
		font-size: 14px;
		line-height: 1.25;
		min-height: calc(1.25em * 2);
	}

	.gl-product-card__price .amount,
	.gl-product-card__price bdi {
		font-size: 22px;
	}

	.gl-product-card__button,
	a.gl-product-card__button {
		min-height: 36px;
		font-size: 14px;
	}
}
</style>
