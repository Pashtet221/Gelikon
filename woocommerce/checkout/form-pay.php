<?php
/**
 * Pay for order form.
 *
 * @package Gelikon
 */

defined('ABSPATH') || exit;

$totals = $order->get_order_item_totals();
?>

<form id="order_review" method="post" class="gl-order-pay woocommerce-checkout" action="<?php echo esc_url($order->get_checkout_payment_url()); ?>">
	<style>
		.gl-order-pay {
			max-width: 1180px;
			margin: 32px auto;
			color: #171b20;
		}

		.gl-order-pay * {
			box-sizing: border-box;
		}

		.gl-order-pay__shell {
			display: grid;
			grid-template-columns: minmax(0, 1fr) 420px;
			gap: 28px;
			align-items: start;
		}

		.gl-order-pay__card {
			background: #fff;
			border: 1px solid #e1e7ec;
			border-radius: 22px;
			padding: 22px;
			box-shadow: 0 14px 36px rgba(24, 39, 75, .08);
		}

		.gl-order-pay__title {
			margin: 0 0 8px;
			font-size: 28px;
			line-height: 1.2;
			font-weight: 900;
			color: #15191f;
		}

		.gl-order-pay__desc {
			margin: 0 0 18px;
			font-size: 15px;
			line-height: 1.45;
			color: #6b7480;
		}

		.gl-order-pay__meta {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			gap: 12px;
			margin-bottom: 18px;
		}

		.gl-order-pay__meta-item {
			padding: 14px;
			border: 1px solid #e3ebe7;
			border-radius: 16px;
			background: #f7fbf8;
		}

		.gl-order-pay__meta-label {
			display: block;
			margin-bottom: 4px;
			font-size: 12px;
			line-height: 1.3;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: .04em;
			color: #7a8491;
		}

		.gl-order-pay__meta-value {
			display: block;
			font-size: 16px;
			line-height: 1.35;
			font-weight: 800;
			color: #171b20;
		}

		.gl-order-pay table.shop_table {
			width: 100% !important;
			border-collapse: separate;
			border-spacing: 0;
			table-layout: fixed;
			border: 1px solid #e2e6ea !important;
			border-radius: 16px !important;
			overflow: hidden;
			background: #fff;
			margin: 0 !important;
		}

		.gl-order-pay table.shop_table th,
		.gl-order-pay table.shop_table td {
			padding: 14px 16px;
			border-color: #edf0f3;
			font-size: 15px;
			line-height: 1.4;
			vertical-align: top;
		}

		.gl-order-pay table.shop_table thead th {
			background: #f7f9fb;
			font-size: 13px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: .04em;
			color: #7a8491;
		}

		.gl-order-pay .product-name,
		.gl-order-pay tfoot th {
			width: 62%;
			text-align: left;
		}

		.gl-order-pay .product-quantity,
		.gl-order-pay .product-total,
		.gl-order-pay tfoot td {
			width: 38%;
			text-align: right;
		}

		.gl-order-pay tbody .product-name {
			font-weight: 600;
			color: #252b33;
		}

		.gl-order-pay .product-quantity {
			font-weight: 800;
			white-space: nowrap;
			color: #6b7480;
		}

		.gl-order-pay tbody .product-total,
		.gl-order-pay tfoot td {
			font-weight: 800;
			white-space: nowrap;
			color: #171b20;
		}

		.gl-order-pay tfoot th {
			font-weight: 700;
			color: #5f6975;
		}

		.gl-order-pay tfoot tr:last-child th,
		.gl-order-pay tfoot tr:last-child td {
			background: #f7fbf8;
			font-size: 18px;
			font-weight: 900;
			color: #11161c;
		}

		.gl-order-pay__payment {
			position: sticky;
			top: 20px;
		}

		.gl-order-pay__payment #payment {
			background: #f5fbf7 !important;
			border: 1px solid #dbece2 !important;
			border-radius: 20px !important;
			padding: 0;
			overflow: hidden;
		}

		.gl-order-pay__payment #payment ul.payment_methods {
			padding: 18px;
			border-bottom: 1px solid #dbece2;
		}

		.gl-order-pay__payment #payment ul.payment_methods li {
			position: relative;
			margin: 0 0 12px;
			padding: 16px 16px 16px 50px;
			background: #fff;
			border: 1px solid #e3ebe7;
			border-radius: 16px;
			list-style: none;
		}

		.gl-order-pay__payment #payment ul.payment_methods li:has(input.input-radio:checked) {
			border-color: #1f7a3d;
			box-shadow: 0 0 0 3px rgba(31, 122, 61, .10);
		}

		.gl-order-pay__payment #payment ul.payment_methods li:last-child {
			margin-bottom: 0;
		}

		.gl-order-pay__payment #payment ul.payment_methods li input.input-radio {
			position: absolute;
			left: 18px;
			width: 18px;
			height: 18px;
			margin: 0;
			accent-color: #42b957;
		}

		.gl-order-pay__payment #payment ul.payment_methods li label {
			display: flex;
			align-items: center;
			gap: 10px;
			margin: 0;
			font-size: 16px;
			line-height: 1.35;
			font-weight: 700;
			color: #252b33;
		}

		.gl-order-pay__payment #payment ul.payment_methods li label img {
			max-height: 30px;
			width: auto;
			margin: 0 !important;
		}

		.gl-order-pay__payment #payment div.payment_box {
			margin: 14px 0 0;
			padding: 14px 15px;
			background: #f4f6f8;
			border-radius: 14px;
			color: #5f6975;
			font-size: 14px;
			line-height: 1.45;
		}

		.gl-order-pay__payment #payment div.payment_box::before,
		.gl-order-pay .cr-customer-consent {
			display: none !important;
		}

		.gl-order-pay__payment #payment .form-row {
			margin: 0 !important;
			padding: 20px 18px;
			background: #f5fbf7;
		}

		.gl-order-pay__payment #payment #place_order,
		.gl-order-pay__payment #payment .button.alt {
			width: 100%;
			min-height: 56px;
			border: none;
			border-radius: 15px;
			background: #1f7a3d;
			color: #fff;
			font-size: 17px;
			font-weight: 800;
			transition: background .2s ease;
		}

		.gl-order-pay__payment #payment #place_order:hover,
		.gl-order-pay__payment #payment .button.alt:hover {
			background: #176431;
			color: #fff;
		}

		@media (max-width: 1024px) {
			.gl-order-pay__shell {
				grid-template-columns: 1fr;
			}

			.gl-order-pay__payment {
				position: static;
			}
		}

		@media (max-width: 768px) {
			.gl-order-pay {
				margin-top: 24px;
			}

			.gl-order-pay__card {
				padding: 16px;
				border-radius: 18px;
			}

			.gl-order-pay__title {
				font-size: 24px;
			}

			.gl-order-pay__meta {
				grid-template-columns: 1fr;
			}

			.gl-order-pay table.shop_table th,
			.gl-order-pay table.shop_table td {
				padding: 12px;
				font-size: 14px;
			}

			.gl-order-pay .product-name,
			.gl-order-pay tfoot th {
				width: 58%;
			}

			.gl-order-pay .product-quantity,
			.gl-order-pay .product-total,
			.gl-order-pay tfoot td {
				width: 42%;
			}
		}
	</style>

	<div class="gl-order-pay__shell">
		<section class="gl-order-pay__card">
			<h1 class="gl-order-pay__title"><?php esc_html_e('Оплатить заказ', 'gelikon'); ?></h1>
			<p class="gl-order-pay__desc"><?php esc_html_e('Проверьте состав заказа и выберите удобный способ оплаты.', 'gelikon'); ?></p>

			<div class="gl-order-pay__meta" aria-label="<?php echo esc_attr__('Данные заказа', 'gelikon'); ?>">
				<div class="gl-order-pay__meta-item">
					<span class="gl-order-pay__meta-label"><?php esc_html_e('Заказ', 'woocommerce'); ?></span>
					<span class="gl-order-pay__meta-value">№<?php echo esc_html($order->get_order_number()); ?></span>
				</div>
				<div class="gl-order-pay__meta-item">
					<span class="gl-order-pay__meta-label"><?php esc_html_e('Дата', 'woocommerce'); ?></span>
					<span class="gl-order-pay__meta-value"><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></span>
				</div>
				<div class="gl-order-pay__meta-item">
					<span class="gl-order-pay__meta-label"><?php esc_html_e('Итого', 'woocommerce'); ?></span>
					<span class="gl-order-pay__meta-value"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
				</div>
			</div>

			<table class="shop_table order_details">
				<thead>
					<tr>
						<th class="product-name"><?php esc_html_e('Товар', 'woocommerce'); ?></th>
						<th class="product-total"><?php esc_html_e('Итого', 'woocommerce'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($order->get_items()) > 0) : ?>
						<?php foreach ($order->get_items() as $item_id => $item) : ?>
							<?php if (! apply_filters('woocommerce_order_item_visible', true, $item)) { continue; } ?>
							<tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
								<td class="product-name">
									<?php
									echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false));
									echo wp_kses_post(apply_filters('woocommerce_order_item_quantity_html', ' <strong class="product-quantity">&times;&nbsp;' . esc_html($item->get_quantity()) . '</strong>', $item));
									do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
									wc_display_item_meta($item);
									do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
									?>
								</td>
								<td class="product-total"><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<?php if ($totals) : ?>
						<?php foreach ($totals as $total) : ?>
							<tr>
								<th scope="row"><?php echo wp_kses_post($total['label']); ?></th>
								<td><?php echo wp_kses_post($total['value']); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tfoot>
			</table>
		</section>

		<aside class="gl-order-pay__card gl-order-pay__payment">
			<h2 class="gl-order-pay__title"><?php esc_html_e('Способ оплаты', 'gelikon'); ?></h2>
			<p class="gl-order-pay__desc"><?php esc_html_e('После нажатия кнопки вы перейдете на защищенную страницу платежного сервиса.', 'gelikon'); ?></p>

			<div id="payment">
				<?php if ($order->needs_payment()) : ?>
					<ul class="wc_payment_methods payment_methods methods">
						<?php
						if (! empty($available_gateways)) {
							foreach ($available_gateways as $gateway) {
								wc_get_template('checkout/payment-method.php', array('gateway' => $gateway));
							}
						} else {
							echo '<li>';
							wc_print_notice(apply_filters('woocommerce_no_available_payment_methods_message', esc_html__('Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce')), 'notice');
							echo '</li>';
						}
						?>
					</ul>
				<?php endif; ?>

				<div class="form-row">
					<input type="hidden" name="woocommerce_pay" value="1" />
					<?php wc_get_template('checkout/terms.php'); ?>
					<?php do_action('woocommerce_pay_order_before_submit'); ?>
					<?php echo apply_filters('woocommerce_pay_order_button_html', '<button type="submit" class="button alt' . esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : '') . '" id="place_order" value="' . esc_attr__('Оплатить заказ', 'gelikon') . '" data-value="' . esc_attr__('Оплатить заказ', 'gelikon') . '">' . esc_html__('Оплатить заказ', 'gelikon') . '</button>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php do_action('woocommerce_pay_order_after_submit'); ?>
					<?php wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce'); ?>
				</div>
			</div>
		</aside>
	</div>
</form>
