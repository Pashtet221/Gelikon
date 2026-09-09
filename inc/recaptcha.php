<?php
/**
 * Google reCAPTCHA v3 protection for public forms.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!defined('GELIKON_RECAPTCHA_SITE_KEY')) {
	define('GELIKON_RECAPTCHA_SITE_KEY', '6LeP0LItAAAAAIsmTpuD0QDy2nPMYUfDE0FwAA-t');
}
if (!defined('GELIKON_RECAPTCHA_SECRET_KEY')) {
	define('GELIKON_RECAPTCHA_SECRET_KEY', '6LeP0LItAAAAAMjykqFKWrRZcUGOhRqP_W8Tq_19');
}

/**
 * Load the v3 client on every public page: forms can also be rendered by plugins.
 */
function gelikon_recaptcha_enqueue_assets() {
	if (is_admin()) {
		return;
	}

	wp_enqueue_script(
		'google-recaptcha-v3',
		'https://www.google.com/recaptcha/api.js?render=' . rawurlencode(GELIKON_RECAPTCHA_SITE_KEY),
		[],
		null,
		true
	);
	wp_enqueue_script(
		'gelikon-recaptcha-v3',
		GELIKON_URI . '/assets/js/recaptcha.js',
		['google-recaptcha-v3'],
		GELIKON_VERSION,
		true
	);
	wp_localize_script('gelikon-recaptcha-v3', 'gelikonRecaptcha', [
		'siteKey' => GELIKON_RECAPTCHA_SITE_KEY,
		'error'   => __('Не удалось пройти проверку reCAPTCHA. Обновите страницу и попробуйте ещё раз.', 'gelikon'),
	]);
}
add_action('wp_enqueue_scripts', 'gelikon_recaptcha_enqueue_assets', 40);

/**
 * Verify a token once per request. Fail closed when Google cannot be reached.
 */
function gelikon_recaptcha_verify_request() {
	static $result = null;

	if (null !== $result) {
		return $result;
	}

	$token  = isset($_POST['gelikon_recaptcha_token']) ? sanitize_text_field(wp_unslash($_POST['gelikon_recaptcha_token'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$action = isset($_POST['gelikon_recaptcha_action']) ? sanitize_key(wp_unslash($_POST['gelikon_recaptcha_action'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if (!$token || !$action) {
		$result = false;
		return $result;
	}

	$response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
		'timeout' => 10,
		'body'    => [
			'secret'   => GELIKON_RECAPTCHA_SECRET_KEY,
			'response' => $token,
			'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
		],
	]);

	if (is_wp_error($response)) {
		$result = false;
		return $result;
	}

	$body = json_decode(wp_remote_retrieve_body($response), true);
	$result = !empty($body['success'])
		&& isset($body['score'])
		&& (float) $body['score'] >= (float) apply_filters('gelikon_recaptcha_min_score', 0.5)
		&& isset($body['action'])
		&& hash_equals($action, sanitize_key($body['action']));

	return $result;
}

function gelikon_recaptcha_error_message() {
	return __('Проверка reCAPTCHA не пройдена. Обновите страницу и попробуйте ещё раз.', 'gelikon');
}

/**
 * Protect plugin/theme POST forms which do not expose a dedicated validation hook.
 * AJAX requests are handled by their owning endpoint (including WooCommerce below),
 * so background cart/search requests are not accidentally blocked.
 */
add_action('init', function () {
	if (
		'POST' !== ($_SERVER['REQUEST_METHOD'] ?? '')
		|| is_admin()
		|| wp_doing_ajax()
		|| (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)
	) {
		return;
	}

	if (!gelikon_recaptcha_verify_request()) {
		wp_die(
			esc_html(gelikon_recaptcha_error_message()),
			esc_html__('Ошибка reCAPTCHA', 'gelikon'),
			['response' => 403, 'back_link' => true]
		);
	}
}, 1);

add_filter('woocommerce_registration_errors', function ($errors) {
	if (!gelikon_recaptcha_verify_request()) {
		$errors->add('recaptcha_error', gelikon_recaptcha_error_message());
	}
	return $errors;
}, 20);

add_filter('woocommerce_process_login_errors', function ($errors) {
	if (!gelikon_recaptcha_verify_request()) {
		$errors->add('recaptcha_error', gelikon_recaptcha_error_message());
	}
	return $errors;
}, 20);

add_action('lostpassword_post', function ($errors) {
	if (!gelikon_recaptcha_verify_request()) {
		$errors->add('recaptcha_error', gelikon_recaptcha_error_message());
	}
});

add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
	if (!gelikon_recaptcha_verify_request()) {
		$errors->add('recaptcha_error', gelikon_recaptcha_error_message());
	}
}, 20, 2);

add_filter('woocommerce_add_to_cart_validation', function ($valid) {
	if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && !gelikon_recaptcha_verify_request()) {
		wc_add_notice(gelikon_recaptcha_error_message(), 'error');
		return false;
	}
	return $valid;
}, 20);

add_filter('preprocess_comment', function ($commentdata) {
	if (!gelikon_recaptcha_verify_request()) {
		wp_die(
			esc_html(gelikon_recaptcha_error_message()),
			esc_html__('Ошибка reCAPTCHA', 'gelikon'),
			['response' => 403, 'back_link' => true]
		);
	}
	return $commentdata;
}, 5);
