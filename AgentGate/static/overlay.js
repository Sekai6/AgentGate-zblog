(function() {
    'use strict';

    var overlay = document.getElementById('agentgate-overlay');
    if (!overlay) return;

    // Hide every direct child of body except the overlay itself and scripts
    var children = document.body.children;
    var hidden = [];
    for (var i = 0; i < children.length; i++) {
        var el = children[i];
        if (el.id === 'agentgate-overlay') continue;
        if (el.tagName === 'SCRIPT') continue;
        if (el.tagName === 'LINK') continue;
        el.setAttribute('data-agentgate-hidden', '1');
        el.style.display = 'none';
        hidden.push(el);
    }

    // Move overlay to be a direct child of body (escape footer stacking context)
    document.body.appendChild(overlay);
    document.body.style.background = '#000';

    var AJAX_URL = window.AGENTGATE_AJAX_URL || '';

    function setCookie() {
        document.cookie = 'agentgate_verified=1; path=/; max-age=3600; SameSite=Lax';
    }

    function showPage() {
        for (var i = 0; i < hidden.length; i++) {
            hidden[i].style.display = '';
            hidden[i].removeAttribute('data-agentgate-hidden');
        }
        document.body.style.background = '';
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    }

    window.onAgentVerified = function(token, identity) {
        var msg = document.getElementById('agentgate-msg');
        if (msg) {
            msg.style.color = '#00ff41';
            msg.textContent = '\u2713 Identity confirmed. Welcome, non-human.';
        }

        // Captcha callback fired = verification passed on widget side.
        // Set cookie client-side immediately as primary mechanism.
        setCookie();

        // Restore page after brief delay
        setTimeout(showPage, 1000);

        // Also attempt server-side verification (best-effort, non-blocking)
        if (AJAX_URL) {
            fetch(AJAX_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'token=' + encodeURIComponent(token)
            }).catch(function() {});
        }
    };
})();
