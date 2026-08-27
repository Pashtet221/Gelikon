<?php
/**
 * Compact order details table shown in emails.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

if ( ! function_exists( 'gelikon_email_format_order_date' ) ) {
	/**
	 * Format an order date for compact Gelikon emails.
	 *
	 * @param WC_Order $order Order object.
	 * @return string
	 */
	function gelikon_email_format_order_date( $order ) {
		$date_created = $order->get_date_created();

		if ( ! $date_created ) {
			return '';
		}

		return $date_created->date_i18n( 'd.m.Y' );
	}
}

if ( ! function_exists( 'gelikon_email_get_payment_method_title' ) ) {
	/**
	 * Get payment method title with a screenshot-friendly fallback.
	 *
	 * @param WC_Order $order Order object.
	 * @return string
	 */
	function gelikon_email_get_payment_method_title( $order ) {
		$payment_method = $order->get_payment_method_title();

		return $payment_method ? $payment_method : __( 'Оплата при получении', 'gelikon' );
	}
}

if ( ! function_exists( 'gelikon_email_item_thumbnail' ) ) {
	/**
	 * Render a compact product thumbnail for email clients.
	 *
	 * @param WC_Order_Item_Product $item Order item.
	 * @return string
	 */
	function gelikon_email_item_thumbnail( $item ) {
		$product = $item->get_product();

		if ( ! $product || ! $product->get_image_id() ) {
			return '';
		}

		$image = wp_get_attachment_image(
			$product->get_image_id(),
			array( 72, 72 ),
			false,
			array(
				'style' => 'display:block;width:72px;max-width:72px;height:auto;border:0;outline:none;text-decoration:none;',
				'alt'   => $product->get_name(),
			)
		);

		return $image ? $image : '';
	}
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
?>

<table class="gelikon-email-card" role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;border:1px solid #e5e7eb;border-radius:14px;border-collapse:separate;overflow:hidden;margin:24px 0 20px 0;background:#ffffff;">
	<tr>
		<td class="gelikon-email-card__header" style="padding:18px 20px 14px 20px;">
			<h2 style="margin:0 0 6px 0;font-family:Arial,Helvetica,sans-serif;font-size:20px;line-height:1.25;font-weight:700;color:#111827;">
				<?php esc_html_e( 'Детали заказа', 'gelikon' ); ?>
			</h2>
			<p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;color:#374151;">
				<?php
				printf(
					/* translators: 1: order number, 2: order date */
					esc_html__( 'Заказ № %1$s от %2$s', 'gelikon' ),
					esc_html( $order->get_order_number() ),
					esc_html( gelikon_email_format_order_date( $order ) )
				);
				?>
			</p>
		</td>
	</tr>
	<tr>
		<td style="padding:0;">
			<table class="gelikon-email-order-table" role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse;table-layout:fixed;">
				<colgroup>
					<col style="width:58%;">
					<col style="width:18%;">
					<col style="width:24%;">
				</colgroup>
				<thead>
					<tr>
						<th scope="col" style="padding:12px 16px;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;text-align:<?php echo esc_attr( $text_align ); ?>;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.35;font-weight:700;color:#111827;">
							<?php esc_html_e( 'Товар', 'gelikon' ); ?>
						</th>
						<th scope="col" style="padding:12px 10px;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.35;font-weight:700;color:#111827;white-space:nowrap;">
							<?php esc_html_e( 'Количество', 'gelikon' ); ?>
						</th>
						<th scope="col" style="padding:12px 16px 12px 10px;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;text-align:right;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.35;font-weight:700;color:#111827;white-space:nowrap;">
							<?php esc_html_e( 'Цена', 'gelikon' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
						<?php
						$product      = $item->get_product();
						$product_name = $product ? $product->get_name() : $item->get_name();
						$thumbnail    = gelikon_email_item_thumbnail( $item );
						?>
						<tr class="gelikon-email-item">
							<td class="gelikon-email-item__product" style="padding:16px;border-bottom:1px solid #e5e7eb;text-align:<?php echo esc_attr( $text_align ); ?>;vertical-align:middle;">
								<table class="gelikon-email-product-table" role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;border-collapse:collapse;">
									<tr>
										<?php if ( $thumbnail ) : ?>
											<td class="gelikon-email-product-thumb" width="84" style="width:84px;padding:0 12px 0 0;vertical-align:middle;">
												<?php echo wp_kses_post( $thumbnail ); ?>
											</td>
										<?php endif; ?>
										<td style="padding:0;vertical-align:middle;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#111827;">
											<?php echo wp_kses_post( $product_name ); ?>
											<?php if ( $sent_to_admin && $product && $product->get_sku() ) : ?>
												<br><span style="color:#6b7280;font-size:12px;line-height:1.4;">SKU: <?php echo esc_html( $product->get_sku() ); ?></span>
											<?php endif; ?>
										</td>
									</tr>
								</table>
							</td>
							<td class="gelikon-email-item__qty" style="padding:16px 10px;border-bottom:1px solid #e5e7eb;text-align:center;vertical-align:middle;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#111827;white-space:nowrap;">
								<?php echo esc_html( $item->get_quantity() ); ?> <?php esc_html_e( 'шт.', 'gelikon' ); ?>
							</td>
							<td class="gelikon-email-item__price" style="padding:16px 16px 16px 10px;border-bottom:1px solid #e5e7eb;text-align:right;vertical-align:middle;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#111827;white-space:nowrap;">
								<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<td class="gelikon-email-summary-label" colspan="2" style="padding:12px 16px;border-bottom:1px solid #e5e7eb;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#374151;text-align:left;"><?php esc_html_e( 'Доставка:', 'gelikon' ); ?></td>
						<td class="gelikon-email-summary-value" style="padding:12px 16px;border-bottom:1px solid #e5e7eb;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#111827;text-align:right;word-break:normal;overflow-wrap:break-word;"><?php echo wp_kses_post( gelikon_order_selected_shipping_method( $order ) ); ?></td>
					</tr>
					<tr>
						<td class="gelikon-email-summary-label" colspan="2" style="padding:14px 16px;border-bottom:1px solid #e5e7eb;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.4;font-weight:700;color:#111827;text-align:left;"><?php esc_html_e( 'Итого:', 'gelikon' ); ?></td>
						<td class="gelikon-email-summary-value" style="padding:14px 16px;border-bottom:1px solid #e5e7eb;font-family:Arial,Helvetica,sans-serif;font-size:18px;line-height:1.3;font-weight:700;color:#111827;text-align:right;white-space:normal;word-break:normal;overflow-wrap:break-word;"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
					</tr>
					<tr>
						<td class="gelikon-email-summary-label" colspan="2" style="padding:12px 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#374151;text-align:left;"><?php esc_html_e( 'Способ оплаты:', 'gelikon' ); ?></td>
						<td class="gelikon-email-summary-value" style="padding:12px 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4;color:#111827;text-align:right;word-break:normal;overflow-wrap:break-word;"><?php echo esc_html( gelikon_email_get_payment_method_title( $order ) ); ?></td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>
</table>

<table class="gelikon-email-note" role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin:0 0 18px 0;border-collapse:collapse;">
	<tr>
		<td style="padding:14px 16px;border:1px solid #a7f3c4;border-radius:8px;background:#effdf3;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.45;color:#111827;">
			<span style="display:inline-block;width:26px;height:26px;margin:0 12px 0 0;vertical-align:middle;text-align:center;font-size:22px;line-height:26px;color:#12D457;">▣</span>
			<span style="vertical-align:middle;"><?php esc_html_e( 'Как только заказ будет передан службе доставки, мы пришлём вам трек-номер для отслеживания.', 'gelikon' ); ?></span>
		</td>
	</tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin:0 0 12px 0;border-collapse:collapse;">
	<tr>
		<td width="42" style="width:42px;padding:0 12px 0 0;vertical-align:top;">
			<span style="display:block;width:30px;height:30px;border-radius:50%;background:#12D457;color:#ffffff;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:20px;line-height:30px;font-weight:700;">?</span>
		</td>
		<td style="padding:2px 0 0 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.45;color:#111827;vertical-align:top;">
			<?php esc_html_e( 'Если у вас возникли вопросы — мы всегда на связи!', 'gelikon' ); ?>
		</td>
	</tr>
</table>

<p style="margin:0 0 4px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;color:#374151;"><?php esc_html_e( 'С уважением,', 'gelikon' ); ?></p>
<p style="margin:0 0 20px 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;color:#374151;">
	<?php esc_html_e( 'команда', 'gelikon' ); ?> <strong style="color:#1ea751;">GELIKON LINE</strong>
</p>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
