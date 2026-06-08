<?php
/**
 * Review order table
 *
 * @package Gelikon
 */

defined('ABSPATH') || exit;
?>

<table class="shop_table woocommerce-checkout-review-order-table gl-order-review-table">
	<thead>
		<tr>
			<th class="product-name"><?php esc_html_e('Товар', 'woocommerce'); ?></th>
			<th class="product-total"></th>
		</tr>
	</thead>

	<tbody>
		<?php
		do_action('woocommerce_review_order_before_cart_contents');

		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

			if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
				?>
				<tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item gl-checkout-cart-item', $cart_item, $cart_item_key)); ?>">
					<td class="product-name">
						<div class="gl-checkout-cart-item__main">
							<button
								type="button"
								class="gl-checkout-cart-item__remove"
								data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
								aria-label="Удалить товар"
							>
								×
							</button>

							<div class="gl-checkout-cart-item__content">
								<div class="gl-checkout-cart-item__name">
									<?php
									echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key));
									echo wc_get_formatted_cart_item_data($cart_item);
									?>
								</div>

								<div class="gl-checkout-cart-item__qty" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
									<button type="button" class="gl-checkout-cart-item__qty-btn" data-qty-action="minus" aria-label="Уменьшить количество">−</button>
									<span class="gl-checkout-cart-item__qty-value"><?php echo esc_html($cart_item['quantity']); ?></span>
									<button type="button" class="gl-checkout-cart-item__qty-btn" data-qty-action="plus" aria-label="Увеличить количество">+</button>
								</div>
							</div>
						</div>
					</td>

					<td class="product-total">
						<?php echo wp_kses_post(apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key)); ?>
					</td>
				</tr>
				<?php
			}
		}

		do_action('woocommerce_review_order_after_cart_contents');
		?>
	</tbody>

	<tfoot>
		<?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
				<th><?php wc_cart_totals_coupon_label($coupon); ?></th>
				<td><?php wc_cart_totals_coupon_html($coupon); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
			<?php do_action('woocommerce_review_order_before_shipping'); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action('woocommerce_review_order_after_shipping'); ?>
		<?php endif; ?>

		<?php foreach (WC()->cart->get_fees() as $fee) : ?>
			<tr class="fee">
				<th><?php echo esc_html($fee->name); ?></th>
				<td><?php wc_cart_totals_fee_html($fee); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
			<?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
				<?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
						<th><?php echo esc_html($tax->label); ?></th>
						<td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action('woocommerce_review_order_before_order_total'); ?>

		<tr class="order-total">
			<th><?php esc_html_e('Итого', 'woocommerce'); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action('woocommerce_review_order_after_order_total'); ?>
	</tfoot>
</table>

<style>
/* Общая таблица */
.gl-order-review-table {
	width: 100%;
	table-layout: fixed;
	color: #171b20;
}

.gl-order-review-table th,
.gl-order-review-table td {
	box-sizing: border-box;
}

.gl-order-review-table .product-name,
.gl-order-review-table tfoot th {
	width: 42%;
}

.gl-order-review-table .product-total,
.gl-order-review-table tfoot td {
	width: 58%;
}
	


/* Заголовок */
.gl-order-review-table thead th {
	font-size: 13px;
	line-height: 1.35;
	font-weight: 600;
	color: #6b7480;
}

.gl-order-review-table thead th.product-total {
	font-size: 0 !important;
	color: transparent !important;
}

/* Товар */
.gl-order-review-table tbody .product-name {
	font-size: 14px;
	line-height: 1.35;
	font-weight: 500;
	color: #252b33;
	vertical-align: top;
}

.gl-order-review-table tbody .product-total {
	font-size: 14px;
	line-height: 1.35;
	font-weight: 700;
	color: #252b33;
	white-space: nowrap;
	text-align: right;
	vertical-align: top;
}

.gl-checkout-cart-item__main {
	display: flex;
	align-items: flex-start;
	gap: 8px;
}

