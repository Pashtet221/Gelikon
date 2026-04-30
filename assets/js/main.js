document.addEventListener('DOMContentLoaded', function () {
  const burger = document.querySelector('.gl-burger');
  const nav = document.querySelector('.gl-nav');

  if (burger && nav) {
    burger.addEventListener('click', function () {
      const expanded = burger.getAttribute('aria-expanded') === 'true';
      burger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      nav.classList.toggle('is-open');
    });
  }
});

document.addEventListener('DOMContentLoaded', function () {
  const detailsBlock = document.querySelector('[data-details]');

  if (!detailsBlock) {
    return;
  }

  const toggleButton = detailsBlock.querySelector('[data-details-toggle]');
  const detailsContent = detailsBlock.querySelector('.gl-company-details__more');

  if (!toggleButton || !detailsContent) {
    return;
  }

  toggleButton.addEventListener('click', function () {
    const isHidden = detailsContent.hasAttribute('hidden');

    if (isHidden) {
      detailsContent.removeAttribute('hidden');
      toggleButton.textContent = 'Скрыть';
    } else {
      detailsContent.setAttribute('hidden', 'hidden');
      toggleButton.textContent = 'Показать полностью';
    }
  });
});
