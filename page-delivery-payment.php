<?php
/**
 * Template Name: Доставка и оплата
 * Template Post Type: page
 */

defined('ABSPATH') || exit;

get_header();

if (!function_exists('gelikon_delivery_get_field')) {
	function gelikon_delivery_get_field($field_name, $default = '', $post_id = 0) {
		if (function_exists('get_field')) {
			$value = get_field($field_name, $post_id);

			if ($value !== null && $value !== false && $value !== '') {
				return $value;
			}
		}

		return $default;
	}
}

$page_id = (int) get_queried_object_id();

$hero_title    = gelikon_delivery_get_field('delivery_hero_title', 'Доставка и оплата', $page_id);
$hero_subtitle = gelikon_delivery_get_field('delivery_hero_subtitle', 'Получение заказа по всей России', $page_id);

$global_benefits = gelikon_delivery_get_field('global_why_buy_items', [], $page_id);
if (empty($global_benefits) || !is_array($global_benefits)) {
	$global_benefits = [
		[
			'title'       => 'Бесплатная доставка',
			'description' => 'при заказе от 3000 ₽ по всей России',
		],
		[
			'title'       => '14 дней на проверку',
			'description' => 'протестируйте товар дома без риска',
		],
		[
			'title'       => 'Защищённая оплата',
			'description' => 'безопасные платежи через проверенные системы',
		],
		[
			'title'       => 'Рассрочка',
			'description' => 'покупайте сейчас — платите позже без переплат',
		],
	];
}

$pickup_methods = gelikon_delivery_get_field('delivery_pickup_methods', [], $page_id);
if (empty($pickup_methods) || !is_array($pickup_methods)) {
	$pickup_methods = [
		['title' => 'Самовывоз'],
		['title' => 'Пункт выдачи'],
		['title' => 'Курьерская доставка'],
	];
}

$delivery_terms = gelikon_delivery_get_field('delivery_terms_table', [], $page_id);
if (empty($delivery_terms) || !is_array($delivery_terms)) {
	$delivery_terms = [
		['method' => 'Пункт выдачи', 'term' => '1–2 дня'],
		['method' => 'Курьер', 'term' => '1–3 дня'],
	];
}

$payment_methods = gelikon_delivery_get_field('delivery_payment_methods', [], $page_id);
if (empty($payment_methods) || !is_array($payment_methods)) {
	$payment_methods = [
		['title' => 'Банковская карта'],
		['title' => 'СБП'],
		['title' => 'Рассрочка'],
		['title' => 'Оплата при получении'],
		['title' => 'Безналичный счёт'],
	];
}

$check_return_items = gelikon_delivery_get_field('delivery_check_return_items', [], $page_id);
if (empty($check_return_items) || !is_array($check_return_items)) {
	$check_return_items = [
		['text' => 'Проверка товара при получении'],
		['text' => '14 дней на тестирование'],
		['text' => 'Возврат в соответствии с законодательством РФ.'],
	];
}
?>

<main id="primary" class="site-main gl-about-page gl-delivery-page">

	<section class="gl-about-hero">
		<div class="gl-about-hero__overlay"></div>

		<div class="gl-container gl-about-hero__content">
			<h1><?php echo esc_html($hero_title); ?></h1>
			<p><?php echo esc_html($hero_subtitle); ?></p>
		</div>
	</section>

	<div class="gl-container gl-page-single gl-about-sections">

		<section class="gl-home-trust gl-card gl-delivery-benefits">
			<h2>Почему у нас покупают</h2>

			<div class="gl-trust-grid">
				<?php foreach ($global_benefits as $item) : ?>
					<article class="gl-trust-item">

						<div class="gl-trust-item__icon-wrap" aria-hidden="true">
							<?php if (!empty($item['icon']['url'])) : ?>
								<img
									class="gl-trust-item__icon-image"
									src="<?php echo esc_url($item['icon']['url']); ?>"
									alt=""
									loading="lazy"
								>
							<?php else : ?>
								<span class="gl-trust-item__icon"></span>
							<?php endif; ?>
						</div>

						<?php if (!empty($item['title'])) : ?>
							<h3><?php echo esc_html($item['title']); ?></h3>
						<?php endif; ?>

						<?php if (!empty($item['description'])) : ?>
							<p><?php echo wp_kses_post($item['description']); ?></p>
						<?php endif; ?>

					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="gl-card">
			<h2>Способы получения</h2>

			<div class="gl-delivery-cards">
				<?php foreach ($pickup_methods as $method) : ?>
					<article class="gl-delivery-card">

						<?php if (!empty($method['icon']['url'])) : ?>
							<img
								class="gl-delivery-card__icon-image"
								src="<?php echo esc_url($method['icon']['url']); ?>"
								alt="<?php echo esc_attr($method['title'] ?? ''); ?>"
								loading="lazy"
							>
						<?php endif; ?>

						<?php if (!empty($method['title'])) : ?>
							<h3><?php echo esc_html($method['title']); ?></h3>
						<?php endif; ?>

						<?php if (!empty($method['description'])) : ?>
							<p><?php echo wp_kses_post($method['description']); ?></p>
						<?php endif; ?>

					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="gl-card">
			<h2>Сроки доставки</h2>

			<div class="gl-delivery-table-wrap">
				<table class="gl-delivery-table">
					<tbody>
						<?php foreach ($delivery_terms as $row) : ?>
							<tr>
								<td><?php echo esc_html($row['method'] ?? ''); ?></td>
								<td><?php echo esc_html($row['term'] ?? ''); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</section>

		<section class="gl-card">
			<h2>Способы оплаты</h2>

			<div class="gl-delivery-cards gl-delivery-payments">
				<?php foreach ($payment_methods as $method) : ?>
					<article class="gl-delivery-card gl-delivery-card--iconed">

						<?php if (!empty($method['icon']['url'])) : ?>
							<img
								class="gl-delivery-card__icon-image"
								src="<?php echo esc_url($method['icon']['url']); ?>"
								alt=""
								loading="lazy"
							>
						<?php else : ?>
							<span class="gl-delivery-card__icon" aria-hidden="true"></span>
						<?php endif; ?>

						<?php echo esc_html($method['title'] ?? ''); ?>

					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="gl-card">
			<h2>Проверка и возврат</h2>

			<ul class="gl-delivery-list">
				<?php foreach ($check_return_items as $item) : ?>
					<li><?php echo esc_html($item['text'] ?? ''); ?></li>
				<?php endforeach; ?>
			</ul>
		</section>

	</div>
