<?php
/**
 * Order details table shown in emails.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

$email_improvements_enabled = FeaturesUtil::feature_is_enabled( 'email_improvements' );
$block_email_editor_enabled = FeaturesUtil::feature_is_enabled( 'block_email_editor' );

$display_section_divider   = (bool) apply_filters( 'woocommerce_email_body_display_section_divider', true );
$heading_class             = $email_improvements_enabled ? 'email-order-detail-heading' : '';
$order_table_class         = $email_improvements_enabled ? 'email-order-details' : '';
$order_total_text_align    = $email_improvements_enabled ? 'right' : 'left';
$order_quantity_text_align = $email_improvements_enabled ? 'right' : 'left';

/**
 * Логотип в письме.
 * Лучше использовать абсолютный URL.
 */
$logo_url    = 'http://paveld9o.beget.tech/gelikon/wp-content/uploads/2026/03/gelikon-line_logor-scaled.png';
$logo_width  = 160;
$logo_height = 'auto';

if ( $email_improvements_enabled ) {
	add_filter( 'woocommerce_order_shipping_to_display_shipped_via', '__return_false' );
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
?>

<?php if ( ! empty( $logo_url ) ) : ?>
	<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%; margin:0 0 24px 0;">
		<tr>
			<td align="center" style="text-align:center;">
				<img
					src="<?php echo esc_url( $logo_url ); ?>"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
					width="<?php echo esc_attr( $logo_width ); ?>"
					style="display:inline-block; max-width:<?php echo esc_attr( $logo_width ); ?>px; width:100%; height:<?php echo esc_attr( $logo_height ); ?>; border:0; outline:none; text-decoration:none;"
				>
			</td>
		</tr>
	</table>
<?php endif; ?>

<h2 class="<?php echo esc_attr( $heading_class ); ?>">
	<?php
	if ( $email_improvements_enabled ) {
		echo wp_kses_post( __( 'Order summary', 'woocommerce' ) );
	}

	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '"' . ( $block_email_editor_enabled ? ' style="text-decoration: none;"' : '' ) . '>';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}

	if ( $email_improvements_enabled ) {
		echo '<br><span>';
	}

	$order_number_string = __( '[Order #%s]', 'woocommerce' );

	if ( $email_improvements_enabled ) {
		$order_number_string = __( 'Order #%s', 'woocommerce' );
	}

	echo wp_kses_post(
		$before .
		sprintf(
			$order_number_string . $after . ' (<time datetime="%s">%s</time>)',
			$order->get_order_number(),
			$order->get_date_created()->format( 'c' ),
			wc_format_datetime( $order->get_date_created() )
		)
	);

	if ( $email_improvements_enabled ) {
		echo '</span>';
	}
	?>
</h2>

<div style="margin-bottom: <?php echo $email_improvements_enabled ? '24px' : '40px'; ?>;">
	<table class="td font-family <?php echo esc_attr( $order_table_class ); ?>" cellspacing="0" cellpadding="6" style="width: 100%; table-layout: fixed;" border="1">
		<colgroup>
			<col style="width: 62%;">
			<col style="width: 14%;">
			<col style="width: 24%;">
		</colgroup>

		<?php if ( ! $block_email_editor_enabled ) : ?>
			<thead>
				<tr>
					<th class="td" scope="col" width="62%" style="width:62%; text-align:<?php echo esc_attr( $text_align ); ?>; overflow-wrap: break-word; word-break: break-word;">
						<?php esc_html_e( 'Product', 'woocommerce' ); ?>
					</th>
					<th class="td" scope="col" width="14%" style="width:14%; text-align:<?php echo esc_attr( $order_quantity_text_align ); ?>;">
						<?php esc_html_e( 'Quantity', 'woocommerce' ); ?>
					</th>
					<th class="td" scope="col" width="24%" style="width:24%; text-align:<?php echo esc_attr( $order_total_text_align ); ?>;">
						<?php esc_html_e( 'Price', 'woocommerce' ); ?>
					</th>
				</tr>
			</thead>
		<?php endif; ?>

		<tbody>
			<?php
			$image_size = $email_improvements_enabled ? 48 : 32;

			echo wc_get_email_order_items(
				$order,
				array(
					'show_sku'      => $sent_to_admin,
					'show_image'    => $email_improvements_enabled,
					'image_size'    => array( $image_size, $image_size ),
					'plain_text'    => $plain_text,
					'sent_to_admin' => $sent_to_admin,
				)
			);
			?>
		</tbody>
	</table>

	<?php if ( $display_section_divider ) : ?>
		<hr style="border: 0; border-top: 1px solid #1E1E1E; border-top-color: rgba(30, 30, 30, 0.2); margin: 20px 0;">
	<?php endif; ?>

	<table class="td font-family <?php echo esc_attr( $order_table_class ); ?>" cellspacing="0" cellpadding="6" style="width: 100%;" border="1">
		<?php
		$item_totals       = $order->get_order_item_totals();
		$item_totals_count = count( $item_totals );

		if ( $item_totals ) {
			$i = 0;

			foreach ( $item_totals as $total ) {
				++$i;

				$last_class = ( $i === $item_totals_count ) ? ' order-totals-last' : '';
				$label      = isset( $total['label'] ) ? $total['label'] : '';
				$meta       = isset( $total['meta'] ) ? $total['meta'] : '';

				if ( isset( $total['type'] ) && 'shipping' === $total['type'] ) {
					$label = 'Доставка:';
					$meta  = '';
				}
				?>
				<tr class="order-totals order-totals-<?php echo esc_attr( $total['type'] ?? 'unknown' ); ?><?php echo esc_attr( $last_class ); ?>">
					<th class="td text-align-left" scope="row" colspan="2" style="<?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>">
						<?php
						echo wp_kses_post( $label ) . ' ';

						if ( $email_improvements_enabled ) {
							echo wp_kses_post( $meta );
						}
						?>
					</th>
					<td class="td text-align-<?php echo esc_attr( $order_total_text_align ); ?>" style="<?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>">
						<?php echo wp_kses_post( $total['value'] ); ?>
					</td>
				</tr>
				<?php
			}
		}

		if ( $order->get_customer_note() && ! $email_improvements_enabled ) {
			?>
			<tr>
				<th class="td text-align-left" scope="row" colspan="2">
					<?php esc_html_e( 'Note:', 'woocommerce' ); ?>
				</th>
				<td class="td text-align-left">
					<?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array() ); ?>
				</td>
			</tr>
			<?php
		}
		?>
	</table>

	<?php if ( $order->get_customer_note() && $email_improvements_enabled ) : ?>
		<?php if ( $display_section_divider ) : ?>
			<hr style="border: 0; border-top: 1px solid #1E1E1E; border-top-color: rgba(30, 30, 30, 0.2); margin: 20px 0;">
		<?php endif; ?>

		<table class="td font-family <?php echo esc_attr( $order_table_class ); ?>" cellspacing="0" cellpadding="6" style="width: 100%;" border="1" role="presentation">
			<tr class="order-customer-note">
				<td class="td text-align-left">
					<b><?php esc_html_e( 'Customer note', 'woocommerce' ); ?></b><br>
					<?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?>
				</td>
			</tr>
		</table>
	<?php endif; ?>
</div>

<?php
if ( $email_improvements_enabled ) {
	remove_filter( 'woocommerce_order_shipping_to_display_shipped_via', '__return_false' );
}

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );
?>