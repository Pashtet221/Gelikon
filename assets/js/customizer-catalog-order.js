(function ($) {
	'use strict';

	function initialize(control) {
		const root = control.container.find('.gelikon-catalog-order');
		const list = root.find('.gelikon-catalog-order__list');
		const value = root.find('.gelikon-catalog-order__value');
		const picker = root.find('.gelikon-catalog-order__picker');

		function sync() {
			const ids = list.children('li').map(function () {
				return $(this).data('product-id');
			}).get();

			value.val(ids.join(', ')).trigger('change');
			root.toggleClass('is-empty', ids.length === 0);
			picker.find('option').prop('disabled', false);
			ids.forEach(function (id) {
				picker.find('option[value="' + id + '"]').prop('disabled', true);
			});
		}

		root.on('click', '.gelikon-catalog-order__add', function () {
			const option = picker.find('option:selected');
			const id = parseInt(option.val(), 10);

			if (!id || list.find('[data-product-id="' + id + '"]').length) return;

			const item = $('<li>').attr('data-product-id', id);
			$('<span class="gelikon-catalog-order__name">').text(option.text()).appendTo(item);
			$('<span class="gelikon-catalog-order__actions">')
				.append('<button type="button" class="button-link" data-move="up" aria-label="Поднять товар">↑</button>')
				.append('<button type="button" class="button-link" data-move="down" aria-label="Опустить товар">↓</button>')
				.append('<button type="button" class="button-link-delete" data-remove aria-label="Удалить товар из порядка">×</button>')
				.appendTo(item);
			list.append(item);
			picker.val('');
			sync();
		});

		root.on('click', '[data-move]', function () {
			const item = $(this).closest('li');
			const sibling = $(this).data('move') === 'up' ? item.prev() : item.next();

			if (!sibling.length) return;
			if ($(this).data('move') === 'up') item.insertBefore(sibling);
			else item.insertAfter(sibling);
			sync();
		});

		root.on('click', '[data-remove]', function () {
			$(this).closest('li').remove();
			sync();
		});

		sync();
	}

	wp.customize.bind('ready', function () {
		wp.customize.control.each(function (control) {
			if (control.params.type === 'gelikon_catalog_order') initialize(control);
		});
	});
}(jQuery));
