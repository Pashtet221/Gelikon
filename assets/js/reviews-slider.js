document.addEventListener('DOMContentLoaded', function () {
	const slider = document.querySelector('.gl-reviews-slider.swiper');

	if (!slider || typeof Swiper === 'undefined') {
		return;
	}

	new Swiper(slider, {
		loop: false,
		spaceBetween: 20,
		slidesPerView: 1.15,
		grabCursor: true,
		navigation: {
			nextEl: '.gl-reviews-slider__next',
			prevEl: '.gl-reviews-slider__prev',
		},
		pagination: {
			el: '.gl-reviews-slider__pagination',
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
			}
		}
	});
});