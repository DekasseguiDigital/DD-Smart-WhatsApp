(function () {
    'use strict';

    function settings() {
        return window.DDSmartWhatsApp || {};
    }

    function deviceType() {
        var ua = navigator.userAgent || '';
        var platform = navigator.platform || '';
        var touchMac = platform === 'MacIntel' && navigator.maxTouchPoints > 1;

        if (/iPad|iPhone|iPod/.test(ua) || touchMac) {
            return 'ios';
        }

        if (/Android/.test(ua)) {
            return 'android';
        }

        return 'desktop';
    }

    function trackEvent(payload, eventType, copyStatus) {
        if (!settings().ajaxUrl) {
            return;
        }

        var form = new window.FormData();
        form.append('action', 'ddsw_track_event');
        form.append('nonce', settings().nonce || '');
        form.append('button_id', payload.id || '');
        form.append('action_id', payload.actionId || payload.action_id || '');
        form.append('action_type', payload.actionType || payload.action_type || '');
        form.append('event_type', eventType);
        form.append('copy_status', copyStatus || '');
        form.append('device', deviceType());
        form.append('page_url', window.location.href);
        form.append('referrer', document.referrer || '');

        window.fetch(settings().ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: form
        }).catch(function (error) {
            if (window.DDSWClipboard) {
                window.DDSWClipboard.debugLog((window.DDSmartWhatsApp || {}).debugTrackingFailed || '', error);
            }
        });
    }

    function sendGa4(payload, eventType, copyStatus) {
        if (!settings().ga4Enabled || typeof window.gtag !== 'function') {
            return;
        }

        window.gtag('event', eventType, {
            button_id: payload.id || '',
            button_label: payload.label || '',
            action_id: payload.actionId || payload.action_id || '',
            action_type: payload.actionType || payload.action_type || '',
            copy_status: copyStatus || '',
            device: deviceType(),
            page_location: window.location.href
        });
    }

    function record(payload, eventType, copyStatus) {
        trackEvent(payload, eventType, copyStatus);
        sendGa4(payload, eventType, copyStatus);
    }

    window.DDSWTracking = {
        deviceType: deviceType,
        record: record
    };
})();
