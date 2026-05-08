'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // ── Element refs ──────────────────────────────────────────
    const cartTrigger      = document.getElementById('cartTrigger');
    const cartPanel        = document.getElementById('cartPanel');
    const cartOverlay      = document.getElementById('cartOverlay');
    const cartClose        = document.getElementById('cartClose');
    const cartBadge        = document.getElementById('cartBadge');
    const cartItemsList    = document.getElementById('cartItemsList');
    const cartTotal        = document.getElementById('cartTotal');
    const cartItemCountLbl = document.getElementById('cartItemCountLabel');
    const checkoutBtn      = document.getElementById('checkoutBtn');
    const continueShopping = document.getElementById('continueShopping');
    const productGrid      = document.getElementById('productGrid');
    const noResults        = document.getElementById('noResults');
    const resultCount      = document.getElementById('resultCount');
    const toastContainer   = document.getElementById('toastContainer');
    const filterBtns       = document.querySelectorAll('.filter-btn');

    const cartStore = {};

    function seedCartFromDOM() {
        const existingRows = cartItemsList.querySelectorAll('.cart-item[data-id]');
        existingRows.forEach(row => {
            const id    = parseInt(row.dataset.id, 10);
            const name  = row.querySelector('.cart-item-name')?.textContent?.trim() ?? '';
            const img   = row.querySelector('.cart-item-img')?.src ?? '';
            const qty   = parseInt(row.querySelector('.qty-value')?.textContent ?? '1', 10);
            // Price per unit: total cell / qty
            const totalText = row.querySelector('.cart-item-price')?.textContent ?? '$0.00';
            const total     = parseFloat(totalText.replace(/[^0-9.]/g, '')) || 0;
            const price     = qty > 0 ? total / qty : 0;

            cartStore[id] = { id, name, price, imageUrl: img, qty };
        });
    }

    function openCart() {
        cartPanel.classList.add('open');
        cartOverlay.classList.add('open');
        cartPanel.setAttribute('aria-hidden', 'false');
        cartOverlay.setAttribute('aria-hidden', 'false');
        cartTrigger.setAttribute('aria-expanded', 'true');
        cartClose.focus();
        document.body.style.overflow = 'hidden';
    }

    function closeCart() {
        cartPanel.classList.remove('open');
        cartOverlay.classList.remove('open');
        cartPanel.setAttribute('aria-hidden', 'true');
        cartOverlay.setAttribute('aria-hidden', 'true');
        cartTrigger.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        // Return focus to trigger
        cartTrigger.focus();
    }

    cartTrigger?.addEventListener('click', openCart);
    cartClose?.addEventListener('click', closeCart);
    cartOverlay?.addEventListener('click', closeCart);
    continueShopping?.addEventListener('click', closeCart);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && cartPanel.classList.contains('open')) {
            closeCart();
        }
    });

    cartPanel?.addEventListener('keydown', e => {
        if (e.key !== 'Tab') return;
        const focusable = cartPanel.querySelectorAll(
            'button:not([disabled]), a:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        const first = focusable[0];
        const last  = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });

    /**
     * Filter the product grid by category.
     * @param {string} category - 'All' or a specific category name.
     */
    function filterProducts(category) {
        const cards = productGrid?.querySelectorAll('.product-card') ?? [];

        let visible = 0;

        cards.forEach(card => {
            const cardCat = card.dataset.category;
            const show    = category === 'All' || cardCat === category;

            if (show) {
                card.classList.remove('hidden');
                visible++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Toggle empty state
        if (noResults) noResults.hidden = visible > 0;

        // Update result count
        if (resultCount) {
            resultCount.textContent = `${visible} product${visible !== 1 ? 's' : ''}`;
        }
    }

    // ── Wire filter buttons ───────────────────────────────────
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active state
            filterBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-pressed', 'true');

            filterProducts(btn.dataset.filter);
        });
    });


    /**
     * Send an Add-to-Cart POST request to the PHP API.
     * @param {number} productId
     * @returns {Promise<{success:boolean, cart:object, message:string}>}
     */
    async function addToCartRequest(productId) {
        const formData = new FormData();
        formData.append('product_id', productId);

        const response = await fetch('api/add_to_cart.php', {
            method:      'POST',
            credentials: 'same-origin', // send session cookie
            body:        formData,
        });

        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message ?? 'Could not add item to cart.');
        }

        return data;
    }

    /**
     * Handle a single "Add to Cart" button click.
     * @param {HTMLButtonElement} btn
     */
    async function handleAddToCart(btn) {
        const productId   = parseInt(btn.dataset.productId, 10);
        const productName = btn.dataset.productName ?? 'Item';

        if (!productId) return;

        // ── Optimistic UI: loading state ──────────────────────
        btn.classList.add('loading');
        btn.setAttribute('aria-busy', 'true');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
                 style="animation:spin .6s linear infinite">
                <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0"/>
            </svg>
            Adding…
        `;

        try {
            const data = await addToCartRequest(productId);

            // ── Update in-memory store with server response ────
            if (data.cart) {
                syncStoreFromServer(data.cart);
            }

            // ── Re-render cart UI ─────────────────────────────
            renderCartItems();
            updateBadge();

            // ── Success feedback ──────────────────────────────
            showToast(`✓ ${productName} added to cart`, 'success');

            // Bounce the badge
            cartBadge.style.transform = 'scale(1.4)';
            setTimeout(() => { cartBadge.style.transform = ''; }, 300);

        } catch (err) {
            console.error('[JBeauty Cart]', err);
            showToast(err.message ?? 'Something went wrong. Please try again.', 'error');
        } finally {
            // ── Restore button ────────────────────────────────
            btn.innerHTML = originalHTML;
            btn.classList.remove('loading');
            btn.removeAttribute('aria-busy');
        }
    }

    // ── Event delegation: all "Add to Cart" buttons ──────────
    productGrid?.addEventListener('click', e => {
        const btn = e.target.closest('.btn-add-cart');
        if (btn) {
            e.preventDefault();
            handleAddToCart(btn);
        }
    });

    /**
     * Update the quantity of a cart item via the server.
     * Action is 'increase' or 'decrease'.
     * @param {number} productId
     * @param {'increase'|'decrease'} action
     */
    async function updateQty(productId, action) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action',     action);

        try {
            const response = await fetch('api/update_cart.php', {
                method:      'POST',
                credentials: 'same-origin',
                body:        formData,
            });

            if (!response.ok) throw new Error(`Server error: ${response.status}`);

            const data = await response.json();

            if (!data.success) throw new Error(data.message ?? 'Could not update cart.');

            if (data.cart) syncStoreFromServer(data.cart);

            renderCartItems();
            updateBadge();

        } catch (err) {
            console.error('[JBeauty Cart Qty]', err);
            showToast('Could not update quantity.', 'error');
        }
    }

    // ── Event delegation: qty buttons inside cart ────────────
    cartItemsList?.addEventListener('click', e => {
        const btn = e.target.closest('.qty-btn');
        if (!btn) return;
        const productId = parseInt(btn.dataset.id, 10);
        const action    = btn.dataset.action; // 'increase' | 'decrease'
        if (productId && action) updateQty(productId, action);
    });


    /**
     * Sync in-memory store from a server-returned cart object.
     * Server shape: { [id]: { id, name, price, image_url, qty } }
     * @param {object} serverCart
     */
    function syncStoreFromServer(serverCart) {
        // Clear then repopulate
        Object.keys(cartStore).forEach(k => delete cartStore[k]);
        Object.values(serverCart).forEach(item => {
            cartStore[item.id] = {
                id:       item.id,
                name:     item.name,
                price:    parseFloat(item.price),
                imageUrl: item.image_url,
                qty:      parseInt(item.qty, 10),
            };
        });
    }

    function renderCartItems() {
        const items = Object.values(cartStore);

        if (items.length === 0) {
            cartItemsList.innerHTML = `
                <div class="cart-empty" id="cartEmptyState">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                    <p>Your cart is empty.<br>Start adding products above.</p>
                </div>`;

            // Disable checkout
            if (checkoutBtn) {
                checkoutBtn.setAttribute('aria-disabled', 'true');
                checkoutBtn.setAttribute('tabindex', '-1');
            }

            if (cartItemCountLbl) cartItemCountLbl.textContent = '';
            if (cartTotal) cartTotal.textContent = '$0.00';
            return;
        }

        // Build HTML for each item
        const rows = items.map(item => {
            const subtotal  = item.price * item.qty;
            const escapedId = parseInt(item.id, 10);
            // Basic XSS-safe escaping for JS-rendered strings
            const safeName  = escapeHtml(item.name);
            const safeImg   = escapeHtml(item.imageUrl);

            return `
                <div class="cart-item" data-id="${escapedId}">
                    <img
                        class="cart-item-img"
                        src="${safeImg}"
                        alt="${safeName}"
                        onerror="this.src='https://placehold.co/72x72/F7F0E8/C4956A?text=✦'">
                    <div class="cart-item-info">
                        <p class="cart-item-name">${safeName}</p>
                        <div class="qty-controls">
                            <button class="qty-btn" data-action="decrease" data-id="${escapedId}" aria-label="Decrease quantity of ${safeName}">−</button>
                            <span class="qty-value" aria-label="Quantity">${item.qty}</span>
                            <button class="qty-btn" data-action="increase" data-id="${escapedId}" aria-label="Increase quantity of ${safeName}">+</button>
                        </div>
                    </div>
                    <p class="cart-item-price">$${subtotal.toFixed(2)}</p>
                </div>`;
        });

        cartItemsList.innerHTML = rows.join('');

        // Update subtotal
        const total = items.reduce((acc, i) => acc + i.price * i.qty, 0);
        if (cartTotal) cartTotal.textContent = `$${total.toFixed(2)}`;

        // Update item count label
        const totalQty = items.reduce((acc, i) => acc + i.qty, 0);
        if (cartItemCountLbl) {
            cartItemCountLbl.textContent =
                `(${totalQty} item${totalQty !== 1 ? 's' : ''})`;
        }

        // Enable checkout
        if (checkoutBtn) {
            checkoutBtn.removeAttribute('aria-disabled');
            checkoutBtn.removeAttribute('tabindex');
        }
    }

    /**
     * Update the header cart badge count.
     */
    function updateBadge() {
        const totalQty = Object.values(cartStore)
            .reduce((acc, i) => acc + i.qty, 0);

        if (cartBadge) {
            cartBadge.textContent      = totalQty > 0 ? totalQty : '';
            cartBadge.dataset.count    = totalQty;
            cartTrigger?.setAttribute(
                'aria-label',
                `Open shopping cart, ${totalQty} item${totalQty !== 1 ? 's' : ''}`
            );
        }
    }


    /**
     * Show a brief toast notification.
     * @param {string}  message
     * @param {'success'|'error'|''} type
     * @param {number}  duration - milliseconds before removal
     */
    function showToast(message, type = '', duration = 3000) {
        const toast = document.createElement('div');
        toast.className  = `toast ${type}`.trim();
        toast.textContent = message;
        toast.setAttribute('role', 'status');

        toastContainer?.appendChild(toast);

        // Remove after animation completes
        setTimeout(() => toast.remove(), duration);
    }

    /**
     * Escape HTML special characters to prevent XSS.
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        const div       = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    if (!document.querySelector('#jb-spin-style')) {
        const style = document.createElement('style');
        style.id    = 'jb-spin-style';
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }

    seedCartFromDOM();
    updateBadge();

}); // end DOMContentLoaded