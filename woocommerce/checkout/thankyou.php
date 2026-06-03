<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<?php wc_get_template( 'checkout/order-received.php', array( 'order' => $order ) ); ?>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

				<li class="woocommerce-order-overview__order order">
					<?php esc_html_e( 'Order number:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php esc_html_e( 'Date:', 'woocommerce' ); ?>
					<strong><?php echo wc_format_datetime( $order->get_date_created() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<li class="woocommerce-order-overview__email email">
						<?php esc_html_e( 'Email:', 'woocommerce' ); ?>
						<strong><?php echo $order->get_billing_email(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<?php esc_html_e( 'Total:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<?php if ( $order->get_payment_method_title() ) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<?php esc_html_e( 'Payment method:', 'woocommerce' ); ?>
						<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
					</li>
				<?php endif; ?>

			</ul>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>


<style>
	/* Thank You / Заказ принят — только типографика и цвета */

.gl-entry__header h1 {
	font-size: 36px;
	line-height: 1.15;
	font-weight: 800;
	color: #15191f;
}

.woocommerce-thankyou-order-received {
	font-size: 20px;
	line-height: 1.4;
	font-weight: 700;
	color: #1f5f36;
}

.woocommerce-order-overview li {
	font-size: 13px;
	line-height: 1.35;
	font-weight: 600;
	color: #68727f;
	text-transform: none;
}

.woocommerce-order-overview li strong {
	font-size: 15px;
	line-height: 1.3;
	font-weight: 800;
	color: #15191f;
}

.woocommerce-order > p:not(.woocommerce-thankyou-order-received) {
	font-size: 15px;
	line-height: 1.45;
	font-weight: 500;
	color: #424b57;
}

.woocommerce-order-details__title,
.woocommerce-column__title {
	font-size: 23px;
	line-height: 1.2;
	font-weight: 800;
	color: #15191f;
}

.woocommerce-table--order-details th,
.woocommerce-table--order-details td {
	font-size: 15px;
	line-height: 1.45;
	color: #242a31;
}

.woocommerce-table--order-details thead th {
	font-size: 13px;
	line-height: 1.3;
	font-weight: 800;
	color: #68727f;
}

.woocommerce-table--order-details .product-name {
	font-weight: 600;
	color: #242a31;
}

.woocommerce-table--order-details .product-total {
	font-weight: 800;
	color: #15191f;
}

.woocommerce-table--order-details .product-name a {
	color: #242a31;
	font-weight: 600;
	text-decoration: none;
}

.woocommerce-table--order-details .product-name a:hover {
	color: #1f7a3d;
}

.woocommerce-table--order-details .product-quantity {
	font-weight: 700;
	color: #68727f;
}

.woocommerce-table--order-details tfoot th {
	font-weight: 700;
	color: #5f6975;
}

.woocommerce-table--order-details tfoot td {
	font-weight: 800;
	color: #15191f;
}

.woocommerce-table--order-details tfoot tr:last-child th,
.woocommerce-table--order-details tfoot tr:last-child td {
	font-size: 17px;
	font-weight: 800;
	color: #11161c;
}

.woocommerce-table--order-details .includes_tax {
	font-size: 13px;
	line-height: 1.3;
	font-weight: 500;
	color: #68727f;
}

.woocommerce-customer-details address {
	font-style: normal;
	font-size: 15px;
	line-height: 1.55;
	font-weight: 500;
	color: #242a31;
}

.woocommerce-customer-details--phone,
.woocommerce-customer-details--email {
	color: #5f6975;
	font-weight: 500;
}

.woocommerce-Price-currencySymbol {
	font-size: .85em;
}
	
.woocommerce .woocommerce-customer-details address{
	border-radius: 24px;
}

@media (max-width: 768px) {
	.gl-entry__header h1 {
		font-size: 28px;
	}

	.woocommerce-thankyou-order-received {
		font-size: 17px;
	}

	.woocommerce-order-details__title,
	.woocommerce-column__title {
		font-size: 21px;
	}

	.woocommerce-table--order-details th,
	.woocommerce-table--order-details td {
		font-size: 14px;
	}
}
	
@media (max-width: 768px) {
	.woocommerce-order-overview {
		display: block !important;
	}

	.woocommerce-order-overview li {
		float: none !important;
		width: 100% !important;
		margin: 0 0 10px !important;
		padding: 0 !important;
		border-right: 0 !important;
		text-align: left !important;
	}

	.woocommerce-order-overview li strong {
		display: block;
		margin-top: 2px;
		font-size: 24px;
		line-height: 1.15;
		font-weight: 800;
		color: #15191f;
	}

	.woocommerce-order-overview__email strong {
		font-size: 22px;
		line-height: 1.2;
		word-break: break-word;
	}

	.woocommerce-order-overview li {
		font-size: 14px;
		line-height: 1.25;
		font-weight: 700;
		color: #68727f;
	}

	.woocommerce-thankyou-order-received {
		font-size: 24px;
		line-height: 1.25;
		font-weight: 800;
		color: #276c3b;
	}

	.gl-entry__header h1 {
		font-size: 42px;
		line-height: 1.1;
		font-weight: 800;
		color: #15191f;
	}
	
	.woocommerce ul.order_details{
		padding: 0;
	}
}

@media (max-width: 480px) {
	.gl-entry__header h1 {
		font-size: 36px;
	}

	.woocommerce-thankyou-order-received {
		font-size: 21px;
	}

	.woocommerce-order-overview li strong {
		font-size: 22px;
	}

	.woocommerce-order-overview__email strong {
		font-size: 20px;
	}
}
</style>