</main>

<style>
	.gl-card {
		padding: 28px;
	}

	.gl-delivery-cards {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
		gap: 16px;
	}

	.gl-delivery-card {
		padding: 18px 20px;
		background: var(--gl-color-surface-alt);
		border: 1px solid var(--gl-color-line);
		border-radius: var(--gl-radius-sm);
		font-weight: 600;
	}

	.gl-delivery-card--iconed {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.gl-delivery-card__icon {
		width: 18px;
		height: 18px;
		border-radius: 50%;
		background: var(--gl-color-accent);
		box-shadow: 0 0 0 4px color-mix(in srgb, var(--gl-color-accent) 20%, transparent);
	}

	.gl-delivery-card__icon-image {
		height: 28px;
		width: auto;
		object-fit: contain;
	}

	.gl-delivery-table {
		width: 100%;
		border-collapse: collapse;
	}

	.gl-delivery-table td {
		padding: 14px;
		border-bottom: 1px solid var(--gl-color-line);
	}

	.gl-delivery-table td:last-child {
		text-align: right;
		font-weight: 700;
	}

	.gl-delivery-list {
		margin: 0;
		padding-left: 22px;
		display: grid;
		gap: 10px;
	}

	.gl-delivery-benefits {
		padding: 34px !important;
		border-radius: 24px;
	}

	.gl-delivery-benefits h2 {
		margin: 0 0 26px;
		font-size: 30px;
		line-height: 1.2;
		font-weight: 800;
		color: var(--gl-color-text, #171b20);
	}

	.gl-delivery-benefits .gl-trust-grid {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 18px;
	}

	.gl-delivery-benefits .gl-trust-item {
		min-height: 210px;
		padding: 30px 34px;
		background: #fff;
		border: 1px solid var(--gl-color-line, #dde6df);
		border-radius: 20px;
		box-shadow: none;
	}

	.gl-delivery-benefits .gl-trust-item__icon-wrap {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 56px;
		height: 56px;
		margin-bottom: 28px;
		border-radius: 18px;
		background: #f0fbf3;
		border: 1px solid #bfe8cc;
		color: var(--gl-color-accent, #1f7a3d);
	}

	.gl-delivery-benefits .gl-trust-item__icon-image {
		display: block;
		width: 28px;
		height: 28px;
		object-fit: contain;
	}

	.gl-delivery-benefits .gl-trust-item__icon {
		display: block;
		width: 24px;
		height: 24px;
		border-radius: 50%;
		background: #beecc9;
	}

	.gl-delivery-benefits .gl-trust-item h3 {
		margin: 0 0 12px;
		font-size: 22px;
		line-height: 1.25;
		font-weight: 800;
		color: var(--gl-color-text, #171b20);
	}

	.gl-delivery-benefits .gl-trust-item p {
		margin: 0;
		font-size: 18px;
		line-height: 1.45;
		font-weight: 400;
		color: #667080;
	}

	@media (max-width: 1180px) {
		.gl-delivery-benefits .gl-trust-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	@media (max-width: 768px) {
		.gl-delivery-benefits {
			padding: 22px !important;
			border-radius: 20px;
		}

		.gl-delivery-benefits h2 {
			margin-bottom: 18px;
			font-size: 24px;
		}

		.gl-delivery-benefits .gl-trust-grid {
			grid-template-columns: 1fr;
			gap: 14px;
		}

		.gl-delivery-benefits .gl-trust-item {
			min-height: auto;
			padding: 22px;
		}

		.gl-delivery-benefits .gl-trust-item__icon-wrap {
			width: 52px;
			height: 52px;
			margin-bottom: 22px;
		}

		.gl-delivery-benefits .gl-trust-item h3 {
			font-size: 20px;
		}

		.gl-delivery-benefits .gl-trust-item p {
			font-size: 16px;
		}
	}
</style>

<?php get_footer(); ?>