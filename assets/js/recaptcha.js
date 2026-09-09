(function () {
	'use strict';

	if (!window.gelikonRecaptcha || !window.gelikonRecaptcha.siteKey) {
		return;
	}

	function actionFor(form) {
		if (form.classList.contains('woocommerce-form-register')) return 'registration';
		if (form.classList.contains('woocommerce-form-login')) return 'login';
		if (form.classList.contains('checkout')) return 'checkout';
		if (form.id === 'commentform') return 'comment';
		if (form.querySelector('[name="gelikon_submit_product_question"]')) return 'product_question';
		if (form.classList.contains('cart')) return 'add_to_cart';
		return 'form_submit';
	}

	function field(form, name) {
		var input = form.querySelector('input[name="' + name + '"]');
		if (!input) {
			input = document.createElement('input');
			input.type = 'hidden';
			input.name = name;
			form.appendChild(input);
		}
		return input;
	}

	document.addEventListener('submit', function (event) {
		var form = event.target;
		if (!(form instanceof HTMLFormElement) || (form.method || 'get').toLowerCase() !== 'post') return;
		if (form.dataset.recaptchaReady === '1') {
			form.dataset.recaptchaReady = '0';
			return;
		}

		event.preventDefault();
		event.stopImmediatePropagation();
		var action = actionFor(form);
		var submitter = event.submitter;

		window.grecaptcha.ready(function () {
			window.grecaptcha.execute(window.gelikonRecaptcha.siteKey, { action: action }).then(function (token) {
				field(form, 'gelikon_recaptcha_token').value = token;
				field(form, 'gelikon_recaptcha_action').value = action;
				form.dataset.recaptchaReady = '1';
				if (form.requestSubmit) form.requestSubmit(submitter || undefined);
				else form.submit();
			}).catch(function () {
				window.alert(window.gelikonRecaptcha.error);
			});
		});
	}, true);
}());
