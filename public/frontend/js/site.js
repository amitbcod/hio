// Get base URL from meta tag
const baseUrl = document.querySelector('meta[name="app-base-url"]')?.content || '';

function getApiUrl(path) {
    // Remove leading slash if present
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;
    return baseUrl ? baseUrl.replace(/\/$/, '') + '/' + cleanPath : '/' + cleanPath;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-search-options]').forEach(function (form) {
        let searchOptions = {};

        try {
            searchOptions = JSON.parse(form.getAttribute('data-search-options') || '{}');
        } catch (error) {
            searchOptions = {};
        }

        const categoryInputs = form.querySelectorAll('input[name="category"]');
        const regionSelect = form.querySelector('[data-search-region]');
        const typeSelect = form.querySelector('[data-search-type]');

        function populateSelect(select, items, placeholder, selectedValue, placeholderValue = '') {
            if (!select) {
                return;
            }

            const currentValue = selectedValue !== undefined ? selectedValue : select.value;
            select.innerHTML = '';

            const placeholderOption = document.createElement('option');
            placeholderOption.value = placeholderValue;
            placeholderOption.textContent = placeholder;
            select.appendChild(placeholderOption);

            items.forEach(function (item) {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                select.appendChild(option);
            });

            const hasMatch = Array.from(select.options).some(function (option) {
                return option.value === currentValue;
            });

            select.value = hasMatch ? currentValue : placeholderValue;
        }

        function updateSearchOptions(keepCurrentValues) {
            const activeCategory = form.querySelector('input[name="category"]:checked');
            const category = activeCategory ? activeCategory.value : 'accommodation';
            const optionSet = searchOptions[category] || { regions: [], types: [] };

            if (regionSelect) {
                populateSelect(
                    regionSelect,
                    optionSet.regions || [],
                    'All',
                    keepCurrentValues ? regionSelect.value : (regionSelect.getAttribute('data-selected') || 'all'),
                    'all'
                );
            }

            if (typeSelect) {
                populateSelect(
                    typeSelect,
                    optionSet.types || [],
                    'Any',
                    keepCurrentValues ? typeSelect.value : (typeSelect.getAttribute('data-selected') || '')
                );
            }
        }

        categoryInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                updateSearchOptions(true);
            });
        });

        updateSearchOptions(false);
    });

    const tabButtons = document.querySelectorAll('[data-tab-target]');
    const tabPanels = document.querySelectorAll('[data-tab-panel]');

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const target = button.getAttribute('data-tab-target');

            tabButtons.forEach(function (item) {
                item.classList.remove('is-active');
            });

            tabPanels.forEach(function (panel) {
                panel.classList.remove('is-active');
            });

            button.classList.add('is-active');
            const targetPanel = document.querySelector('[data-tab-panel="' + target + '"]');
            if (targetPanel) {
                targetPanel.classList.add('is-active');
            }
        });
    });

    const slides = document.querySelectorAll('[data-hero-slide]');
    const dots = document.querySelectorAll('[data-hero-dot]');
    let currentSlide = 0;
    let autoPlay = null;

    function showSlide(index) {
        if (!slides.length) {
            return;
        }

        currentSlide = index;

        slides.forEach(function (slide, slideIndex) {
            slide.classList.toggle('is-active', slideIndex === currentSlide);
        });

        dots.forEach(function (dot, dotIndex) {
            dot.classList.toggle('is-active', dotIndex === currentSlide);
        });
    }

    function startAutoPlay() {
        if (slides.length <= 1) {
            return;
        }

        stopAutoPlay();
        autoPlay = window.setInterval(function () {
            const nextIndex = (currentSlide + 1) % slides.length;
            showSlide(nextIndex);
        }, 5000);
    }

    function stopAutoPlay() {
        if (autoPlay) {
            window.clearInterval(autoPlay);
            autoPlay = null;
        }
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            const index = Number(dot.getAttribute('data-hero-dot'));
            showSlide(index);
            startAutoPlay();
        });
    });

    const hero = document.querySelector('[data-hero-slider]');
    if (hero) {
        hero.addEventListener('mouseenter', stopAutoPlay);
        hero.addEventListener('mouseleave', startAutoPlay);
    }

    showSlide(0);
    startAutoPlay();
    initMiniCart();
});

