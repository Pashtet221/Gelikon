<?php
defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product instanceof WC_Product || !$product->is_visible()) {
	return;
}

$product_id   = $product->get_id();
$product_url  = get_permalink($product_id);
$product_name = $product->get_name();
$price_html   = $product->get_price_html();
$is_in_stock  = $product->is_in_stock();
$is_preorder  = (function_exists('gelikon_is_product_preorder') && gelikon_is_product_preorder($product_id)) || $product->get_stock_status() === 'onbackorder';
$is_discontinued = function_exists('gelikon_is_product_discontinued') && gelikon_is_product_discontinued($product_id);
$is_purchase_available = function_exists('gelikon_product_can_be_purchased') ? gelikon_product_can_be_purchased($product) : $is_in_stock;
$stock_label = $is_discontinued ? __('Снят с продажи', 'gelikon') : ($is_preorder ? __('Предзаказ', 'gelikon') : ($is_in_stock ? __('В наличии', 'gelikon') : __('Нет в наличии', 'gelikon')));
$stock_class = $is_discontinued ? 'is-discontinued' : ($is_preorder ? 'is-preorder' : ($is_in_stock ? 'is-in-stock' : 'is-out-of-stock'));
$product_type = $product->get_type();

$image_html = '';
$image_id = (int) $product->get_image_id();

if ($image_id > 0) {
	$image_html = wp_get_attachment_image($image_id, 'full', false, [
		'class'   => 'gl-product-card__image',
		'loading' => 'lazy',
	]);
}

$add_to_cart_url  = $product->add_to_cart_url();
$add_to_cart_desc = $product->add_to_cart_description();

/**
 * Важно:
 * При AJAX-фильтрации и AJAX-пагинации is_shop(), is_product_category()
 * и похожие условные теги могут не работать, потому что запрос идет через admin-ajax.php.
 * Поэтому текст кнопки фиксируем напрямую.
 */
$primary_cta_text = __('В корзину', 'gelikon');
?>

<li <?php wc_product_class('gl-product-card', $product); ?>>
	<div class="gl-product-card__inner">

		<a class="gl-product-card__link" href="<?php echo esc_url($product_url); ?>">
			<div class="gl-product-card__media">
				<?php
				if (function_exists('gelikon_render_product_badges')) {
					echo gelikon_render_product_badges($product_id, 'card');
				}
				?>

				<?php if (!empty($image_html)) : ?>
					<?php echo wp_kses_post($image_html); ?>
				<?php else : ?>
					<?php echo wc_placeholder_img('woocommerce_thumbnail', [
						'class' => 'gl-product-card__image',
					]); ?>
				<?php endif; ?>
			</div>

			<div class="gl-product-card__content">
				<h3 class="gl-product-card__title">
					<?php echo esc_html($product_name); ?>
				</h3>

				<div class="gl-product-card__meta">
					<span class="gl-product-card__stock <?php echo esc_attr($stock_class); ?>">
						<span class="gl-product-card__stock-dot" aria-hidden="true"></span>
						<?php echo esc_html($stock_label); ?>
					</span>
				</div>
			</div>
		</a>

		<div class="gl-product-card__purchase">
			<?php if (!empty($price_html)) : ?>
				<div class="gl-product-card__price">
					<?php echo wp_kses_post($price_html); ?>
				</div>
			<?php endif; ?>

			<div class="gl-product-card__actions">
				<?php if ($is_purchase_available) : ?>

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
	height: clamp(340px, 27vw, 390px);
	min-height: 340px;
	padding: 8px;
	margin-bottom: 18px;
	background: #fff;
	border-radius: 22px;
	overflow: hidden;
	flex-shrink: 0;
}

