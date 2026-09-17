<?php
/** Category-based WooCommerce discounts. @package Gelikon */
defined('ABSPATH') || exit;

const GELIKON_CATEGORY_DISCOUNT_OPTION = 'gelikon_category_discount_percent';
const GELIKON_CATEGORY_DISCOUNT_MODE = 'gelikon_discount_mode';
const GELIKON_CATEGORY_DISCOUNT_VALUE = 'gelikon_discount_percent';

function gelikon_sanitize_discount_percent($value) {
	return min(100, max(0, (float) str_replace(',', '.', (string) $value)));
}

function gelikon_register_category_discounts_page() {
	add_submenu_page('woocommerce', __('Скидки по категориям', 'gelikon'), __('Скидки по категориям', 'gelikon'), 'manage_woocommerce', 'gelikon-category-discounts', 'gelikon_render_category_discounts_page');
}
add_action('admin_menu', 'gelikon_register_category_discounts_page', 60);

function gelikon_save_category_discounts() {
	if (!current_user_can('manage_woocommerce')) {
		wp_die(esc_html__('Недостаточно прав.', 'gelikon'));
	}
	check_admin_referer('gelikon_save_category_discounts');
	$global = isset($_POST['global_percent']) ? gelikon_sanitize_discount_percent(wp_unslash($_POST['global_percent'])) : 0;
	update_option(GELIKON_CATEGORY_DISCOUNT_OPTION, $global);
	$modes = isset($_POST['category_mode']) && is_array($_POST['category_mode']) ? wp_unslash($_POST['category_mode']) : [];
	$values = isset($_POST['category_percent']) && is_array($_POST['category_percent']) ? wp_unslash($_POST['category_percent']) : [];
	foreach ($modes as $term_id => $mode) {
		$term_id = absint($term_id);
		$mode = in_array($mode, ['inherit', 'custom', 'exclude'], true) ? $mode : 'inherit';
		if (!$term_id || !term_exists($term_id, 'product_cat')) {
			continue;
		}
		if ('inherit' === $mode) {
			delete_term_meta($term_id, GELIKON_CATEGORY_DISCOUNT_MODE);
			delete_term_meta($term_id, GELIKON_CATEGORY_DISCOUNT_VALUE);
		} else {
			update_term_meta($term_id, GELIKON_CATEGORY_DISCOUNT_MODE, $mode);
			if ('custom' === $mode) {
				update_term_meta($term_id, GELIKON_CATEGORY_DISCOUNT_VALUE, gelikon_sanitize_discount_percent($values[$term_id] ?? 0));
			} else {
				delete_term_meta($term_id, GELIKON_CATEGORY_DISCOUNT_VALUE);
			}
		}
	}
	update_option('gelikon_category_discount_version', time());
	if (function_exists('wc_delete_product_transients')) {
		wc_delete_product_transients();
	}
	wp_safe_redirect(add_query_arg(['page' => 'gelikon-category-discounts', 'updated' => '1'], admin_url('admin.php')));
	exit;
}
add_action('admin_post_gelikon_save_category_discounts', 'gelikon_save_category_discounts');

function gelikon_render_category_discounts_page() {
	if (!current_user_can('manage_woocommerce')) {
		return;
	}
	$categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
	?>
	<div class="wrap">
		<h1><?php esc_html_e('Скидки по категориям', 'gelikon'); ?></h1>
		<?php if (!empty($_GET['updated'])) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Настройки скидок сохранены.', 'gelikon'); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e('Выберите общую скидку, отдельный процент или исключение. Правило наиболее вложенной категории имеет приоритет.', 'gelikon'); ?></p>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
			<input type="hidden" name="action" value="gelikon_save_category_discounts"><?php wp_nonce_field('gelikon_save_category_discounts'); ?>
			<table class="form-table" role="presentation"><tr><th scope="row"><label for="gelikon-global-percent"><?php esc_html_e('Общая скидка', 'gelikon'); ?></label></th><td><input id="gelikon-global-percent" name="global_percent" type="number" min="0" max="100" step="0.01" value="<?php echo esc_attr(get_option(GELIKON_CATEGORY_DISCOUNT_OPTION, 0)); ?>"> %<p class="description"><?php esc_html_e('0% отключает общую автоматическую скидку.', 'gelikon'); ?></p></td></tr></table>
			<h2><?php esc_html_e('Категории', 'gelikon'); ?></h2>
			<table class="widefat striped"><thead><tr><th><?php esc_html_e('Категория', 'gelikon'); ?></th><th><?php esc_html_e('Режим', 'gelikon'); ?></th><th><?php esc_html_e('Процент', 'gelikon'); ?></th></tr></thead><tbody>
			<?php foreach ($categories as $category) :
				$mode = get_term_meta($category->term_id, GELIKON_CATEGORY_DISCOUNT_MODE, true) ?: 'inherit';
				$value = get_term_meta($category->term_id, GELIKON_CATEGORY_DISCOUNT_VALUE, true);
				$depth = count(get_ancestors($category->term_id, 'product_cat', 'taxonomy'));
				?>
				<tr><td><?php echo esc_html(str_repeat('— ', $depth) . $category->name); ?></td><td><select name="category_mode[<?php echo esc_attr($category->term_id); ?>]"><option value="inherit" <?php selected($mode, 'inherit'); ?>><?php esc_html_e('Общий процент', 'gelikon'); ?></option><option value="custom" <?php selected($mode, 'custom'); ?>><?php esc_html_e('Отдельный процент', 'gelikon'); ?></option><option value="exclude" <?php selected($mode, 'exclude'); ?>><?php esc_html_e('Исключить', 'gelikon'); ?></option></select></td><td><input name="category_percent[<?php echo esc_attr($category->term_id); ?>]" type="number" min="0" max="100" step="0.01" value="<?php echo esc_attr($value); ?>"> %</td></tr>
			<?php endforeach; ?>
			</tbody></table><?php submit_button(__('Сохранить скидки', 'gelikon')); ?>
		</form>
	</div>
	<?php
}

