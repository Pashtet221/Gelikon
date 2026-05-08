jQuery(function ($) {
    function paymentNeedsEmail() {
        const method = $('input[name="payment_method"]:checked').val() || '';
        return ['yookassa', 'cloudpayments', 'tinkoff', 'online_payment', 'installments'].includes(method);
    }

    function updateEmailRequirement() {
        const $emailField = $('#billing_email_field');
        if (!$emailField.length) {
            return;
        }

        const required = paymentNeedsEmail();
        $emailField.toggleClass('validate-required', required);
        $emailField.find('label .required').remove();
        if (required) {
            $emailField.find('label').append(' <abbr class="required" title="обязательно">*</abbr>');
        }
    }

    function updateShippingVisibility() {
        const method = $('input[name^="shipping_method"]:checked').val() || '';
        const isPickup = method.includes('local_pickup');
        const isCdek = method.includes('cdek');

        $('.woocommerce-shipping-fields').toggle(!isPickup);

        if (isPickup) {
            if (!$('#gelikon-pickup-address').length) {
                $('.woocommerce-shipping-fields').after(
                    '<div id="gelikon-pickup-address" class="woocommerce-additional-fields"><h3>Адрес самовывоза</h3><p>' + gelikonCheckout.officeAddress + '</p></div>'
                );
            }
        } else {
            $('#gelikon-pickup-address').remove();
        }

        if (isCdek) {
            $('#shipping_address_1_field label').text('ПВЗ СДЭК');
            $('#shipping_address_1').attr('placeholder', 'Выберите пункт выдачи на карте/в списке');
            $('#shipping_address_2_field').hide();
        } else {
            $('#shipping_address_1_field label').text('Улица и дом');
            $('#shipping_address_2_field').show();
        }
    }

    $(document.body).on('updated_checkout', function () {
        updateEmailRequirement();
        updateShippingVisibility();
    });

    $('form.checkout').on('change', 'input[name="payment_method"], input[name^="shipping_method"]', function () {
        updateEmailRequirement();
        updateShippingVisibility();
    });

    updateEmailRequirement();
    updateShippingVisibility();
});
