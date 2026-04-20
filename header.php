<?php
if (!defined('ABSPATH')) {
	exit;
}

$gl_address = '';

if (function_exists('get_field')) {
	$gl_address = get_field('address', 'option');

	if (!$gl_address) {
		$gl_address = get_field('contact_address', 'option');
	}

	if (!$gl_address) {
		$gl_address = get_field('company_address', 'option');
	}
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="gl-header" id="site-header">
	<div class="gl-container">
		<div class="gl-header__inner">

			<div class="gl-header__top">
				<div class="gl-header__brand">
					<button class="gl-burger" type="button" aria-expanded="false" aria-controls="primary-menu-mobile" aria-label="<?php esc_attr_e('Открыть меню', 'gelikon'); ?>">
						<span></span>
						<span></span>
						<span></span>
					</button>

					<div class="gl-logo-block">
						<div class="gl-logo">
							<?php gelikon_site_logo(); ?>
						</div>

						<div class="gl-logo-slogan">
							Умные технологии для жизни и дома
						</div>
					</div>
				</div>

				<div class="gl-header__catalog">
					<?php echo do_shortcode('[gelikon_catalog_dropdown title="Каталог"]'); ?>
				</div>

				<nav class="gl-nav" id="primary-menu" aria-label="<?php esc_attr_e('Основная навигация', 'gelikon'); ?>">
					<?php
					wp_nav_menu([
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'gl-menu',
						'fallback_cb'    => false,
					]);
					?>
				</nav>

				<div class="gl-header__actions">
					
					<button
	class="gl-header__icon gl-mobile-contact-trigger desktop"
	type="button"
	aria-label="<?php esc_attr_e('Контакты', 'gelikon'); ?>"
	data-gl-open-contact-modal
	style="width:42px !important;height:42px !important;min-width:42px !important;max-width:42px !important;min-height:42px !important;max-height:42px !important;flex:0 0 42px !important;padding:0 !important;"
>
	<svg class="gl-mobile-contact-trigger__icon" width="22" height="22" viewBox="0 0 512 512" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
		<path fill="currentColor" d="M94.811,21.696c-35.18,22.816-42.091,94.135-28.809,152.262c10.344,45.266,32.336,105.987,69.42,163.165
		c34.886,53.79,83.557,102.022,120.669,129.928c47.657,35.832,115.594,58.608,150.774,35.792
		c17.789-11.537,44.218-43.058,45.424-48.714c0,0-15.498-23.896-18.899-29.14l-51.972-80.135
		c-3.862-5.955-28.082-0.512-40.386,6.457c-16.597,9.404-31.882,34.636-31.882,34.636c-11.38,6.575-20.912,0.024-40.828-9.142
		c-24.477-11.262-51.997-46.254-73.9-77.947c-20.005-32.923-40.732-72.322-41.032-99.264c-0.247-21.922-2.341-33.296,8.304-41.006
		c0,0,29.272-3.666,44.627-14.984c11.381-8.392,26.228-28.286,22.366-34.242l-51.972-80.134
		c-3.401-5.244-18.899-29.14-18.899-29.14C152.159-1.117,112.6,10.159,94.811,21.696z"/>
	</svg>
</button>
					
					
					

					<div class="gl-header__phones-wrap">
						<?php gelikon_header_phones(); ?>
					</div>

					<div class="gl-header__search-icon gl-header__search-icon--desktop">
						<?php echo do_shortcode('[gelikon_product_search]'); ?>
					</div>

<button
	class="gl-header__icon gl-mobile-contact-trigger mobile"
	type="button"
	aria-label="<?php esc_attr_e('Контакты', 'gelikon'); ?>"
	data-gl-open-mobile-contacts
	style="width:42px !important;height:42px !important;min-width:42px !important;max-width:42px !important;min-height:42px !important;max-height:42px !important;flex:0 0 42px !important;padding:0 !important;"
>
	<svg class="gl-mobile-contact-trigger__icon" width="22" height="22" viewBox="0 0 512 512" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
		<path fill="currentColor" d="M94.811,21.696c-35.18,22.816-42.091,94.135-28.809,152.262c10.344,45.266,32.336,105.987,69.42,163.165
		c34.886,53.79,83.557,102.022,120.669,129.928c47.657,35.832,115.594,58.608,150.774,35.792
		c17.789-11.537,44.218-43.058,45.424-48.714c0,0-15.498-23.896-18.899-29.14l-51.972-80.135
		c-3.862-5.955-28.082-0.512-40.386,6.457c-16.597,9.404-31.882,34.636-31.882,34.636c-11.38,6.575-20.912,0.024-40.828-9.142
		c-24.477-11.262-51.997-46.254-73.9-77.947c-20.005-32.923-40.732-72.322-41.032-99.264c-0.247-21.922-2.341-33.296,8.304-41.006
		c0,0,29.272-3.666,44.627-14.984c11.381-8.392,26.228-28.286,22.366-34.242l-51.972-80.134
		c-3.401-5.244-18.899-29.14-18.899-29.14C152.159-1.117,112.6,10.159,94.811,21.696z"/>
	</svg>
</button>

					<a class="gl-header__icon gl-cart-link" href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/')); ?>" aria-label="<?php esc_attr_e('Корзина', 'gelikon'); ?>">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
							<path d="M5 6h16l-1.5 8.5a2 2 0 0 1-2 1.5H9a2 2 0 0 1-2-1.5L5 4H2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="10" cy="20" r="1.5" fill="currentColor"/>
							<circle cx="18" cy="20" r="1.5" fill="currentColor"/>
						</svg>

						<?php if (class_exists('WooCommerce')) : ?>
							<span class="gl-cart-count"><?php echo esc_html(WC()->cart ? WC()->cart->get_cart_contents_count() : 0); ?></span>
						<?php endif; ?>
					</a>
				</div>
			</div>

			<div class="gl-header__bottom" id="primary-menu-mobile">
				<div class="gl-header__search-mobile">
					<?php echo do_shortcode('[gelikon_product_search]'); ?>
				</div>

				<div class="gl-header__catalog gl-header__catalog--mobile">
					<?php echo do_shortcode('[gelikon_catalog_dropdown title="Каталог"]'); ?>
				</div>

				<nav class="gl-nav gl-nav--mobile" aria-label="<?php esc_attr_e('Мобильная навигация', 'gelikon'); ?>">
					<?php
					wp_nav_menu([
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'gl-menu',
						'fallback_cb'    => false,
					]);
					?>
				</nav>

				<div class="gl-header__phones-mobile">
					<?php gelikon_header_phones(); ?>

					<?php if ($gl_address) : ?>
						<div class="gl-header__address-mobile">
							<strong>Адрес</strong>
							<div class="gl-header__address-mobile-value"><?php echo wp_kses_post($gl_address); ?></div>
						</div>
					<?php endif; ?>
				</div>
			</div>

		</div>
	</div>
</header>

<div class="gl-contact-modal" id="gl-contact-modal" hidden>
	<div class="gl-contact-modal__overlay" data-gl-close-contact-modal></div>

	<div class="gl-contact-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="gl-contact-modal-title">
		<div class="gl-contact-modal__title" id="gl-contact-modal-title">Выберите удобный способ связи</div>

		<div class="gl-contact-modal__list">
			<?php if (have_rows('contacts', 'option')) : ?>
				<?php while (have_rows('contacts', 'option')) : the_row();

					$icon  = get_sub_field('icon');
					$title = get_sub_field('title');
					$text  = get_sub_field('text');
					$link  = get_sub_field('link');
					$style = get_sub_field('style');

					$target = ' target="_blank"';
					$rel    = ' rel="noopener noreferrer"';
				?>
					<a class="gl-contact-card <?php echo $style ? 'gl-contact-card--' . esc_attr($style) : ''; ?>" href="<?php echo esc_url($link); ?>"<?php echo $target . $rel; ?>>
						<?php if ($icon && !empty($icon['url'])) : ?>
							<span class="gl-contact-card__icon">
								<img
									src="<?php echo esc_url($icon['url']); ?>"
									alt="<?php echo esc_attr($title); ?>"
									width="40"
									height="40"
									loading="lazy"
								>
							</span>
						<?php endif; ?>

						<span>
							<?php if ($title) : ?>
								<strong><?php echo esc_html($title); ?></strong>
							<?php endif; ?>

							<?php if ($text) : ?>
								<small><?php echo esc_html($text); ?></small>
							<?php endif; ?>
						</span>
					</a>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="gl-mobile-contacts-modal" id="gl-mobile-contacts-modal" hidden>
	<div class="gl-mobile-contacts-modal__overlay" data-gl-close-mobile-contacts></div>

	<div class="gl-mobile-contacts-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="gl-mobile-contacts-title">
		<div class="gl-mobile-contacts-modal__head">
			<div class="gl-mobile-contacts-modal__title" id="gl-mobile-contacts-title">Контакты</div>

			<button class="gl-mobile-contacts-modal__close" type="button" aria-label="<?php esc_attr_e('Закрыть', 'gelikon'); ?>" data-gl-close-mobile-contacts>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
					<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
				</svg>
			</button>
		</div>

		<div class="gl-mobile-contacts-modal__list">
			<?php
			$phones  = [];
			$emails  = [];
			$socials = [];

			if (have_rows('contacts', 'option')) :
				while (have_rows('contacts', 'option')) : the_row();
					$icon  = get_sub_field('icon');
					$title = get_sub_field('title');
					$text  = get_sub_field('text');
					$link  = trim((string) get_sub_field('link'));
					$style = get_sub_field('style');

					if (!$link) {
						continue;
					}

					$item = [
						'icon'  => $icon,
						'title' => $title,
						'text'  => $text,
						'link'  => $link,
						'style' => $style,
					];

					if (strpos($link, 'tel:') === 0) {
						$phones[] = $item;
					} elseif (strpos($link, 'mailto:') === 0) {
						$emails[] = $item;
					} else {
						$socials[] = $item;
					}
				endwhile;
			endif;
			?>

			<?php if (!empty($phones)) : ?>
				<div class="gl-mobile-contacts-group">
					<div class="gl-mobile-contacts-group__title">Телефоны</div>

					<div class="gl-mobile-contacts-info-list">
						<?php foreach ($phones as $item) : ?>
							<a class="gl-mobile-contact-info-card" href="<?php echo esc_url($item['link']); ?>">
								<span class="gl-mobile-contact-info-card__label">
									<?php echo esc_html($item['title'] ?: 'Телефон'); ?>
								</span>

								<span class="gl-mobile-contact-info-card__value">
									<?php echo esc_html($item['text'] ?: str_replace('tel:', '', $item['link'])); ?>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if (!empty($emails)) : ?>
				<div class="gl-mobile-contacts-group">
					<div class="gl-mobile-contacts-group__title">Почта</div>

					<div class="gl-mobile-contacts-info-list">
						<?php foreach ($emails as $item) : ?>
							<?php $email_to_copy = trim(str_replace('mailto:', '', $item['link'])); ?>

							<div class="gl-mobile-contact-info-card gl-mobile-contact-info-card--email">
								<a class="gl-mobile-contact-info-card__main" href="<?php echo esc_url($item['link']); ?>">
									<span class="gl-mobile-contact-info-card__label">
										<?php echo esc_html($item['title'] ?: 'Email'); ?>
									</span>

									<span class="gl-mobile-contact-info-card__value">
										<?php echo esc_html($item['text'] ?: $email_to_copy); ?>
									</span>
								</a>

								<button class="gl-mobile-contact-card__copy" type="button" data-copy-email="<?php echo esc_attr($email_to_copy); ?>">
									Копировать
								</button>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>



			<?php if (!empty($socials)) : ?>
	<div class="gl-mobile-contacts-group">

		<div class="gl-mobile-contacts-group__title">Телефоны</div>
		<div class="gl-mobile-contact-phones">
			<?php gelikon_header_phones(); ?>
		</div>

		<div class="gl-mobile-contacts-group__title">Мессенджеры и соцсети</div>
		<div class="gl-mobile-contacts-socials">
			<?php foreach ($socials as $item) : ?>
				<a class="gl-mobile-contact-card <?php echo $item['style'] ? 'gl-mobile-contact-card--' . esc_attr($item['style']) : ''; ?>" href="<?php echo esc_url($item['link']); ?>" target="_blank" rel="noopener noreferrer">
					<?php if (!empty($item['icon']['url'])) : ?>
						<span class="gl-mobile-contact-card__icon">
							<img
								src="<?php echo esc_url($item['icon']['url']); ?>"
								alt="<?php echo esc_attr($item['title']); ?>"
								width="40"
								height="40"
								loading="lazy"
							>
						</span>
					<?php endif; ?>

					<span class="gl-mobile-contact-card__content">
						<?php if (!empty($item['title'])) : ?>
							<strong><?php echo esc_html($item['title']); ?></strong>
						<?php endif; ?>

						<?php if (!empty($item['text'])) : ?>
							<small><?php echo esc_html($item['text']); ?></small>
						<?php endif; ?>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>
		</div>
	</div>
</div>

<style>	
/* ===== MOBILE ===== */
.mobile {
	display: none !important;
}

@media (max-width: 991px) {
	.mobile {
		display: inline-flex !important;
	}
}

/* ===== DESKTOP ===== */
.desktop {
	display: inline-flex !important;
}

@media (max-width: 991px) {
	.desktop {
		display: none !important;
	}
}

.gl-mobile-contact-phones {
	display: grid;
	gap: 8px;
	margin-bottom: 14px;
}

.gl-mobile-contact-phones,
.gl-mobile-contact-phones * {
	line-height: 1.4;
}

.gl-mobile-contact-phones .gl-header__phones,
.gl-mobile-contact-phones .gl-header__phones-wrap,
.gl-mobile-contact-phones .gl-header__phones *,
.gl-mobile-contact-phones .gl-phone,
.gl-mobile-contact-phones .phone {
	display: block !important;
}

.gl-mobile-contact-phones .gl-header__phones a,
.gl-mobile-contact-phones .gl-header__phones .phone,
.gl-mobile-contact-phones .gl-header__phones .gl-phone,
.gl-mobile-contact-phones a,
.gl-mobile-contact-phones .phone,
.gl-mobile-contact-phones .gl-phone {
	display: block !important;
	padding: 8px 14px;
	border-radius: 14px;
	background: #fff;
	font-size: 15px;
	font-weight: 700;
	color: var(--gl-color-text);
	text-decoration: none;
}
	
	
:root {
	--gl-header-bg: rgba(255, 255, 255, 0.96);
	--gl-header-border: #e8edf2;
	--gl-text: #1b2230;
	--gl-muted: #7e8794;
	--gl-field-border: #dbe3ea;
	--gl-green: var(--gl-color-accent);
	--gl-badge: #39bf74;
}

.gl-header {
	position: sticky;
	top: 0;
	z-index: 1000;
	background: rgba(255, 255, 255, 0.96);
	backdrop-filter: blur(10px);
	-webkit-backdrop-filter: blur(10px);
	border-bottom: 1px solid var(--gl-color-line);
	box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
	overflow: visible;
}

.gl-header__inner {
	display: flex;
	flex-direction: column;
}

.gl-header__top {
	display: grid;
	grid-template-columns: auto auto 1fr auto;
	align-items: center;
	gap: 24px;
	padding: 14px 0;
}

.gl-header__bottom {
	display: none;
}

.gl-header__brand {
	display: flex;
	align-items: center;
	gap: 16px;
	min-width: 0;
}

.gl-logo-block {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	line-height: 1;
	min-width: 0;
}

.gl-logo img,
.gl-logo svg {
	display: block;
	width: auto;
	height: auto;
	max-width: 220px;
}

.gl-logo-slogan {
	margin-top: 8px;
	font-size: 12px;
	line-height: 1.3;
	font-weight: 600;
	color: var(--gl-color-subtitle);
}

.gl-header__catalog {
	min-width: 0;
	flex: 0 0 auto;
}

.gl-nav {
	min-width: 0;
}

.gl-menu {
	display: flex;
	align-items: center;
	justify-content: flex-start;
	flex-wrap: nowrap;
	gap: 22px;
	margin: 0;
	padding: 0;
	list-style: none;
	overflow-x: auto;
	overflow-y: hidden;
	scrollbar-width: none;
}

.gl-menu::-webkit-scrollbar {
	display: none;
}

.gl-menu > li {
	margin: 0;
	flex: 0 0 auto;
}

.gl-menu > li > a {
	display: inline-flex;
	align-items: center;
	min-height: 36px;
	text-decoration: none;
	color: var(--gl-color-helper);
	font-size: 15px;
	font-weight: 700;
	line-height: 1;
	white-space: nowrap;
}

.gl-header__actions {
	display: flex;
	align-items: center;
	gap: 14px;
	flex: 0 0 auto;
	margin-left: auto;
}

button {
	min-width: 42px;
	min-height: 42px;
}

.gl-write-btn {
	display: inline;
	padding: 0;
	min-width: 0;
	height: auto;
	border: 0;
	border-radius: 0;
	background: transparent;
	box-shadow: none;
	color: var(--gl-color-text);
	font-size: 15px;
	font-weight: 600;
	line-height: 1.2;
	text-decoration: underline;
	text-underline-offset: 3px;
	cursor: pointer;
	appearance: none;
	-webkit-appearance: none;
	white-space: nowrap;
	order: 1;
}

.gl-write-btn:hover {
	background: transparent;
	box-shadow: none;
	color: var(--gl-color-text);
	opacity: 0.8;
}

.gl-write-btn:focus,
.gl-write-btn:active {
	outline: none;
	box-shadow: none;
	background: transparent;
}

.gl-write-btn__text {
	display: inline;
}

.gl-header__phones-wrap {
	flex: 0 0 auto;
	text-align: right;
	order: 2;
}

.gl-header__phones-wrap,
.gl-header__phones-wrap * {
	line-height: 1.25;
}

.gl-header__phones-wrap a,
.gl-header__phones-wrap .phone,
.gl-header__phones-wrap .gl-phone {
	font-size: 15px;
	font-weight: 800;
	color: var(--gl-color-text);
	text-decoration: none;
	white-space: nowrap;
}

.gl-header__search-icon {
	order: 3;
	flex: 0 0 auto;
}

.gl-header__search-icon .gl-header-search-trigger-wrap,
.gl-header__search-icon .gl-product-search {
	display: flex;
	align-items: center;
}

.gl-header__icon,
.gl-header-search-trigger {
	position: relative;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 46px;
	height: 46px;
	border-radius: 50%;
	text-decoration: none;
	color: var(--gl-color-text);
	border: 1px solid var(--gl-color-line);
	background: var(--gl-color-surface);
	flex: 0 0 auto;
	box-shadow: none;
}

.gl-header__icon:hover,
.gl-header-search-trigger:hover {
	border-color: var(--gl-color-helper);
}

.gl-cart-link {
	order: 4;
}

.gl-cart-count {
	position: absolute;
	top: -5px;
	right: -2px;
	min-width: 20px;
	height: 20px;
	padding: 0 6px;
	border-radius: 999px;
	background: var(--gl-color-accent);
	color: #fff;
	font-size: 11px;
	font-weight: 700;
	line-height: 20px;
	text-align: center;
}

.gl-burger {
	display: none;
	align-items: center;
	justify-content: center;
	flex-direction: column;
	gap: 4px;
	width: 42px;
	height: 42px;
	padding: 0;
	border: 0;
	background: transparent;
	cursor: pointer;
	flex: 0 0 auto;
}

.gl-burger span {
	display: block;
	width: 20px;
	height: 2px;
	border-radius: 2px;
	background: var(--gl-color-text);
}

.gl-header__phones-mobile {
	display: none;
}

.gl-mobile-contact-trigger {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #12D457;
}

.gl-mobile-contact-trigger__icon {
	display: block;
	width: 22px;
	height: 22px;
	flex: 0 0 22px;
}

.gl-header__search {
	display: none !important;
}

.gl-contact-modal[hidden],
.gl-mobile-contacts-modal[hidden] {
	display: none !important;
}

.gl-contact-modal,
.gl-mobile-contacts-modal {
	position: fixed;
	inset: 0;
	z-index: 9999;
}

.gl-contact-modal__overlay,
.gl-mobile-contacts-modal__overlay {
	position: absolute;
	inset: 0;
	background: rgba(16, 22, 31, 0.45);
	backdrop-filter: blur(4px);
	-webkit-backdrop-filter: blur(4px);
}

.gl-contact-modal__dialog {
	position: relative;
	width: min(520px, calc(100% - 24px));
	margin: 80px auto;
	background: var(--gl-color-surface);
	border-radius: 20px;
	padding: 28px;
	box-shadow: 0 30px 80px rgba(0, 0, 0, 0.22);
	z-index: 2;
}

.gl-contact-modal__title {
	margin-bottom: 22px;
	padding-right: 48px;
	font-size: 28px;
	line-height: 1.2;
	font-weight: 800;
	color: var(--gl-color-heading);
}

.gl-contact-modal__list {
	display: grid;
	gap: 14px;
}

.gl-contact-card {
	display: flex;
	align-items: center;
	gap: 14px;
	padding: 16px 18px;
	border-radius: 16px;
	text-decoration: none;
	color: var(--gl-color-text);
	border: 1px solid var(--gl-color-line);
	background: var(--gl-color-surface);
}

.gl-contact-card__icon {
	width: 46px;
	height: 46px;
	border-radius: 50%;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	flex: 0 0 auto;
}

.gl-contact-card__icon img {
	display: block;
	width: 40px;
	height: 40px;
	object-fit: contain;
}

.gl-contact-card--tg .gl-contact-card__icon {
	background: #2aabee;
}

.gl-contact-card--wa .gl-contact-card__icon {
	background: #25d366;
}

.gl-contact-card strong {
	display: block;
	font-size: 18px;
	line-height: 1.2;
	font-weight: 700;
	color: var(--gl-color-heading);
}

.gl-contact-card small {
	display: block;
	margin-top: 4px;
	font-size: 14px;
	line-height: 1.4;
	color: var(--gl-color-subtitle);
}

.gl-mobile-contacts-modal {
	z-index: 10050;
}

.gl-mobile-contacts-modal__dialog {
	position: absolute;
	left: 12px;
	right: 12px;
	bottom: 12px;
	background: #fff;
	border-radius: 18px;
	padding: 18px;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
	z-index: 2;
	max-height: calc(100vh - 24px);
	overflow-y: auto;
}

.gl-mobile-contacts-modal__head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 14px;
}

