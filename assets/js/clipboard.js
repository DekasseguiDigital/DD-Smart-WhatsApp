(function () {
    'use strict';

    function settings() {
        return window.DDSmartWhatsApp || {};
    }

    function debugLog(message, error) {
        if (settings().debug && window.console && typeof window.console.warn === 'function') {
            window.console.warn(message, error || '');
        }
    }

    function focusElement(node) {
        if (!node || typeof node.focus !== 'function') {
            return;
        }

        try {
            node.focus({ preventScroll: true });
        } catch (error) {
            node.focus();
        }
    }

    function copyText(text) {
        if (!text) {
            return Promise.resolve(false);
        }

        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function' && window.isSecureContext) {
            return navigator.clipboard.writeText(text).then(function () {
                return true;
            });
        }

        return new Promise(function (resolve) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', 'readonly');
            textarea.style.position = 'fixed';
            textarea.style.top = '0';
            textarea.style.left = '-9999px';
            textarea.style.width = '1px';
            textarea.style.height = '1px';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            focusElement(textarea);
            textarea.select();
            textarea.setSelectionRange(0, textarea.value.length);

            try {
                resolve(document.execCommand('copy'));
            } catch (error) {
                debugLog(settings().debugExecCommandFailed || '', error);
                resolve(false);
            } finally {
                document.body.removeChild(textarea);
            }
        });
    }

    window.DDSWClipboard = {
        copyText: copyText,
        focusElement: focusElement,
        debugLog: debugLog
    };
})();
