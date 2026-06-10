<?php
/**
 * Minimal compact checkout form template.
 *
 * @package Gelikon
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_checkout_form', $checkout);

if (
	! $checkout->is_registration_enabled()
	&& $checkout->is_registration_required()
	&& ! is_user_logged_in()
) {
	echo esc_html(
		apply_filters(
			'woocommerce_checkout_must_be_logged_in_message',
			__('You must be logged in to checkout.', 'woocommerce')
		)
	);
	return;
}
?>

<form name="checkout"
	  method="post"
	  class="checkout woocommerce-checkout gl-checkout-compact"
	  action="<?php echo esc_url(wc_get_checkout_url()); ?>"
	  enctype="multipart/form-data"
	  aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">

<style>
.woocommerce-form-coupon-toggle {
	display: none !important;
}

.woocommerce form.checkout,
.woocommerce-checkout-review-order {
	border: none;
	box-shadow: none;
}

.cr-customer-consent {
	display: none !important;
}

.gl-checkout-compact {
	margin-top: 32px;
	background: transparent;
	border: 0;
	padding: 0;
	color: #1c1f23;
}

.gl-checkout-compact * {
	box-sizing: border-box;
}

.gl-checkout-compact__grid {
	display: grid;
	grid-template-columns: minmax(0, 1fr) 460px;
	gap: 28px;
	align-items: start;
}

.gl-checkout-compact__main {
	min-width: 0;
}

.gl-checkout-card {
	background: #fff;
	border: 1px solid #e4e9ee;
	border-radius: 18px;
	padding: 22px;
	margin-bottom: 16px;
	box-shadow: 0 8px 24px rgba(24, 39, 75, .04);
}

.gl-checkout-card__head {
	margin-bottom: 18px;
}

.gl-checkout-card__title {
	margin: 0 0 6px;
	font-size: 23px;
	line-height: 1.2;
	font-weight: 800;
	color: #15191f;
}

.gl-checkout-card__desc {
	margin: 0;
	font-size: 15px;
	line-height: 1.45;
	color: #6b7480;
}

.gl-checkout-compact .woocommerce-billing-fields > h3,
.gl-checkout-compact .shipping_address > h3,
.gl-checkout-compact .woocommerce-additional-fields > h3 {
	display: none;
}

.gl-checkout-compact #ship-to-different-address {
	display: none;
}


.gl-checkout-compact__main > .gl-checkout-card:first-child #billing_address_1_field,
.gl-checkout-compact__main > .gl-checkout-card:first-child #billing_address_2_field,
.gl-checkout-compact__main > .gl-checkout-card:first-child #billing_city_field,
.gl-checkout-compact__main > .gl-checkout-card:first-child #billing_state_field,
.gl-checkout-compact__main > .gl-checkout-card:first-child #billing_postcode_field,
.gl-checkout-compact__main > .gl-checkout-card:first-child #billing_country_field {
	display: none !important;
}

.gl-checkout-compact .shipping_address {
	display: block !important;
}

.gl-checkout-compact .form-row {
	width: 100% !important;
	float: none !important;
	clear: none !important;
	margin: 0 0 12px !important;
}

.gl-checkout-compact label {
	margin-bottom: 6px;
	font-size: 14px;
	line-height: 1.3;
	font-weight: 700;
	color: #2a3038;
}

.gl-checkout-compact .optional {
	font-weight: 500;
	color: #8a94a3;
}

.gl-checkout-compact input.input-text,
.gl-checkout-compact select,
.gl-checkout-compact textarea {
	width: 100%;
	min-height: 50px;
	border: 1px solid #cfd6dc;
	border-radius: 12px;
	padding: 11px 14px;
	background: #fff;
	box-shadow: none;
	font-size: 16px;
	line-height: 1.35;
	color: #171b20;
	transition: border-color .2s ease, box-shadow .2s ease;
}

.gl-checkout-compact input.input-text:focus,
.gl-checkout-compact select:focus,
.gl-checkout-compact textarea:focus {
	outline: none;
	border-color: #1f7a3d;
	box-shadow: 0 0 0 3px rgba(31, 122, 61, .12);
}

.gl-checkout-compact textarea {
	min-height: 96px;
	resize: vertical;
}

.gl-checkout-compact input.input-text::placeholder,
.gl-checkout-compact textarea::placeholder {
	color: #9aa3ad;
	font-size: 15px;
}

.gl-checkout-compact .select2-container {
	width: 100% !important;
}

.gl-checkout-compact .select2-container .select2-selection--single {
	min-height: 50px;
	border: 1px solid #cfd6dc;
	border-radius: 12px;
	padding: 9px 12px;
}

.gl-checkout-compact .select2-container--default .select2-selection--single .select2-selection__rendered {
	line-height: 30px;
	padding-left: 0;
	font-size: 16px;
	color: #171b20;
}

.gl-checkout-compact .select2-container--default .select2-selection--single .select2-selection__arrow {
	height: 48px;
}

.gl-checkout-compact .woocommerce-billing-fields__field-wrapper,
.gl-checkout-compact .woocommerce-shipping-fields__field-wrapper {
	display: grid;
	grid-template-columns: 1fr;
	gap: 0;
}

.gl-checkout-coupon {
	background: #f7fbf8;
	border: 1px dashed #b9d8c3;
	border-radius: 18px;
	padding: 0;
	margin-bottom: 16px;
	overflow: hidden;
}

.gl-checkout-coupon details {
	padding: 0;
}

.gl-checkout-coupon summary {
	position: relative;
	display: flex;
	align-items: center;
	gap: 12px;
	cursor: pointer;
	list-style: none;
	padding: 18px 52px 18px 18px;
	font-size: 16px;
	font-weight: 800;
	color: #1d3124;
}

.gl-checkout-coupon summary::-webkit-details-marker {
	display: none;
}

.gl-checkout-coupon summary:before {
	content: '%';
	display: flex;
	align-items: center;
	justify-content: center;
	width: 34px;
	height: 34px;
	border-radius: 10px;
	background: #e5f5ea;
	color: #1f7a3d;
	font-weight: 900;
	flex: 0 0 34px;
}

.gl-checkout-coupon summary:after {
	content: '+';
	position: absolute;
	right: 20px;
	top: 50%;
	transform: translateY(-50%);
	font-size: 24px;
	line-height: 1;
	color: #1f7a3d;
}

.gl-checkout-coupon details[open] summary:after {
	content: '–';
}

.gl-checkout-coupon__text {
	display: block;
	margin-top: 3px;
	font-size: 14px;
	font-weight: 500;
	color: #6b7480;
}

.gl-checkout-coupon .checkout_coupon {
	margin: 0 !important;
	padding: 0 18px 18px !important;
	border: 0 !important;
	background: transparent !important;
}

.gl-checkout-coupon .checkout_coupon p {
	margin-bottom: 10px;
}

.gl-checkout-coupon .checkout_coupon .button {
	min-height: 48px;
	border-radius: 12px;
	background: #1f7a3d;
	color: #fff;
	font-weight: 700;
	border: none;
	padding: 0 18px;
}

.gl-checkout-compact__sidebar {
	position: sticky;
	top: 20px;
	min-width: 0;
	overflow: visible;
	background: #fff;
	border: 1px solid #e1e7ec !important;
	border-radius: 22px;
	padding: 20px !important;
	box-shadow: 0 14px 36px rgba(24, 39, 75, .08);
}

#order_review_heading {
	margin: 0 0 6px;
	font-size: 24px;
	line-height: 1.2;
	font-weight: 900;
	color: #15191f;
}

.gl-checkout-sidebar__desc {
	margin: 0 0 16px;
	font-size: 14px;
	line-height: 1.35;
	color: #6b7480;
	white-space: normal;
}

.gl-checkout-compact #order_review {
	min-width: 0;
	overflow: visible;
}

.gl-checkout-compact #order_review table.shop_table {
	width: 100% !important;
	max-width: 100%;
	table-layout: fixed;
	border-collapse: separate;
	border-spacing: 0;
	border: 1px solid #e2e6ea !important;
	border-radius: 16px !important;
	overflow: hidden;
	background: #fff;
	margin-bottom: 18px;
}

.gl-checkout-compact #order_review table.shop_table th,
.gl-checkout-compact #order_review table.shop_table td {
	padding: 13px 14px;
	font-size: 15px;
	line-height: 1.4;
	border-color: #edf0f3;
	word-break: normal;
	overflow-wrap: normal;
}

.gl-checkout-compact #order_review table.shop_table thead th {
	font-size: 13px;
	text-transform: uppercase;
	letter-spacing: .04em;
	color: #7a8491;
	background: #f7f9fb;
}

.gl-checkout-compact #order_review table.shop_table thead th.product-total {
	font-size: 0 !important;
	color: transparent !important;
}

.gl-checkout-compact #order_review table.shop_table .product-name {
	width: 62%;
	color: #242a31;
	font-weight: normal;
	white-space: normal;
	word-break: normal;
	overflow-wrap: normal;
	hyphens: none;
}

.gl-checkout-compact #order_review table.shop_table .product-total {
	width: 38%;
	min-width: 0;
	text-align: right;
	white-space: nowrap;
	font-weight: 800;
	color: #171b20;
}

.gl-checkout-compact #order_review table.shop_table .product-quantity {
	white-space: nowrap;
	font-weight: 800;
}

.gl-checkout-compact #order_review table.shop_table tfoot th {
	color: #5f6975;
	font-weight: 700;
}

.gl-checkout-compact #order_review table.shop_table tfoot td {
	text-align: right;
	font-weight: 800;
	color: #171b20;
}

.gl-checkout-compact #order_review table.shop_table .cart-subtotal {
	display: none !important;
}

.gl-checkout-compact #order_review table.shop_table .order-total th,
.gl-checkout-compact #order_review table.shop_table .order-total td {
	font-size: 18px;
	color: #11161c;
	background: #f7fbf8;
	vertical-align: middle;
}

.gl-checkout-compact #order_review table.shop_table .order-total th {
	width: 34%;
}

.gl-checkout-compact #order_review table.shop_table .order-total td {
	width: 66%;
	white-space: normal;
}

.gl-checkout-compact #order_review table.shop_table .order-total .amount {
	white-space: nowrap;
}

.gl-checkout-compact #order_review table.shop_table .order-total small,
.gl-checkout-compact #order_review table.shop_table .order-total .includes_tax {
	display: block !important;
	margin-left: 0;
	margin-top: 4px;
	font-size: 13px;
	line-height: 1.25;
	font-weight: 600;
	color: #6b7480;
	white-space: normal;
}

.woocommerce-Price-currencySymbol {
	font-size: 13px;
}

.woocommerce-checkout #payment {
	background: #f5fbf7 !important;
	border: 1px solid #dbece2 !important;
	border-radius: 20px !important;
	padding: 0;
	overflow: hidden;
}

.woocommerce-checkout #payment ul.payment_methods {
	padding: 18px;
	border-bottom: 1px solid #dbece2;
}

.woocommerce-checkout #payment ul.payment_methods li {
	position: relative;
	margin: 0 0 12px;
	padding: 16px 16px 16px 50px;
	background: #fff;
	border: 1px solid #e3ebe7;
	border-radius: 16px;
	list-style: none;
	transition: border-color .2s ease, box-shadow .2s ease;
}

.woocommerce-checkout #payment ul.payment_methods li:has(input.input-radio:checked) {
	border-color: #1f7a3d;
	box-shadow: 0 0 0 3px rgba(31, 122, 61, .10);
}

.woocommerce-checkout #payment ul.payment_methods li:last-child {
	margin-bottom: 0;
}

.woocommerce-checkout #payment ul.payment_methods li input.input-radio {
	position: absolute;
	left: 18px;
	width: 18px;
	height: 18px;
	margin: 0;
	accent-color: #42b957;
}

.woocommerce-checkout #payment ul.payment_methods li label {
	display: flex;
	align-items: center;
	gap: 10px;
	margin: 0;
	font-size: 17px;
	font-weight: 700;
	line-height: 1.3;
	color: #20242a;
	cursor: pointer;
	flex-wrap: nowrap;
}

.woocommerce-checkout #payment ul.payment_methods li label img {
	max-height: 30px;
	width: auto;
	margin: 0 !important;
	vertical-align: middle;
}

.woocommerce-checkout #payment ul.payment_methods li.payment_method_tbank label img {
	max-height: 18px;
	border-radius: 8px;
}

.woocommerce-checkout #payment div.payment_box {
	margin: 14px 0 0;
	padding: 14px 15px;
	background: #f4f6f8;
	border-radius: 14px;
	color: #5f6975;
	font-size: 14px;
	line-height: 1.45;
}

.woocommerce-checkout #payment div.payment_box::before {
	display: none;
}

.woocommerce-checkout #payment div.payment_box p {
	margin: 0 0 10px;
}

.woocommerce-checkout #payment div.payment_box p:last-child {
	margin-bottom: 0;
}

.woocommerce-checkout .yandex-pay-and-split_widget_container {
	margin-top: 14px;
}

.woocommerce-checkout .ya-pay-widget {
	max-width: 100%;
	border-radius: 14px !important;
}

.woocommerce-checkout #payment .form-row.place-order {
	padding: 20px 18px;
	background: #f5fbf7;
	margin: 0 !important;
}

.woocommerce-checkout .woocommerce-privacy-policy-text {
	margin-bottom: 16px;
	font-size: 13px;
	line-height: 1.5;
	color: #667080;
}

.woocommerce-checkout .woocommerce-privacy-policy-text p {
	margin: 0;
}

.woocommerce-checkout .woocommerce-privacy-policy-text a {
	color: #1f7a3d;
	text-decoration: underline;
	text-underline-offset: 3px;
}

.woocommerce-checkout .gl-personal-data-consent--checkout {
	margin: 0 0 16px;
	font-size: 13px;
	line-height: 1.5;
	color: #667080;
}

.woocommerce-checkout .gl-personal-data-consent--checkout label {
	display: flex;
	align-items: flex-start;
	gap: 10px;
	margin: 0;
	font-weight: 400;
}

.woocommerce-checkout .gl-personal-data-consent--checkout input[type="checkbox"] {
	flex: 0 0 auto;
	width: 18px;
	height: 18px;
	margin: 2px 0 0;
	accent-color: #1f7a3d;
}

.woocommerce-checkout .gl-personal-data-consent--checkout a {
	color: #1f7a3d;
	text-decoration: underline;
	text-underline-offset: 3px;
}

.woocommerce-checkout #payment #place_order,
.woocommerce-checkout #payment .button.alt {
	width: 100%;
	min-height: 56px;
	border-radius: 15px;
	background: #1f7a3d;
	color: #fff;
	font-size: 17px;
	font-weight: 800;
	border: none;
	transition: .2s ease;
}

.woocommerce-checkout #payment #place_order:hover,
.woocommerce-checkout #payment .button.alt:hover {
	background: #176431;
	color: #fff;
}

.woocommerce-checkout #yandex-pay-and-split-button-container {
	margin-bottom: 14px;
}

.woocommerce-checkout #yandex-pay-and-split-button-container .ya-pay-button {
	width: 100% !important;
	border-radius: 14px !important;
}
.gl-checkout-coupon #coupon_code {
	width: 95%;
	margin: 0 20px;
}
	
.button[name="apply_coupon"] {
	margin: 0 20px !important;
}
	
@media (min-width: 769px) {
	.gl-checkout-compact .woocommerce-billing-fields__field-wrapper,
	.gl-checkout-compact .woocommerce-shipping-fields__field-wrapper {
		grid-template-columns: repeat(2, minmax(0, 1fr));
		column-gap: 12px;
		row-gap: 0;
	}

	#billing_last_name_field,
	#billing_company_field,
	#billing_country_field,
	#billing_address_1_field,
	#billing_address_2_field,
	#billing_state_field,
	#billing_postcode_field,
	#order_comments_field,
	#shipping_last_name_field,
	#shipping_company_field,
	#shipping_country_field,
	#shipping_address_1_field,
	#shipping_address_2_field,
	#shipping_state_field,
	#shipping_postcode_field {
		grid-column: 1 / -1;
	}
}

@media (max-width: 1200px) {
	.gl-checkout-compact__grid {
		grid-template-columns: minmax(0, 1fr) 430px;
		gap: 22px;
	}
}

@media (max-width: 1024px) {
	.gl-checkout-compact__grid {
		grid-template-columns: 1fr;
	}

	.gl-checkout-compact__sidebar {
		position: static;
	}
}

@media (max-width: 768px) {
	.gl-checkout-compact {
		margin-top: 24px;
	}

	.gl-checkout-compact__grid {
		gap: 18px;
	}

	.gl-checkout-card {
		padding: 18px;
		border-radius: 16px;
	}

	.gl-checkout-card__title,
	#order_review_heading {
		font-size: 21px;
	}

	.gl-checkout-compact .woocommerce-billing-fields__field-wrapper,
	.gl-checkout-compact .woocommerce-shipping-fields__field-wrapper {
		grid-template-columns: 1fr;
	}

	.gl-checkout-compact input.input-text,
	.gl-checkout-compact select,
	.gl-checkout-compact textarea {
		min-height: 50px;
		font-size: 16px;
	}

	.gl-checkout-compact__sidebar {
		padding: 16px !important;
		border-radius: 18px;
	}

	.gl-checkout-compact #order_review table.shop_table {
		table-layout: fixed;
	}

	.gl-checkout-compact #order_review table.shop_table th,
	.gl-checkout-compact #order_review table.shop_table td {
		padding: 11px 12px;
	}

	.gl-checkout-compact #order_review table.shop_table .product-name {
		width: 58%;
		font-size: 14px;
		line-height: 1.35;
		white-space: normal;
		word-break: normal;
		overflow-wrap: normal;
		hyphens: none;
	}

	.gl-checkout-compact #order_review table.shop_table .product-total {
		width: 42%;
		min-width: 0;
		font-size: 14px;
		line-height: 1.3;
		white-space: nowrap;
	}

	.gl-checkout-compact #order_review table.shop_table .order-total td {
		white-space: normal;
	}

	.gl-checkout-compact #order_review table.shop_table .order-total small,
	.gl-checkout-compact #order_review table.shop_table .order-total .includes_tax {
		display: block !important;
		margin-left: 0;
		margin-top: 4px;
		white-space: normal;
	}

	.woocommerce-checkout #payment ul.payment_methods {
		padding: 16px;
	}

	.woocommerce-checkout #payment ul.payment_methods li {
		padding: 15px 15px 15px 46px;
	}

	.woocommerce-checkout #payment .form-row.place-order {
		padding: 16px;
	}
}

@media (max-width: 480px) {
	.gl-checkout-compact #order_review table.shop_table .product-name {
		width: 56%;
		font-size: 13px;
	}

	.gl-checkout-compact #order_review table.shop_table .product-total {
		width: 44%;
		font-size: 13px;
	}

	.gl-checkout-compact #order_review table.shop_table .order-total th,
	.gl-checkout-compact #order_review table.shop_table .order-total td {
		font-size: 16px;
	}

	.woocommerce-checkout #payment ul.payment_methods li label {
		flex-wrap: wrap !important;
	}
	
	.gl-checkout-coupon #coupon_code {
	    width: 88%;
    }
}
	
	
	
/* Комментарий к заказу как обычные input-поля */
.gl-checkout-compact #order_comments_field {
	margin-top: 0 !important;
}

