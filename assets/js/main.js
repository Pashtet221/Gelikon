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


  const accountAuthTabs = document.querySelector('[data-gl-auth-tabs]');

  if (accountAuthTabs) {
    const tabs = accountAuthTabs.querySelectorAll('[data-gl-auth-tab]');
    const panels = accountAuthTabs.querySelectorAll('.gl-myaccount-auth__panel');

    const activateAuthTab = function (target) {
      tabs.forEach(function (tab) {
        const isActive = tab.dataset.glAuthTab === target;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      panels.forEach(function (panel) {
        const isActive = panel.id === 'gl-account-' + target + '-panel';
        panel.classList.toggle('is-active', isActive);
        panel.hidden = !isActive;
      });
    };

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        activateAuthTab(tab.dataset.glAuthTab);
      });
    });

    if (window.location.hash === '#register') {
      activateAuthTab('register');
    }
  }
});
