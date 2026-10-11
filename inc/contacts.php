<?php
/** Rich text for the contacts page, preserving existing ACF values. */
if (!defined('ABSPATH')) {
	exit;
}

function gelikon_contacts_rich_text($value) {
	return wp_kses_post(wpautop((string) $value));
}

function gelikon_contacts_legacy_details($post_id) {
	$fields = array(
		'company_kpp' => array('КПП', '771401001'),
		'company_legal_address' => array('Юридический адрес', 'Москва, ул. Складочная, д. 1, стр. 18'),
		'company_actual_address' => array('Фактический адрес', 'Москва, ул. Складочная, д. 1, стр. 18, офис 205'),
		'company_bank_details' => array('Банковские реквизиты', 'ПАО Сбербанк, р/с 40702810900000000000, БИК 044525225'),
		'company_director' => array('Генеральный директор', 'Генеральный директор — Иванов Иван Иванович'),
	);
	$content = '';
	foreach ($fields as $name => $field) {
		$value = get_post_meta($post_id, $name, true);
		$value = $value !== '' ? $value : $field[1];
		$content .= '<p><strong>' . esc_html($field[0]) . ':</strong> ' . nl2br(esc_html($value)) . '</p>';
	}
	return $content;
}

/** Prefill the new editor once; clearing/saving it must not revive old fields. */
function gelikon_contacts_load_details($value, $post_id, $field) {
	if (!is_numeric($post_id) || metadata_exists('post', (int) $post_id, 'company_details_content')) {
		return $value;
	}
	return gelikon_contacts_legacy_details((int) $post_id);
}
add_filter('acf/load_value/name=company_details_content', 'gelikon_contacts_load_details', 10, 3);

function gelikon_contacts_details_content($post_id) {
	if (function_exists('get_field')) {
		return gelikon_contacts_rich_text(get_field('company_details_content', $post_id));
	}
	$value = metadata_exists('post', $post_id, 'company_details_content')
		? get_post_meta($post_id, 'company_details_content', true)
		: gelikon_contacts_legacy_details($post_id);
	return gelikon_contacts_rich_text($value);
}

add_filter('acf/fields/wysiwyg/toolbars', function ($toolbars) {
	$toolbars['Contacts'] = array(1 => array('formatselect', 'bold', 'italic', 'underline', 'bullist', 'numlist', 'link', 'unlink', 'undo', 'redo'));
	return $toolbars;
}, 20);

/** Load the native editor before Gutenberg/ACF renders the contacts metabox. */
add_action('admin_enqueue_scripts', function ($hook) {
	if (!in_array($hook, array('post.php', 'post-new.php'), true) || !function_exists('acf')) {
		return;
	}
	$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
	if (!$post_id || get_page_template_slug($post_id) !== 'page-contacts.php') {
		return;
	}
	wp_enqueue_editor();
	wp_enqueue_media(array('post' => $post_id));
	wp_enqueue_script('gelikon-contacts-social-admin', GELIKON_URI . '/assets/js/contacts-social-admin.js', array('media-editor'), (string) filemtime(GELIKON_DIR . '/assets/js/contacts-social-admin.js'), true);
	wp_enqueue_style('gelikon-contacts-social-admin', GELIKON_URI . '/assets/css/contacts-social-admin.css', array(), (string) filemtime(GELIKON_DIR . '/assets/css/contacts-social-admin.css'));
	$script = '/assets/js/contacts-editor.js';
	wp_enqueue_script('gelikon-contacts-editor', GELIKON_URI . $script, array('acf-input', 'editor', 'quicktags'), (string) filemtime(GELIKON_DIR . $script), true);
});

/** A repeatable list that works with both ACF Free and Pro. */
function gelikon_contacts_social_links($post_id) {
	$rows = get_post_meta($post_id, '_gelikon_contacts_social_links', true);
	return is_array($rows) ? $rows : array();
}

function gelikon_contacts_social_row($row, $index) {
	$name = 'gelikon_contact_social[' . $index . ']';
	$icon_id = absint($row['icon_id'] ?? 0);
	?>
	<div class="gl-social-admin-row">
		<div class="gl-social-admin-icon">
			<div data-social-preview><?php if ($icon_id) echo wp_get_attachment_image($icon_id, 'thumbnail'); ?></div>
			<input type="hidden" data-social-icon name="<?php echo esc_attr($name); ?>[icon_id]" value="<?php echo esc_attr($icon_id); ?>">
			<button type="button" class="button" data-social-choose>Выбрать / загрузить иконку</button>
		</div>
		<div class="gl-social-admin-fields">
			<label>Название <input type="text" name="<?php echo esc_attr($name); ?>[label]" value="<?php echo esc_attr($row['label'] ?? ''); ?>" placeholder="Например, Telegram"></label>
			<label>Ссылка <input type="url" name="<?php echo esc_attr($name); ?>[url]" value="<?php echo esc_attr($row['url'] ?? ''); ?>" placeholder="https://…"></label>
		</div>
		<div class="gl-social-admin-actions">
			<button type="button" class="button" data-social-up aria-label="Переместить выше">↑</button>
			<button type="button" class="button" data-social-down aria-label="Переместить ниже">↓</button>
			<button type="button" class="button-link-delete" data-social-remove>Удалить</button>
		</div>
	</div>
	<?php
}

add_action('acf/render_field/key=field_company_social_links', function () {
	$post_id = get_the_ID();
	wp_nonce_field('gelikon_contacts_social_' . $post_id, 'gelikon_contacts_social_nonce', false);
	echo '<div class="gl-social-admin"><div data-social-rows>';
	foreach (gelikon_contacts_social_links($post_id) as $index => $row) {
		gelikon_contacts_social_row($row, $index);
	}
	echo '</div><button type="button" class="button button-primary" data-social-add>Добавить соцсеть</button><template data-social-template>';
	gelikon_contacts_social_row(array(), '__index__');
	echo '</template></div>';
});

add_action('save_post_page', function ($post_id) {
	if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id) || !current_user_can('edit_post', $post_id)) {
		return;
	}
	$nonce = isset($_POST['gelikon_contacts_social_nonce']) && is_string($_POST['gelikon_contacts_social_nonce']) ? wp_unslash($_POST['gelikon_contacts_social_nonce']) : '';
	if (!wp_verify_nonce($nonce, 'gelikon_contacts_social_' . $post_id) || get_page_template_slug($post_id) !== 'page-contacts.php') {
		return;
	}
	$submitted = isset($_POST['gelikon_contact_social']) && is_array($_POST['gelikon_contact_social']) ? wp_unslash($_POST['gelikon_contact_social']) : array();
	$rows = array();
	foreach ($submitted as $row) {
		if (!is_array($row)) continue;
		$icon_id = isset($row['icon_id']) && is_scalar($row['icon_id']) ? absint($row['icon_id']) : 0;
		$url = isset($row['url']) && is_string($row['url']) ? esc_url_raw($row['url'], array('http', 'https')) : '';
		$label = isset($row['label']) && is_string($row['label']) ? sanitize_text_field($row['label']) : '';
		// Keep partial rows editable; display only complete, valid links on the site.
		if ($icon_id && !wp_attachment_is_image($icon_id)) $icon_id = 0;
		if ($icon_id || $url || $label) $rows[] = array('icon_id' => $icon_id, 'url' => $url, 'label' => $label);
	}
	update_post_meta($post_id, '_gelikon_contacts_social_links', $rows);
});
