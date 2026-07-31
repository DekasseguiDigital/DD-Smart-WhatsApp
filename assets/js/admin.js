(function () {
    'use strict';

    var __ = window.wp && window.wp.i18n ? window.wp.i18n.__ : function (text) {
        return text;
    };

    function nextIndex(rows) {
        return String(Date.now() + rows.children.length);
    }

    function syncCustomStyleFields(scope) {
        var root = scope || document;

        root.querySelectorAll('[data-ddsw-appearance]').forEach(function (group) {
            var select = group.querySelector('[data-ddsw-style-select]');
            var custom = group.querySelector('[data-ddsw-custom-style]');

            if (!select || !custom) {
                return;
            }

            custom.hidden = select.value !== 'custom';
        });
    }

    function validColor(value) {
        value = String(value || '').trim();

        return value && window.CSS && window.CSS.supports && window.CSS.supports('color', value);
    }

    function adminReferenceButton(row) {
        var selectors = ['.button-primary', '.wp-element-button', '.button'];
        var found = null;

        selectors.some(function (selector) {
            return Array.prototype.slice.call(document.querySelectorAll(selector)).some(function (button) {
                var style;
                var rect;

                if (button.closest('.ddsw-wrap, .ddsw-button-row') === row || button.closest('.ddsw-wrap')) {
                    return false;
                }

                style = window.getComputedStyle(button);
                rect = button.getBoundingClientRect();

                if (style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity || 1) <= 0 || rect.width <= 0 || rect.height <= 0) {
                    return false;
                }

                found = button;
                return true;
            });
        });

        return found;
    }

    function hoverColor(value) {
        var probe = document.createElement('span');
        var computed;
        var match;
        var rgb;
        var luminance;
        var target;

        if (!validColor(value)) {
            return value;
        }

        probe.style.color = value;
        document.body.appendChild(probe);
        computed = window.getComputedStyle(probe).color;
        probe.remove();
        match = computed.match(/rgba?\(([^)]+)\)/i);

        if (!match) {
            return value;
        }

        rgb = match[1].split(',').slice(0, 3).map(function (part) {
            return Number(part.trim());
        });
        luminance = (0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2]) / 255;
        target = luminance > 0.55 ? 0 : 255;

        return String.fromCharCode(114, 103, 98, 40) + rgb.map(function (channel) {
            return Math.round(channel + ((target - channel) * 0.14));
        }).join(', ') + ')';
    }

    function adminAutoStyles(row) {
        var reference = adminReferenceButton(row);
        var rootStyles = window.getComputedStyle(document.documentElement);
        var bodyStyles = window.getComputedStyle(document.body);
        var style = reference ? window.getComputedStyle(reference) : null;
        var background = style && validColor(style.backgroundColor) && style.backgroundColor !== 'transparent'
            ? style.backgroundColor
            : (rootStyles.getPropertyValue('--wp-admin-theme-color').trim() || '#2271b1');
        var color = style && validColor(style.color) ? style.color : '#ffffff';

        return {
            source: reference ? 'button' : 'fallback',
            background: background,
            color: color,
            hoverBackground: hoverColor(background),
            hoverColor: color,
            borderColor: style && validColor(style.borderColor) ? style.borderColor : background,
            borderWidth: style ? style.borderWidth : '1px',
            borderStyle: style && style.borderStyle !== 'none' ? style.borderStyle : 'solid',
            radius: style ? style.borderRadius : '8px',
            fontFamily: style ? style.fontFamily : bodyStyles.fontFamily,
            fontSize: style ? style.fontSize : '16px',
            fontWeight: style ? style.fontWeight : '700',
            lineHeight: style ? style.lineHeight : '1.25',
            letterSpacing: style ? style.letterSpacing : 'normal',
            textTransform: style ? style.textTransform : 'none',
            paddingY: style ? (style.paddingTop || '14px') : '14px',
            paddingX: style ? (style.paddingRight || '28px') : '28px',
            shadow: style && style.boxShadow !== 'none' ? style.boxShadow : 'none',
            hoverShadow: style && style.boxShadow !== 'none' ? style.boxShadow : 'none',
            transition: style ? (String(style.transitionDuration || '').split(',')[0].trim() || '200ms') : '200ms'
        };
    }

    function setPreviewToken(wrap, property, value) {
        if (value) {
            wrap.style.setProperty(property, value);
        }
    }

    function clearAutoPreview(wrap) {
        [
            '--ddsw-background',
            '--ddsw-color',
            '--ddsw-hover-background',
            '--ddsw-hover-color',
            '--ddsw-border-color',
            '--ddsw-hover-border-color',
            '--ddsw-border-width',
            '--ddsw-border-style',
            '--ddsw-radius',
            '--ddsw-font-family',
            '--ddsw-font-size',
            '--ddsw-font-weight',
            '--ddsw-line-height',
            '--ddsw-letter-spacing',
            '--ddsw-text-transform',
            '--ddsw-padding-y',
            '--ddsw-padding-x',
            '--ddsw-shadow',
            '--ddsw-hover-shadow',
            '--ddsw-transition'
        ].forEach(function (property) {
            wrap.style.removeProperty(property);
        });
        wrap.removeAttribute('data-ddsw-auto-source');
    }

    function applyAutoPreview(row) {
        var wrap = row.querySelector('[data-ddsw-preview] .ddsw-wrap');
        var styles;

        if (!wrap) {
            return;
        }

        styles = adminAutoStyles(row);
        setPreviewToken(wrap, '--ddsw-background', styles.background);
        setPreviewToken(wrap, '--ddsw-color', styles.color);
        setPreviewToken(wrap, '--ddsw-hover-background', styles.hoverBackground);
        setPreviewToken(wrap, '--ddsw-hover-color', styles.hoverColor);
        setPreviewToken(wrap, '--ddsw-border-color', styles.borderColor);
        setPreviewToken(wrap, '--ddsw-hover-border-color', styles.hoverBackground);
        setPreviewToken(wrap, '--ddsw-border-width', styles.borderWidth);
        setPreviewToken(wrap, '--ddsw-border-style', styles.borderStyle);
        setPreviewToken(wrap, '--ddsw-radius', styles.radius);
        setPreviewToken(wrap, '--ddsw-font-family', styles.fontFamily);
        setPreviewToken(wrap, '--ddsw-font-size', styles.fontSize);
        setPreviewToken(wrap, '--ddsw-font-weight', styles.fontWeight);
        setPreviewToken(wrap, '--ddsw-line-height', styles.lineHeight);
        setPreviewToken(wrap, '--ddsw-letter-spacing', styles.letterSpacing);
        setPreviewToken(wrap, '--ddsw-text-transform', styles.textTransform);
        setPreviewToken(wrap, '--ddsw-padding-y', styles.paddingY);
        setPreviewToken(wrap, '--ddsw-padding-x', styles.paddingX);
        setPreviewToken(wrap, '--ddsw-shadow', styles.shadow);
        setPreviewToken(wrap, '--ddsw-hover-shadow', styles.hoverShadow);
        setPreviewToken(wrap, '--ddsw-transition', styles.transition);
        wrap.setAttribute('data-ddsw-auto-source', styles.source);
    }

    function templateDefaultsFor(row) {
        var i18n = window.DDSWAdminI18n || {};
        var localeSelect = row.querySelector('[data-ddsw-template-locale]');
        var keySelect = row.querySelector('[data-ddsw-template-key]');
        var locale = localeSelect && localeSelect.value ? localeSelect.value : i18n.currentLocale;
        var key = keySelect && keySelect.value ? keySelect.value : 'support';
        var library = i18n.templateLibrary || {};
        var templates = library[locale] || library.en_US || {};

        return templates[key] || templates.support || (i18n.templateDefaults || {})[locale] || (i18n.templateDefaults || {}).en_US || {};
    }

    function fieldKeysForGroup(group) {
        if (group === 'cta') {
            return ['label'];
        }

        if (group === 'message') {
            return ['message'];
        }

        return ['modal_title', 'modal_success', 'desktop_instruction', 'ios_instruction', 'android_instruction', 'open_label', 'close_label', 'retry_label', 'error_message'];
    }

    function defaultsFor(row, locale, key) {
        var i18n = window.DDSWAdminI18n || {};
        var library = i18n.templateLibrary || {};
        var templates = library[locale] || library.en_US || {};

        return templates[key] || templates.support || {};
    }

    function currentLocale(row) {
        var i18n = window.DDSWAdminI18n || {};
        var localeSelect = row.querySelector('[data-ddsw-template-locale]');

        return localeSelect && localeSelect.value ? localeSelect.value : i18n.currentLocale;
    }

    function currentTemplateKey(row) {
        var keySelect = row.querySelector('[data-ddsw-template-key]');

        return keySelect && keySelect.value ? keySelect.value : 'support';
    }

    function populateTemplateOptions(row, selectedKey) {
        var i18n = window.DDSWAdminI18n || {};
        var keySelect = row.querySelector('[data-ddsw-template-key]');
        var library = i18n.templateLibrary || {};
        var templates = library[currentLocale(row)] || library.en_US || {};
        var keys = Object.keys(templates);

        if (!keySelect || !keys.length) {
            return;
        }

        keySelect.replaceChildren();
        keys.forEach(function (key) {
            var option = document.createElement('option');
            option.value = key;
            option.textContent = templates[key].name || key;
            option.selected = key === selectedKey;
            keySelect.appendChild(option);
        });

        if (!keySelect.value) {
            keySelect.value = templates[selectedKey] ? selectedKey : 'support';
        }
    }

    function applyTemplateDefaults(row, options) {
        var defaults = templateDefaultsFor(row);
        var previous = defaultsFor(row, options.previousLocale || currentLocale(row), options.previousKey || currentTemplateKey(row));
        var groups = options.groups || ['cta', 'message', 'modal'];
        var force = Boolean(options.force);

        groups.forEach(function (group) {
            fieldKeysForGroup(group).forEach(function (key) {
                var field = row.querySelector('[data-ddsw-template-field="' + key + '"]');
                var defaultValue = defaults[key];
                var previousValue = previous[key];

                if (!field || typeof defaultValue === 'undefined') {
                    return;
                }

                if (!force && previousValue && field.value !== previousValue) {
                    return;
                }

                field.value = defaultValue;
                field.dispatchEvent(new Event('change', { bubbles: true }));
                field.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });
    }

    function restoreTemplateDefaults(row) {
        var i18n = window.DDSWAdminI18n || {};
        var confirmText = i18n.confirmRestore || __('Restore the default texts for the selected language?', 'dd-smart-whatsapp');

        if (confirmText && !window.confirm(confirmText)) {
            return;
        }

        applyTemplateDefaults(row, { force: true });
        updatePreview(row);
    }

    function restoreAllButtons() {
        var i18n = window.DDSWAdminI18n || {};
        var confirmText = i18n.confirmRestoreAll || __('Restore all buttons using their selected language and model?', 'dd-smart-whatsapp');

        if (confirmText && !window.confirm(confirmText)) {
            return;
        }

        document.querySelectorAll('.ddsw-button-row').forEach(function (row) {
            applyTemplateDefaults(row, { force: true });
            updatePreview(row);
        });
    }

    function templateChangeModal(row, previousLocale, previousKey) {
        var i18n = window.DDSWAdminI18n || {};
        var overlay = document.createElement('div');
        var dialog = document.createElement('div');
        var title = document.createElement('h2');
        var text = document.createElement('p');
        var form = document.createElement('div');
        var actions = document.createElement('div');
        var update = document.createElement('button');
        var cancel = document.createElement('button');
        var groups = [
            ['cta', i18n.updateCtaLabel || __('CTA', 'dd-smart-whatsapp')],
            ['message', i18n.updateMessageLabel || __('Message', 'dd-smart-whatsapp')],
            ['modal', i18n.updateModalLabel || __('Smart Copy modal', 'dd-smart-whatsapp')]
        ];

        overlay.className = 'ddsw-admin-modal';
        dialog.className = 'ddsw-admin-modal__dialog';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        dialog.setAttribute('tabindex', '-1');
        title.textContent = i18n.templateChangeTitle || __('The template language changed.', 'dd-smart-whatsapp');
        text.textContent = i18n.templateChangeDescription || __('Update fields that still use the previous default?', 'dd-smart-whatsapp');
        form.className = 'ddsw-admin-modal__options';
        title.id = 'ddsw-admin-modal-title';
        text.id = 'ddsw-admin-modal-description';
        dialog.setAttribute('aria-labelledby', title.id);
        dialog.setAttribute('aria-describedby', text.id);

        groups.forEach(function (group) {
            var label = document.createElement('label');
            var input = document.createElement('input');
            input.type = 'checkbox';
            input.value = group[0];
            input.checked = true;
            label.appendChild(input);
            label.appendChild(document.createTextNode(' ' + group[1]));
            form.appendChild(label);
        });

        update.type = 'button';
        update.className = 'button button-primary';
        update.textContent = i18n.updateButtonLabel || __('Update', 'dd-smart-whatsapp');
        cancel.type = 'button';
        cancel.className = 'button';
        cancel.textContent = i18n.cancelButtonLabel || __('Cancel', 'dd-smart-whatsapp');
        actions.className = 'ddsw-admin-modal__actions';
        actions.appendChild(update);
        actions.appendChild(cancel);
        dialog.appendChild(title);
        dialog.appendChild(text);
        dialog.appendChild(form);
        dialog.appendChild(actions);
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        dialog.focus();

        function close() {
            overlay.remove();
        }

        update.addEventListener('click', function () {
            var selected = Array.prototype.slice.call(form.querySelectorAll('input:checked')).map(function (input) {
                return input.value;
            });
            applyTemplateDefaults(row, {
                groups: selected,
                previousLocale: previousLocale,
                previousKey: previousKey,
                force: false
            });
            close();
            updatePreview(row);
        });
        cancel.addEventListener('click', close);
        overlay.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                close();
            }
        });
    }

    function replaceTemplateToken(node, token, replacement) {
        Array.prototype.slice.call(node.querySelectorAll('*')).forEach(function (element) {
            Array.prototype.slice.call(element.attributes).forEach(function (attribute) {
                if (attribute.value.indexOf(token) !== -1) {
                    element.setAttribute(attribute.name, attribute.value.split(token).join(replacement));
                }
            });
        });

        Array.prototype.slice.call(node.childNodes).forEach(function walk(child) {
            if (child.nodeType === window.Node.TEXT_NODE && child.nodeValue.indexOf(token) !== -1) {
                child.nodeValue = child.nodeValue.split(token).join(replacement);
                return;
            }

            Array.prototype.slice.call(child.childNodes || []).forEach(walk);
        });
    }

    function newTemplateRow(template, rows) {
        var fragment = template.content.cloneNode(true);
        var replacement = nextIndex(rows);

        replaceTemplateToken(fragment, '__INDEX__', replacement);

        return fragment.firstElementChild;
    }

    function addFloatingHub() {
        var rows = document.querySelector('[data-ddsw-floating-hubs]');
        var template = document.querySelector('[data-ddsw-floating-hub-template]');
        var fragment;
        var replacement;

        if (!rows || !template) {
            return;
        }

        fragment = template.content.cloneNode(true);
        replacement = nextIndex(rows);
        replaceTemplateToken(fragment, '__HUB_INDEX__', replacement);
        replaceTemplateToken(fragment, '__ACTION_INDEX__', '0');
        rows.appendChild(fragment);
    }

    function addFloatingAction(hub) {
        var rows = hub ? hub.querySelector('[data-ddsw-floating-actions]') : null;
        var template = document.querySelector('[data-ddsw-floating-action-template]');
        var hubIndex;
        var actionIndex;
        var fragment;
        var row;

        if (!hub || !rows || !template) {
            return;
        }

        hubIndex = (function () {
            var field = hub.querySelector('[name*="[floating_hubs]"]');
            var match = field ? field.name.match(/\[floating_hubs\]\[([^\]]+)\]/) : null;
            return match ? match[1] : nextIndex(rows);
        }());
        actionIndex = nextIndex(rows);
        fragment = template.content.cloneNode(true);
        replaceTemplateToken(fragment, '__HUB_INDEX__', hubIndex);
        replaceTemplateToken(fragment, '__ACTION_INDEX__', actionIndex);
        row = fragment.firstElementChild;

        if (!row) {
            return;
        }

        rows.appendChild(row);
        prepareFloatingActionRow(row, actionIndex);
        refreshFloatingOrder(rows);
    }

    function prepareFloatingActionRow(row, actionIndex) {
        var idField = row.querySelector('input[name*="[id]"]');
        var typeField = row.querySelector('select[name*="[type]"]');
        var orderField = row.querySelector('input[name*="[order]"]');
        var type = typeField && typeField.value ? typeField.value : 'custom';

        if (idField) {
            idField.value = type + '-' + actionIndex;
        }

        if (orderField) {
            orderField.value = String(row.parentElement ? row.parentElement.children.length : 1);
        }
    }

    function refreshFloatingOrder(container) {
        Array.prototype.slice.call(container.querySelectorAll('[data-ddsw-floating-action-row]')).forEach(function (row, index) {
            var order = row.querySelector('input[name*="[order]"]');
            if (order) {
                order.value = String(index + 1);
            }
        });
    }

    function updatePreview(row) {
        var labelField = row.querySelector('[data-ddsw-template-field="label"]');
        var messageField = row.querySelector('[data-ddsw-template-field="message"]');
        var label = row.querySelector('[data-ddsw-preview-label]');
        var message = row.querySelector('[data-ddsw-preview-message]');
        var button = row.querySelector('[data-ddsw-preview-button]');
        var style = row.querySelector('[data-ddsw-style-select]');
        var wrap = row.querySelector('[data-ddsw-preview] .ddsw-wrap');
        var styleValue = style ? (style.value || 'auto') : 'auto';

        if (label && labelField) {
            label.replaceChildren(document.createTextNode(labelField.value || labelField.placeholder || ''));
        }

        if (message && messageField) {
            message.replaceChildren(document.createTextNode((messageField.value || '').split(/\s+/).slice(0, 18).join(' ')));
        }

        if (button && style) {
            button.className = 'ddsw-button ddsw-style-' + styleValue;
        }

        if (wrap) {
            wrap.setAttribute('data-ddsw-style', styleValue);
            if (styleValue === 'auto') {
                applyAutoPreview(row);
            } else {
                clearAutoPreview(wrap);
            }
        }
    }

    function drawCharts() {
        document.querySelectorAll('[data-ddsw-chart]').forEach(function (canvas) {
            var rows = [];
            var ctx = canvas.getContext && canvas.getContext('2d');
            var width = canvas.width;
            var height = canvas.height;
            var max;

            if (!ctx) {
                return;
            }

            try {
                rows = JSON.parse(canvas.getAttribute('data-ddsw-chart') || '[]');
            } catch (error) {
                rows = [];
            }

            max = rows.reduce(function (current, row) {
                return Math.max(current, Number(row.total || 0));
            }, 1);

            if (window.Chart) {
                new window.Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: rows.map(function (row) {
                            return row.day || '';
                        }),
                        datasets: [{
                            label: __('Events', 'dd-smart-whatsapp'),
                            data: rows.map(function (row) {
                                return Number(row.total || 0);
                            }),
                            borderColor: '#2271b1',
                            backgroundColor: 'rgba(34, 113, 177, 0.14)',
                            fill: true,
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                return;
            }

            ctx.clearRect(0, 0, width, height);
            ctx.fillStyle = '#f0f6f3';
            ctx.fillRect(0, 0, width, height);
            ctx.strokeStyle = '#2271b1';
            ctx.lineWidth = 2;
            ctx.beginPath();
            rows.forEach(function (row, index) {
                var x = rows.length > 1 ? (index / (rows.length - 1)) * (width - 16) + 8 : 8;
                var y = height - 12 - ((Number(row.total || 0) / max) * (height - 24));
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            ctx.stroke();
        });
    }

    function updateShortcodeGenerator() {
        var output = document.querySelector('[data-ddsw-shortcode-output]');
        var button = document.querySelector('[data-ddsw-shortcode-button]');
        var mode = document.querySelector('[data-ddsw-shortcode-mode]');

        if (!output || !button || !mode) {
            return;
        }

        output.value = '[dd_smart_whatsapp id="' + button.value + '" mode="' + mode.value + '"]';
    }

    document.addEventListener('click', function (event) {
        var addButton = event.target.closest('[data-ddsw-add-row]');
        var removeButton;
        var restoreButton;

        if (addButton) {
            var rows = document.querySelector('[data-ddsw-rows]');
            var template = document.querySelector('[data-ddsw-template]');
            var row;

            if (!rows || !template) {
                return;
            }

            row = newTemplateRow(template, rows);
            if (row) {
                rows.appendChild(row);
            }
            syncCustomStyleFields(rows);
            return;
        }

        removeButton = event.target.closest('[data-ddsw-remove-row]');
        if (removeButton) {
            var row = removeButton.closest('.ddsw-button-row');
            if (row) {
                row.remove();
            }
        }

        restoreButton = event.target.closest('[data-ddsw-restore-defaults]');
        if (restoreButton) {
            var restoreRow = restoreButton.closest('.ddsw-button-row');
            if (restoreRow) {
                restoreTemplateDefaults(restoreRow);
            }
        }

        if (event.target.closest('[data-ddsw-restore-all]')) {
            restoreAllButtons();
        }

        if (event.target.closest('[data-ddsw-copy-shortcode]')) {
            var output = document.querySelector('[data-ddsw-shortcode-output]');
            if (output) {
                output.select();
                navigator.clipboard.writeText(output.value).catch(function () {
                    document.execCommand('copy');
                });
            }
        }

        if (event.target.closest('[data-ddsw-add-floating-hub]')) {
            event.preventDefault();
            addFloatingHub();
            return;
        }

        if (event.target.closest('[data-ddsw-remove-floating-hub]')) {
            event.preventDefault();
            var hub = event.target.closest('[data-ddsw-floating-hub-row]');
            if (hub) {
                hub.remove();
            }
            return;
        }

        if (event.target.closest('[data-ddsw-add-floating-action]')) {
            event.preventDefault();
            addFloatingAction(event.target.closest('[data-ddsw-floating-hub-row]'));
            return;
        }

        if (event.target.closest('[data-ddsw-remove-floating-action]')) {
            event.preventDefault();
            var actionRow = event.target.closest('[data-ddsw-floating-action-row]');
            var parent = actionRow ? actionRow.parentElement : null;
            if (actionRow) {
                actionRow.remove();
            }
            if (parent) {
                refreshFloatingOrder(parent);
            }
            return;
        }
    });

    document.addEventListener('dragstart', function (event) {
        var row = event.target.closest('[data-ddsw-floating-action-row]');
        if (row) {
            row.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
        }
    });

    document.addEventListener('dragover', function (event) {
        var container = event.target.closest('[data-ddsw-floating-actions]');
        var dragging = document.querySelector('[data-ddsw-floating-action-row].is-dragging');
        var target = event.target.closest('[data-ddsw-floating-action-row]');

        if (!container || !dragging || !target || dragging === target) {
            return;
        }

        event.preventDefault();
        if (target.getBoundingClientRect().top + (target.offsetHeight / 2) > event.clientY) {
            container.insertBefore(dragging, target);
        } else {
            container.insertBefore(dragging, target.nextSibling);
        }
    });

    document.addEventListener('dragend', function () {
        document.querySelectorAll('[data-ddsw-floating-action-row].is-dragging').forEach(function (row) {
            var parent = row.parentElement;
            row.classList.remove('is-dragging');
            if (parent) {
                refreshFloatingOrder(parent);
            }
        });
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches('[data-ddsw-style-select]')) {
            var styleRow = event.target.closest('.ddsw-button-row');
            syncCustomStyleFields(event.target.closest('[data-ddsw-appearance]') || document);
            if (styleRow) {
                updatePreview(styleRow);
            }
        }

        if (event.target.matches('[data-ddsw-template-locale], [data-ddsw-template-key]')) {
            var row = event.target.closest('.ddsw-button-row');
            var localeSelect = row ? row.querySelector('[data-ddsw-template-locale]') : null;
            var keySelect = row ? row.querySelector('[data-ddsw-template-key]') : null;
            var previousLocale = localeSelect ? (localeSelect.getAttribute('data-ddsw-previous-locale') || currentLocale(row)) : currentLocale(row);
            var previousKey = keySelect ? (keySelect.getAttribute('data-ddsw-previous-key') || 'support') : 'support';

            if (row) {
                if (event.target.matches('[data-ddsw-template-locale]')) {
                    populateTemplateOptions(row, previousKey);
                }
                applyTemplateDefaults(row, {
                    previousLocale: previousLocale,
                    previousKey: previousKey,
                    force: false
                });
                updatePreview(row);
                templateChangeModal(row, previousLocale, previousKey);
                if (localeSelect) {
                    localeSelect.setAttribute('data-ddsw-previous-locale', currentLocale(row));
                }
                if (keySelect) {
                    keySelect.setAttribute('data-ddsw-previous-key', currentTemplateKey(row));
                }
            }
        }

        if (event.target.matches('[data-ddsw-shortcode-button], [data-ddsw-shortcode-mode]')) {
            updateShortcodeGenerator();
        }
    });

    document.addEventListener('input', function (event) {
        var row = event.target.closest('.ddsw-button-row');
        if (row) {
            updatePreview(row);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            syncCustomStyleFields(document);
            document.querySelectorAll('.ddsw-button-row').forEach(updatePreview);
            drawCharts();
            updateShortcodeGenerator();
        });
    } else {
        syncCustomStyleFields(document);
        document.querySelectorAll('.ddsw-button-row').forEach(updatePreview);
        drawCharts();
        updateShortcodeGenerator();
    }
})();
