(function ($) {
	'use strict';

	var config = window.gelikonDadata || {};
	var instances = [];
	var checkoutSubmitting = false;

	function Autocomplete(input, mode) {
		this.$input = $(input);
		this.mode = mode;
		this.timer = null;
		this.request = null;
		this.sequence = 0;
		this.$list = $('<div class="gl-dadata-suggestions" role="listbox"></div>').hide();
		this.$input.attr({ autocomplete: 'off', 'aria-autocomplete': 'list', 'aria-expanded': 'false' });
		this.$input.after(this.$list);
		this.bind();
	}

	Autocomplete.prototype.bind = function () {
		var self = this;

		this.$input.on('input', function () {
			clearTimeout(self.timer);
			self.timer = setTimeout(function () { self.search(); }, 280);
		});

		this.$input.on('keydown', function (event) { self.onKeydown(event); });
		this.$input.on('blur', function () {
			setTimeout(function () { self.close(); }, 160);
		});
	};

	Autocomplete.prototype.search = function () {
		var self = this;
		var query = $.trim(this.$input.val());

		if (checkoutSubmitting || query.length < (config.minChars || 2)) {
			this.close();
			return;
		}

		if (this.request) {
			this.request.abort();
		}

		var sequence = ++this.sequence;
		this.message(config.messages.loading);
		this.request = $.ajax({
			url: config.ajaxUrl,
			method: 'POST',
			timeout: 3000,
			data: {
				action: 'gelikon_dadata_suggest',
				nonce: config.nonce,
				mode: this.mode,
				query: query,
				city: this.mode === 'address' ? $('[name="shipping_city"]').val() : ''
			}
		}).done(function (response) {
			if (sequence !== self.sequence) return;
			var items = response && response.success ? response.data.suggestions : [];
			self.render(items);
		}).fail(function () {
			// Suggestions are optional; leave manual input usable without an error state.
			self.close();
		}).always(function () {
			if (sequence === self.sequence) self.request = null;
		});
	};

	Autocomplete.prototype.cancel = function () {
		clearTimeout(this.timer);
		this.sequence++;
		if (this.request) this.request.abort();
		this.request = null;
		this.close();
	};

	Autocomplete.prototype.render = function (items) {
		var self = this;
		this.$list.empty();

		if (!items.length) {
			this.message(config.messages.empty);
			return;
		}

		$.each(items, function (_, item) {
			$('<button type="button" class="gl-dadata-suggestion" role="option"></button>')
				.text(item.label)
				.data('suggestion', item)
				.on('mousedown', function (event) { event.preventDefault(); })
				.on('click', function () { self.select($(this).data('suggestion')); })
				.appendTo(self.$list);
		});
		this.open();
	};

	Autocomplete.prototype.select = function (item) {
		this.$input.val(item.value).trigger('change');
		if (this.mode === 'address' && item.city && !$('[name="shipping_city"]').val()) {
			$('[name="shipping_city"]').val(item.city).trigger('change');
		}
		this.close();
	};

	Autocomplete.prototype.message = function (text) {
		this.$list.empty().append($('<div class="gl-dadata-suggestions__message"></div>').text(text || ''));
		this.open();
	};

	Autocomplete.prototype.open = function () {
		this.$list.show();
		this.$input.attr('aria-expanded', 'true');
	};

	Autocomplete.prototype.close = function () {
		this.$list.hide();
		this.$input.attr('aria-expanded', 'false');
	};

	Autocomplete.prototype.onKeydown = function (event) {
		var $options = this.$list.find('.gl-dadata-suggestion');
		var $active = $options.filter('.is-active');
		var index = $options.index($active);

		if (event.key === 'Escape') {
			this.close();
			return;
		}
		if (event.key === 'ArrowDown') index = Math.min(index + 1, $options.length - 1);
		else if (event.key === 'ArrowUp') index = Math.max(index - 1, 0);
		else if (event.key === 'Enter' && $active.length) {
			event.preventDefault();
			$active.trigger('click');
			return;
		} else return;

		event.preventDefault();
		$options.removeClass('is-active').eq(index).addClass('is-active');
	};

	$(function () {
		$('[name="shipping_city"]').each(function () { instances.push(new Autocomplete(this, 'city')); });
		$('[name="shipping_address_1"]').each(function () { instances.push(new Autocomplete(this, 'address')); });

		/* Never let a suggestion request participate in the critical checkout path. */
		$(document.body).on('checkout_place_order', function () {
			checkoutSubmitting = true;
			$.each(instances, function (_, instance) { instance.cancel(); });
		});
		$('form.checkout').on('submit', function () {
			checkoutSubmitting = true;
			$.each(instances, function (_, instance) { instance.cancel(); });
		});
		$(document.body).on('checkout_error', function () {
			checkoutSubmitting = false;
		});
	});
}(jQuery));