.gl-checkout-cart-item__remove {
	width: 22px;
	height: 22px;
	min-width: 22px;
	margin: -2px 0 0;
	padding: 0;
	border: 0;
	border-radius: 50%;
	background: transparent;
	color: #7a8491;
	font-size: 18px;
	line-height: 20px;
	font-weight: 500;
	cursor: pointer;
	transition: color .2s ease, opacity .2s ease;
}

.gl-checkout-cart-item__remove:hover {
	color: #171b20;
}

.gl-checkout-cart-item__remove.is-loading {
	pointer-events: none;
	opacity: .45;
}

.gl-checkout-cart-item__qty.is-loading {
	opacity: .45;
}

.gl-checkout-cart-item.is-removing,
.gl-checkout-cart-item.is-updating {
	opacity: .55;
}

.gl-checkout-cart-item__content,
.gl-checkout-cart-item__name {
	min-width: 0;
}

.gl-checkout-cart-item__qty {
	display: inline-flex;
	align-items: center;
	gap: 4px;
	margin-top: 8px;
	padding: 3px;
	border-radius: 999px;
	background: #f4f6f8;
	vertical-align: middle;
}

.gl-checkout-cart-item__qty-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 24px;
	height: 24px;
	padding: 0;
	border: 0;
	border-radius: 50%;
	background: #fff;
	color: #171b20;
	font-size: 16px;
	line-height: 1;
	font-weight: 700;
	cursor: pointer;
	box-shadow: 0 2px 8px rgba(23, 29, 42, .08);
	transition: background .2s ease, color .2s ease, opacity .2s ease;
}

.gl-checkout-cart-item__qty-btn:hover {
	background: #12D457;
	color: #fff;
}

.gl-checkout-cart-item__qty-value {
	min-width: 22px;
	text-align: center;
	font-size: 13px;
	line-height: 1;
	font-weight: 700;
	color: #252b33;
}

/* Подытог WooCommerce скрываем */
.gl-order-review-table .cart-subtotal {
	display: none !important;
}

/* Footer таблицы */
.gl-order-review-table tfoot th,
.gl-order-review-table tfoot td {
	font-size: 14px;
	line-height: 1.35;
	font-weight: 500;
	color: #5f6975;
	vertical-align: top;
}

.gl-order-review-table tfoot td {
	text-align: right;
}

.gl-order-review-table tfoot tr:not(.order-total) th,
.gl-order-review-table tfoot tr:not(.order-total) td {
	padding-top: 16px;
	padding-bottom: 16px;
}

/* Доставка */
.gl-order-review-table tfoot .shipping th {
	font-size: 16px;
	line-height: 1.3;
	font-weight: 800;
	color: #5f6975;
	text-align: left;
	vertical-align: top;
}

.gl-order-review-table tfoot .shipping td {
	text-align: right !important;
	vertical-align: top;
}

.gl-order-review-table tfoot .shipping td > span,
.gl-order-review-table tfoot .shipping td > strong {
	display: inline-block;
	text-align: right;
}

.gl-order-review-table #shipping_method {
	display: flex;
	flex-direction: column;
	gap: 12px;
	width: 100%;
	margin: 0;
	padding: 0;
	list-style: none;
}

.gl-order-review-table #shipping_method li {
	display: grid;
	grid-template-columns: 20px minmax(0, 1fr);
	gap: 10px;
	align-items: flex-start;
	width: 100%;
	margin: 0 !important;
	padding: 0;
	text-align: left;
}

.gl-order-review-table #shipping_method li::after {
	content: "";
	display: block;
	clear: both;
}

.gl-order-review-table #shipping_method input[type="radio"] {
	width: 16px;
	height: 16px;
	min-width: 16px;
	margin: 3px 0 0;
	accent-color: #12D457;
}

.gl-order-review-table #shipping_method label {
	display: flex;
	align-items: flex-start;
	justify-content: space-between;
	gap: 12px;
	min-width: 0;
	width: 100%;
	margin: 0;
	font-size: 13px;
	line-height: 1.35;
	font-weight: 500;
	color: #252b33;
	text-align: left;
	white-space: normal;
	word-break: normal;
	overflow-wrap: anywhere;
}