.gl-mobile-contacts-modal__title {
	font-size: 20px;
	font-weight: 800;
	line-height: 1.2;
	color: var(--gl-color-heading);
}

.gl-mobile-contacts-modal__close {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 40px;
	height: 40px;
	padding: 0;
	border: 0;
	border-radius: 50%;
	background: #f3f5f7;
	color: var(--gl-color-text);
	cursor: pointer;
	flex: 0 0 auto;
}

.gl-mobile-contacts-modal__list {
	display: grid;
	gap: 10px;
}

.gl-mobile-contacts-group + .gl-mobile-contacts-group {
	margin-top: 14px;
}

.gl-mobile-contacts-group__title {
	margin: 0 0 8px;
	font-size: 13px;
	font-weight: 700;
	line-height: 1.3;
	letter-spacing: 0.02em;
	text-transform: uppercase;
	color: var(--gl-color-subtitle);
}

.gl-mobile-contacts-info-list,
.gl-mobile-contacts-socials {
	display: grid;
	gap: 8px;
}

.gl-mobile-contact-info-card {
	display: block;
	padding: 12px 14px;
	border: 1px solid var(--gl-color-line);
	border-radius: 14px;
	background: #fff;
	text-decoration: none;
	color: var(--gl-color-text);
}

.gl-mobile-contact-info-card__label {
	display: block;
	margin: 0 0 4px;
	font-size: 12px;
	line-height: 1.3;
	color: var(--gl-color-subtitle);
}

