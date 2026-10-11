(function () {
    'use strict';

    function reindex(list) {
        Array.from(list.children).forEach(function (row, index) {
            row.querySelectorAll('input[name]').forEach(function (input) {
                input.name = input.name.replace(/\[[^\]]+\]/, '[' + index + ']');
            });
            row.querySelector('[data-social-up]').disabled = index === 0;
            row.querySelector('[data-social-down]').disabled = index === list.children.length - 1;
        });
        // Notify Gutenberg that the metabox has been edited.
        list.dispatchEvent(new Event('change', { bubbles: true }));
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('button');
        const root = button && button.closest('.gl-social-admin');
        if (!root) return;
        const list = root.querySelector('[data-social-rows]');
        const row = button.closest('.gl-social-admin-row');
        if (button.hasAttribute('data-social-add')) {
            list.appendChild(root.querySelector('template').content.cloneNode(true));
            reindex(list);
            list.lastElementChild.querySelector('input[type="text"]').focus();
        } else if (button.hasAttribute('data-social-remove')) {
            row.remove();
            reindex(list);
        } else if (button.hasAttribute('data-social-up') && row.previousElementSibling) {
            list.insertBefore(row, row.previousElementSibling);
            reindex(list);
        } else if (button.hasAttribute('data-social-down') && row.nextElementSibling) {
            list.insertBefore(row.nextElementSibling, row);
            reindex(list);
        } else if (button.hasAttribute('data-social-choose')) {
            const frame = window.wp.media({ title: 'Иконка соцсети', button: { text: 'Выбрать иконку' }, library: { type: 'image' }, multiple: false });
            frame.on('select', function () {
                const image = frame.state().get('selection').first().toJSON();
                row.querySelector('[data-social-icon]').value = image.id;
                const preview = document.createElement('img');
                preview.src = image.sizes && image.sizes.thumbnail ? image.sizes.thumbnail.url : image.url;
                preview.alt = image.alt || '';
                row.querySelector('[data-social-preview]').replaceChildren(preview);
                reindex(list);
            });
            frame.open();
        }
    });
})();
