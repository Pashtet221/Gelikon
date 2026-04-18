document.addEventListener('DOMContentLoaded', function () {
	const layout = document.querySelector('.gl-catalog-layout');
	if (!layout || typeof gelikonCatalogAjax === 'undefined') return;

	const productsWrap = document.getElementById('gl-catalog-products-wrap');
	const countNode = document.getElementById('gl-catalog-count');
	const resetBtn = document.getElementById('gl-catalog-reset');
	const sortSelect = document.getElementById('gl-catalog-sort');

	const termId = parseInt(layout.dataset.termId || '0', 10);
	const perPage = parseInt(layout.dataset.perPage || '12', 10);

	const minInput = document.getElementById('gl-price-min');
	const maxInput = document.getElementById('gl-price-max');
	const minValueNode = document.getElementById('gl-price-min-value');
	const maxValueNode = document.getElementById('gl-price-max-value');

	const slider = document.getElementById('gl-price-slider');
	const range = document.getElementById('gl-price-slider-range');
	const thumbMin = document.getElementById('gl-price-thumb-min');
	const thumbMax = document.getElementById('gl-price-thumb-max');

	const sidebar = document.getElementById('gl-catalog-sidebar');
	const overlay = document.getElementById('gl-catalog-overlay');
	const openBtn = document.getElementById('gl-open-filters');
	const closeBtn = document.getElementById('gl-close-filters');

	let debounceTimer = null;
	let activeThumb = null;

	function openFilters() {
		if (!sidebar || !overlay) return;
		sidebar.classList.add('is-open');
		overlay.classList.add('is-visible');
		document.body.classList.add('gl-filters-open');
	}

	function closeFilters() {
		if (!sidebar || !overlay) return;
		sidebar.classList.remove('is-open');
		overlay.classList.remove('is-visible');
		document.body.classList.remove('gl-filters-open');
	}

	if (openBtn) {
		openBtn.addEventListener('click', openFilters);
	}

	if (closeBtn) {
		closeBtn.addEventListener('click', closeFilters);
	}

	if (overlay) {
		overlay.addEventListener('click', closeFilters);
	}

	function bindAccordion() {
		document.querySelectorAll('[data-filter-toggle]').forEach(function (toggle) {
			if (toggle.dataset.bound === '1') return;
			toggle.dataset.bound = '1';

			toggle.addEventListener('click', function () {
				const block = toggle.closest('[data-filter-block]');
				const body = block.querySelector('[data-filter-body]');
				if (!block || !body) return;

				const collapsed = block.classList.contains('is-collapsed');

				block.classList.toggle('is-collapsed', !collapsed);
				body.hidden = !collapsed;
			});
		});
	}

	function collapseAllFiltersExceptPrice() {
		document.querySelectorAll('[data-filter-block]').forEach(function (block, index) {
			const body = block.querySelector('[data-filter-body]');
			if (!body) return;

			if (index === 0) {
				block.classList.remove('is-collapsed');
				body.hidden = false;
			} else {
				block.classList.add('is-collapsed');
				body.hidden = true;
			}
		});
	}

	function refreshActiveItems() {
		document.querySelectorAll('.gl-catalog-filter__item').forEach(function (item) {
			const input = item.querySelector('input[type="checkbox"]');
			if (!input) return;
			item.classList.toggle('is-active', input.checked);
		});
	}

	function getFilters() {
		const filters = {};

		document.querySelectorAll('.gl-filter-checkbox:checked').forEach(function (checkbox) {
			const taxonomy = checkbox.dataset.taxonomy;
			if (!taxonomy) return;

			if (!filters[taxonomy]) {
				filters[taxonomy] = [];
			}

			filters[taxonomy].push(checkbox.value);
		});

		return filters;
	}

	function debounceRequest() {
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(function () {
			requestProducts(1);
		}, 250);
	}

	function requestProducts(page = 1) {
		if (!productsWrap) return;

		const formData = new FormData();
		formData.append('action', 'gelikon_filter_products');
		formData.append('term_id', termId);
		formData.append('page', page);
		formData.append('per_page', perPage);

		const filters = getFilters();
		Object.keys(filters).forEach(function (taxonomy) {
			filters[taxonomy].forEach(function (value) {
				formData.append(`filters[${taxonomy}][]`, value);
			});
		});

		if (minInput) formData.append('min_price', minInput.value);
		if (maxInput) formData.append('max_price', maxInput.value);
		if (sortSelect) formData.append('orderby', sortSelect.value);

		productsWrap.classList.add('is-loading');

		fetch(gelikonCatalogAjax.ajaxurl, {
			method: 'POST',
			body: formData
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (response) {
				if (!response || !response.success || !response.data) {
					productsWrap.classList.remove('is-loading');
					return;
				}

				productsWrap.innerHTML = response.data.html;
				productsWrap.classList.remove('is-loading');

				if (countNode) {
					countNode.textContent = `${response.data.count} ${gelikonCatalogAjax.i18n.countSuffix}`;
				}

				bindPagination();
			})
			.catch(function () {
				productsWrap.classList.remove('is-loading');
			});
	}

	function updatePriceUI() {
		if (!slider || !minInput || !maxInput || !range || !thumbMin || !thumbMax) return;

		const min = parseInt(slider.dataset.min || '0', 10);
		const max = parseInt(slider.dataset.max || '0', 10);

		let from = parseInt(minInput.value || min, 10);
		let to = parseInt(maxInput.value || max, 10);

		if (from < min) from = min;
		if (to > max) to = max;

		if (from > to) {
			from = to;
			minInput.value = String(from);
		}

		const fromPercent = max > min ? ((from - min) / (max - min)) * 100 : 0;
		const toPercent = max > min ? ((to - min) / (max - min)) * 100 : 100;

		thumbMin.style.left = `${fromPercent}%`;
		thumbMax.style.left = `${toPercent}%`;

		range.style.left = `${fromPercent}%`;
		range.style.width = `${toPercent - fromPercent}%`;

		if (minValueNode) minValueNode.textContent = String(from);
		if (maxValueNode) maxValueNode.textContent = String(to);
	}

	function setValueFromPointer(clientX) {
		if (!slider || !activeThumb || !minInput || !maxInput) return;

		const rect = slider.getBoundingClientRect();
		const min = parseInt(slider.dataset.min || '0', 10);
		const max = parseInt(slider.dataset.max || '0', 10);

		if (rect.width <= 0) return;

		let percent = (clientX - rect.left) / rect.width;
		percent = Math.max(0, Math.min(1, percent));

		let value = Math.round(min + percent * (max - min));

		let from = parseInt(minInput.value || min, 10);
		let to = parseInt(maxInput.value || max, 10);

		if (activeThumb === 'min') {
			value = Math.min(value, to);
			value = Math.max(value, min);
			minInput.value = String(value);
		}

		if (activeThumb === 'max') {
			value = Math.max(value, from);
			value = Math.min(value, max);
			maxInput.value = String(value);
		}

		updatePriceUI();
	}

	function bindPriceSlider() {
		if (!slider || !thumbMin || !thumbMax) return;

		thumbMin.addEventListener('mousedown', function (e) {
			e.preventDefault();
			activeThumb = 'min';
		});

		thumbMax.addEventListener('mousedown', function (e) {
			e.preventDefault();
			activeThumb = 'max';
		});

		document.addEventListener('mousemove', function (e) {
			if (!activeThumb) return;
			setValueFromPointer(e.clientX);
		});

		document.addEventListener('mouseup', function () {
			if (!activeThumb) return;
			activeThumb = null;
			debounceRequest();
		});

		thumbMin.addEventListener('touchstart', function () {
			activeThumb = 'min';
		}, { passive: true });

		thumbMax.addEventListener('touchstart', function () {
			activeThumb = 'max';
		}, { passive: true });

		document.addEventListener('touchmove', function (e) {
			if (!activeThumb || !e.touches.length) return;
			setValueFromPointer(e.touches[0].clientX);
		}, { passive: true });

		document.addEventListener('touchend', function () {
			if (!activeThumb) return;
			activeThumb = null;
			debounceRequest();
		});

		slider.addEventListener('click', function (e) {
			const rect = slider.getBoundingClientRect();
			const clickX = e.clientX - rect.left;
			const minLeft = thumbMin.offsetLeft;
			const maxLeft = thumbMax.offsetLeft;

			activeThumb = Math.abs(clickX - minLeft) < Math.abs(clickX - maxLeft) ? 'min' : 'max';
			setValueFromPointer(e.clientX);
			activeThumb = null;
			debounceRequest();
		});

		updatePriceUI();
	}

	function bindCheckboxes() {
		document.querySelectorAll('.gl-filter-checkbox').forEach(function (checkbox) {
			checkbox.addEventListener('change', function () {
				refreshActiveItems();
				debounceRequest();
			});
		});
	}

	function bindPagination() {
		if (!productsWrap) return;

		productsWrap.querySelectorAll('.gl-catalog-pagination a.page-numbers').forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();

				const href = link.getAttribute('href') || '';
				const match = href.match(/\/page\/(\d+)\/|paged=(\d+)/);
				const page = match ? parseInt(match[1] || match[2], 10) : 1;

				requestProducts(page);
			});
		});
	}

	if (resetBtn) {
		resetBtn.addEventListener('click', function () {
			document.querySelectorAll('.gl-filter-checkbox').forEach(function (checkbox) {
				checkbox.checked = false;
			});

			if (slider && minInput && maxInput) {
				minInput.value = slider.dataset.min || '0';
				maxInput.value = slider.dataset.max || '0';
			}

			if (sortSelect) {
				sortSelect.value = 'menu_order';
			}

			refreshActiveItems();
			updatePriceUI();
			collapseAllFiltersExceptPrice();
			requestProducts(1);
		});
	}

	if (sortSelect) {
		sortSelect.addEventListener('change', function () {
			requestProducts(1);
		});
	}

	bindAccordion();
	bindPriceSlider();
	bindCheckboxes();
	bindPagination();
	refreshActiveItems();
	updatePriceUI();
	collapseAllFiltersExceptPrice();
});