.gl-mobile-contact-info-card__value {
	display: block;
	font-size: 15px;
	line-height: 1.35;
	font-weight: 700;
	color: var(--gl-color-heading);
	word-break: break-word;
}

.gl-mobile-contact-info-card__value p {
	margin: 0;
}

.gl-mobile-contact-info-card__value br {
	display: block;
}

.gl-mobile-contact-info-card--email {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 10px;
}

.gl-mobile-contact-info-card--email .gl-mobile-contact-info-card__main {
	display: block;
	min-width: 0;
	flex: 1 1 auto;
	text-decoration: none;
	color: inherit;
}

.gl-mobile-contact-card {
	display: flex;
	align-items: center;
	gap: 12px;
	padding: 12px;
	border: 1px solid var(--gl-color-line);
	border-radius: 14px;
	background: #fff;
	text-decoration: none;
	color: var(--gl-color-text);
}

.gl-mobile-contact-card__icon {
	width: 44px;
	height: 44px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 50%;
	flex: 0 0 44px;
	background: #f3f5f7;
}

.gl-mobile-contact-card__icon img {
	display: block;
	width: 28px;
	height: 28px;
	object-fit: contain;
}

.gl-mobile-contact-card__content {
	display: flex;
	flex-direction: column;
	min-width: 0;
	flex: 1 1 auto;
}

