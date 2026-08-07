(function () {
  'use strict';

  const homepage = document.querySelector('.gl-homepage');

  if (!homepage) {
    return;
  }

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const revealSelectors = [
    '.gl-home-banner',
    '.gl-home-products',
    '.gl-home-trust',
    '.gl-home-blog',
    '.gl-home-reviews',
    '.gl-prefooter'
  ];
  const revealElements = homepage.querySelectorAll(revealSelectors.join(','));

  if (!revealElements.length) {
    return;
  }

  revealElements.forEach(function (element, index) {
    element.classList.add('gl-reveal');

    if (element.classList.contains('gl-home-banner')) {
      element.classList.add('gl-reveal--scale');
      element.style.setProperty('--gl-reveal-delay', (index % 4) * 70 + 'ms');
    }
  });

  document.documentElement.classList.add('gl-animations-ready');

  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealElements.forEach(function (element) {
      element.classList.add('is-visible');
    });
    return;
  }

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) {
        return;
      }

      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    });
  }, {
    rootMargin: '0px 0px -10% 0px',
    threshold: 0.12
  });

  revealElements.forEach(function (element) {
    observer.observe(element);
  });
}());
