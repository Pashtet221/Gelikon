document.addEventListener('DOMContentLoaded', function () {
	const dropdowns = document.querySelectorAll('.gl-catalog-dropdown');

	if (!dropdowns.length) {
		return;
	}

	dropdowns.forEach(function (dropdown) {
		const toggle = dropdown.querySelector('.gl-catalog-dropdown__toggle');
		const panel = dropdown.querySelector('.gl-catalog-dropdown__panel');
		const parentRows = dropdown.querySelectorAll('.gl-catalog-dropdown__parent-row');
		const childPanels = dropdown.querySelectorAll('.gl-catalog-dropdown__children-panel');

		if (!toggle || !panel) {
			return;
		}

		toggle.addEventListener('click', function () {
			const isOpen = dropdown.classList.contains('is-open');

			dropdown.classList.toggle('is-open', !isOpen);
			toggle.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
			panel.hidden = isOpen;
		});

		parentRows.forEach(function (row) {
			row.addEventListener('mouseenter', function () {
				activatePanel(row.dataset.target);
			});

			row.addEventListener('click', function (e) {
				if (e.target.closest('.gl-catalog-dropdown__parent-link-main')) {
					return;
				}
				activatePanel(row.dataset.target);
			});

			row.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					activatePanel(row.dataset.target);
				}
			});
		});

		function activatePanel(target) {
			parentRows.forEach(function (row) {
				const active = row.dataset.target === target;
				row.classList.toggle('is-active', active);
				row.setAttribute('aria-selected', active ? 'true' : 'false');
			});

			childPanels.forEach(function (panelItem) {
				const active = panelItem.dataset.panel === target;
				panelItem.classList.toggle('is-active', active);
				panelItem.hidden = !active;
			});
		}

		document.addEventListener('click', function (e) {
			if (!dropdown.contains(e.target)) {
				dropdown.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
				panel.hidden = true;
			}
		});
	});
});