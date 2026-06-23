(function () {
  'use strict';

  const settings = window.gelikonPrintProductPrice || {};

  if (!settings.ajaxUrl || !settings.nonce || !settings.productId) {
    return;
  }

  const form = document.querySelector('form.cart');

  if (!form) {
    return;
  }

  const priceNodes = Array.from(document.querySelectorAll([
    '.gl-product-buybox__price',
    '.gl-product-mobile-bar__price',
    '.gl-product-desktop-bar__price',
    '.summary .price',
    '.woocommerce-variation-price .price'
  ].join(',')));

  const status = document.createElement('div');
  status.className = 'gl-print-product-price-status';
  status.setAttribute('aria-live', 'polite');
  status.hidden = true;

  const target = document.querySelector('.gl-product-buybox__price') || priceNodes[0] || form;
  target.insertAdjacentElement('afterend', status);

  let timer = null;
  let controller = null;
  let lastSignature = '';

  const setLoading = function (isLoading) {
    form.classList.toggle('gl-print-product-price-is-loading', isLoading);

    if (isLoading) {
      status.hidden = false;
      status.textContent = settings.loadingText || 'Пересчитываем цену…';
    }
  };

  const clearStatus = function () {
    status.hidden = true;
    status.textContent = '';
  };

  const normalizeValue = function (value) {
    return typeof value === 'string' ? value.trim() : value;
  };

  const collectOptions = function () {
    const formData = new FormData(form);
    const options = {};

    formData.forEach(function (value, key) {
      if (!key || key === 'add-to-cart' || key === 'product_id' || key === 'variation_id') {
        return;
      }

      const normalized = normalizeValue(value);

      if (Object.prototype.hasOwnProperty.call(options, key)) {
        if (!Array.isArray(options[key])) {
          options[key] = [options[key]];
        }

        options[key].push(normalized);
      } else {
        options[key] = normalized;
      }
    });

    return options;
  };

  const updatePrice = function (payload) {
    if (!payload || !payload.price_html) {
      return;
    }

    priceNodes.forEach(function (node) {
      node.innerHTML = payload.price_html;
    });
  };

  const recalculate = function () {
    const options = collectOptions();
    const signature = JSON.stringify(options);

    if (signature === lastSignature) {
      return;
    }

    lastSignature = signature;

    if (controller) {
      controller.abort();
    }

    controller = new AbortController();
    setLoading(true);

    const requestData = new FormData();
    requestData.append('action', 'gelikon_recalculate_print_product_price');
    requestData.append('nonce', settings.nonce);
    requestData.append('product_id', settings.productId);
    requestData.append('options', JSON.stringify(options));

    fetch(settings.ajaxUrl, {
      method: 'POST',
      body: requestData,
      credentials: 'same-origin',
      signal: controller.signal
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Price request failed');
        }

        return response.json();
      })
      .then(function (response) {
        if (response && response.success) {
          updatePrice(response.data);
          clearStatus();
        }
      })
      .catch(function (error) {
        if (error.name !== 'AbortError') {
          status.hidden = false;
          status.textContent = settings.errorText || 'Не удалось пересчитать цену. Попробуйте изменить параметры ещё раз.';
        }
      })
      .finally(function () {
        if (!controller || !controller.signal.aborted) {
          controller = null;
          form.classList.remove('gl-print-product-price-is-loading');
        }
      });
  };

  const scheduleRecalculate = function () {
    window.clearTimeout(timer);
    timer = window.setTimeout(recalculate, 350);
  };

  ['change', 'input'].forEach(function (eventName) {
    form.addEventListener(eventName, scheduleRecalculate, true);
  });

  form.addEventListener('click', function (event) {
    if (event.target.closest('button, [role="button"], label, input, select, textarea')) {
      scheduleRecalculate();
    }
  }, true);

  scheduleRecalculate();
}());
