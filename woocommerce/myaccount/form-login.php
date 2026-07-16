<?php
/**
 * Login and registration form on the My Account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$active_tab           = isset( $_POST['register'] ) ? 'register' : 'login'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
$button_class         = function_exists( 'wc_wp_theme_get_element_class_name' ) && wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';

if ( ! $registration_enabled ) {
	$active_tab = 'login';
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="gl-myaccount-auth" data-gl-auth-tabs>
	<?php if ( $registration_enabled ) : ?>
		<div class="gl-myaccount-auth__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Account access', 'gelikon' ); ?>">
			<button
				type="button"
				class="gl-myaccount-auth__tab <?php echo 'login' === $active_tab ? 'is-active' : ''; ?>"
				id="gl-account-login-tab"
				role="tab"
				aria-selected="<?php echo 'login' === $active_tab ? 'true' : 'false'; ?>"
				aria-controls="gl-account-login-panel"
				data-gl-auth-tab="login"
			>
				<?php esc_html_e( 'Вход', 'gelikon' ); ?>
			</button>
			<button
				type="button"
				class="gl-myaccount-auth__tab <?php echo 'register' === $active_tab ? 'is-active' : ''; ?>"
				id="gl-account-register-tab"
				role="tab"
				aria-selected="<?php echo 'register' === $active_tab ? 'true' : 'false'; ?>"
				aria-controls="gl-account-register-panel"
				data-gl-auth-tab="register"
			>
				<?php esc_html_e( 'Регистрация', 'gelikon' ); ?>
			</button>
		</div>
	<?php endif; ?>

	<div class="gl-myaccount-auth__panels">
		<section
			class="gl-myaccount-auth__panel <?php echo 'login' === $active_tab ? 'is-active' : ''; ?>"
			id="gl-account-login-panel"
			role="tabpanel"
			aria-labelledby="gl-account-login-tab"
			<?php echo 'login' === $active_tab ? '' : 'hidden'; ?>
		>
			<h2 class="gl-myaccount-auth__title"><?php esc_html_e( 'Вход', 'gelikon' ); ?></h2>

			<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="username"><?php esc_html_e( 'Имя пользователя или email', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
				</p>
				<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
					<label for="password"><?php esc_html_e( 'Пароль', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
					<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
				</p>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<p class="form-row gl-myaccount-auth__actions">
					<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
						<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Запомнить меня', 'woocommerce' ); ?></span>
					</label>
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( $button_class ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Войти', 'woocommerce' ); ?></button>
				</p>
				<p class="woocommerce-LostPassword lost_password">
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Забыли пароль?', 'woocommerce' ); ?></a>
				</p>

				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>
		</section>

		<?php if ( $registration_enabled ) : ?>
			<section
				class="gl-myaccount-auth__panel <?php echo 'register' === $active_tab ? 'is-active' : ''; ?>"
				id="gl-account-register-panel"
				role="tabpanel"
				aria-labelledby="gl-account-register-tab"
				<?php echo 'register' === $active_tab ? '' : 'hidden'; ?>
			>
				<h2 class="gl-myaccount-auth__title"><?php esc_html_e( 'Регистрация', 'gelikon' ); ?></h2>

				<form method="post" class="woocommerce-form woocommerce-form-register register" novalidate>
					<?php do_action( 'woocommerce_register_form_start' ); ?>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_username"><?php esc_html_e( 'Имя пользователя', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
						</p>
					<?php endif; ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_email"><?php esc_html_e( 'Email', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) && is_string( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
					</p>

					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_password"><?php esc_html_e( 'Пароль', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
						</p>
					<?php else : ?>
						<p class="gl-myaccount-auth__hint"><?php esc_html_e( 'Имя пользователя и сгенерированный пароль будут отправлены на ваш email.', 'woocommerce' ); ?></p>
					<?php endif; ?>

					<?php do_action( 'woocommerce_register_form' ); ?>

					<p class="woocommerce-form-row form-row gl-myaccount-auth__actions">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit<?php echo esc_attr( $button_class ); ?>" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Зарегистрироваться', 'woocommerce' ); ?></button>
					</p>

					<?php do_action( 'woocommerce_register_form_end' ); ?>
				</form>
			</section>
		<?php endif; ?>
	</div>
</div>

<style>
	.woocommerce-form__label.woocommerce-form__label-for-checkbox.woocommerce-form-login__rememberme {
   display: flex;
}
	
.gl-myaccount-auth .woocommerce-form-login__rememberme .woocommerce-form__input-checkbox:checked::after{
	width: 4px;
}
</style>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>