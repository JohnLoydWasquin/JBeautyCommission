/**
 * checkout.js — JBeauty Phase 4
 * Handles: payment toggle, client-side validation,
 *          async order submission, loading state, redirect.
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    /* ── DOM refs ─────────────────────────────────────────── */
    const form          = document.getElementById('checkoutForm');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    const formAlert     = document.getElementById('formAlert');
    const formAlertText = document.getElementById('formAlertText');

    // Payment
    const payCredit     = document.getElementById('pay_credit');
    const payCOD        = document.getElementById('pay_cod');
    const cardFields    = document.getElementById('cardFields');

    // Fields that need validation
    const requiredFields = [
        { el: document.getElementById('full_name'),        errId: 'full_name_err',  check: v => v.trim().length >= 2 },
        { el: document.getElementById('email'),            errId: 'email_err',      check: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) },
        { el: document.getElementById('shipping_address'), errId: 'address_err',    check: v => v.trim().length >= 10 },
    ];

    /* ── Payment method toggle ────────────────────────────── */
    function toggleCardFields() {
        const show = payCredit && payCredit.checked;
        cardFields.classList.toggle('visible', show);

        // Make card inputs required only when card is selected
        cardFields.querySelectorAll('input').forEach(inp => {
            inp.required = show;
        });
    }

    if (payCredit) payCredit.addEventListener('change', toggleCardFields);
    if (payCOD)    payCOD.addEventListener('change',    toggleCardFields);

    // Initialise on load
    toggleCardFields();

    /* ── Card number auto-formatter (#### #### #### ####) ── */
    const cardNumber = document.getElementById('card_number');
    if (cardNumber) {
        cardNumber.addEventListener('input', () => {
            let val = cardNumber.value.replace(/\D/g, '').slice(0, 16);
            cardNumber.value = val.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    /* ── Expiry auto-formatter (MM / YY) ─────────────────── */
    const cardExpiry = document.getElementById('card_expiry');
    if (cardExpiry) {
        cardExpiry.addEventListener('input', e => {
            let val = cardExpiry.value.replace(/\D/g, '').slice(0, 4);
            if (val.length > 2) val = val.slice(0, 2) + ' / ' + val.slice(2);
            cardExpiry.value = val;
        });
    }

    /* ── Inline field validation ──────────────────────────── */
    function validateField(fieldDef) {
        const { el, errId, check } = fieldDef;
        const errEl = document.getElementById(errId);
        const valid = check(el.value);

        el.classList.toggle('invalid', !valid);
        if (errEl) errEl.classList.toggle('visible', !valid);
        el.setAttribute('aria-invalid', valid ? 'false' : 'true');

        return valid;
    }

    // Validate on blur for a nicer UX
    requiredFields.forEach(def => {
        def.el.addEventListener('blur', () => validateField(def));
        def.el.addEventListener('input', () => {
            // Clear invalid state eagerly once the user starts correcting
            if (def.check(def.el.value)) validateField(def);
        });
    });

    function validateAll() {
        return requiredFields.map(validateField).every(Boolean);
    }

    /* ── Alert helpers ────────────────────────────────────── */
    function showAlert(message) {
        formAlertText.textContent = message;
        formAlert.classList.add('error');
        formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        formAlert.classList.remove('error');
        formAlertText.textContent = '';
    }

    /* ── Loading state helpers ────────────────────────────── */
    function setLoading(loading) {
        placeOrderBtn.disabled = loading;
        placeOrderBtn.classList.toggle('loading', loading);
        placeOrderBtn.setAttribute('aria-busy', loading ? 'true' : 'false');
    }

    /* ── Form submission ──────────────────────────────────── */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        hideAlert();

        // Client-side validation
        if (!validateAll()) {
            showAlert('Please fill in all required fields correctly before placing your order.');
            return;
        }

        setLoading(true);

        // Build payload from the form
        const payload = new FormData(form);

        try {
            const response = await fetch('api/process_order.php', {
                method: 'POST',
                body:   payload,
            });

            // Handle non-200 HTTP status
            if (!response.ok) {
                throw new Error(`Server error (${response.status}). Please try again.`);
            }

            const data = await response.json();

            if (data.success && data.order_id) {
                // Redirect to the confirmation page
                window.location.href = `order_success.php?id=${encodeURIComponent(data.order_id)}`;
            } else {
                // API returned success:false with a message
                setLoading(false);
                showAlert(data.message || 'Something went wrong. Please try again.');
            }

        } catch (err) {
            setLoading(false);

            if (err instanceof TypeError) {
                // Network failure (fetch itself threw)
                showAlert('Network error. Please check your connection and try again.');
            } else {
                showAlert(err.message || 'An unexpected error occurred. Please try again.');
            }

            console.error('[JBeauty checkout]', err);
        }
    });

});