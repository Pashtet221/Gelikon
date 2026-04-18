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
