document.addEventListener('DOMContentLoaded', function () {
	if (typeof Swiper !== 'undefined') {
		document.querySelectorAll('.gl-home-blog-slider.swiper').forEach(function (slider) {
			new Swiper(slider, {
				loop: false,
				grabCursor: true,
				watchOverflow: true,
				spaceBetween: 20,
				slidesPerView: 1.05,
				navigation: {
					nextEl: slider.querySelector('.gl-home-blog-slider__next') || '.gl-home-blog-slider__next',
					prevEl: slider.querySelector('.gl-home-blog-slider__prev') || '.gl-home-blog-slider__prev',
				},
				pagination: {
					el: slider.querySelector('.gl-home-blog-slider__pagination'),
					clickable: true,
				},
				breakpoints: {
					640: { slidesPerView: 2, spaceBetween: 18 },
					992: { slidesPerView: 3, spaceBetween: 20 },
				},
			});
		});
	}

	document.querySelectorAll('[data-gl-blog-filter]').forEach(function (filters) {
		const cards = Array.from(document.querySelectorAll('[data-gl-blog-card]'));
		const buttons = Array.from(filters.querySelectorAll('[data-category]'));

		buttons.forEach(function (button) {
			button.addEventListener('click', function () {
				const category = button.getAttribute('data-category') || 'all';

				buttons.forEach(function (item) {
					const isActive = item === button;
					item.classList.toggle('is-active', isActive);
					item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
				});

				cards.forEach(function (card) {
					const categories = (card.getAttribute('data-categories') || '').split(' ');
					card.hidden = category !== 'all' && !categories.includes(category);
				});
			});
		});
	});
});
