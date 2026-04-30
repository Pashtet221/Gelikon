<?php
/**
 * Template Name: О компании Gelikon
 * Template Post Type: page
 */

defined('ABSPATH') || exit;

get_header();

if (!function_exists('gelikon_about_get_field')) {
	function gelikon_about_get_field($field_name, $default = '', $post_id = 0) {
		if (function_exists('get_field')) {
			$value = get_field($field_name, $post_id);
			if ($value !== null && $value !== false && $value !== '') {
				return $value;
			}
		}

		return $default;
	}
}

if (!function_exists('gelikon_about_media_url')) {
	function gelikon_about_media_url($field, $size = 'large') {
		if (empty($field)) {
			return '';
		}

		if (is_array($field)) {
			if (!empty($field['url'])) {
				return $field['url'];
			}

			if (!empty($field['sizes'][$size])) {
				return $field['sizes'][$size];
			}

			if (!empty($field['ID'])) {
				$src = wp_get_attachment_image_src((int) $field['ID'], $size);
				return !empty($src[0]) ? $src[0] : '';
			}
		}

		if (is_numeric($field)) {
			$src = wp_get_attachment_image_src((int) $field, $size);
			return !empty($src[0]) ? $src[0] : '';
		}

		if (is_string($field)) {
			return $field;
		}

		return '';
	}
}

$page_id = (int) get_queried_object_id();

$hero_title = gelikon_about_get_field('hero_title', 'Gelikon Line', $page_id);
$hero_subtitle = gelikon_about_get_field('hero_subtitle', 'Технологии для жизни и здоровья', $page_id);
$hero_background = gelikon_about_get_field('hero_background', '', $page_id);
$hero_background_url = gelikon_about_media_url($hero_background, 'full');
$hero_is_video = is_string($hero_background_url) && preg_match('/\.mp4($|\?)/i', $hero_background_url);

$intro_text = gelikon_about_get_field('intro_text', 'Gelikon Line работает на рынке электроники и радиотехнического оборудования с 2001 года, развивая продуктовые направления для дома, связи и персонального здоровья.', $page_id);

$history_title = gelikon_about_get_field('history_title', 'История и опыт', $page_id);
$history_text = gelikon_about_get_field('history_text', 'С первых лет работы мы участвовали в становлении рынка видеорегистраторов и электроники, выстраивая процессы контроля качества и сервисной поддержки.', $page_id);
$history_image = gelikon_about_get_field('history_image', '', $page_id);
$history_image_url = gelikon_about_media_url($history_image, 'large');

$approach_items = gelikon_about_get_field('approach_items', [], $page_id);
if (empty($approach_items) || !is_array($approach_items)) {
	$approach_items = [
		['icon' => '', 'title' => 'Отбор на выставках', 'description' => 'Выбираем перспективные решения и партнеров по реальному качеству продукции.'],
		['icon' => '', 'title' => 'Тестирование', 'description' => 'Проверяем устройства в рабочих сценариях до вывода в продажи.'],
		['icon' => '', 'title' => 'Адаптация', 'description' => 'Локализуем, адаптируем документацию и пользовательский опыт для рынка РФ.'],
		['icon' => '', 'title' => 'Контроль производства', 'description' => 'Сопровождаем партии и контролируем стабильность поставок и характеристик.'],
	];
}

$production_text = gelikon_about_get_field('production_text', 'Продукция Gelikon Line производится на крупных современных фабриках с выстроенными системами контроля и технологической дисциплины.', $page_id);
$production_image = gelikon_about_get_field('production_image', '', $page_id);
$production_image_url = gelikon_about_media_url($production_image, 'large');

$clients_text = gelikon_about_get_field('clients_text', 'За годы работы Gelikon Line сотрудничала с крупными коммерческими и розничными партнерами, подтверждая репутацию стабильного поставщика.', $page_id);
$clients_logos = gelikon_about_get_field('clients_logos', [], $page_id);

$warranty_text = gelikon_about_get_field('warranty_text', 'Мы несем гарантийные обязательства по всей поставляемой продукции и обеспечиваем поддержку по сервисным вопросам.', $page_id);
$brand_text = gelikon_about_get_field('brand_text', 'В 2022 году был зарегистрирован товарный знак Gelikon Line (№ 884837), что закрепило развитие бренда на рынке.', $page_id);
?>

