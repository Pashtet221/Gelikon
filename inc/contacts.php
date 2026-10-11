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
	$script = '/assets/js/contacts-editor.js';
	wp_enqueue_script('gelikon-contacts-editor', GELIKON_URI . $script, array('acf-input', 'editor', 'quicktags'), (string) filemtime(GELIKON_DIR . $script), true);
});
