(function () {
    'use strict';

    var autoApplied = false;
    var adaptiveStylesRunning = false;
    var adaptiveStylesScheduled = false;
    var __ = window.wp && window.wp.i18n ? window.wp.i18n.__ : function (text) {
        return text;
    };

    function parsePayload(button) {
        var wrap = button.closest('.ddsw-wrap');
        var node = wrap ? wrap.querySelector('.ddsw-payload') : null;

        if (!node) {
            return null;
        }

        try {
            return JSON.parse(node.textContent || '{}');
        } catch (error) {
            if (window.DDSWClipboard) {
                window.DDSWClipboard.debugLog((window.DDSmartWhatsApp || {}).debugInvalidPayload || '', error);
            }
            return null;
        }
    }

    function validColor(value) {
        value = String(value || '').trim();

        return value && window.CSS && window.CSS.supports && window.CSS.supports('color', value);
    }

    function rootValue(rootStyles, names, fallback) {
        var value = '';

        names.some(function (name) {
            value = rootStyles.getPropertyValue(name).trim();
            return validColor(value);
        });

        return validColor(value) ? value : fallback;
    }

    function visibleElement(element) {
        var style;
        var rect;

        if (!element || element.closest('.ddsw-wrap, .ddsw-button, .ddsw-floating-hub, #wpadminbar, .wp-admin, .components-popover, .media-modal')) {
            return false;
        }

        style = window.getComputedStyle(element);
        rect = element.getBoundingClientRect();

        return style.display !== 'none' &&
            style.visibility !== 'hidden' &&
            Number(style.opacity || 1) > 0 &&
            rect.width > 0 &&
            rect.height > 0;
    }

    function contentScopes() {
        var selectors = [
            'main',
            '[role="main"]',
            '.site-main',
            '#main',
            '#content',
            '.entry-content',
            '.elementor-location-single',
            '.elementor-location-archive',
            '.elementor'
        ];
        var scopes = selectors.map(function (selector) {
            return document.querySelector(selector);
        }).filter(Boolean);

        scopes.push(document);

        return scopes;
    }

    function findThemeReferenceButton() {
        var selectors = [
            '.elementor-button',
            '.wp-element-button',
            '.wp-block-button__link',
            'a.button',
            'button[type="submit"]',
            'input[type="submit"]',
            'button',
            '.button'
        ];
        var scopes = contentScopes();
        var found = null;

        scopes.some(function (scope) {
            return selectors.some(function (selector) {
                var buttons = Array.prototype.slice.call(scope.querySelectorAll(selector));

                return buttons.some(function (button) {
                    if (!visibleElement(button)) {
                        return false;
                    }

                    found = button;
                    return true;
                });
            });
        });

        return found;
    }

    function alphaFromColor(value) {
        var match = String(value || '').match(/rgba?\(([^)]+)\)/i);
        var parts;

        if (!match) {
            return 1;
        }

        parts = match[1].split(',').map(function (part) {
            return Number(part.trim());
        });

        return parts.length > 3 ? parts[3] : 1;
    }

    function usableBackground(value) {
        return validColor(value) && value !== 'transparent' && alphaFromColor(value) > 0;
    }

    function clampRgb(value) {
        value = Number(value);

        if (!Number.isFinite(value)) {
            return null;
        }

        return Math.max(0, Math.min(255, Math.round(value)));
    }

    function rgbParts(value) {
        var color = String(value || '').trim();
        var match;
        var hex;
        var parts;

        match = color.match(/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i);
        if (match) {
            hex = match[1];

            if (hex.length === 3 || hex.length === 4) {
                return [
                    parseInt(hex.charAt(0) + hex.charAt(0), 16),
                    parseInt(hex.charAt(1) + hex.charAt(1), 16),
                    parseInt(hex.charAt(2) + hex.charAt(2), 16)
                ];
            }

            return [
                parseInt(hex.slice(0, 2), 16),
                parseInt(hex.slice(2, 4), 16),
                parseInt(hex.slice(4, 6), 16)
            ];
        }

        match = color.match(/^rgba?\(([^)]+)\)$/i);

        if (!match) {
            return null;
        }

        parts = match[1].indexOf(',') !== -1
            ? match[1].split(',').slice(0, 3)
            : match[1].replace(/\/.*$/, '').trim().split(/\s+/).slice(0, 3);

        if (parts.length < 3 || parts.some(function (part) {
            return String(part).indexOf('%') !== -1;
        })) {
            return null;
        }

        parts = parts.map(function (part) {
            return clampRgb(part);
        });

        return parts.some(function (part) {
            return part === null;
        }) ? null : parts;
    }

    function hoverColor(value) {
        var rgb = rgbParts(value);
        var luminance;
        var target;
        var amount = 0.14;

        if (!rgb) {
            return value;
        }

        luminance = (0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2]) / 255;
        target = luminance > 0.55 ? 0 : 255;

        return String.fromCharCode(114, 103, 98, 40) + rgb.map(function (channel) {
            return Math.round(channel + ((target - channel) * amount));
        }).join(', ') + ')';
    }

    function transitionDuration(style) {
        return String(style.transitionDuration || '').split(',')[0].trim() || '200ms';
    }

    function usableLength(value) {
        value = String(value || '').trim();

        return value && !/^0(?:\.0+)?(?:px|em|rem|%|vh|vw)?$/i.test(value);
    }

    function horizontalPadding(style) {
        return style.paddingRight && style.paddingRight !== '0px' ? style.paddingRight : style.paddingLeft;
    }

    function verticalPadding(style) {
        return style.paddingTop && style.paddingTop !== '0px' ? style.paddingTop : style.paddingBottom;
    }

    function referenceStyles(button) {
        var style = window.getComputedStyle(button);
        var background = usableBackground(style.backgroundColor) ? style.backgroundColor : '';
        var borderColor = validColor(style.borderColor) ? style.borderColor : background;
        var paddingY = verticalPadding(style);
        var paddingX = horizontalPadding(style);

        if (!background) {
            return null;
        }

        if (!usableLength(paddingY) || !usableLength(paddingX)) {
            return null;
        }

        return {
            source: 'button',
            background: background,
            color: validColor(style.color) ? style.color : '#ffffff',
            hoverBackground: hoverColor(background),
            hoverColor: validColor(style.color) ? style.color : '#ffffff',
            borderColor: usableBackground(borderColor) ? borderColor : background,
            borderWidth: style.borderWidth || '1px',
            borderStyle: style.borderStyle && style.borderStyle !== 'none' ? style.borderStyle : 'solid',
            radius: style.borderRadius || '8px',
            fontFamily: style.fontFamily || 'inherit',
            fontSize: style.fontSize || '16px',
            fontWeight: style.fontWeight || '700',
            lineHeight: style.lineHeight || '1.25',
            letterSpacing: style.letterSpacing || 'normal',
            textTransform: style.textTransform || 'none',
            paddingY: paddingY,
            paddingX: paddingX,
            shadow: style.boxShadow && style.boxShadow !== 'none' ? style.boxShadow : 'none',
            hoverShadow: style.boxShadow && style.boxShadow !== 'none' ? style.boxShadow : 'none',
            transition: transitionDuration(style)
        };
    }

    function fallbackStyles() {
        var rootStyles = window.getComputedStyle(document.documentElement);
        var background = rootValue(rootStyles, [
            '--e-global-color-accent',
            '--e-global-color-primary',
            '--wp--preset--color--primary',
            '--ast-global-color-0',
            '--global-palette1',
            '--accent'
        ], '#25d366');
        var hover = rootValue(rootStyles, [
            '--e-global-color-secondary',
            '--wp--preset--color--secondary',
            '--ast-global-color-1',
            '--global-palette2'
        ], hoverColor(background));

        return {
            source: background === '#25d366' ? 'fallback' : 'root',
            background: background,
            color: '#ffffff',
            hoverBackground: hover,
            hoverColor: '#ffffff',
            borderColor: background,
            borderWidth: '1px',
            borderStyle: 'solid',
            radius: rootStyles.getPropertyValue('--wp--custom--border-radius').trim() || '8px',
            fontFamily: window.getComputedStyle(document.body).fontFamily || 'inherit',
            fontSize: '16px',
            fontWeight: '700',
            lineHeight: '1.25',
            letterSpacing: 'normal',
            textTransform: 'none',
            paddingY: '14px',
            paddingX: '28px',
            shadow: '0 10px 24px rgba(37, 211, 102, 0.24)',
            hoverShadow: '0 14px 30px rgba(18, 140, 126, 0.28)',
            transition: '200ms'
        };
    }

    function applyStyleTokens(wrap, styles) {
        var map = {
            '--ddsw-background': styles.background,
            '--ddsw-color': styles.color,
            '--ddsw-hover-background': styles.hoverBackground,
            '--ddsw-hover-color': styles.hoverColor,
            '--ddsw-border-color': styles.borderColor,
            '--ddsw-hover-border-color': styles.hoverBackground,
            '--ddsw-border-width': styles.borderWidth,
            '--ddsw-border-style': styles.borderStyle,
            '--ddsw-radius': styles.radius,
            '--ddsw-font-family': styles.fontFamily,
            '--ddsw-font-size': styles.fontSize,
            '--ddsw-font-weight': styles.fontWeight,
            '--ddsw-line-height': styles.lineHeight,
            '--ddsw-letter-spacing': styles.letterSpacing,
            '--ddsw-text-transform': styles.textTransform,
            '--ddsw-padding-y': styles.paddingY,
            '--ddsw-padding-x': styles.paddingX,
            '--ddsw-shadow': styles.shadow,
            '--ddsw-hover-shadow': styles.hoverShadow,
            '--ddsw-transition': styles.transition
        };

        Object.keys(map).forEach(function (property) {
            if (map[property]) {
                wrap.style.setProperty(property, map[property]);
            }
        });

        wrap.setAttribute('data-ddsw-auto-source', styles.source);
    }

    function applyAdaptiveThemeStyles() {
        var reference;
        var styles;
        var wraps;

        if (!document.documentElement || !window.getComputedStyle) {
            return;
        }

        if (adaptiveStylesRunning) {
            return;
        }

        wraps = document.querySelectorAll('.ddsw-wrap[data-ddsw-style="auto"]');

        if (!wraps.length) {
            return;
        }

        adaptiveStylesRunning = true;

        try {
            reference = findThemeReferenceButton();
            styles = reference ? referenceStyles(reference) : null;
            styles = styles || fallbackStyles();

            wraps.forEach(function (wrap) {
                applyStyleTokens(wrap, styles);
            });

            autoApplied = true;
        } finally {
            adaptiveStylesRunning = false;
        }
    }

    function scheduleAdaptiveThemeStyles() {
        if (adaptiveStylesScheduled) {
            return;
        }

        adaptiveStylesScheduled = true;

        if (window.requestAnimationFrame) {
            window.requestAnimationFrame(function () {
                adaptiveStylesScheduled = false;
                applyAdaptiveThemeStyles();
            });
            return;
        }

        window.setTimeout(function () {
            adaptiveStylesScheduled = false;
            applyAdaptiveThemeStyles();
        }, 16);
    }

    function browserName() {
        var ua = navigator.userAgent || '';

        if (ua.indexOf('Edg/') !== -1) {
            return 'Edge';
        }

        if (ua.indexOf('Chrome/') !== -1 && ua.indexOf('Chromium') === -1) {
            return 'Chrome';
        }

        if (ua.indexOf('Safari/') !== -1 && ua.indexOf('Chrome/') === -1) {
            return 'Safari';
        }

        if (ua.indexOf('Firefox/') !== -1) {
            return 'Firefox';
        }

        return __('Browser', 'dd-smart-whatsapp');
    }

    function placeholderDevice() {
        var ua = navigator.userAgent || '';

        if (/ipad|tablet|playbook|silk/i.test(ua)) {
            return __('tablet', 'dd-smart-whatsapp');
        }

        if (/mobi|android|iphone|ipod/i.test(ua)) {
            return __('mobile', 'dd-smart-whatsapp');
        }

        return __('desktop', 'dd-smart-whatsapp');
    }

    function utmValue(name) {
        try {
            return new URLSearchParams(window.location.search).get(name) || '';
        } catch (error) {
            return '';
        }
    }

    function safeReferrer() {
        try {
            return document.referrer ? new URL(document.referrer).href : '';
        } catch (error) {
            return '';
        }
    }

    function resolveBrowserPlaceholders(message) {
        var values = {
            browser: browserName(),
            device: placeholderDevice(),
            language: navigator.language || '',
            referrer: safeReferrer(),
            utm_source: utmValue('utm_source'),
            utm_medium: utmValue('utm_medium'),
            utm_campaign: utmValue('utm_campaign'),
            utm_content: utmValue('utm_content'),
            utm_term: utmValue('utm_term'),
            page_url: window.location.href,
            page_title: document.title || ''
        };

        Object.keys(values).forEach(function (key) {
            message = String(message).split('{' + key + '}').join(values[key]);
        });

        return message;
    }

    function preparePayload(payload) {
        payload.message = resolveBrowserPlaceholders(payload.message || '');

        if (payload.mode !== 'smart' && payload.mode !== 'universal_smart' && payload.baseUrl) {
            payload.url = payload.baseUrl + '?text=' + encodeURIComponent(payload.message);
        }

        return payload;
    }

    function logDebug(payload) {
        var config = window.DDSmartWhatsApp || {};
        var debug = payload && payload.modal ? payload.modal.debug : null;
        var labels = config.debugLabels || {};
        var modalFields = {};
        var modalPayload = {};
        var rows = {};

        if (!config.debug || !debug || !window.console) {
            return;
        }

        ['title', 'description', 'instruction', 'button', 'close', 'success', 'failed'].forEach(function (field) {
            modalFields[field] = debug.resolvedBy && debug.resolvedBy[field] ? debug.resolvedBy[field] : '';
            modalPayload[field] = payload.modal && payload.modal[field] ? payload.modal[field] : '';
        });

        rows[labels.resolvedLocale || 'resolved_locale'] = debug.resolvedLocale || config.resolvedLocale || config.locale || '';
        rows[labels.languageSource || 'language_source'] = debug.languageSource || config.languageSource || '';
        rows[labels.templateLoaded || 'template_loaded'] = debug.templateLoaded || debug.template || '';
        rows[labels.gettextLocale || 'gettext_locale'] = debug.gettextLocale || config.gettextLocale || '';
        rows[labels.payloadLanguage || 'payload_language'] = debug.payloadLanguage || config.payloadLanguage || '';
        rows[labels.htmlLang || 'html_lang'] = debug.htmlLang || '';
        rows[labels.documentLang || 'document_lang'] = document.documentElement ? (document.documentElement.getAttribute('lang') || '') : '';
        rows[labels.locale || 'locale'] = debug.locale;
        rows[labels.template || 'template'] = debug.template;
        rows[labels.modalSource || 'modalSource'] = debug.modalSource;
        rows[labels.titleField || 'title'] = modalFields.title;
        rows[labels.descriptionField || 'description'] = modalFields.description;
        rows[labels.instructionField || 'instruction'] = modalFields.instruction;
        rows[labels.buttonField || 'button'] = modalFields.button;
        rows[labels.modalFields || 'modalFields'] = modalFields;
        rows[labels.modalPayload || 'modalPayload'] = modalPayload;
        rows[labels.resolvedBy || 'resolvedBy'] = debug.resolvedBy;
        rows[labels.customOverride || 'customOverride'] = debug.customOverride;
        rows[labels.translationLoaded || 'translationLoaded'] = debug.translationLoaded;
        rows[labels.moLoaded || 'moLoaded'] = debug.moLoaded;
        rows[labels.poLoaded || 'poLoaded'] = debug.poLoaded;
        window.console.info(config.debugTitle || 'DDSW', rows);
    }

    function setFeedback(button, active, payload) {
        var label = button.querySelector('.ddsw-label, .ddsw-button__label');
        var settings = window.DDSmartWhatsApp || {};
        var feedback;

        if (!label) {
            return;
        }

        if (active) {
            button.setAttribute('data-ddsw-original-label', label.textContent);
            feedback = (payload && payload.modal && payload.modal.copyFeedback) ? payload.modal.copyFeedback : (settings.copyFeedback || label.textContent);
            label.replaceChildren(document.createTextNode(feedback));
            button.classList.add('ddsw-button--loading');
            return;
        }

        var original = button.getAttribute('data-ddsw-original-label');
        if (original) {
            label.replaceChildren(document.createTextNode(original));
        }
        button.classList.remove('ddsw-button--loading');
    }

    function handleTraditional(button, payload) {
        setFeedback(button, true, payload);

        window.DDSWClipboard.copyText(payload.message || '').then(function (copied) {
            window.DDSWTracking.record(payload, copied ? 'dd_smart_whatsapp_copy_success' : 'dd_smart_whatsapp_copy_error', copied ? 'success' : 'error');
            payload.baseUrl = payload.url;
            window.DDSWModal.openWhatsApp(payload);
        }).catch(function (error) {
            window.DDSWClipboard.debugLog((window.DDSmartWhatsApp || {}).debugTraditionalCopyFailed || '', error);
            window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_error', 'error');
            payload.baseUrl = payload.url;
            window.DDSWModal.openWhatsApp(payload);
        }).finally(function () {
            window.setTimeout(function () {
                setFeedback(button, false, payload);
            }, 900);
        });
    }

    function handleSmart(payload) {
        window.DDSWClipboard.copyText(payload.message || '').then(function (copied) {
            if (copied) {
                window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_success', 'success');
                window.DDSWModal.show(payload, true);
                return;
            }

            window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_error', 'error');
            window.DDSWModal.show(payload, false);
        }).catch(function (error) {
            window.DDSWClipboard.debugLog((window.DDSmartWhatsApp || {}).debugSmartCopyFailed || '', error);
            window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_error', 'error');
            window.DDSWModal.show(payload, false);
        });
    }

    function completeUniversalCopy(payload) {
        window.DDSWClipboard.copyText(payload.message || '').then(function (copied) {
            if (copied) {
                window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_success', 'success');
                window.DDSWTracking.record(payload, 'smart_copy_platform', 'success');
                window.DDSWModal.show(payload, true);
                return;
            }

            window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_error', 'error');
            window.DDSWTracking.record(payload, 'smart_copy_platform', 'error');
            window.DDSWModal.show(payload, false);
        }).catch(function (error) {
            window.DDSWClipboard.debugLog((window.DDSmartWhatsApp || {}).debugUniversalCopyFailed || '', error);
            window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_error', 'error');
            window.DDSWTracking.record(payload, 'smart_copy_platform', 'error');
            window.DDSWModal.show(payload, false);
        });
    }

    function handleUniversalSmartCopy(payload) {
        payload = preparePayload(payload);
        logDebug(payload);

        if (payload.messageMode === 'ask') {
            window.DDSWModal.confirm(payload, function () {
                completeUniversalCopy(payload);
            });
            return;
        }

        completeUniversalCopy(payload);
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-ddsw-button]');
        var payload;

        if (!button) {
            return;
        }

        if (!autoApplied) {
            applyAdaptiveThemeStyles();
        }

        payload = parsePayload(button);
        if (!payload) {
            return;
        }

        event.preventDefault();
        payload = preparePayload(payload);
        logDebug(payload);

        if (payload.mode === 'smart') {
            handleSmart(payload);
            return;
        }

        handleTraditional(button, payload);
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyAdaptiveThemeStyles);
    } else {
        applyAdaptiveThemeStyles();
    }

    if ('MutationObserver' in window) {
        new MutationObserver(function () {
            scheduleAdaptiveThemeStyles();
        }).observe(document.documentElement, { childList: true, subtree: true });
    }

    window.DDSWFrontend = {
        handleUniversalSmartCopy: handleUniversalSmartCopy,
        preparePayload: preparePayload
    };
})();
