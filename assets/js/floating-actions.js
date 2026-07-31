(function () {
    'use strict';

    function closeHub(hub) {
        var trigger = hub.querySelector('[data-ddsw-floating-toggle]');
        var menu = hub.querySelector('.ddsw-floating-hub__menu');

        hub.classList.remove('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        if (menu) {
            menu.setAttribute('aria-hidden', 'true');
            setMenuTabbable(menu, false);
        }
    }

    function closeAll(except) {
        document.querySelectorAll('[data-ddsw-floating-hub].is-open').forEach(function (hub) {
            if (hub !== except) {
                closeHub(hub);
            }
        });
    }

    function toggleHub(hub) {
        var trigger = hub.querySelector('[data-ddsw-floating-toggle]');
        var menu = hub.querySelector('.ddsw-floating-hub__menu');
        var open = !hub.classList.contains('is-open');

        closeAll(hub);
        hub.classList.toggle('is-open', open);

        if (trigger) {
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (menu) {
            menu.setAttribute('aria-hidden', open ? 'false' : 'true');
            setMenuTabbable(menu, open);
        }
    }

    function focusableItems(menu) {
        return Array.prototype.slice.call(menu.querySelectorAll('a, button'));
    }

    function setMenuTabbable(menu, enabled) {
        focusableItems(menu).forEach(function (item) {
            if (enabled) {
                if (item.hasAttribute('data-ddsw-floating-original-tabindex')) {
                    item.setAttribute('tabindex', item.getAttribute('data-ddsw-floating-original-tabindex'));
                    item.removeAttribute('data-ddsw-floating-original-tabindex');
                } else {
                    item.removeAttribute('tabindex');
                }
                return;
            }

            if (item.hasAttribute('tabindex') && !item.hasAttribute('data-ddsw-floating-original-tabindex')) {
                item.setAttribute('data-ddsw-floating-original-tabindex', item.getAttribute('tabindex'));
            }
            item.setAttribute('tabindex', '-1');
        });
    }

    function trackAction(link) {
        if (!window.DDSWTracking) {
            return;
        }

        window.DDSWTracking.record({
            id: link.getAttribute('data-ddsw-floating-hub-id') || '',
            label: link.getAttribute('data-ddsw-floating-action-label') || '',
            actionId: link.getAttribute('data-ddsw-floating-action-id') || '',
            actionType: link.getAttribute('data-ddsw-floating-action-type') || ''
        }, 'dd_smart_whatsapp_click', '');
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-ddsw-floating-toggle]');
        var action = event.target.closest('[data-ddsw-floating-action], .ddsw-floating-action--whatsapp');
        var hub;

        if (toggle) {
            event.preventDefault();
            hub = toggle.closest('[data-ddsw-floating-hub]');
            if (hub) {
                toggleHub(hub);
            }
            return;
        }

        if (action) {
            if (action.hasAttribute('data-ddsw-floating-action')) {
                trackAction(action);
            }
            closeAll();
            return;
        }

        if (!event.target.closest('[data-ddsw-floating-hub]')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function (event) {
        var activeHub;
        var items;
        var currentIndex;
        var next;

        if (event.key === 'Escape') {
            activeHub = document.querySelector('[data-ddsw-floating-hub].is-open');
            closeAll();
            if (activeHub) {
                var trigger = activeHub.querySelector('[data-ddsw-floating-toggle]');
                if (trigger) {
                    trigger.focus();
                }
            }
            return;
        }

        if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
            return;
        }

        activeHub = document.querySelector('[data-ddsw-floating-hub].is-open');
        if (!activeHub) {
            return;
        }

        items = focusableItems(activeHub.querySelector('.ddsw-floating-hub__menu') || activeHub);
        currentIndex = items.indexOf(document.activeElement);
        next = event.key === 'ArrowDown' ? currentIndex + 1 : currentIndex - 1;

        if (next < 0) {
            next = items.length - 1;
        }

        if (next >= items.length) {
            next = 0;
        }

        if (items[next]) {
            event.preventDefault();
            items[next].focus();
        }
    });

    function init() {
        document.querySelectorAll('[data-ddsw-floating-hub]').forEach(function (hub) {
            var menu = hub.querySelector('.ddsw-floating-hub__menu');
            if (menu) {
                menu.setAttribute('aria-hidden', hub.classList.contains('is-open') ? 'false' : 'true');
                setMenuTabbable(menu, hub.classList.contains('is-open'));
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