.gl-checkout-compact #order_comments {
	width: 100%;
	min-height: 64px;
	height: 64px;
	border: 1px solid #dce5de;
	border-radius: 18px;
	padding: 18px 22px;
	background: #fff;
	box-shadow: none;
	font-size: 16px;
	line-height: 1.35;
	color: #171b20;
	resize: vertical;
}

.gl-checkout-compact #order_comments::placeholder {
	color: #9aa3ad;
	font-size: 16px;
}

.gl-checkout-compact #order_comments:focus {
	outline: none;
	border-color: #1f7a3d;
	box-shadow: 0 0 0 3px rgba(31, 122, 61, .12);
}

@media (max-width: 768px) {
	.gl-checkout-compact #order_comments {
		min-height: 58px;
		height: 58px;
		border-radius: 14px;
		padding: 14px 16px;
		font-size: 14px;
	}

	.gl-checkout-compact #order_comments::placeholder {
		font-size: 14px;
	}
}
</style>

	<div class="gl-checkout-compact__grid">

		<div class="gl-checkout-compact__main">

			<section class="gl-checkout-card">
				<div class="gl-checkout-card__head">
					<h3 class="gl-checkout-card__title">Контактные данные</h3>
					<p class="gl-checkout-card__desc">Укажите данные получателя, чтобы мы могли связаться с вами по заказу.</p>
				</div>

				<?php do_action('woocommerce_checkout_billing'); ?>
			</section>

			<section class="gl-checkout-card">
				<div class="gl-checkout-card__head">
					<h3 class="gl-checkout-card__title">Получение заказа</h3>
					<p class="gl-checkout-card__desc">Выберите город, адрес и удобный способ получения заказа.</p>
				</div>

				<?php do_action('woocommerce_checkout_shipping'); ?>
			</section>

			<section class="gl-checkout-coupon">
				<details>
					<summary>
						<span>
							Есть промокод?
							<span class="gl-checkout-coupon__text">Нажмите, чтобы ввести купон на скидку</span>
						</span>
					</summary>

					<?php woocommerce_checkout_coupon_form(); ?>
				</details>
			</section>

		</div>

		<aside class="gl-checkout-compact__sidebar">

			<?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

			<h3 id="order_review_heading"><?php esc_html_e('Ваш заказ', 'woocommerce'); ?></h3>
			<p class="gl-checkout-sidebar__desc">Проверьте товары, оплату и итоговую сумму.</p>

			<?php do_action('woocommerce_checkout_before_order_review'); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action('woocommerce_checkout_order_review'); ?>
			</div>

			<?php do_action('woocommerce_checkout_after_order_review'); ?>

		</aside>

	</div>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>