.gl-mobile-contact-card__content strong {
	display: block;
	font-size: 15px;
	line-height: 1.3;
	font-weight: 700;
	color: var(--gl-color-heading);
	word-break: break-word;
}

.gl-mobile-contact-card__content small {
	display: block;
	margin-top: 2px;
	font-size: 13px;
	line-height: 1.35;
	color: var(--gl-color-subtitle);
	word-break: break-word;
}

.gl-mobile-contact-card__copy {
	flex: 0 0 auto;
	min-height: 38px;
	padding: 8px 12px;
	border: 1px solid var(--gl-color-line);
	border-radius: 10px;
	background: #fff;
	color: var(--gl-color-text);
	font-size: 13px;
	font-weight: 600;
	cursor: pointer;
}

.gl-mobile-contact-card--tg .gl-mobile-contact-card__icon {
	background: rgba(42, 171, 238, 0.12);
}

.gl-mobile-contact-card--wa .gl-mobile-contact-card__icon {
	background: rgba(37, 211, 102, 0.12);
}

body.gl-modal-open {
	overflow: hidden;
}

.gl-search-popup__close {
	max-width: 50px !important;
}

@media (max-width: 1400px) {
	.gl-header__top {
		grid-template-columns: auto auto 1fr auto;
		gap: 18px;
	}

	.gl-logo img,
	.gl-logo svg {
		max-width: 190px;
	}

	.gl-menu {
		gap: 18px;
	}

	.gl-header__icon,
	.gl-header-search-trigger {
		width: 56px;
		height: 56px;
	}
}