function initMiniCart() {
    const miniCartOverlay = document.getElementById('miniCartOverlay');
    const miniCartItems = document.getElementById('miniCartItems');
    const miniCartEmpty = document.getElementById('miniCartEmpty');
    const miniCartMessage = document.getElementById('miniCartMessage');
    const miniCartCountText = document.getElementById('miniCartCountText');
    const headerCartCount = document.getElementById('headerCartCount');
    const headerCartToggle = document.getElementById('headerCartToggle');
    const closeMiniCartBtn = document.getElementById('closeMiniCartBtn');

    // Localised messages provided via data attributes on the mini cart overlay
    const messages = {
        itemAdded: miniCartOverlay?.dataset.msgItemAdded || 'Item added to cart.',
        itemRemoved: miniCartOverlay?.dataset.msgItemRemoved || 'Item removed from cart.',
        cartEmpty: miniCartOverlay?.dataset.msgCartEmpty || 'Your cart is empty',
        unableLoad: miniCartOverlay?.dataset.msgUnableLoad || 'Item added to cart. Unable to load cart details.',
        unableAdd: miniCartOverlay?.dataset.msgUnableAdd || 'Unable to add item to cart.',
        unableRemove: miniCartOverlay?.dataset.msgUnableRemove || 'Unable to remove item from cart.'
    };

    function formatMoney(amount, currency = 'USD') {
        return currency + ' ' + Number(amount || 0).toFixed(2);
    }

    function getQuantityText(item) {
        if (item.type === 'accommodation') {
            const rooms = parseInt(item.rooms || 1, 10);
            return rooms + ' room' + (rooms !== 1 ? 's' : '');
        }

        if (item.type === 'activity') {
            const participants = parseInt(item.participants || 1, 10);
            return participants + ' participant' + (participants !== 1 ? 's' : '');
        }

        return 'Qty: 1';
    }

    function getItemSubtitle(item) {
        if (item.type === 'accommodation') {
            return item.room_name ? item.room_name : 'Accommodation';
        }

        if (item.type === 'activity') {
            return item.variant_name ? item.variant_name : 'Activity';
        }

        return 'Item';
    }

    function getItemDateLabel(item) {
        if (item.type === 'accommodation') {
            return item.check_in_display && item.check_out_display ? item.check_in_display + ' → ' + item.check_out_display : '';
        }

        if (item.type === 'activity') {
            return item.check_in_display ? 'Date: ' + item.check_in_display : '';
        }

        return '';
    }

    function getItemGuestDetails(item) {
        const details = [];
        const adults = Math.max(0, parseInt(item.adults, 10) || 0);
        const children = Math.max(0, parseInt(item.children, 10) || 0);
        let infants = Math.max(0, parseInt(item.infants, 10) || 0);
        const participants = parseInt(item.participants, 10);

        if (infants === 0 && Number.isInteger(participants) && participants > adults + children) {
            infants = participants - adults - children;
        }

        if (adults > 0) {
            details.push(adults + ' Adult' + (adults !== 1 ? 's' : ''));
        }
        if (children > 0) {
            details.push(children + ' Child' + (children !== 1 ? 'ren' : ''));
        }
        if (infants > 0) {
            details.push(infants + ' Infant' + (infants !== 1 ? 's' : ''));
        }
        return details.length > 0 ? details.join(', ') : '';
    }

    function updateHeaderCartCount(count) {
        if (headerCartCount) {
            headerCartCount.textContent = Number(count || 0);
        }

        if (miniCartCountText) {
            const itemCount = Number(count || 0);
            // Use localized strings provided by the server via data attributes
            const zeroText = miniCartOverlay?.dataset.cartItemsZero || 'No items in cart';
            const oneText = miniCartOverlay?.dataset.cartItemsOne || '1 item in cart';
            const manyText = miniCartOverlay?.dataset.cartItemsMany || ':count items in cart';

            if (itemCount === 0) {
                miniCartCountText.textContent = zeroText;
            } else if (itemCount === 1) {
                miniCartCountText.textContent = oneText;
            } else {
                miniCartCountText.textContent = manyText.replace(':count', itemCount);
            }
        }
    }

    function showMiniCart(message, type = 'success') {
        if (!miniCartOverlay) {
            return;
        }

        if (miniCartMessage) {
            miniCartMessage.textContent = message || '';
            miniCartMessage.className = 'mini-cart-message';
            if (message) {
                miniCartMessage.classList.add(type === 'error' ? 'error' : 'success');
                miniCartMessage.style.display = 'block';
            } else {
                miniCartMessage.style.display = 'none';
            }
        }

        miniCartOverlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        miniCartOverlay.setAttribute('aria-hidden', 'false');
    }

    function closeMiniCart() {
        if (!miniCartOverlay) {
            return;
        }

        miniCartOverlay.style.display = 'none';
        document.body.style.overflow = 'auto';
        miniCartOverlay.setAttribute('aria-hidden', 'true');
    }

    function renderMiniCart(data) {
        const cartItems = Array.isArray(data?.cart) ? data.cart : [];
        const summary = data?.summary || {};

        updateHeaderCartCount(summary.item_count || cartItems.length);

        if (!miniCartItems || !miniCartEmpty) {
            return;
        }

        miniCartItems.innerHTML = '';

        const miniCartSummary = document.getElementById('miniCartSummary');
        const miniCartActions = document.querySelector('.mini-cart-actions');

        if (cartItems.length === 0) {
            miniCartEmpty.style.display = 'block';
            if (miniCartSummary) miniCartSummary.style.display = 'none';
            if (miniCartActions) miniCartActions.style.display = 'none';
            return;
        }

        miniCartEmpty.style.display = 'none';
        if (miniCartSummary) miniCartSummary.style.display = 'block';
        if (miniCartActions) miniCartActions.style.display = 'flex';

        cartItems.forEach(function (item) {
            const itemWrapper = document.createElement('div');
            itemWrapper.className = 'mini-cart-item';

            const imageWrap = document.createElement('div');
            imageWrap.className = 'mini-cart-item-image';
            const image = document.createElement('img');
            image.src = item.image || '/images/placeholder.jpg';
            image.alt = item.title || (item.type === 'accommodation' ? 'Accommodation' : 'Activity');
            imageWrap.appendChild(image);

            const imageLabel = document.createElement('p');
            imageLabel.className = 'mini-cart-item-image-label';
            imageLabel.textContent = item.title || (item.type === 'accommodation' ? 'Accommodation' : 'Activity');
            imageWrap.appendChild(imageLabel);

            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'mini-cart-item-delete';
            deleteBtn.innerHTML = '×';
            deleteBtn.setAttribute('title', 'Remove item');
            deleteBtn.onclick = function (e) {
                e.preventDefault();
                removeFromCart(item.cart_key || item.id);
            };
            imageWrap.appendChild(deleteBtn);

            const details = document.createElement('div');
            details.className = 'mini-cart-item-details';

            const title = document.createElement('div');
            title.className = 'mini-cart-item-title';
            title.innerHTML = `<strong>${getItemSubtitle(item)}</strong>`;
            details.appendChild(title);

            const dateLabel = getItemDateLabel(item);
            if (dateLabel) {
                const date = document.createElement('div');
                date.className = 'mini-cart-item-date';
                date.textContent = dateLabel;
                details.appendChild(date);
            }

            const guestDetails = getItemGuestDetails(item);
            if (guestDetails) {
                const guests = document.createElement('div');
                guests.className = 'mini-cart-item-guests';
                guests.textContent = guestDetails;
                details.appendChild(guests);
            }

            const price = document.createElement('div');
            price.className = 'mini-cart-item-price';
            price.textContent = formatMoney(item.net_amount || item.total_price || 0, item.currency || 'USD');

            itemWrapper.appendChild(imageWrap);
            itemWrapper.appendChild(details);
            itemWrapper.appendChild(price);
            miniCartItems.appendChild(itemWrapper);
        });

        document.getElementById('miniCartSubtotal').textContent = formatMoney(summary.subtotal || 0, summary.currency || 'USD');
        document.getElementById('miniCartDiscount').textContent = formatMoney(summary.total_discount || 0, summary.currency || 'USD');
        document.getElementById('miniCartTaxFees').textContent = formatMoney((summary.total_tax || 0) + (summary.total_fees || 0), summary.currency || 'USD');
        document.getElementById('miniCartTotal').textContent = formatMoney(summary.net_payable || 0, summary.currency || 'USD');
    }

    async function fetchMiniCart(showPopup = false, message = '') {
        try {
            const response = await fetch(getApiUrl('/booking/cart'), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Unable to load cart preview.');
            }

            const data = await response.json();
            renderMiniCart(data);
            
            if (showPopup) {
                const cartItems = Array.isArray(data?.cart) ? data.cart : [];
                    if (cartItems.length === 0) {
                        showMiniCart(messages.cartEmpty);
                    } else {
                        showMiniCart(message || '');
                    }
            }
        } catch (error) {
            console.error(error);
            if (showPopup) {
                showMiniCart(messages.unableLoad, 'error');
            }
        }
    }

    async function handleAddToCart(event) {
        event.preventDefault();
        const form = event.currentTarget;
        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Unable to add item to cart.');
            }

            renderMiniCart(payload);
            showMiniCart(payload.message || messages.itemAdded);
        } catch (error) {
            showMiniCart(error.message || messages.unableAdd, 'error');
        }
    }

    async function removeFromCart(cartKey) {
        try {
            const response = await fetch(getApiUrl('/booking/cart/remove'), {
                method: 'POST',
                body: JSON.stringify({ cart_key: cartKey }),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            });

            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Unable to remove item from cart.');
            }

            renderMiniCart(payload);
            showMiniCart(payload.message || messages.itemRemoved);
        } catch (error) {
            showMiniCart(error.message || messages.unableRemove, 'error');
        }
    }

    document.querySelectorAll('form').forEach(function (form) {
        if (form.action && form.action.trim().endsWith('/booking/cart/add')) {
            form.addEventListener('submit', handleAddToCart);
        }
    });

    if (headerCartToggle) {
        headerCartToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            fetchMiniCart(true);
            return false;
        });
    }

    if (closeMiniCartBtn) {
        closeMiniCartBtn.addEventListener('click', closeMiniCart);
    }

    if (miniCartOverlay) {
        miniCartOverlay.addEventListener('click', function (event) {
            if (event.target === miniCartOverlay) {
                closeMiniCart();
            }
        });
    }

    fetchMiniCart(false);
}