.gl-product-card__image,
.gl-product-card__media img {
	display: block;
	width: 100%;
	height: 100%;
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

.gl-product-card__meta {
	font-size: 14px;
	line-height: 1.4;
	flex-shrink: 0;
}

.gl-product-card__stock {
	display: inline-flex;
	align-items: center;
	gap: 5px;
}

.gl-product-card__stock-dot {
	display: inline-block;
	width: 6px;
	height: 6px;
	border-radius: 50%;
	background: currentColor;
	flex: 0 0 6px;
}

.gl-product-card__stock.is-in-stock {
	color: #0f9f57 !important;
	font-weight: 400 !important;
}

.gl-product-card__stock.is-preorder {
	color: #f59e0b;
	font-weight: 500;
}

.gl-product-card__stock.is-out-of-stock,
.gl-product-card__stock.is-discontinued {
	color: #ef4444;
	font-weight: 500;
}

.gl-product-card__purchase {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-top: 16px;
	flex-wrap: nowrap;
}

.gl-product-card__price {
	margin-bottom: 0;
	flex: 1 1 auto;
	min-width: 0;
	line-height: 1.1;
	color: #171d2a;
	display: flex;
	flex-direction: column;
	justify-content: center;
	gap: 4px;
}

.gl-product-card__price > .amount,
.gl-product-card__price > bdi,
.gl-product-card__price .price > .amount,
.gl-product-card__price .price > bdi {
	font-size: 24px;
	font-weight: 700;
	letter-spacing: -0.03em;
	color: #171d2a;
}

.gl-product-card__price del {
	display: block;
	margin: 0;
	order: 1;
	font-size: 13px;
	font-weight: 400;
	line-height: 1;
	color: #9aa3ad;
	opacity: 1;
}

.gl-product-card__price del .amount,
.gl-product-card__price del bdi,
.gl-product-card__price del .woocommerce-Price-amount {
	font-size: 13px !important;
	font-weight: 400 !important;
	letter-spacing: 0;
	color: #9aa3ad !important;
}
	
.woocommerce ul.products li.product .button{
	margin-top: 0;
}

.gl-product-card__price ins {
	display: block;
	order: 2;
	margin: 0;
	text-decoration: none;
}

.gl-product-card__price ins .amount,
.gl-product-card__price ins bdi,
.gl-product-card__price ins .woocommerce-Price-amount {
	font-size: 24px !important;
	font-weight: 700 !important;
	letter-spacing: -0.03em;
	color: #171d2a !important;
}
	
.gl-product-card__price del .woocommerce-Price-currencySymbol{
	font-size: 10px;
}

.gl-product-card__price .screen-reader-text {
	display: none;
}

.gl-product-card__actions {
	margin-top: 0;
	flex: 0 0 auto;
	display: flex;
	align-items: center;
	justify-content: flex-end;
}

.gl-product-card__button,
a.gl-product-card__button {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: auto;
	min-width: 112px;
	min-height: 38px;
	padding: 8px 16px;
	border: 1.5px solid var(--gl-color-buy-button) !important;
	border-radius: 999px;
	background: var(--gl-color-buy-button) !important;
	color: #fff !important;
	text-decoration: none;
	font-size: 14px;
	font-weight: 600;
	line-height: 1;
	cursor: pointer;
	white-space: nowrap;
	flex-shrink: 0;
	transition: transform .2s ease, color .2s ease, background-color .2s ease, border-color .2s ease;
}

.gl-catalog-products .gl-product-card__button,
.gl-catalog-products a.gl-product-card__button {
	border: 1.5px solid var(--gl-color-buy-button) !important;
	background: var(--gl-color-buy-button) !important;
	color: #fff !important;
}

.gl-product-card__button:hover,
a.gl-product-card__button:hover {
	color: #fff !important;
	background: var(--gl-color-buy-button) !important;
	border-color: var(--gl-color-buy-button) !important;
	transform: translateY(-1px);
}

.gl-catalog-products .gl-product-card__button:hover,
.gl-catalog-products a.gl-product-card__button:hover {
	color: #fff !important;
	background: var(--gl-color-buy-button) !important;
	border-color: var(--gl-color-buy-button) !important;
	transform: translateY(-1px);
}

.gl-product-card__button--disabled {
	background: #cfd6d1 !important;
	border-color: #cfd6d1 !important;
	color: #fff !important;
	pointer-events: none;
}

.woocommerce ul.products li.product .button {
	display: inline-flex;
}

.woocommerce ul.products li.product a img {
	margin: 0;
}

@media (max-width: 767px) {
	.gl-product-card__inner {
		padding: 14px;
		border-radius: 22px;
	}

	.gl-product-card__media {
		height: clamp(260px, 58vw, 340px);
		min-height: 260px;
		padding: 6px;
		border-radius: 18px;
	}

	.gl-product-card__image,
	.gl-product-card__media img {
		height: 100%;
	}

	.gl-product-card__title {
		font-size: 14px;
		line-height: 1.25;
		min-height: calc(1.25em * 2);
	}

	.gl-product-card__purchase {
		align-items: stretch;
		gap: 10px;
	}

	.gl-product-card__price > .amount,
	.gl-product-card__price > bdi,
	.gl-product-card__price .price > .amount,
	.gl-product-card__price .price > bdi,
	.gl-product-card__price ins .amount,
	.gl-product-card__price ins bdi,
	.gl-product-card__price ins .woocommerce-Price-amount {
		font-size: 22px !important;
	}

	.gl-product-card__price del,
	.gl-product-card__price del .amount,
	.gl-product-card__price del bdi,
	.gl-product-card__price del .woocommerce-Price-amount {
		font-size: 12px !important;
	}

	.gl-product-card__button,
	a.gl-product-card__button {
		min-height: 36px;
		min-width: 104px;
		font-size: 14px;
	}
}
</style>