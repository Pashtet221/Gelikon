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
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="padding:0;background:#f6f7f8;">
	<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="background:#f6f7f8;margin:0;padding:32px 0;width:100%;">
		<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" role="presentation">
			<tr>
				<td align="center" valign="top">
					<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container" role="presentation" style="background:#ffffff;border:0;border-radius:0;box-shadow:none;width:600px;max-width:100%;">
						<tr>
							<td align="left" valign="top">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header" role="presentation" style="background:#ffffff;border:0;color:#1A1A1A;">
									<tr>
										<td id="header_wrapper" style="padding:0 32px 18px 32px;text-align:left;">
											<?php if ( $header_image ) : ?>
												<p style="margin:0 0 20px 0;">
													<img src="<?php echo esc_url( $header_image ); ?>" <?php echo wc_implode_html_attributes( $header_image_attributes ); ?> />
												</p>
											<?php else : ?>
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
													<td valign="top" style="padding:0 32px 32px 32px;">
														<div id="body_content_inner" style="color:#1A1A1A;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;text-align:left;">