function gelikon_get_product_category_discount($product) {
	if (!$product instanceof WC_Product) {
		return 0;
	}
	$product_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();
	$candidates = [];
	foreach (wc_get_product_term_ids($product_id, 'product_cat') as $term_id) {
		foreach (array_unique(array_merge([$term_id], get_ancestors($term_id, 'product_cat', 'taxonomy'))) as $candidate_id) {
			$mode = get_term_meta($candidate_id, GELIKON_CATEGORY_DISCOUNT_MODE, true);
			if (in_array($mode, ['custom', 'exclude'], true)) {
				$candidates[$candidate_id] = ['id' => (int) $candidate_id, 'depth' => count(get_ancestors($candidate_id, 'product_cat', 'taxonomy')), 'mode' => $mode];
			}
		}
	}
	if ($candidates) {
		usort($candidates, static function ($a, $b) {
			return ($b['depth'] <=> $a['depth']) ?: (('exclude' === $b['mode']) <=> ('exclude' === $a['mode'])) ?: ($a['id'] <=> $b['id']);
		});
		$rule = reset($candidates);
		return 'exclude' === $rule['mode'] ? 0 : gelikon_sanitize_discount_percent(get_term_meta($rule['id'], GELIKON_CATEGORY_DISCOUNT_VALUE, true));
	}
	return gelikon_sanitize_discount_percent(get_option(GELIKON_CATEGORY_DISCOUNT_OPTION, 0));
}

function gelikon_apply_category_discount_to_price($price, $product) {
	if ((is_admin() && !wp_doing_ajax()) || !$product instanceof WC_Product) {
		return $price;
	}
	$percent = gelikon_get_product_category_discount($product);
	$regular = (float) $product->get_regular_price('edit');
	if ($percent <= 0 || $regular <= 0) {
		return $price;
	}
	$discounted = round($regular * (1 - ($percent / 100)), wc_get_price_decimals());
	$current = (float) $price;
	return (string) (($current > 0 && $current < $discounted) ? $current : $discounted);
}
add_filter('woocommerce_product_get_price', 'gelikon_apply_category_discount_to_price', 20, 2);
add_filter('woocommerce_product_get_sale_price', 'gelikon_apply_category_discount_to_price', 20, 2);
add_filter('woocommerce_product_variation_get_price', 'gelikon_apply_category_discount_to_price', 20, 2);
add_filter('woocommerce_product_variation_get_sale_price', 'gelikon_apply_category_discount_to_price', 20, 2);
add_filter('woocommerce_variation_prices_price', 'gelikon_apply_category_discount_to_price', 20, 2);
add_filter('woocommerce_variation_prices_sale_price', 'gelikon_apply_category_discount_to_price', 20, 2);

function gelikon_category_discount_price_hash($hash) {
	$hash['gelikon_category_discount'] = get_option(GELIKON_CATEGORY_DISCOUNT_OPTION, 0) . ':' . get_option('gelikon_category_discount_version', 1);
	return $hash;
}
add_filter('woocommerce_get_variation_prices_hash', 'gelikon_category_discount_price_hash');

function gelikon_get_actual_discount_percent($product) {
	if (!$product instanceof WC_Product) {
		return 0;
	}
	if ($product->is_type('variable')) {
		$percentages = array_map(static function ($variation_id) { return gelikon_get_actual_discount_percent(wc_get_product($variation_id)); }, $product->get_children());
		return $percentages ? max($percentages) : 0;
	}
	$regular = (float) $product->get_regular_price('edit');
	$current = (float) $product->get_price();
	return ($regular > 0 && $current < $regular) ? (int) round((($regular - $current) / $regular) * 100) : 0;
}

function gelikon_add_variation_discount_data($data, $product, $variation) {
	$data['gelikon_discount_percent'] = gelikon_get_actual_discount_percent($variation);
	return $data;
}
add_filter('woocommerce_available_variation', 'gelikon_add_variation_discount_data', 20, 3);
