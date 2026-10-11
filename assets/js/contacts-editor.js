(function ($, acf) {
    'use strict';

    if (!acf) {
        return;
    }

    const names = [
        'company_address', 'company_phones', 'company_email', 'company_work_hours',
        'company_parking_note', 'company_name', 'company_inn', 'company_ogrn',
        'company_details_content'
    ];
    const starting = new Set();

    function isContactEditor(field) {
        return field && names.indexOf(field.$el.attr('data-name')) !== -1;
    }

    // Configure each ACF instance, including editors opened from the Text tab.
    acf.addFilter('wysiwyg_tinymce_settings', function (settings, id, field) {
        if (isContactEditor(field)) {
            settings.toolbar1 = 'formatselect,bold,italic,underline,bullist,numlist,link,unlink,undo,redo';
            settings.toolbar2 = '';
            settings.toolbar3 = '';
            settings.toolbar4 = '';
            settings.readonly = false;
        }
        return settings;
    });

    function openVisualEditor(field) {
        if (!isContactEditor(field) || !window.tinymce || !window.switchEditors) {
            return;
        }
        const input = field.$el.find('textarea.wp-editor-area');
        const id = input.attr('id');
        // ACF assigns its final ID on window load; never initialize the temporary ID.
        if (!id || id !== field.get('id') || starting.has(id)) {
            return;
        }
        let editor = window.tinymce.get(id);
        if (editor && !editor.initialized) {
            return;
        }
        // Moving a Gutenberg metabox reloads its iframe, but TinyMCE retains the
        // previous document. Its toolbar survives while the new body is read-only.
        const frame = field.$el.find('iframe')[0];
        const body = editor && editor.getBody();
        if (editor && (!frame || !body || body.ownerDocument !== frame.contentDocument || !body.isContentEditable)) {
            let value = input.val();
            if (field.$el.find('.acf-editor-wrap').hasClass('tmce-active') && body && body.isContentEditable) {
                value = editor.getContent();
            }
            editor.remove();
            // remove() can save the stale iframe. Restore the authoritative value.
            input.val(value);
            editor = null;
        }
        // ACF may initially register only Quicktags when the saved preference is Text.
        if (!editor) {
            starting.add(id);
            acf.tinymce.initialize(id, {
                tinymce: true,
                quicktags: false,
                toolbar: 'contacts',
                mode: 'text',
                field: field
            });
            const settings = window.tinyMCEPreInit.mceInit[id];
            if (!settings) {
                starting.delete(id);
                return;
            }
            const setup = settings.setup;
            settings.setup = function (editor) {
                if (setup) {
                    setup(editor);
                }
                editor.on('init', function () { starting.delete(id); });
            };
        }
        // Use WordPress' switcher so the textarea, iframe and tab state stay in sync.
        window.switchEditors.go(id, 'tmce');
    }

    function initialize(field) {
        window.setTimeout(function () { openVisualEditor(field); }, 0);
    }

    function initializeFields() {
        acf.getFields({ type: 'wysiwyg' }).forEach(initialize);
    }
    // Wysiwyg fields initialize on load, after ACF has renamed their editor IDs.
    if (document.readyState === 'complete') {
        initializeFields();
    } else {
        $(window).on('load', initializeFields);
    }

    acf.addAction('load_field/type=wysiwyg', initialize);
    acf.addAction('append_field/type=wysiwyg', initialize);
    acf.addAction('show_field/type=wysiwyg', initialize);

    let repairTimer;
    function scheduleRepair() {
        window.clearTimeout(repairTimer);
        repairTimer = window.setTimeout(function () {
            acf.getFields({ type: 'wysiwyg' }).forEach(function (field) {
                if (isContactEditor(field) && field.$el.is(':visible') && field.$el.find('.acf-editor-wrap').hasClass('tmce-active')) {
                    openVisualEditor(field);
                }
            });
        }, 50);
    }

    // Gutenberg and plugins may move the metabox again after the initial load.
    new MutationObserver(function (records) {
        if (records.some(function (record) {
            return Array.from(record.addedNodes).some(function (node) {
                return node.nodeType === 1 && (node.matches('.postbox, .acf-field-wysiwyg, iframe') || node.querySelector('.acf-field-wysiwyg, iframe'));
            });
        })) {
            scheduleRepair();
        }
    }).observe(document.body, { childList: true, subtree: true });
    document.addEventListener('load', function (event) {
        if (event.target.tagName === 'IFRAME' && event.target.closest('.acf-field-wysiwyg')) {
            scheduleRepair();
        }
    }, true);

    // Restore missing settings before WordPress handles the Visual tab click.
    document.addEventListener('click', function (event) {
        const tab = event.target.closest('.acf-field-wysiwyg .switch-tmce');
        if (!tab) {
            return;
        }
        const field = acf.getField($(tab).closest('.acf-field'));
        if (isContactEditor(field)) {
            event.preventDefault();
            event.stopPropagation();
            openVisualEditor(field);
        }
    }, true);
})(jQuery, window.acf);