@media (max-width: 1199px) and (min-width: 992px) {
	.gl-header__top {
		grid-template-columns: auto auto 1fr auto;
		gap: 14px;
	}

	.gl-logo img,
	.gl-logo svg {
		max-width: 170px;
	}

	.gl-logo-slogan {
		font-size: 11px;
	}

	.gl-menu {
		gap: 14px;
	}

	.gl-menu > li > a {
		font-size: 14px;
	}

	.gl-header__phones-wrap a,
	.gl-header__phones-wrap .phone,
	.gl-header__phones-wrap .gl-phone {
		font-size: 14px;
	}

	.gl-header__icon,
	.gl-header-search-trigger {
		width: 50px;
		height: 50px;
	}
}

@media (max-width: 991px) {
	.gl-header__top {
		display: grid;
		grid-template-columns: auto 1fr auto;
		align-items: center;
		gap: 10px;
		padding: 10px 0;
	}

	.gl-header__brand {
		gap: 10px;
		min-width: 0;
	}

	.gl-burger {
		display: inline-flex;
	}

	.gl-logo img,
	.gl-logo svg {
		max-width: 145px;
	}

	.gl-logo-slogan,
	.gl-header__phones-wrap {
		display: none;
	}

	.gl-header__top .gl-header__catalog,
	.gl-header__top .gl-nav,
	.gl-header__actions .gl-write-btn {
		display: none;
	}

	.gl-header__actions {
		gap: 8px;
		margin-left: auto;
	}

	.gl-header__search-icon--desktop {
		display: none;
	}

	.gl-cart-link {
		order: 2;
	}

	.gl-header__icon,
	.gl-header-search-trigger {
		width: 42px;
		height: 42px;
	}

	.gl-cart-count {
		min-width: 18px;
		height: 18px;
		font-size: 10px;
		line-height: 18px;
		top: -4px;
		right: -2px;
	}

	.gl-header__bottom {
		display: none;
		padding: 10px 0 12px;
		border-top: 1px solid var(--gl-color-line);
	}

	.gl-header__bottom.is-open {
		display: grid;
		grid-template-columns: 1fr;
		gap: 12px;
	}

	.gl-header__bottom .gl-header__search-mobile {
		display: block;
		order: 1;
	}

	.gl-header__bottom .gl-header__search-mobile .gl-product-search,
	.gl-header__bottom .gl-header__search-mobile .gl-header-search-trigger-wrap {
		display: block;
		width: 100%;
	}

	.gl-header__bottom .gl-header__search-mobile form,
	.gl-header__bottom .gl-header__search-mobile .searchform,
	.gl-header__bottom .gl-header__search-mobile .woocommerce-product-search {
		display: block;
		width: 100%;
		margin: 0;
	}

	.gl-header__bottom .gl-header__search-mobile input[type="search"],
	.gl-header__bottom .gl-header__search-mobile input[type="text"] {
		width: 100%;
		height: 46px;
		padding: 0 14px;
		border: 1px solid var(--gl-color-line);
		border-radius: 12px;
		background: #fff;
		font-size: 14px;
		color: var(--gl-color-text);
		box-sizing: border-box;
	}

	.gl-header__bottom .gl-header__search-mobile button,
	.gl-header__bottom .gl-header__search-mobile input[type="submit"] {
		margin-top: 8px;
		width: 100%;
		min-height: 44px;
		border-radius: 12px;
	}

	.gl-header__bottom .gl-header__catalog--mobile {
		display: block;
		order: 2;
	}

	.gl-header__bottom .gl-nav--mobile {
		display: block;
		order: 3;
	}

	.gl-header__bottom .gl-menu {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 6px;
		overflow: visible;
	}

	.gl-header__bottom .gl-menu > li,
	.gl-header__bottom .gl-menu > li > a {
		width: 100%;
	}

	.gl-header__bottom .gl-menu > li > a {
		min-height: 36px;
		font-size: 15px;
	}

	.gl-header__phones-mobile {
		display: block;
		order: 4;
		padding: 14px 16px;
		border: 1px solid var(--gl-color-line);
		border-radius: 16px;
		background: var(--gl-color-surface);
	}

	.gl-header__phones-mobile,
	.gl-header__phones-mobile * {
		line-height: 1.4;
	}

	.gl-header__phones-mobile .gl-header__phones,
	.gl-header__phones-mobile .gl-header__phones-wrap,
	.gl-header__phones-mobile .gl-header__phones *,
	.gl-header__phones-mobile .gl-phone,
	.gl-header__phones-mobile .phone {
		display: block !important;
	}

	.gl-header__phones-mobile .gl-header__phones a,
	.gl-header__phones-mobile .gl-header__phones .phone,
	.gl-header__phones-mobile .gl-header__phones .gl-phone,
	.gl-header__phones-mobile a,
	.gl-header__phones-mobile .phone,
	.gl-header__phones-mobile .gl-phone {
		display: block !important;
		font-size: 15px;
		font-weight: 800;
		color: var(--gl-color-text);
		text-decoration: none;
	}

	.gl-header__phones-mobile a + a,
	.gl-header__phones-mobile .phone + .phone,
	.gl-header__phones-mobile .gl-phone + .gl-phone,
	.gl-header__phones-mobile * + * {
		margin-top: 6px;
	}

	.gl-header__address-mobile {
		margin-top: 10px;
		padding-top: 10px;
		border-top: 1px solid var(--gl-color-line);
	}

	.gl-header__address-mobile strong {
		display: block;
		font-size: 13px;
		line-height: 1.3;
		color: var(--gl-color-subtitle);
	}

	.gl-header__address-mobile-value {
		display: block;
		margin-top: 4px;
		font-size: 14px;
		line-height: 1.4;
		font-weight: 600;
		color: var(--gl-color-text);
	}

	.gl-header__address-mobile-value p {
		margin: 0;
	}

	.gl-header__address-mobile-value br {
		display: block;
	}

	.gl-mobile-contacts-modal__dialog {
		left: 10px;
		right: 10px;
		bottom: 10px;
		padding: 16px;
		border-radius: 16px;
	}
}