<main id="primary" class="site-main gl-about-page">
	<section class="gl-about-hero"<?php echo ($hero_background_url && !$hero_is_video) ? ' style="background-image:url(' . esc_url($hero_background_url) . ');"' : ''; ?>>
		<?php if ($hero_background_url && $hero_is_video) : ?>
			<video class="gl-about-hero__video" autoplay muted loop playsinline>
				<source src="<?php echo esc_url($hero_background_url); ?>" type="video/mp4">
			</video>
		<?php endif; ?>
		<div class="gl-about-hero__overlay"></div>
		<div class="gl-container gl-about-hero__content">
			<h1><?php echo esc_html($hero_title); ?></h1>
			<p><?php echo esc_html($hero_subtitle); ?></p>
		</div>
	</section>

	<div class="gl-container gl-page-single gl-about-sections">
		<section class="gl-card gl-about-intro">
			<div class="gl-about-text"><?php echo wp_kses_post(wpautop($intro_text)); ?></div>
		</section>

		<section class="gl-card gl-about-split">
			<div>
				<h2><?php echo esc_html($history_title); ?></h2>
				<div class="gl-about-text"><?php echo wp_kses_post(wpautop($history_text)); ?></div>
			</div>
			<div class="gl-about-media">
				<?php if (!empty($history_image_url)) : ?>
					<img src="<?php echo esc_url($history_image_url); ?>" alt="<?php echo esc_attr($history_title); ?>">
				<?php else : ?>
					<div class="gl-about-placeholder">История компании</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="gl-card gl-about-approach">
			<h2>Подход к продукции</h2>
			<div class="gl-about-approach__grid">
				<?php foreach ($approach_items as $item) :
					$icon_url = !empty($item['icon']) ? gelikon_about_media_url($item['icon'], 'thumbnail') : '';
					$title = !empty($item['title']) ? $item['title'] : 'Пункт';
					$description = !empty($item['description']) ? $item['description'] : '';
				?>
					<article class="gl-about-approach__item">
						<div class="gl-about-approach__icon">
							<?php if ($icon_url) : ?>
								<img src="<?php echo esc_url($icon_url); ?>" alt="">
							<?php else : ?>
								<span>•</span>
							<?php endif; ?>
						</div>
						<h3><?php echo esc_html($title); ?></h3>
						<p><?php echo esc_html($description); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="gl-card gl-about-split">
			<div class="gl-about-media">
				<?php if (!empty($production_image_url)) : ?>
					<img src="<?php echo esc_url($production_image_url); ?>" alt="Производство Gelikon Line">
				<?php else : ?>
					<div class="gl-about-placeholder">Производство и фабрики</div>
				<?php endif; ?>
			</div>
			<div>
				<h2>Производство</h2>
				<div class="gl-about-text"><?php echo wp_kses_post(wpautop($production_text)); ?></div>
			</div>
		</section>

		<section class="gl-card gl-about-clients">
			<h2>Клиенты и репутация</h2>
			<div class="gl-about-text"><?php echo wp_kses_post(wpautop($clients_text)); ?></div>
			<div class="gl-about-logos">
				<?php if (!empty($clients_logos) && is_array($clients_logos)) : ?>
					<?php foreach ($clients_logos as $logo) :
						$logo_url = gelikon_about_media_url($logo, 'medium');
						if (empty($logo_url)) { continue; }
					?>
						<div class="gl-about-logos__item"><img src="<?php echo esc_url($logo_url); ?>" alt="Логотип клиента"></div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="gl-about-logos__empty">Логотипы клиентов появятся здесь</div>
				<?php endif; ?>
			</div>
		</section>

		<section class="gl-card gl-about-warranty">
			<h2>Гарантии</h2>
			<div class="gl-about-text"><?php echo wp_kses_post(wpautop($warranty_text)); ?></div>
		</section>

		<section class="gl-card gl-about-brand">
			<h2>Развитие бренда</h2>
			<div class="gl-about-text"><?php echo wp_kses_post(wpautop($brand_text)); ?></div>
		</section>
	</div>
</main>

<?php get_footer(); ?>
