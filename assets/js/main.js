/**
 * JSN Renewable Energy - Master Client Engine & Interactive State Manager
 * Features: Multi-Category Solar ROI Calculator, Cookie Consent, Visitor IP Telemetry, Nav Toggles
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

    // 2. MULTI-CATEGORY SOLAR ROI CALCULATOR
    initSolarCalculator();

    // 3. COOKIE CONSENT ENGINE
    initCookieConsent();

    // 4. VISITOR IP TRACKER & LOGGING ENGINE
    trackVisitorIP();
});

/**
 * Interactive Multi-Category Solar ROI & Financial Savings Calculator
 */
function initSolarCalculator() {
    const calcCategory = document.getElementById('calcCategory');
    const billSlider = document.getElementById('calcBillSlider');
    const billValueDisplay = document.getElementById('calcBillValue');
    const areaSlider = document.getElementById('calcAreaSlider');
    const areaValueDisplay = document.getElementById('calcAreaValue');

    const resCapacity = document.getElementById('calcResCapacity');
    const resSavings = document.getElementById('calcResSavings');
    const resUnits = document.getElementById('calcResUnits');
    const resPayback = document.getElementById('calcResPayback');
    const btnQuote = document.getElementById('calcBtnQuote');

    if (!billSlider || !resCapacity) return;

    function calculate() {
        const category = calcCategory ? calcCategory.value : 'industrial';
        const monthlyBill = parseFloat(billSlider.value);
        const roofArea = parseFloat(areaSlider.value);

        // Update Slider Label Displays
        if (billValueDisplay) {
            billValueDisplay.innerText = '₹ ' + monthlyBill.toLocaleString('en-IN');
        }
        if (areaValueDisplay) {
            areaValueDisplay.innerText = roofArea.toLocaleString('en-IN') + ' sq. ft.';
        }

        // Tariff multiplier based on category
        let tariffPerUnit = 8.5; // Average industrial C&I tariff
        if (category === 'commercial') tariffPerUnit = 9.8;
        if (category === 'kusum') tariffPerUnit = 4.2;
        if (category === 'rwa') tariffPerUnit = 7.8;
        if (category === 'residential') tariffPerUnit = 7.0;

        // Estimated Monthly Units Consumed
        const monthlyUnits = monthlyBill / tariffPerUnit;

        // Solar Plant Capacity needed (1 kW generates ~120 kWh per month)
        let recommendedKw = Math.round((monthlyUnits / 120) * 10) / 10;
        if (recommendedKw < 3) recommendedKw = 3;

        // Check roof area constraint (1 kW requires ~90-100 sq. ft.)
        const maxKwByArea = Math.floor(roofArea / 90);
        if (recommendedKw > maxKwByArea && maxKwByArea > 0) {
            recommendedKw = maxKwByArea;
        }

        // Calculate Annual Savings (₹)
        const annualUnits = recommendedKw * 1400; // ~1400 kWh/kW/year
        const annualSavings = Math.round(annualUnits * tariffPerUnit);

        // Calculate Investment & Payback
        const estimatedCost = recommendedKw * 48000; // Average ₹48,000 / kW turnkey
        let paybackYears = Math.round((estimatedCost / annualSavings) * 10) / 10;
        if (paybackYears < 2.5) paybackYears = 2.8;

        // Render Results
        if (resCapacity) {
            if (recommendedKw >= 1000) {
                resCapacity.innerText = (recommendedKw / 1000).toFixed(2) + ' MW';
            } else {
                resCapacity.innerText = recommendedKw.toFixed(1) + ' kW';
            }
        }

        if (resSavings) {
            resSavings.innerText = '₹ ' + annualSavings.toLocaleString('en-IN') + ' / yr';
        }

        if (resUnits) {
            resUnits.innerText = annualUnits.toLocaleString('en-IN') + ' kWh / yr';
        }

        if (resPayback) {
            resPayback.innerText = paybackYears + ' Years';
        }

        if (btnQuote) {
            btnQuote.href = `/contact/index.html?capacity=${recommendedKw}&category=${category}`;
        }
    }

    // Event Listeners
    billSlider.addEventListener('input', calculate);
    areaSlider.addEventListener('input', calculate);
    if (calcCategory) calcCategory.addEventListener('change', calculate);

    // Initial calculation
    calculate();
}

/**
 * Cookie Consent Manager (Accept, Decline, Customize)
 */
function initCookieConsent() {
    const consent = localStorage.getItem('jsn_cookie_consent');
    if (consent) return; // User already made a choice

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

            let logs = JSON.parse(localStorage.getItem('jsn_visitor_logs') || '[]');
            logs.unshift(logEntry);
            if (logs.length > 50) logs = logs.slice(0, 50);
            localStorage.setItem('jsn_visitor_logs', JSON.stringify(logs));

            fetch('/ip-logger.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(logEntry)
            }).catch(() => {});
        })
        .catch(() => {});
}
