<?php
/**
 * Email header customized for Gelikon brand emails.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 */

defined( 'ABSPATH' ) || exit;

$blog_name               = get_bloginfo( 'name', 'display' );
$header_image            = get_option( 'woocommerce_email_header_image' );
$header_image            = apply_filters( 'woocommerce_email_header_image', $header_image, $email );
$hide_header_brand       = $email instanceof WC_Email && 'customer_new_account' === $email->id;
$header_image_attributes = array(
	'alt'   => $blog_name ? $blog_name : __( 'Site logo', 'gelikon' ),
	'style' => 'display:block;border:0;outline:none;text-decoration:none;height:auto;max-width:220px;width:auto;',
);
$header_image_attributes = apply_filters( 'woocommerce_email_header_image_attributes', $header_image_attributes, $email );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo esc_html( $blog_name ); ?></title>
	<style type="text/css">
		@media only screen and (max-width: 480px) {
			body { width: 100% !important; min-width: 100% !important; }
			#wrapper { padding: 18px 0 !important; }
			#template_container { width: 100% !important; max-width: 100% !important; }
			#header_wrapper { padding: 0 16px 16px 16px !important; }
			#header_wrapper h1 { font-size: 24px !important; line-height: 1.25 !important; }
			#body_content_inner_wrap { padding: 0 16px 24px 16px !important; }
			.gelikon-email-card { margin: 18px 0 18px 0 !important; border-radius: 12px !important; }
			.gelikon-email-card__header { padding: 16px 14px 12px 14px !important; }
			.gelikon-email-order-table, .gelikon-email-order-table tbody, .gelikon-email-order-table tr, .gelikon-email-order-table td { display: block !important; width: 100% !important; box-sizing: border-box !important; }
			.gelikon-email-order-table thead { display: none !important; }
			.gelikon-email-order-table colgroup { display: none !important; }
			.gelikon-email-item { border-bottom: 1px solid #e5e7eb !important; }
			.gelikon-email-item__product { padding: 14px 14px 8px 14px !important; border-bottom: 0 !important; }
			.gelikon-email-product-table, .gelikon-email-product-table tbody, .gelikon-email-product-table tr { display: table !important; width: 100% !important; table-layout: fixed !important; }
			.gelikon-email-product-table td { display: table-cell !important; width: auto !important; box-sizing: border-box !important; }
			.gelikon-email-product-thumb { width: 64px !important; padding-right: 10px !important; }
			.gelikon-email-product-thumb img { width: 56px !important; max-width: 56px !important; }
			.gelikon-email-item__qty, .gelikon-email-item__price { display: inline-block !important; width: 50% !important; padding: 0 14px 14px 14px !important; border-bottom: 0 !important; text-align: left !important; white-space: normal !important; }
			.gelikon-email-item__qty:before { content: 'Количество: '; color: #6b7280; font-weight: 400; }
			.gelikon-email-item__price:before { content: 'Цена: '; color: #6b7280; font-weight: 400; }
			.gelikon-email-summary-label, .gelikon-email-summary-value { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: left !important; }
			.gelikon-email-summary-label { padding: 12px 14px 4px 14px !important; border-bottom: 0 !important; }
			.gelikon-email-summary-value { padding: 0 14px 12px 14px !important; white-space: normal !important; overflow-wrap: anywhere !important; }
			.gelikon-email-note td { padding: 12px 14px !important; }
		}
	</style>
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="padding:0;background:#ffffff;">
	<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="background:#ffffff;margin:0;padding:32px 0;width:100%;text-align:center;">
		<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" role="presentation">
			<tr>
				<td align="center" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" role="presentation" style="background:#ffffff;border:0;border-radius:0;box-shadow:none;margin:0 auto;width:600px;max-width:100%;">
						<tr>
							<td align="left" valign="top">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" role="presentation" style="background:#ffffff;border:0;color:#1A1A1A;">
									<tr>
										<td id="header_wrapper" style="padding:0 32px 18px 32px;text-align:left;">
											<?php if ( $header_image ) : ?>
												<p style="margin:0 0 20px 0;">
													<img src="<?php echo esc_url( $header_image ); ?>" <?php echo wc_implode_html_attributes( $header_image_attributes ); ?> />
												</p>
											<?php elseif ( ! $hide_header_brand ) : ?>
												<p style="margin:0 0 20px 0;font-family:Arial,Helvetica,sans-serif;font-size:18px;line-height:1.35;font-weight:700;color:#12D457;">
													<?php echo esc_html( $blog_name ); ?>
												</p>
											<?php endif; ?>
											<h1 style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:30px;line-height:1.2;font-weight:700;color:#1A1A1A;">
												<?php echo esc_html( $email_heading ); ?>
											</h1>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td align="left" valign="top">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body" role="presentation">
									<tr>
										<td valign="top" id="body_content" style="background:#ffffff;">
											<table border="0" cellpadding="20" cellspacing="0" width="100%" role="presentation">
												<tr>
													<td valign="top" id="body_content_inner_wrap" style="padding:0 32px 32px 32px;">
														<div id="body_content_inner" style="color:#1A1A1A;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;text-align:left;">
