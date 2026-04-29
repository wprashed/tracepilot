/**
 * TracePilot client-side runtime error collector.
 */
(function() {
    'use strict';

    if (window.__tracepilotErrorCaptureLoaded || typeof window.tracepilot_client_error_vars === 'undefined') {
        return;
    }
    window.__tracepilotErrorCaptureLoaded = true;

    function send(payload) {
        if (!window.fetch || !window.FormData) {
            return;
        }

        try {
            const data = new window.FormData();
            data.append('action', 'tracepilot_capture_client_error');
            data.append('nonce', window.tracepilot_client_error_vars.nonce || '');
            data.append('page', window.location.href || '');
            data.append('message', payload.message || '');
            data.append('source', payload.source || '');
            data.append('stack', payload.stack || '');

            window.fetch(window.tracepilot_client_error_vars.ajax_url, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            });
        } catch (error) {
            // Ignore capture failures; never block user interactions.
        }
    }

    window.addEventListener('error', function(event) {
        send({
            message: event && event.message ? event.message : 'JavaScript error',
            source: event && event.filename ? event.filename : '',
            stack: event && event.error && event.error.stack ? event.error.stack : ''
        });
    });

    window.addEventListener('unhandledrejection', function(event) {
        const reason = (event && event.reason) ? event.reason : {};
        send({
            message: reason.message || String(reason) || 'Unhandled promise rejection',
            source: 'unhandledrejection',
            stack: reason.stack || ''
        });
    });
})();
