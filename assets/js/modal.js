(function () {
    'use strict';

    var activeModal = null;
    var lastFocus = null;

    function settings() {
        return window.DDSmartWhatsApp || {};
    }

    function focusableNodes(modal) {
        return Array.prototype.slice.call(modal.querySelectorAll('button, [href], textarea, input, select, [tabindex]:not([tabindex="-1"])'))
            .filter(function (node) {
                return !node.disabled && node.offsetParent !== null;
            });
    }

    function closeModal() {
        if (!activeModal) {
            return;
        }

        document.removeEventListener('keydown', onModalKeydown);
        activeModal.remove();
        activeModal = null;

        if (lastFocus && window.DDSWClipboard) {
            window.DDSWClipboard.focusElement(lastFocus);
        }
    }

    function onModalKeydown(event) {
        if (!activeModal) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        var nodes = focusableNodes(activeModal);
        if (!nodes.length) {
            return;
        }

        var first = nodes[0];
        var last = nodes[nodes.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function openWhatsApp(payload) {
        openTarget(payload);
    }

    function openTarget(payload, pendingWindow) {
        var destination = payload.baseUrl || payload.url;

        if (window.DDSWTracking) {
            window.DDSWTracking.record(payload, 'dd_smart_whatsapp_open', '');
        }

        if (pendingWindow && !pendingWindow.closed) {
            pendingWindow.location.href = destination;
            return;
        }

        if (payload.target === '_self') {
            window.location.href = destination;
            return;
        }

        var opened = window.open(destination, '_blank', 'noopener');
        if (!opened) {
            window.location.href = destination;
        }
    }

    function setText(node, value) {
        node.replaceChildren(document.createTextNode(value || ''));
    }

    function createElement(tagName, className, attributes) {
        var node = document.createElement(tagName);

        if (className) {
            node.className = className;
        }

        Object.keys(attributes || {}).forEach(function (name) {
            node.setAttribute(name, attributes[name]);
        });

        return node;
    }

    function createSvgIcon(pathData, className) {
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('focusable', 'false');
        if (className) {
            svg.setAttribute('class', className);
        }
        path.setAttribute('fill', 'currentColor');
        path.setAttribute('d', pathData);
        svg.appendChild(path);

        return svg;
    }

    function showToast(payload) {
        var modalConfig = payload.modal || {};
        var toast = createElement('div', 'ddsw-toast', { role: 'status', 'aria-live': 'polite' });
        var icon = createElement('span', 'ddsw-toast__icon', { 'aria-hidden': 'true' });
        var body = createElement('span', 'ddsw-toast__body');
        var title = createElement('strong', 'ddsw-toast__title');
        var message = createElement('span', 'ddsw-toast__message');

        icon.appendChild(document.createTextNode('✓'));
        setText(title, modalConfig.toastTitle || modalConfig.success || settings().modalSuccess || '');
        setText(message, modalConfig.toastMessage || modalConfig.instruction || settings().modalInstruction || '');
        body.appendChild(title);
        body.appendChild(message);
        toast.appendChild(icon);
        toast.appendChild(body);
        document.body.appendChild(toast);

        window.setTimeout(function () {
            toast.classList.add('is-visible');
        }, 10);

        window.setTimeout(function () {
            toast.classList.remove('is-visible');
            window.setTimeout(function () {
                toast.remove();
            }, 220);
        }, Number(modalConfig.toastDelay || 3600));
    }

    function confirmCopy(payload, onConfirm) {
        closeModal();

        var modalConfig = payload.modal || {};
        var overlay = document.createElement('div');
        var titleId = 'ddsw-modal-title-' + Date.now();
        var descId = 'ddsw-modal-desc-' + Date.now();
        var backdrop = createElement('div', 'ddsw-modal__backdrop', { 'data-ddsw-close': '' });
        var dialog = createElement('div', 'ddsw-modal__dialog', { role: 'document' });
        var closeButton = createElement('button', 'ddsw-modal__close', { type: 'button', 'data-ddsw-close': '' });
        var icon = createElement('div', 'ddsw-modal__icon', { 'aria-hidden': 'true' });
        var title = createElement('h2', 'ddsw-modal__title', { id: titleId });
        var content = createElement('div', 'ddsw-modal__content');
        var status = createElement('p', 'ddsw-modal__status', { id: descId });
        var instruction = createElement('p', 'ddsw-modal__instruction');
        var actions = createElement('div', 'ddsw-modal__actions');
        var confirmButton = createElement('button', 'ddsw-modal__open', { type: 'button' });

        overlay.className = 'ddsw-modal ddsw-modal--' + (modalConfig.style || 'clean');
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', titleId);
        overlay.setAttribute('aria-describedby', descId);
        overlay.setAttribute('tabindex', '-1');

        closeButton.setAttribute('aria-label', modalConfig.close || settings().modalClose || '');
        closeButton.appendChild(createSvgIcon('M18.3 5.71 16.89 4.3 12 9.17 7.11 4.3 5.7 5.71 10.59 10.6 5.7 15.49l1.41 1.41L12 12.01l4.89 4.89 1.41-1.41-4.89-4.89 4.89-4.89Z'));
        icon.appendChild(createSvgIcon('M8 3h8a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-1H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3Zm0 3h8V5H8v1Zm0 2v11h11V8H8ZM5 5v11h1V8a2 2 0 0 1 2-2V5H5Zm5 6h7v2h-7v-2Zm0 4h5v2h-5v-2Z'));

        setText(title, modalConfig.confirmTitle || modalConfig.title || settings().modalTitle || '');
        setText(status, modalConfig.confirmDescription || modalConfig.description || settings().modalDescription || '');
        setText(instruction, modalConfig.confirmInstruction || modalConfig.instruction || settings().modalInstruction || '');
        setText(confirmButton, modalConfig.button || settings().modalButton || '');

        confirmButton.addEventListener('click', function () {
            closeModal();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });

        overlay.addEventListener('click', function (event) {
            if (event.target.closest('[data-ddsw-close]')) {
                closeModal();
            }
        });

        content.appendChild(status);
        content.appendChild(instruction);
        actions.appendChild(confirmButton);
        dialog.appendChild(closeButton);
        dialog.appendChild(icon);
        dialog.appendChild(title);
        dialog.appendChild(content);
        dialog.appendChild(actions);
        overlay.appendChild(backdrop);
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        lastFocus = document.activeElement;
        activeModal = overlay;
        document.addEventListener('keydown', onModalKeydown);
        window.DDSWClipboard.focusElement(confirmButton);
    }

    function showModal(payload, success) {
        closeModal();

        var modalConfig = payload.modal || {};
        var hideKey = 'ddsw_hide_smart_modal_' + (payload.id || 'default');
        var overlay = document.createElement('div');
        var titleId = 'ddsw-modal-title-' + Date.now();
        var descId = 'ddsw-modal-desc-' + Date.now();
        var style = modalConfig.style || 'clean';
        var backdrop = createElement('div', 'ddsw-modal__backdrop', { 'data-ddsw-close': '' });
        var dialog = createElement('div', 'ddsw-modal__dialog', { role: 'document' });
        var closeButton = createElement('button', 'ddsw-modal__close', { type: 'button', 'data-ddsw-close': '' });
        var icon = createElement('div', 'ddsw-modal__icon', { 'aria-hidden': 'true' });
        var title = createElement('h2', 'ddsw-modal__title', { id: titleId });
        var content = createElement('div', 'ddsw-modal__content');
        var status = createElement('p', 'ddsw-modal__status', { id: descId });
        var instruction = createElement('p', 'ddsw-modal__instruction');
        var actions = createElement('div', 'ddsw-modal__actions');
        var openButton = createElement('button', 'ddsw-modal__open', { type: 'button' });
        var textarea;
        var retry;
        var hideAgain;

        try {
            if (success && modalConfig.hideAgain && window.localStorage && window.localStorage.getItem(hideKey) === '1') {
                if (modalConfig.autoOpen) {
                    window.setTimeout(function () {
                        openWhatsApp(payload);
                    }, Number(modalConfig.autoOpenDelay || 1000));
                }
                return;
            }
        } catch (error) {
            if (window.DDSWClipboard) {
                window.DDSWClipboard.debugLog('', error);
            }
        }

        overlay.className = 'ddsw-modal ddsw-modal--' + style;
        if (payload.style) {
            overlay.setAttribute('style', payload.style);
        }
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', titleId);
        overlay.setAttribute('aria-describedby', descId);
        overlay.setAttribute('tabindex', '-1');

        closeButton.setAttribute('aria-label', modalConfig.close || settings().modalClose || '');
        closeButton.appendChild(createSvgIcon('M18.3 5.71 16.89 4.3 12 9.17 7.11 4.3 5.7 5.71 10.59 10.6 5.7 15.49l1.41 1.41L12 12.01l4.89 4.89 1.41-1.41-4.89-4.89 4.89-4.89Z'));
        icon.appendChild(createSvgIcon('M8 3h8a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-1H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3Zm0 3h8V5H8v1Zm0 2v11h11V8H8ZM5 5v11h1V8a2 2 0 0 1 2-2V5H5Zm5 6h7v2h-7v-2Zm0 4h5v2h-5v-2Z'));

        setText(title, modalConfig.title || settings().modalTitle || '');
        setText(status, success ? (modalConfig.description || settings().modalDescription || '') : (modalConfig.failed || settings().modalFailed || ''));
        setText(instruction, success ? (modalConfig.instruction || settings().modalInstruction || '') : '');
        setText(openButton, modalConfig.button || settings().modalButton || '');

        content.appendChild(status);
        content.appendChild(instruction);

        if (!success) {
            var manual = createElement('div', 'ddsw-modal__manual');
            textarea = createElement('textarea', '', { readonly: 'readonly', rows: '7' });
            retry = createElement('button', 'ddsw-modal__retry', { type: 'button' });
            textarea.value = payload.message || '';
            setText(retry, modalConfig.retryLabel || settings().retryCopyLabel || '');
            retry.addEventListener('click', function () {
                window.DDSWClipboard.copyText(payload.message || '').then(function (copied) {
                    if (copied) {
                        window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_success', 'success');
                        showModal(payload, true);
                    } else {
                        window.DDSWTracking.record(payload, 'dd_smart_whatsapp_copy_error', 'error');
                        textarea.focus();
                        textarea.select();
                    }
                });
            });
            manual.appendChild(textarea);
            manual.appendChild(retry);
            content.appendChild(manual);
        }

        if (success && modalConfig.hideAgain) {
            var hideLabel = createElement('label', 'ddsw-modal__hide-again');
            hideAgain = createElement('input', '', { type: 'checkbox' });
            hideLabel.appendChild(hideAgain);
            hideLabel.appendChild(document.createTextNode(' ' + (modalConfig.hideAgainLabel || settings().hideAgainLabel || '')));
            hideAgain.addEventListener('change', function () {
                try {
                    if (window.localStorage) {
                        window.localStorage.setItem(hideKey, hideAgain.checked ? '1' : '0');
                    }
                } catch (error) {
                    if (window.DDSWClipboard) {
                        window.DDSWClipboard.debugLog('', error);
                    }
                }
            });
            content.appendChild(hideLabel);
        }

        openButton.addEventListener('click', function () {
            openWhatsApp(payload);
        });

        overlay.addEventListener('click', function (event) {
            if (event.target.closest('[data-ddsw-close]')) {
                closeModal();
            }
        });

        lastFocus = document.activeElement;
        actions.appendChild(openButton);
        dialog.appendChild(closeButton);
        dialog.appendChild(icon);
        dialog.appendChild(title);
        dialog.appendChild(content);
        dialog.appendChild(actions);
        overlay.appendChild(backdrop);
        overlay.appendChild(dialog);
        document.body.appendChild(overlay);
        activeModal = overlay;
        document.addEventListener('keydown', onModalKeydown);

        var firstFocus = success ? openButton : textarea;
        window.DDSWClipboard.focusElement(firstFocus);
        if (!success) {
            firstFocus.select();
        }

        if (success && modalConfig.autoOpen) {
            window.setTimeout(function () {
                if (activeModal === overlay) {
                    openWhatsApp(payload);
                }
            }, Number(modalConfig.autoOpenDelay || 1000));
        }

        if (success && modalConfig.autoClose) {
            window.setTimeout(function () {
                if (activeModal === overlay) {
                    closeModal();
                }
            }, Number(modalConfig.autoCloseDelay || 5000));
        }
    }

    window.DDSWModal = {
        close: closeModal,
        confirm: confirmCopy,
        openTarget: openTarget,
        openWhatsApp: openWhatsApp,
        show: showModal,
        toast: showToast
    };
})();
