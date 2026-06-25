<?php
/**
 * Template Name: Гарантия и возврат
 * Template Post Type: page
 */

defined('ABSPATH') || exit;

get_header();

if (!function_exists('gelikon_warranty_get_field')) {
	function gelikon_warranty_get_field($field_name, $default = '', $post_id = 0) {
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

$hero_title = gelikon_warranty_get_field('warranty_hero_title', 'Гарантия и возврат', $page_id);
$hero_subtitle = gelikon_warranty_get_field('warranty_hero_subtitle', 'Прозрачные условия обслуживания и возврата продукции', $page_id);

$benefits = gelikon_warranty_get_field('warranty_benefits', [], $page_id);
if (empty($benefits) || !is_array($benefits)) {
	$benefits = [
		['title' => 'Гарантия 1 год', 'text' => 'На всю продукцию бренда'],
		['title' => '14 дней на проверку', 'text' => 'Для обмена и возврата'],
		['title' => 'Простой возврат', 'text' => 'Понятный порядок оформления'],
	];
}

$warranty_text = gelikon_warranty_get_field('warranty_main_text', 'На все товары бренда предоставляется гарантия 1 год, если иной срок не указан в описании конкретной модели.
Гарантия распространяется на производственные дефекты и неисправности, возникшие при нормальной эксплуатации устройства.', $page_id);

$return_conditions = gelikon_warranty_get_field('warranty_return_conditions', "сохранён товарный вид и отсутствуют следы эксплуатации\nсохранена оригинальная упаковка и защитные плёнки\nтовар возвращается в полной комплектации\nимеется документ, подтверждающий покупку", $page_id);

$warranty_exclusions = gelikon_warranty_get_field('warranty_exclusions', "механические повреждения\nповреждения от попадания жидкости\nестественный износ аккумуляторов\nнеисправности, вызванные нарушением правил эксплуатации", $page_id);

$return_steps = gelikon_warranty_get_field('warranty_return_steps', "Сообщить о возврате по телефону или электронной почте\nОтправить или передать товар в офис компании\nПриложить заявление на возврат и документ о покупке", $page_id);

$refund_text = gelikon_warranty_get_field('warranty_refund_text', 'Возврат средств осуществляется на банковский счёт в течение до 10 рабочих дней после получения и проверки товара.
В большинстве случаев возврат выполняется в течение 1–2 рабочих дней.', $page_id);

$defect_text = gelikon_warranty_get_field('warranty_defect_text', "Если обнаружен производственный дефект:\nтовар принимается на экспертизу\nсрок экспертизы — до 20 рабочих дней (обычно 1–3 дня)\nпри подтверждении брака возвращается полная стоимость товара и доставки", $page_id);

$return_address = gelikon_warranty_get_field('warranty_return_address', "ООО «Геликон Лайн»\n127254, Москва, ул. Складочная, д. 1, стр. 18, офис 205", $page_id);
$return_schedule = gelikon_warranty_get_field('warranty_return_schedule', 'Пн–Пт, 9:00–17:00', $page_id);
$return_phones = gelikon_warranty_get_field('warranty_phones', "8-800-444-68-67\n+7 (495) 604-48-43", $page_id);
$return_email = gelikon_warranty_get_field('warranty_email', 'info@gelikon-line.ru', $page_id);
?>

<main id="primary" class="site-main gl-about-page gl-warranty-page">
	<section class="gl-about-hero">
		<div class="gl-about-hero__overlay"></div>
		<div class="gl-container gl-about-hero__content">
			<h1><?php echo esc_html($hero_title); ?></h1>
			<p><?php echo esc_html($hero_subtitle); ?></p>
		</div>
	</section>

	<div class="gl-container gl-page-single gl-about-sections">
		
		
<section class="gl-home-trust gl-card">
	<div class="gl-trust-grid">
		<?php foreach ($benefits as $benefit) : ?>
			<a class="gl-trust-item" href="<?php echo esc_url(home_url('/user-agreement/')); ?>">
				<div class="gl-trust-item__icon" aria-hidden="true">
					<?php if (!empty($benefit['icon'])) : ?>
						<?php if (is_array($benefit['icon']) && !empty($benefit['icon']['url'])) : ?>
							<img
								src="<?php echo esc_url($benefit['icon']['url']); ?>"
								alt="<?php echo esc_attr($benefit['icon']['alt'] ?? ''); ?>"
								loading="lazy"
							>
						<?php elseif (is_string($benefit['icon'])) : ?>
							<?php echo $benefit['icon']; ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<h3><?php echo esc_html($benefit['title'] ?? ''); ?></h3>
				<p><?php echo esc_html($benefit['text'] ?? ''); ?></p>
			</a>
		<?php endforeach; ?>
	</div>
</section>

		<section class="gl-card"><h2>Гарантия</h2><div class="gl-about-text"><?php echo wp_kses_post(wpautop($warranty_text)); ?></div></section>
		<section class="gl-card"><h2>Условия возврата</h2><p>Возврат товара возможен в течение 14 дней с момента получения при соблюдении следующих условий:</p><div class="gl-warranty-list"><?php echo wp_kses_post(wpautop($return_conditions)); ?></div></section>
		<section class="gl-card"><h2>Когда гарантия не действует</h2><p>Гарантия не распространяется на:</p><div class="gl-warranty-list"><?php echo wp_kses_post(wpautop($warranty_exclusions)); ?></div></section>
		<section class="gl-card"><h2>Как оформить возврат</h2><div class="gl-warranty-list"><?php echo wp_kses_post(wpautop($return_steps)); ?></div></section>
		<section class="gl-card"><h2>Возврат денежных средств</h2><div class="gl-about-text"><?php echo wp_kses_post(wpautop($refund_text)); ?></div></section>
		<section class="gl-card"><h2>Товары ненадлежащего качества</h2><div class="gl-warranty-list"><?php echo wp_kses_post(wpautop($defect_text)); ?></div></section>
		<section class="gl-card"><h2>Адрес для возврата</h2><div class="gl-about-text"><?php echo wp_kses_post(wpautop($return_address)); ?></div><p><strong>График работы:</strong> <?php echo esc_html($return_schedule); ?></p></section>
		<section class="gl-card"><h2>Контакты</h2><p><strong>Телефон:</strong><br><?php echo nl2br(esc_html($return_phones)); ?></p><p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($return_email); ?>"><?php echo esc_html($return_email); ?></a></p></section>
	</div>
</main>

<style>
	.gl-card{
		padding: 28px;
	}
	.gl-warranty-list p { margin: 0 0 10px; }
.gl-warranty-list p:last-child { margin-bottom: 0; }
	
	
	.gl-trust-item__icon{
		display: flex;
align-items: center;
justify-content: center;
background: linear-gradient(
180deg,
rgba(44,188,99,.12),
rgba(44,188,99,.03));
	}
</style>

<?php get_footer(); ?>