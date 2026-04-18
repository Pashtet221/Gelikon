document.addEventListener('DOMContentLoaded', function () {
	const sliders = document.querySelectorAll('.gl-home-products-slider.swiper');

	if (!sliders.length || typeof Swiper === 'undefined') {
		return;
	}

	sliders.forEach(function (slider) {
		const nextBtn = slider.querySelector('.gl-home-products-slider__next');
		const prevBtn = slider.querySelector('.gl-home-products-slider__prev');
		const pagination = slider.querySelector('.gl-home-products-slider__pagination');

		new Swiper(slider, {
			loop: false,
			grabCursor: true,
			watchOverflow: true,
			spaceBetween: 16,
			slidesPerView: 1.15,
			navigation: {
				nextEl: nextBtn,
				prevEl: prevBtn,
			},
			pagination: {
				el: pagination,
				clickable: true,
			},
			breakpoints: {
				640: {
					slidesPerView: 2,
					spaceBetween: 18,
				},
				992: {
					slidesPerView: 3,
					spaceBetween: 20,
				},
				1280: {
					slidesPerView: 4,
					spaceBetween: 24,
				}
			}
		});
	});
});