@media (max-width: 767px) {
	.gl-logo img,
	.gl-logo svg {
		max-width: 132px;
	}

	.gl-contact-modal__dialog {
		margin: 50px auto;
		padding: 22px;
		border-radius: 18px;
	}

	.gl-contact-modal__title {
		font-size: 22px;
	}

	.gl-mobile-contacts-modal__title {
		font-size: 18px;
	}

	.gl-mobile-contact-card {
		padding: 11px;
	}

	.gl-mobile-contact-card__content strong {
		font-size: 14px;
	}

	.gl-mobile-contact-card__content small {
		font-size: 12px;
	}

	.gl-mobile-contact-card__copy {
		padding: 8px 10px;
		font-size: 12px;
	}
}

@media (min-width: 992px) {
	.gl-mobile-contacts-modal {
		display: none !important;
	}
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var burger = document.querySelector('.gl-burger');
	var bottom = document.querySelector('.gl-header__bottom');

	var modal = document.getElementById('gl-contact-modal');
	var openModalButtons = document.querySelectorAll('[data-gl-open-contact-modal]');
	var closeModalButtons = document.querySelectorAll('[data-gl-close-contact-modal]');

	var mobileModal = document.getElementById('gl-mobile-contacts-modal');
	var openMobileButtons = document.querySelectorAll('[data-gl-open-mobile-contacts]');
	var closeMobileButtons = document.querySelectorAll('[data-gl-close-mobile-contacts]');
	var copyButtons = document.querySelectorAll('[data-copy-email]');

	if (burger && bottom) {
		burger.addEventListener('click', function () {
			var isOpen = bottom.classList.toggle('is-open');
			burger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

			if (isOpen && mobileModal) {
				mobileModal.hidden = true;
				document.body.classList.remove('gl-modal-open');
			}
		});
	}

	function openModal() {
		if (!modal) return;
		modal.hidden = false;
		document.body.classList.add('gl-modal-open');
	}

	function closeModal() {
		if (!modal) return;
		modal.hidden = true;
		document.body.classList.remove('gl-modal-open');
	}

	function openMobileModal() {
		if (!mobileModal) return;

		if (bottom && bottom.classList.contains('is-open')) {
			bottom.classList.remove('is-open');
			if (burger) {
				burger.setAttribute('aria-expanded', 'false');
			}
		}

		mobileModal.hidden = false;
		document.body.classList.add('gl-modal-open');
	}

	function closeMobileModal() {
		if (!mobileModal) return;
		mobileModal.hidden = true;
		document.body.classList.remove('gl-modal-open');
	}

	openModalButtons.forEach(function (button) {
		button.addEventListener('click', openModal);
	});

	closeModalButtons.forEach(function (button) {
		button.addEventListener('click', closeModal);
	});

	openMobileButtons.forEach(function (button) {
		button.addEventListener('click', openMobileModal);
	});

	closeMobileButtons.forEach(function (button) {
		button.addEventListener('click', closeMobileModal);
	});

	copyButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			var email = button.getAttribute('data-copy-email');
			if (!email) return;

			function showCopiedState(btn) {
				var originalText = btn.textContent;
				btn.textContent = 'Скопировано';
				setTimeout(function () {
					btn.textContent = originalText;
				}, 1400);
			}

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(email).then(function () {
					showCopiedState(button);
				}).catch(function () {
					var tempInput = document.createElement('input');
					tempInput.value = email;
					document.body.appendChild(tempInput);
					tempInput.select();
					document.execCommand('copy');
					document.body.removeChild(tempInput);
					showCopiedState(button);
				});
			} else {
				var tempInput = document.createElement('input');
				tempInput.value = email;
				document.body.appendChild(tempInput);
				tempInput.select();
				document.execCommand('copy');
				document.body.removeChild(tempInput);
				showCopiedState(button);
			}
		});
	});

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			closeModal();
			closeMobileModal();

			if (bottom && bottom.classList.contains('is-open')) {
				bottom.classList.remove('is-open');
				if (burger) {
					burger.setAttribute('aria-expanded', 'false');
				}
			}
		}
	});
});
</script>