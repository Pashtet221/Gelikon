document.addEventListener('DOMContentLoaded', function () {
	const reviewsSlider = document.querySelector('.gl-reviews-slider.swiper');
	const blogSlider = document.querySelector('.gl-blog-slider.swiper');

	if (typeof Swiper === 'undefined') {
		return;
	}

	if (reviewsSlider) {
		new Swiper(reviewsSlider, {
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
	}

	if (blogSlider) {
		new Swiper(blogSlider, {
			loop: false,
			spaceBetween: 20,
			slidesPerView: 1.15,
			grabCursor: true,
			navigation: {
				nextEl: '.gl-blog-slider__next',
				prevEl: '.gl-blog-slider__prev',
			},
			pagination: {
				el: '.gl-blog-slider__pagination',
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
	}
});
