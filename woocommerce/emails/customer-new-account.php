<?php
/**
 * Customer new account email
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 9.8.0
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = class_exists( FeaturesUtil::class ) && FeaturesUtil::feature_is_enabled( 'email_improvements' );

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );

$generated_password = '';
$customer_id        = ! empty( $user_id ) ? absint( $user_id ) : 0;

if ( ! empty( $user_pass ) && is_string( $user_pass ) ) {
	$generated_password = $user_pass;
} elseif ( $customer_id ) {
	$generated_password = (string) get_user_meta( $customer_id, '_gelikon_generated_customer_password', true );
}
?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
<p>
	<?php
	printf(
		/* translators: %s: Customer username. */
		esc_html__( 'Здравствуйте, %s!', 'gelikon' ),
		esc_html( $user_login )
	);
	?>
</p>
<p><?php esc_html_e( 'Для вас создан личный кабинет. Ниже указаны данные для входа:', 'gelikon' ); ?></p>
<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<p>
	<?php esc_html_e( 'Имя пользователя:', 'gelikon' ); ?> <strong><?php echo esc_html( $user_login ); ?></strong><br>
	<?php if ( $generated_password ) : ?>
		<?php esc_html_e( 'Пароль:', 'gelikon' ); ?> <strong><?php echo esc_html( $generated_password ); ?></strong>
	<?php else : ?>
		<?php esc_html_e( 'Пароль был задан при регистрации.', 'gelikon' ); ?>
	<?php endif; ?>
</p>

<p>
	<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
		<?php esc_html_e( 'Перейти в личный кабинет', 'gelikon' ); ?>
	</a>
</p>

<?php if ( $generated_password ) : ?>
	<p><?php esc_html_e( 'Рекомендуем сохранить пароль в надежном месте и не пересылать это письмо третьим лицам.', 'gelikon' ); ?></p>
<?php endif; ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );

if ( $customer_id && $generated_password ) {
	delete_user_meta( $customer_id, '_gelikon_generated_customer_password' );
}
