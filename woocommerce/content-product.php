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

.gl-product-card__meta {
	font-size: 14px;
	line-height: 1.4;
	flex-shrink: 0;
}

.gl-catalog-products .gl-product-card__stock.is-in-stock {
	color: #0f9f57 !important;
	font-weight: 400 !important;
}

.gl-product-card__stock.is-out-of-stock {
	color: var(--gl-color-helper);
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
	border: 1.5px solid #22C55E;
	border-radius: 999px;
	background: transparent;
	color: #22C55E !important;
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
	border: 1.5px solid #12D457;
	color: #12D457 !important;
}

.gl-product-card__button:hover,
a.gl-product-card__button:hover {
	color: #fff !important;
	background: #16A34A;
	border-color: #16A34A;
	transform: translateY(-1px);
}

.gl-catalog-products .gl-product-card__button:hover,
.gl-catalog-products a.gl-product-card__button:hover {
	color: #fff !important;
	background: #12D457;
	border-color: #12D457;
	transform: translateY(-1px);
}

.gl-product-card__button--disabled {
	background: #cfd6d1;
	border-color: #cfd6d1;
	color: #fff !important;
	pointer-events: none;
}

.woocommerce ul.products li.product .button {
	display: inline-flex;
}

.woocommerce ul.products li.product a img {
	margin: 0;
}

.gl-product-card__actions .added_to_cart.wc-forward {
	display: none !important;
}

.gl-product-card__button.is-in-cart,
.gl-product-card__button.added {
	background: #12D457;
	border-color: #12D457;
	color: #fff !important;
}

.gl-mini-cart {
	position: fixed;
	right: 16px;
	bottom: 16px;
	z-index: 9999;
	display: grid;
	grid-template-columns: 72px 1fr;
	gap: 12px;
	max-width: 380px;
	width: calc(100% - 32px);
	padding: 12px;
	border-radius: 16px;
	background: #fff;
	box-shadow: 0 18px 45px rgba(23, 29, 42, .18);
	opacity: 0;
	transform: translateY(12px);
	pointer-events: none;
	transition: opacity .2s ease, transform .2s ease;
}

.gl-mini-cart.is-visible {
	opacity: 1;
	transform: translateY(0);
	pointer-events: auto;
}

.gl-mini-cart__media img {
	width: 72px;
	height: 72px;
	object-fit: contain;
	border-radius: 10px;
	background: #f4f7f6;
}

.gl-mini-cart__status,
.gl-mini-cart__title,
.gl-mini-cart__price { margin: 0; }

.gl-mini-cart__status { color: #0f9f57; font-size: 13px; font-weight: 600; }
.gl-mini-cart__title { color: #171d2a; font-size: 14px; font-weight: 600; line-height: 1.3; margin-top: 2px; }
.gl-mini-cart__price { color: #171d2a; font-size: 16px; font-weight: 700; margin-top: 6px; }

.gl-mini-cart__actions { display: flex; gap: 8px; margin-top: 10px; }
.gl-mini-cart__checkout,
.gl-mini-cart__continue {
	border: 1px solid #12D457;
	border-radius: 999px;
	padding: 8px 10px;
	font-size: 12px;
	font-weight: 600;
	text-decoration: none;
	cursor: pointer;
}
.gl-mini-cart__checkout { background: #12D457; color: #fff; }
.gl-mini-cart__continue { background: #fff; color: #12D457; }

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

	.gl-mini-cart {
		right: 12px;
		left: 12px;
		bottom: 12px;
		width: auto;
		max-width: none;
		grid-template-columns: 64px 1fr;
	}

	.gl-mini-cart__media img {
		width: 64px;
		height: 64px;
	}
}
</style>

<script>
if (!window.glCartUxInitialized) {
	window.glCartUxInitialized = true;
	(function () {
		const MINI_CART_ID = 'gl-mini-cart-feedback';

		const ensureMiniCart = function () {
			let miniCart = document.getElementById(MINI_CART_ID);

			if (!miniCart) {
				miniCart = document.createElement('div');
				miniCart.id = MINI_CART_ID;
				miniCart.className = 'gl-mini-cart';
				miniCart.innerHTML = '<div class="gl-mini-cart__media"><img alt="" loading="lazy"></div><div class="gl-mini-cart__content"><p class="gl-mini-cart__status">Товар добавлен в корзину</p><p class="gl-mini-cart__title"></p><p class="gl-mini-cart__price"></p><div class="gl-mini-cart__actions"><a class="gl-mini-cart__checkout" href="/checkout/">Оформить заказ</a><button type="button" class="gl-mini-cart__continue">Продолжить покупки</button></div></div>';
				document.body.appendChild(miniCart);
				miniCart.querySelector('.gl-mini-cart__continue').addEventListener('click', function () {
					miniCart.classList.remove('is-visible');
				});
			}

			return miniCart;
		};

		const setButtonInCartState = function (button) {
			if (!button) return;
			button.classList.add('is-in-cart');
			button.textContent = 'В корзине';
		};

		const getProductData = function (sourceElement) {
			const card = sourceElement ? sourceElement.closest('.gl-product-card') : null;
			if (card) {
				return {
					title: (card.querySelector('.gl-product-card__title') || {}).textContent || '',
					price: (card.querySelector('.gl-product-card__price') || {}).textContent || '',
					image: (card.querySelector('.gl-product-card__image') || {}).src || ''
				};
			}

			const productRoot = document.querySelector('.gl-product-page, .product');
			return {
				title: ((productRoot && productRoot.querySelector('h1')) || {}).textContent || '',
				price: ((productRoot && productRoot.querySelector('.price')) || {}).textContent || '',
				image: ((productRoot && productRoot.querySelector('.woocommerce-product-gallery__image img')) || {}).src || ''
			};
		};

		const showMiniCart = function (productData) {
			const miniCart = ensureMiniCart();
			miniCart.querySelector('.gl-mini-cart__title').textContent = (productData.title || '').trim();
			miniCart.querySelector('.gl-mini-cart__price').textContent = (productData.price || '').trim();
			if (productData.image) miniCart.querySelector('.gl-mini-cart__media img').src = productData.image;
			if (window.wc_add_to_cart_params && window.wc_add_to_cart_params.checkout_url) {
				miniCart.querySelector('.gl-mini-cart__checkout').href = window.wc_add_to_cart_params.checkout_url;
			}

			miniCart.classList.add('is-visible');
			window.clearTimeout(miniCart._timer);
			miniCart._timer = window.setTimeout(function () { miniCart.classList.remove('is-visible'); }, 4500);
		};

		document.querySelectorAll('.gl-product-card__button.added').forEach(setButtonInCartState);

		document.body.addEventListener('added_to_cart', function () {
			const clickedButton = document.querySelector('.gl-product-card__button.ajax_add_to_cart.loading') || document.activeElement;
			if (clickedButton && clickedButton.classList && clickedButton.classList.contains('ajax_add_to_cart')) {
				setButtonInCartState(clickedButton);
				showMiniCart(getProductData(clickedButton));
			}
		});

		document.body.addEventListener('submit', function (event) {
			const cartForm = event.target.closest('form.cart');
			if (!cartForm) return;
			const submitButton = cartForm.querySelector('.single_add_to_cart_button');
			if (!submitButton || submitButton.disabled) return;
			showMiniCart(getProductData(submitButton));
		});
	})();
}
</script>