.gl-order-review-table #shipping_method label .amount {
	display: inline-block;
	margin-left: auto;
	font-size: 13px;
	line-height: 1.2;
	font-weight: 800;
	color: #171b20;
	white-space: nowrap;
	text-align: right;
}

.gl-order-review-table .cdek-office-info {
	grid-column: 2 / -1;
	display: flex;
	align-items: flex-start;
	gap: 9px;
	width: 100%;
	max-width: 100%;
	min-width: 0;
	margin: 8px 0 0;
	padding: 11px 13px;
	border-radius: 15px;
	background: #f4f8f5;
	border: 1px solid rgba(18, 212, 87, .22);
	font-size: 12px;
	line-height: 1.35;
	font-weight: 600;
	color: #252b33;
	text-align: left;
	overflow-wrap: anywhere;
	word-break: break-word;
}

.gl-order-review-table .cdek-office-info::before {
	content: "";
	width: 16px;
	height: 16px;
	min-width: 16px;
	margin-top: 1px;
	background: #12D457;
	border-radius: 50%;
	box-shadow: 0 0 0 5px rgba(18, 212, 87, .12);
}

.gl-order-review-table .open-pvz-btn {
	grid-column: 2 / -1;
	display: inline-flex !important;
	align-items: center;
	justify-content: center;
	width: 100%;
	min-height: 40px;
	margin: 8px 0 0;
	padding: 9px 16px;
	border-radius: 999px;
	background: #12D457;
	color: #fff !important;
	font-size: 13px;
	line-height: 1.2;
	font-weight: 800;
	text-align: center;
	text-decoration: none !important;
	white-space: nowrap;
	cursor: pointer;
	box-shadow: 0 5px 12px rgba(18, 212, 87, .22);
	transition: background .2s ease, transform .2s ease;
}

.gl-order-review-table .open-pvz-btn:hover {
	background: #10bf4f;
	transform: translateY(-1px);
}

.gl-order-review-table .open-pvz-btn script {
	display: none !important;
}

/* Скидки, сборы, налоги */
.gl-order-review-table tfoot .fee th,
.gl-order-review-table tfoot .fee td,
.gl-order-review-table tfoot .tax-total th,
.gl-order-review-table tfoot .tax-total td,
.gl-order-review-table tfoot .cart-discount th,
.gl-order-review-table tfoot .cart-discount td {
	font-size: 14px;
	font-weight: 600;
	color: #6b7480;
}

.gl-order-review-table tfoot .fee td,
.gl-order-review-table tfoot .cart-discount td {
	color: #276c3b;
	font-weight: 700;
}

/* Итого */
.gl-order-review-table tfoot .order-total th {
	padding-top: 20px;
	font-size: 16px;
	line-height: 1.25;
	font-weight: 800;
	color: #171b20;
	vertical-align: middle;
}

.gl-order-review-table tfoot .order-total td {
	padding-top: 20px;
	font-size: 20px;
	line-height: 1.2;
	font-weight: 800;
	color: #171b20;
	white-space: nowrap;
	text-align: right;
	vertical-align: middle;
}

.gl-order-review-table .order-total td small,
.gl-order-review-table .order-total td .includes_tax {
	display: block !important;
	margin-top: 3px;
	margin-left: 0;
	font-size: 12px;
	line-height: 1.3;
	font-weight: 500;
	color: #7a8491;
	white-space: nowrap;
}

/* Оплата */
.woocommerce-checkout #payment ul.payment_methods li label {
	font-size: 14px;
	line-height: 1.35;
	font-weight: 500;
	color: #252b33;
	flex-wrap: nowrap;
}

.woocommerce-checkout #payment ul.payment_methods li label .payment-method-title,
.woocommerce-checkout #payment ul.payment_methods li label span {
	white-space: nowrap;
}

/* Описание сайдбара */
.gl-checkout-sidebar__desc {
	margin: 0 0 16px;
	font-size: 14px;
	line-height: 1.4;
	font-weight: 400;
	color: #6b7480;
	white-space: nowrap;
}


