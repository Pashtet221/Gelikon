(function () {
  'use strict';

  const pageConfigurations = [
    {
      root: '.gl-homepage',
      sections: '.gl-home-banners, .gl-home-products, .gl-home-trust, .gl-home-blog, .gl-home-reviews, .gl-prefooter',
      groups: [
        { container: '.gl-home-banners', items: '.gl-home-banner', stagger: 90, cycle: 4 },
        { container: '.gl-home-products-slider', items: '.swiper-slide', stagger: 110, cycle: 4 },
        { container: '.gl-trust-grid', items: '.gl-trust-item', stagger: 100, cycle: 3 },
        { container: '.gl-trust-payments__list', items: '.gl-trust-payments__logo', stagger: 80, cycle: 4 },
        { container: '.gl-home-blog-slider', items: '.swiper-slide', stagger: 110, cycle: 3 },
        { container: '.gl-reviews-slider', items: '.swiper-slide', stagger: 110, cycle: 3 },
        { container: '.gl-prefooter__inner', items: '.gl-prefooter__content, .gl-prefooter__media', stagger: 120, cycle: 2 }
      ]
    },
    {
      root: '.gl-warranty-page',
      sections: '.gl-about-hero__content, .gl-about-sections > .gl-card',
      groups: [
        { container: '.gl-trust-grid', items: '.gl-trust-item', stagger: 100, cycle: 3 }
      ]
    },
    {
      root: '.gl-delivery-page',
      sections: '.gl-about-hero__content, .gl-about-sections > .gl-card',
      groups: [
        { container: '.gl-trust-grid', items: '.gl-trust-item', stagger: 100, cycle: 3 },
        { container: '.gl-delivery-cards', items: '.gl-delivery-card', stagger: 100, cycle: 3 }
      ]
    },
    {
      root: '.gl-about-page:not(.gl-warranty-page):not(.gl-delivery-page)',
      sections: '.gl-about-hero__content, .gl-about-sections > .gl-card',
      groups: [
        { container: '.gl-about-approach__grid', items: '.gl-about-approach__item', stagger: 90, cycle: 4 },
        { container: '.gl-about-logos', items: '.gl-about-logos__item', stagger: 80, cycle: 4 }
      ]
    },
    {
      root: '.gl-contacts-page',
      sections: '.gl-page-single > .gl-card',
      groups: [
        { container: '.gl-contacts-grid', items: ':scope > div', stagger: 90, cycle: 4 },
        { container: '.gl-contacts-map-grid', items: ':scope > div', stagger: 110, cycle: 2 }
      ]
    }
  ];
  const activeConfiguration = pageConfigurations.find(function (configuration) {
    return document.querySelector(configuration.root);
  });

  if (!activeConfiguration) {
    return;
  }

  const page = document.querySelector(activeConfiguration.root);
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const sectionElements = Array.from(page.querySelectorAll(activeConfiguration.sections));
  const itemElements = [];

  activeConfiguration.groups.forEach(function (group) {
    page.querySelectorAll(group.container).forEach(function (container) {
      container.querySelectorAll(group.items).forEach(function (element, index) {
        element.classList.add('gl-reveal-item');
        element.style.setProperty('--gl-reveal-delay', (index % group.cycle) * group.stagger + 'ms');
        itemElements.push(element);
      });
    });
  });

  const revealElements = sectionElements.concat(itemElements);

  if (!revealElements.length) {
    return;
  }

  sectionElements.forEach(function (element) {
    element.classList.add('gl-reveal');
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
