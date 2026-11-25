/**
 * JSN Renewable Energy - Master Client Engine
 * Features: Verified Solar ROI Calculator, Sticky Navbar Scroll, Mobile Menu, Cookie Consent, Server-Side Analytics Ping
 */

document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) {
        lucide.createIcons();
    }

    // 1. Sticky Navbar Scroll Effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const onScroll = () => {
            if (window.scrollY > 20) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // 2. Mobile Menu Toggle
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const navMenu = document.getElementById('navMenu');

    if (mobileBtn && navMenu) {
        mobileBtn.addEventListener('click', () => {
            const isActive = navMenu.classList.toggle('active');
            mobileBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                mobileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 3. Solar ROI & Subsidy Calculator
    initSolarCalculator();

    // 4. Cookie Consent Banner
    initCookieConsent();

    // 5. Server-Side Visitor Analytics Ping (non-blocking)
    trackVisitorSession();
});

/**
 * Verified Solar ROI, Generation & PM Surya Ghar Subsidy Calculator
 * All outputs are clearly presented as engineering estimates.
 */
function initSolarCalculator() {
    const calcCategory = document.getElementById('calcCategory');
    const calcState = document.getElementById('calcState');
    const billSlider = document.getElementById('calcBillSlider');
    const billInput = document.getElementById('calcBillInput');
    const billValueDisplay = document.getElementById('calcBillValue');
    const rateInput = document.getElementById('calcRateInput');
    const areaSlider = document.getElementById('calcAreaSlider');
    const areaInput = document.getElementById('calcAreaInput');
    const areaValueDisplay = document.getElementById('calcAreaValue');
    const calcError = document.getElementById('calcValidationError');

    const resCapacity = document.getElementById('calcResCapacity');
    const resMonthlyUnits = document.getElementById('calcResMonthlyUnits');
    const resUnits = document.getElementById('calcResUnits');
    const resCost = document.getElementById('calcResCost');
    const resMonthlySavings = document.getElementById('calcResMonthlySavings');
    const resSavings = document.getElementById('calcResSavings');
    const resPayback = document.getElementById('calcResPayback');
    const resSubsidy = document.getElementById('calcResSubsidy');
    const btnQuote = document.getElementById('calcBtnQuote');

    if (!resCapacity || (!billSlider && !billInput)) return;

    function formatINR(amount) {
        return '₹ ' + Math.round(amount).toLocaleString('en-IN');
    }

    function formatCost(amount) {
        if (amount >= 10000000) {
            return '₹ ' + (amount / 10000000).toFixed(2) + ' Cr';
        }
        if (amount >= 100000) {
            return '₹ ' + (amount / 100000).toFixed(2) + ' Lakh';
        }
        return formatINR(amount);
    }

    function syncRateFromState() {
        if (calcState && rateInput) {
            const stateRate = parseFloat(calcState.value);
            if (!isNaN(stateRate) && stateRate > 0) {
                rateInput.value = stateRate.toFixed(1);
            }
        }
    }

    function calculate() {
        const category = calcCategory ? calcCategory.value : 'residential';

        let rawBill = billInput ? parseFloat(billInput.value) : parseFloat(billSlider ? billSlider.value : 0);
        let rawArea = areaInput ? parseFloat(areaInput.value) : parseFloat(areaSlider ? areaSlider.value : 0);
        let rawRate = rateInput ? parseFloat(rateInput.value) : parseFloat(calcState ? calcState.value : 8.0);

        let warning = '';

        if (isNaN(rawBill) || rawBill < 0) {
            rawBill = 0;
            warning = 'Monthly electricity bill cannot be negative or empty.';
        }
        if (isNaN(rawArea) || rawArea < 0) {
            rawArea = 0;
            warning = 'Available rooftop area cannot be negative or empty.';
        }
        if (isNaN(rawRate) || rawRate <= 0) {
            rawRate = 8.0;
            if (!warning) warning = 'Electricity rate must be greater than ₹0/unit. Using ₹8.0/unit.';
        }

        if (calcError) {
            if (warning) {
                calcError.textContent = warning;
                calcError.style.display = 'block';
            } else {
                calcError.style.display = 'none';
            }
        }

        if (billValueDisplay) {
            billValueDisplay.textContent = formatINR(rawBill);
        }
        if (areaValueDisplay) {
            areaValueDisplay.textContent = Math.round(rawArea).toLocaleString('en-IN') + ' sq. ft.';
        }

        if (rawBill === 0 || rawArea < 100) {
            resCapacity.textContent = '0.0 kW';
            if (resMonthlyUnits) resMonthlyUnits.textContent = '0 kWh / mo';
            if (resUnits) resUnits.textContent = '0 kWh / yr';
            if (resCost) resCost.textContent = '₹ 0';
            if (resMonthlySavings) resMonthlySavings.textContent = '₹ 0 / mo';
            if (resSavings) resSavings.textContent = '₹ 0 / yr';
            if (resPayback) resPayback.textContent = '—';
            if (resSubsidy) resSubsidy.textContent = '₹ 0';
            return;
        }

        // Core Engineering Assumptions:
        // 1 kWp generates ~1,400 kWh/year (~116.67 kWh/month) in North/West India
        // 1 kWp requires ~100 sq. ft. of shade-free roof area
        const monthlyUnitsConsumed = rawBill / rawRate;
        let recommendedKw = monthlyUnitsConsumed / (1400 / 12);
        const maxKwByArea = rawArea / 100;

        if (recommendedKw > maxKwByArea) {
            recommendedKw = maxKwByArea;
        }
        recommendedKw = Math.max(1.0, Math.round(recommendedKw * 10) / 10);

        const annualUnits = Math.round(recommendedKw * 1400);
        const monthlyUnitsGen = Math.round(annualUnits / 12);

        // Tiered Turnkey EPC Benchmark Cost (₹/kW)
        let costPerKw = 55000;
        if (recommendedKw > 100) costPerKw = 40000;
        else if (recommendedKw > 10) costPerKw = 45000;
        else if (recommendedKw > 3) costPerKw = 50000;

        const estimatedCost = Math.round(recommendedKw * costPerKw);

        // PM Surya Ghar Subsidy Rules
        let subsidy = 0;
        if (category === 'residential') {
            if (recommendedKw <= 2) {
                subsidy = Math.round(recommendedKw * 30000);
            } else if (recommendedKw <= 3) {
                subsidy = Math.round(60000 + (recommendedKw - 2) * 18000);
            } else {
                subsidy = 78000;
            }
        } else if (category === 'rwa') {
            subsidy = Math.round(Math.min(recommendedKw, 500) * 18000);
        }

        const netCost = Math.max(0, estimatedCost - subsidy);
        const annualSavings = Math.round(annualUnits * rawRate);
        const monthlySavings = Math.round(annualSavings / 12);
        const paybackYears = annualSavings > 0 ? (netCost / annualSavings).toFixed(1) : '—';

        // Render Output
        if (recommendedKw >= 1000) {
            resCapacity.textContent = (recommendedKw / 1000).toFixed(2) + ' MW';
        } else {
            resCapacity.textContent = recommendedKw.toFixed(1) + ' kW';
        }

        if (resMonthlyUnits) resMonthlyUnits.textContent = monthlyUnitsGen.toLocaleString('en-IN') + ' kWh / mo';
        if (resUnits) resUnits.textContent = annualUnits.toLocaleString('en-IN') + ' kWh / yr';
        if (resCost) resCost.textContent = formatCost(estimatedCost);
        if (resMonthlySavings) resMonthlySavings.textContent = formatINR(monthlySavings) + ' / mo';
        if (resSavings) resSavings.textContent = formatINR(annualSavings) + ' / yr';
        if (resPayback) resPayback.textContent = paybackYears === '—' ? '—' : paybackYears + ' Years';
        if (resSubsidy) {
            resSubsidy.textContent = subsidy > 0 ? formatINR(subsidy) : '₹ 0 (40% AD Tax Benefit)';
        }

        if (btnQuote) {
            btnQuote.href = `/contact/index.html?capacity=${encodeURIComponent(recommendedKw)}&category=${encodeURIComponent(category)}`;
        }
    }

    // Sync sliders and numeric inputs
    if (billSlider && billInput) {
        billSlider.addEventListener('input', () => {
            billInput.value = billSlider.value;
            calculate();
        });
        billInput.addEventListener('input', () => {
            const v = parseFloat(billInput.value);
            if (!isNaN(v) && v >= 0) {
                billSlider.value = Math.min(parseFloat(billSlider.max), Math.max(parseFloat(billSlider.min), v));
            }
            calculate();
        });
    } else if (billSlider) {
        billSlider.addEventListener('input', calculate);
    }

    if (areaSlider && areaInput) {
        areaSlider.addEventListener('input', () => {
            areaInput.value = areaSlider.value;
            calculate();
        });
        areaInput.addEventListener('input', () => {
            const v = parseFloat(areaInput.value);
            if (!isNaN(v) && v >= 0) {
                areaSlider.value = Math.min(parseFloat(areaSlider.max), Math.max(parseFloat(areaSlider.min), v));
            }
            calculate();
        });
    } else if (areaSlider) {
        areaSlider.addEventListener('input', calculate);
    }

    if (calcState) {
        calcState.addEventListener('change', () => {
            syncRateFromState();
            calculate();
        });
    }
    if (rateInput) {
        rateInput.addEventListener('input', calculate);
    }
    if (calcCategory) {
        calcCategory.addEventListener('change', calculate);
    }

    syncRateFromState();
    calculate();
}

/**
 * Cookie Consent Manager
 */
function initCookieConsent() {
    const consent = localStorage.getItem('jsn_cookie_consent');
    if (consent) return;

    const bannerHtml = `
        <div id="cookieConsentBanner" class="cookie-consent-banner">
            <div class="cookie-consent-title">Privacy &amp; Cookie Notice</div>
            <div class="cookie-consent-text">
                JSN Renewable Energy uses essential session cookies and anonymized server-side analytics to improve website performance and solar sizing tools.
            </div>
            <div class="cookie-actions">
                <button id="btnAcceptCookies" class="btn-cookie-accept">Accept</button>
                <button id="btnDeclineCookies" class="btn-cookie-decline">Essential Only</button>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', bannerHtml);
    const banner = document.getElementById('cookieConsentBanner');
    setTimeout(() => {
        if (banner) banner.style.display = 'block';
    }, 1000);

    document.getElementById('btnAcceptCookies')?.addEventListener('click', () => {
        localStorage.setItem('jsn_cookie_consent', JSON.stringify({ choice: 'accepted', date: new Date().toISOString() }));
        if (banner) banner.style.display = 'none';
    });

    document.getElementById('btnDeclineCookies')?.addEventListener('click', () => {
        localStorage.setItem('jsn_cookie_consent', JSON.stringify({ choice: 'essential', date: new Date().toISOString() }));
        if (banner) banner.style.display = 'none';
    });
}

/**
 * Lightweight Server-Side Visitor Analytics Ping
 * IP address and user-agent are captured strictly on the server.
 */
function trackVisitorSession() {
    const endpoint = window.location.pathname.split('/').length > 2 ? '../ip-logger.php' : 'ip-logger.php';
    fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            page: window.location.pathname || '/',
            referrer: document.referrer || ''
        })
    }).catch(() => {});
}
