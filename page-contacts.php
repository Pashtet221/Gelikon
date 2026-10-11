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
$details_content = gelikon_contacts_details_content($page_id);
$details_pdf = gelikon_contacts_file_url(gelikon_contacts_get_field('company_details_pdf', '', $page_id));
?>

<main id="primary" class="site-main gl-contacts-page">
	<div class="gl-container gl-page-single">
		<?php /*
		<section class="gl-card gl-contacts-hero">
			<h1>Контакты</h1>
			<p>Офис и служба поддержки в Москве</p>
		</section>
		*/ ?>

		<section class="gl-card gl-contacts-block">
			<h2>Основные контакты</h2>
			<div class="gl-contacts-grid">
				<div>
					<div class="gl-contacts-label">Адрес</div>
					<div class="gl-contacts-value"><?php echo gelikon_contacts_rich_text($address); ?></div>
				</div>
				<div>
					<div class="gl-contacts-label">Телефон</div>
					<div class="gl-contacts-value"><?php echo gelikon_contacts_rich_text($phones); ?></div>
				</div>
				<div>
					<div class="gl-contacts-label">Email</div>
					<div class="gl-contacts-value"><?php
						$email_text = trim(wp_strip_all_tags($email));
						if (is_email($email_text) && stripos($email, '<a') === false) {
							$email = str_replace($email_text, '<a href="' . esc_url('mailto:' . $email_text) . '">' . esc_html($email_text) . '</a>', $email);
						}
						echo gelikon_contacts_rich_text($email);
					?></div>
				</div>
				<div>
					<div class="gl-contacts-label">График работы</div>
					<div class="gl-contacts-value"><?php echo gelikon_contacts_rich_text($work_hours); ?></div>
				</div>
			</div>
		</section>

		<section class="gl-card gl-contacts-block">
			<h2>Карта и навигация</h2>
			<div class="gl-contacts-map-grid">
				<div>
					<div class="gl-contacts-label">Координаты</div>
					<div class="gl-contacts-value"><?php echo esc_html($latitude . ', ' . $longitude); ?></div>
					<div class="gl-contacts-note"><?php echo gelikon_contacts_rich_text($parking); ?></div>
					<a class="gl-btn gl-btn--ghost" href="<?php echo esc_url($yandex_link); ?>" target="_blank" rel="noopener">Открыть в Яндекс.Картах →</a>
				</div>
				<div class="gl-contacts-map-card">
					<?php if (!empty($location_image_url)) : ?>
						<img src="<?php echo esc_url($location_image_url); ?>" alt="Схема проезда" loading="lazy" decoding="async">
					<?php else : ?>
						<iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3A3b927b54a49f02ef6c60565720048d4ad22b8b47d1ca0323e832e66478a5300f&amp;source=constructor" width="100%" height="400" frameborder="0"></iframe>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<section class="gl-card gl-contacts-block gl-company-details" data-details>
			<h2>Реквизиты компании</h2>
			<div class="gl-contacts-value">
				<div class="gl-company-details__name"><?php echo gelikon_contacts_rich_text($company_name); ?></div>
				<div class="gl-company-details__summary-row"><span>ИНН</span><div><?php echo gelikon_contacts_rich_text($inn); ?></div></div>
				<div class="gl-company-details__summary-row"><span>ОГРН</span><div><?php echo gelikon_contacts_rich_text($ogrn); ?></div></div>
			</div>
			<?php if ($details_content !== '') : ?>
				<div class="gl-company-details__more" hidden><?php echo $details_content; ?></div>
			<?php endif; ?>
			<div class="gl-company-details__actions">
				<?php if ($details_content !== '') : ?>
					<button type="button" class="gl-btn gl-btn--ghost" data-details-toggle>Показать полностью</button>
				<?php endif; ?>
				<?php if (!empty($details_pdf)) : ?>
					<a class="gl-btn gl-btn--primary" href="<?php echo esc_url($details_pdf); ?>" target="_blank" rel="noopener">Скачать реквизиты PDF</a>
				<?php endif; ?>
			</div>
		</section>
	</div>
</main>

<style>
	.gl-contacts-page .gl-page-single {
  display: grid;
  gap: 24px;
}
.gl-contacts-hero,
.gl-contacts-block {
  padding: 28px;
}
.gl-contacts-hero h1,
.gl-contacts-block h2 {
  margin: 0 0 12px;
  font-size: clamp(28px, 3vw, 42px);
  line-height: 1.06;
  letter-spacing: -.03em;
}
.gl-contacts-hero p {
  margin: 0;
  color: var(--gl-color-muted);
  font-size: 18px;
}
.gl-contacts-grid,
.gl-contacts-map-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
.gl-contacts-label {
  font-weight: 700;
  margin-bottom: 8px;
}
.gl-contacts-value,
.gl-contacts-note {
  color: var(--gl-color-muted);
}
.gl-contacts-note {
  margin: 16px 0;
}
.gl-contacts-map-card {
  min-height: 260px;
  border: 1px solid var(--gl-color-line);
  border-radius: calc(var(--gl-radius) - 8px);
  overflow: hidden;
  background: linear-gradient(180deg, #f8faf8 0%, #eef3ef 100%);
}
.gl-contacts-map-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.gl-contacts-map-placeholder {
  height: 100%;
  min-height: 260px;
  display: grid;
  place-items: center;
  color: var(--gl-color-muted);
}
.gl-contacts-value p,
.gl-contacts-note p {
  margin: 0 0 8px;
}
.gl-contacts-value p:last-child,
.gl-contacts-note p:last-child {
  margin-bottom: 0;
}
.gl-company-details__name {
  font-weight: 700;
}
.gl-company-details__summary-row {
  display: flex;
  gap: 4px;
}
.gl-company-details__actions {
  margin-top: 18px;
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.gl-company-details__more p {
  margin: 12px 0 0;
}
@media (max-width: 767px) {
  .gl-contacts-grid,
  .gl-contacts-map-grid {
    grid-template-columns: 1fr;
  }
}
</style>


<script>
	document.addEventListener('DOMContentLoaded', function () {
  const detailsBlock = document.querySelector('[data-details]');

  if (!detailsBlock) {
    return;
  }

  const toggleButton = detailsBlock.querySelector('[data-details-toggle]');
  const detailsContent = detailsBlock.querySelector('.gl-company-details__more');

  if (!toggleButton || !detailsContent) {
    return;
  }

  toggleButton.addEventListener('click', function () {
    const isHidden = detailsContent.hasAttribute('hidden');

    if (isHidden) {
      detailsContent.removeAttribute('hidden');
      toggleButton.textContent = 'Скрыть';
    } else {
      detailsContent.setAttribute('hidden', 'hidden');
      toggleButton.textContent = 'Показать полностью';
    }
  });
});
</script>

<?php get_footer(); ?>
