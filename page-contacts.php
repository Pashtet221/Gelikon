<?php
/**
 * Template Name: Контакты Gelikon
 * Template Post Type: page
 */

defined('ABSPATH') || exit;

get_header();

$page_id = (int) get_queried_object_id();

if (!function_exists('gelikon_contacts_get_field')) {
	function gelikon_contacts_get_field($field_name, $default = '', $post_id = 0) {
		if (function_exists('get_field')) {
			$value = get_field($field_name, $post_id);
			if ($value !== null && $value !== false && $value !== '') {
				return $value;
			}
		}

		return $default;
	}
}

if (!function_exists('gelikon_contacts_file_url')) {
	function gelikon_contacts_file_url($file_field) {
		if (empty($file_field)) {
			return '';
		}

		if (is_array($file_field) && !empty($file_field['url'])) {
			return $file_field['url'];
		}

		if (is_numeric($file_field)) {
			return wp_get_attachment_url((int) $file_field) ?: '';
		}

		if (is_string($file_field)) {
			return $file_field;
		}

		return '';
	}
}

$address = gelikon_contacts_get_field('company_address', "Москва, ул. Складочная, д. 1, стр. 18\nБЦ «Станколит», подъезд 12, офис 205", $page_id);
$phones = gelikon_contacts_get_field('company_phones', "8-800-444-68-67\n+7 (495) 604-48-43", $page_id);
$email = gelikon_contacts_get_field('company_email', 'info@gelikon-line.ru', $page_id);
$work_hours = gelikon_contacts_get_field('company_work_hours', 'Пн–Пт, 9:00–17:00', $page_id);

$location_group = gelikon_contacts_get_field('company_location', [], $page_id);
$latitude = !empty($location_group['latitude']) ? $location_group['latitude'] : '55.801435';
$longitude = !empty($location_group['longitude']) ? $location_group['longitude'] : '37.592231';
$yandex_link = !empty($location_group['yandex_maps_link']) ? $location_group['yandex_maps_link'] : 'https://yandex.ru/maps/?pt=37.592231,55.801435&z=17&l=map';
$location_image = !empty($location_group['location_scheme_image']) ? $location_group['location_scheme_image'] : '';
$location_image_url = '';
if (is_array($location_image) && !empty($location_image['url'])) {
	$location_image_url = $location_image['url'];
} elseif (is_numeric($location_image)) {
	$location_image_url = wp_get_attachment_url((int) $location_image);
}

$parking = gelikon_contacts_get_field('company_parking_note', 'Бесплатная парковка на территории БЦ «Станколит» (30 минут)', $page_id);

$company_name = gelikon_contacts_get_field('company_name', 'ООО «Геликон Лайн»', $page_id);
$inn = gelikon_contacts_get_field('company_inn', '7714198800', $page_id);
$ogrn = gelikon_contacts_get_field('company_ogrn', '1027700364702', $page_id);
$kpp = gelikon_contacts_get_field('company_kpp', '771401001', $page_id);
$legal_address = gelikon_contacts_get_field('company_legal_address', 'Москва, ул. Складочная, д. 1, стр. 18', $page_id);
$actual_address = gelikon_contacts_get_field('company_actual_address', 'Москва, ул. Складочная, д. 1, стр. 18, офис 205', $page_id);
$bank_details = gelikon_contacts_get_field('company_bank_details', 'ПАО Сбербанк, р/с 40702810900000000000, БИК 044525225', $page_id);
$director = gelikon_contacts_get_field('company_director', 'Генеральный директор — Иванов Иван Иванович', $page_id);
$details_pdf = gelikon_contacts_file_url(gelikon_contacts_get_field('company_details_pdf', '', $page_id));
?>

<main id="primary" class="site-main gl-contacts-page">
	<div class="gl-container gl-page-single">
		<section class="gl-card gl-contacts-hero">
			<h1>Контакты</h1>
			<p>Офис и служба поддержки в Москве</p>
		</section>

		<section class="gl-card gl-contacts-block">
			<h2>Основные контакты</h2>
			<div class="gl-contacts-grid">
				<div>
					<div class="gl-contacts-label">Адрес</div>
					<div class="gl-contacts-value"><?php echo nl2br(esc_html($address)); ?></div>
				</div>
				<div>
					<div class="gl-contacts-label">Телефон</div>
					<div class="gl-contacts-value"><?php echo nl2br(esc_html($phones)); ?></div>
				</div>
				<div>
					<div class="gl-contacts-label">Email</div>
					<div class="gl-contacts-value"><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></div>
				</div>
				<div>
					<div class="gl-contacts-label">График работы</div>
					<div class="gl-contacts-value"><?php echo esc_html($work_hours); ?></div>
				</div>
			</div>
		</section>

		<section class="gl-card gl-contacts-block">
			<h2>Карта и навигация</h2>
			<div class="gl-contacts-map-grid">
				<div>
					<div class="gl-contacts-label">Координаты</div>
					<div class="gl-contacts-value"><?php echo esc_html($latitude . ', ' . $longitude); ?></div>
					<div class="gl-contacts-note"><?php echo esc_html($parking); ?></div>
					<a class="gl-btn gl-btn--ghost" href="<?php echo esc_url($yandex_link); ?>" target="_blank" rel="noopener">Открыть в Яндекс.Картах →</a>
				</div>
				<div class="gl-contacts-map-card">
					<?php if (!empty($location_image_url)) : ?>
						<img src="<?php echo esc_url($location_image_url); ?>" alt="Схема проезда">
					<?php else : ?>
						<div class="gl-contacts-map-placeholder">[картинка карты]</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="gl-card gl-contacts-block gl-company-details" data-details>
			<h2>Реквизиты компании</h2>
			<div class="gl-contacts-value">
				<strong><?php echo esc_html($company_name); ?></strong><br>
				ИНН <?php echo esc_html($inn); ?><br>
				ОГРН <?php echo esc_html($ogrn); ?>
			</div>
			<div class="gl-company-details__more" hidden>
				<p><strong>КПП:</strong> <?php echo esc_html($kpp); ?></p>
				<p><strong>Юридический адрес:</strong> <?php echo esc_html($legal_address); ?></p>
				<p><strong>Фактический адрес:</strong> <?php echo esc_html($actual_address); ?></p>
				<p><strong>Банковские реквизиты:</strong> <?php echo esc_html($bank_details); ?></p>
				<p><strong>Генеральный директор:</strong> <?php echo esc_html($director); ?></p>
			</div>
			<div class="gl-company-details__actions">
				<button type="button" class="gl-btn gl-btn--ghost" data-details-toggle>Показать полностью</button>
				<?php if (!empty($details_pdf)) : ?>
					<a class="gl-btn gl-btn--primary" href="<?php echo esc_url($details_pdf); ?>" target="_blank" rel="noopener">Скачать реквизиты PDF</a>
				<?php endif; ?>
			</div>
		</section>
	</div>
</main>

<?php get_footer(); ?>
