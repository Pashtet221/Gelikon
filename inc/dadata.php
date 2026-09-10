<?php
/**
 * DaData address suggestions for the WooCommerce checkout.
 *
 * @package Gelikon
 */

defined('ABSPATH') || exit;

/**
 * Return the DaData API token. A constant is preferable on production sites.
 */
function gelikon_dadata_token() {
	if (defined('GELIKON_DADATA_TOKEN') && GELIKON_DADATA_TOKEN) {
		return trim((string) GELIKON_DADATA_TOKEN);
	}

	return trim((string) get_theme_mod('gelikon_dadata_token', ''));
}

/**
 * Add the token field to the existing theme settings panel.
 */
function gelikon_dadata_customize_register($wp_customize) {
	$wp_customize->add_section('gelikon_dadata', [
		'title'       => __('Подсказки адреса DaData', 'gelikon'),
		'panel'       => 'gelikon_theme_options',
		'priority'    => 38,
		'description' => __('Укажите API-ключ сервиса DaData для подсказок города, улицы и дома на странице оформления заказа.', 'gelikon'),
	]);

	$wp_customize->add_setting('gelikon_dadata_token', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	]);

	$wp_customize->add_control('gelikon_dadata_token', [
		'label'       => __('API-ключ DaData', 'gelikon'),
		'description' => __('Можно безопаснее задать ключ константой GELIKON_DADATA_TOKEN в wp-config.php. Константа имеет приоритет.', 'gelikon'),
		'section'     => 'gelikon_dadata',
		'type'        => 'password',
	]);
}
add_action('customize_register', 'gelikon_dadata_customize_register', 20);

/**
 * Load the checkout autocomplete only when it has been configured.
 */
function gelikon_dadata_enqueue_checkout_assets() {
	if (!function_exists('is_checkout') || !is_checkout() || is_order_received_page() || !gelikon_dadata_token()) {
		return;
	}

	wp_enqueue_style('gelikon-dadata', GELIKON_URI . '/assets/css/dadata.css', [], GELIKON_VERSION);
	wp_enqueue_script('gelikon-dadata', GELIKON_URI . '/assets/js/dadata.js', ['jquery'], GELIKON_VERSION, true);
	wp_localize_script('gelikon-dadata', 'gelikonDadata', [
		'ajaxUrl'  => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('gelikon_dadata_suggest'),
		'minChars' => 2,
		'messages' => [
			'loading' => __('Ищем варианты…', 'gelikon'),
			'empty'   => __('Ничего не найдено', 'gelikon'),
			'error'   => __('Не удалось загрузить подсказки', 'gelikon'),
		],
	]);
}
add_action('wp_enqueue_scripts', 'gelikon_dadata_enqueue_checkout_assets', 30);

/**
 * Proxy requests so the DaData token is never exposed in the page source.
 */
function gelikon_dadata_ajax_suggest() {
	check_ajax_referer('gelikon_dadata_suggest', 'nonce');

	$token = gelikon_dadata_token();
	$mode  = isset($_POST['mode']) ? sanitize_key(wp_unslash($_POST['mode'])) : '';
	$query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';
	$city  = isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '';

	if (!$token || !in_array($mode, ['city', 'address'], true) || mb_strlen($query) < 2) {
		wp_send_json_error(['message' => __('Некорректный запрос подсказок.', 'gelikon')], 400);
	}

	$body = [
		'query' => 'address' === $mode && $city ? $city . ', ' . $query : $query,
		'count' => 7,
	];

	if ('city' === $mode) {
		$body['from_bound'] = ['value' => 'city'];
		$body['to_bound']   = ['value' => 'settlement'];
	} else {
		$body['from_bound'] = ['value' => 'street'];
		$body['to_bound']   = ['value' => 'house'];
	}

	$response = wp_remote_post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address', [
		'timeout' => 8,
		'headers' => [
			'Authorization' => 'Token ' . $token,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		],
		'body' => wp_json_encode($body),
	]);

	if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
		wp_send_json_error(['message' => __('Сервис подсказок временно недоступен.', 'gelikon')], 502);
	}

	$payload = json_decode(wp_remote_retrieve_body($response), true);
	$items   = [];

	foreach ((array) ($payload['suggestions'] ?? []) as $suggestion) {
		$data = (array) ($suggestion['data'] ?? []);

		if ('city' === $mode) {
			$value = $data['city'] ?? $data['settlement'] ?? '';
		} else {
			$parts = array_filter([
				$data['street_with_type'] ?? '',
				trim(($data['house_type'] ?? '') . ' ' . ($data['house'] ?? '')),
				trim(($data['block_type'] ?? '') . ' ' . ($data['block'] ?? '')),
			]);
			$value = implode(', ', $parts);
		}

		if (!$value) {
			continue;
		}

		$items[] = [
			'value'    => $value,
			'label'    => (string) ($suggestion['value'] ?? $value),
			'city'     => (string) ($data['city'] ?? $data['settlement'] ?? ''),
			'kladr_id' => (string) ($data['kladr_id'] ?? ''),
		];
	}

	wp_send_json_success(['suggestions' => $items]);
}
add_action('wp_ajax_gelikon_dadata_suggest', 'gelikon_dadata_ajax_suggest');
add_action('wp_ajax_nopriv_gelikon_dadata_suggest', 'gelikon_dadata_ajax_suggest');