/* CDEK widget polish */
.cdek-map .ymaps-2-1-79-copyrights-pane,
.cdek-map [class*="copyright"],
.cdek-map [class*="logo"] {
	pointer-events: none;
}

.cdek-map [class*="geolocation"],
.cdek-map [class*="location"],
.cdek-map button[title*="местоп" i],
.cdek-map button[aria-label*="местоп" i] {
	margin-top: 42px !important;
}

/* Мобильная версия */
@media (max-width: 480px) {
	.gl-checkout-sidebar__desc {
		white-space: normal;
	}

	.gl-order-review-table {
		table-layout: auto;
	}

	.gl-order-review-table .product-name,
	.gl-order-review-table .product-total,
	.gl-order-review-table tfoot th,
	.gl-order-review-table tfoot td {
		width: auto;
	}

	.gl-order-review-table tbody .product-name,
	.gl-order-review-table tbody .product-total,
	.gl-order-review-table tfoot th,
	.gl-order-review-table tfoot td {
		font-size: 13px;
	}

	.gl-order-review-table #shipping_method {
		gap: 10px;
	}

	.gl-order-review-table #shipping_method li {
		grid-template-columns: 18px minmax(0, 1fr);
		gap: 8px;
	}

	.gl-order-review-table #shipping_method input[type="radio"] {
		width: 15px;
		height: 15px;
		min-width: 15px;
	}

	.gl-order-review-table #shipping_method label {
		font-size: 12px;
		line-height: 1.35;
	}

	.gl-order-review-table .cdek-office-info {
		padding: 9px 10px;
		font-size: 12px;
	}

	.gl-order-review-table .open-pvz-btn {
		min-height: 38px;
		font-size: 12px;
	}

	.gl-order-review-table tfoot .order-total th {
		font-size: 15px;
	}

	.gl-order-review-table tfoot .order-total td {
		font-size: 18px;
	}

	.gl-order-review-table .order-total td small,
	.gl-order-review-table .order-total td .includes_tax {
		font-size: 11px;
		white-space: normal;
	}

	.gl-checkout-cart-item__qty-btn {
		width: 23px;
		height: 23px;
		font-size: 15px;
	}

	.gl-checkout-cart-item__qty-value {
		min-width: 20px;
		font-size: 12px;
	}
}
</style>

