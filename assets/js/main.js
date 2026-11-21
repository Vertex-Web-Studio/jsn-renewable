/**
 * JSN Renewable Energy - Master Client Engine & Interactive State Manager
 * Features: Cookie Consent (Accept/Decline/Customize), Visitor IP Tracking, Navigation Toggles
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide Icons
    if (window.lucide) {
        lucide.createIcons();
    }

    // 1. MOBILE MENU TOGGLE
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const navMenu = document.getElementById('navMenu');

    if (mobileBtn && navMenu) {
        mobileBtn.addEventListener('click', () => {
            const isActive = navMenu.classList.toggle('active');
            mobileBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                mobileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 2. COOKIE CONSENT ENGINE
    initCookieConsent();

    // 3. VISITOR IP TRACKER & LOGGING ENGINE
    trackVisitorIP();
});

/**
 * Cookie Consent Manager (Accept, Decline, Customize)
 */
function initCookieConsent() {
    const consent = localStorage.getItem('jsn_cookie_consent');
    if (consent) return; // User already made a choice

    // Inject Cookie Consent Banner HTML into body
    const bannerHtml = `
        <div id="cookieConsentBanner" class="cookie-consent-banner">
            <div class="cookie-consent-title">
                🍪 Privacy & Cookie Preferences
            </div>
            <div class="cookie-consent-text">
                We use necessary cookies and anonymous visitor telemetry to optimize performance and solar ROI calculations for JSN Renewable Energy Private Limited.
            </div>
            <div class="cookie-actions">
                <button id="btnAcceptCookies" class="btn-cookie-accept">Accept All</button>
                <button id="btnDeclineCookies" class="btn-cookie-decline">Decline</button>
                <button id="btnCustomCookies" class="btn-cookie-custom">Customize</button>
            </div>
        </div>

        <!-- COOKIE PREFERENCES MODAL -->
        <div id="cookieModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:10000; padding:40px 20px;">
            <div style="background:#041B40; color:#fff; max-width:500px; margin:40px auto; padding:28px; border-radius:12px; border:1px solid rgba(255,105,0,0.3);">
                <h3 style="color:#fff; margin-top:0; margin-bottom:12px;">Cookie Preferences</h3>
                <p style="font-size:13px; color:#CBD5E1; margin-bottom:20px;">Manage cookie preferences for JSN Renewable Energy:</p>

                <div style="margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <strong>Essential / Necessary</strong>
                        <div style="font-size:11px; color:#94A3B8;">Required for core website security & routing.</div>
                    </div>
                    <input type="checkbox" checked disabled style="accent-color:#FF6900;">
                </div>

                <div style="margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <strong>Visitor IP Telemetry</strong>
                        <div style="font-size:11px; color:#94A3B8;">Anonymized location & lead security logging.</div>
                    </div>
                    <input type="checkbox" id="chkAnalytics" checked style="accent-color:#FF6900;">
                </div>

                <div style="margin-top:24px; display:flex; justify-content:flex-end; gap:10px;">
                    <button id="btnSaveCookiePrefs" class="btn-cookie-accept">Save My Preferences</button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', bannerHtml);

    const banner = document.getElementById('cookieConsentBanner');
    const modal = document.getElementById('cookieModal');

    setTimeout(() => {
        if (banner) banner.style.display = 'block';
    }, 1000);

    document.getElementById('btnAcceptCookies')?.addEventListener('click', () => {
        localStorage.setItem('jsn_cookie_consent', JSON.stringify({ choice: 'accepted', date: new Date().toISOString() }));
        banner.style.display = 'none';
    });

    document.getElementById('btnDeclineCookies')?.addEventListener('click', () => {
        localStorage.setItem('jsn_cookie_consent', JSON.stringify({ choice: 'declined', date: new Date().toISOString() }));
        banner.style.display = 'none';
    });

    document.getElementById('btnCustomCookies')?.addEventListener('click', () => {
        modal.style.display = 'block';
    });

    document.getElementById('btnSaveCookiePrefs')?.addEventListener('click', () => {
        const isAnalytics = document.getElementById('chkAnalytics').checked;
        localStorage.setItem('jsn_cookie_consent', JSON.stringify({ choice: 'custom', analytics: isAnalytics, date: new Date().toISOString() }));
        modal.style.display = 'none';
        banner.style.display = 'none';
    });
}

/**
 * Visitor IP Tracker & Telemetry Engine
 */
function trackVisitorIP() {
    fetch('https://api.ipify.org?format=json')
        .then(res => res.json())
        .then(data => {
            const visitorIP = data.ip;
            const logEntry = {
                ip: visitorIP,
                page: window.location.pathname,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString()
            };

            // Store in visitor history array
            let logs = JSON.parse(localStorage.getItem('jsn_visitor_logs') || '[]');
            logs.unshift(logEntry);
            if (logs.length > 50) logs = logs.slice(0, 50); // Keep last 50 logs
            localStorage.setItem('jsn_visitor_logs', JSON.stringify(logs));

            // Post to backend ip-logger if endpoint exists
            fetch('/ip-logger.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(logEntry)
            }).catch(() => {});
        })
        .catch(() => {});
}