<script>
jQuery(function($) {
	function glCloseCdekWidget() {
		$('.cdek-modal, .cdek-widget-modal, .cdek-widget__popup, .cdek-popup, .modal-cdek, .cdekmap-modal, #cdek-map, #cdek-map-modal').each(function() {
			const $modal = $(this);
			$modal.find('.cdek-close, .cdek-modal__close, .cdek-widget__close, .close, [data-dismiss="modal"], [aria-label="Close"], [aria-label="Закрыть"]').first().trigger('click');
			$modal.removeClass('show is-open active').hide();
		});
		$('body').removeClass('modal-open cdek-modal-open');
	}

	$(document).on('click', '.cdek-map [class*="choose"], .cdek-map [class*="select"], .cdek-map button, .cdek-map a', function() {
		setTimeout(function() {
			if ($('.cdek-office-info').text().trim().length) {
				glCloseCdekWidget();
			}
		}, 500);
	});

	$(document.body).on('updated_checkout', function() {
		$('.open-pvz-btn').attr('title', 'Выбрать пункт выдачи СДЭК');
	});

	const checkoutCart = window.gelikonCheckoutCart || {
		qtyTimers: {},
		qtyRequests: {},
		qtyDelay: 500
	};

	window.gelikonCheckoutCart = checkoutCart;

	const qtyTimers = checkoutCart.qtyTimers;
	const qtyRequests = checkoutCart.qtyRequests;
	const qtyDelay = checkoutCart.qtyDelay;
	const eventNamespace = '.gelikonCheckoutCart';

	function updateHeaderCartCount(count) {
		const safeCount = Math.max(parseInt(count, 10) || 0, 0);
		$('.gl-cart-count').text(String(safeCount));
	}

	$(document).off('click' + eventNamespace, '.gl-checkout-cart-item__remove');
	$(document).off('click' + eventNamespace, '.gl-checkout-cart-item__qty-btn');

	$(document).on('click' + eventNamespace, '.gl-checkout-cart-item__remove', function(e) {
		e.preventDefault();

		const $button = $(this);
		const cartItemKey = $button.data('cart-item-key');
		const $row = $button.closest('.gl-checkout-cart-item');

		if (!cartItemKey || $button.hasClass('is-loading')) {
			return;
		}

		$button.addClass('is-loading');
		$row.addClass('is-removing');

		$.ajax({
			type: 'POST',
			url: wc_checkout_params.ajax_url,
			dataType: 'json',
			data: {
				action: 'gelikon_checkout_remove_cart_item',
				cart_item_key: cartItemKey,
				nonce: wc_checkout_params.update_order_review_nonce
			},
			success: function(response) {
				if (response && response.success) {
					if (response.data && typeof response.data.count !== 'undefined') {
						updateHeaderCartCount(response.data.count);
					}

					$(document.body).trigger('update_checkout');
					$(document.body).trigger('wc_fragment_refresh');
				} else {
					$button.removeClass('is-loading');
					$row.removeClass('is-removing');
				}
			},
			error: function() {
				$button.removeClass('is-loading');
				$row.removeClass('is-removing');
			}
		});
	});

	$(document).on('click' + eventNamespace, '.gl-checkout-cart-item__qty-btn', function(e) {
		e.preventDefault();

		const $button = $(this);
		const $qty = $button.closest('.gl-checkout-cart-item__qty');
		const $row = $button.closest('.gl-checkout-cart-item');
		const $value = $qty.find('.gl-checkout-cart-item__qty-value');

		const cartItemKey = $qty.data('cart-item-key');
		const action = $button.data('qty-action');

		if (!cartItemKey) {
			return;
		}

		let currentQty = parseInt($qty.data('pending-quantity'), 10);

		if (Number.isNaN(currentQty)) {
			currentQty = parseInt($value.text(), 10) || 1;
		}

		let newQty = currentQty;

		if (action === 'plus') {
			newQty++;
		}

		if (action === 'minus') {
			newQty--;
		}

		if (newQty < 0) {
			return;
		}

		$value.text(newQty);
		$qty.data('pending-quantity', newQty);
		$qty.removeClass('is-loading');
		$row.removeClass('is-updating');

		if (newQty === 0) {
			$row.addClass('is-removing');
		} else {
			$row.removeClass('is-removing');
		}

		clearTimeout(qtyTimers[cartItemKey]);

		qtyTimers[cartItemKey] = setTimeout(function() {
			const finalQty = parseInt($qty.data('pending-quantity'), 10);

			$qty.addClass('is-loading');
			$row.addClass('is-updating');

			if (qtyRequests[cartItemKey]) {
				qtyRequests[cartItemKey].abort();
			}

			qtyRequests[cartItemKey] = $.ajax({
				type: 'POST',
				url: wc_checkout_params.ajax_url,
				dataType: 'json',
				data: {
					action: 'gelikon_checkout_update_cart_item_qty',
					cart_item_key: cartItemKey,
					quantity: finalQty,
					nonce: wc_checkout_params.update_order_review_nonce
				},
				success: function(response) {
					if (response && response.success) {
						if (response.data && typeof response.data.count !== 'undefined') {
							updateHeaderCartCount(response.data.count);
						}

						$(document.body).trigger('update_checkout');
						$(document.body).trigger('wc_fragment_refresh');
					} else {
						$qty.removeClass('is-loading');
						$row.removeClass('is-updating is-removing');
					}
				},
				error: function(xhr, status) {
					if (status !== 'abort') {
						$qty.removeClass('is-loading');
						$row.removeClass('is-updating is-removing');
					}
				},
				complete: function() {
					qtyRequests[cartItemKey] = null;
				}
			});
		}, qtyDelay);
	});
});
</script>