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

            populateSelect(
                regionSelect,
                optionSet.regions || [],
                'All',
                keepCurrentValues ? regionSelect.value : regionSelect.getAttribute('data-selected') || 'all',
                'all'
            );

            populateSelect(
                typeSelect,
                optionSet.types || [],
                'Any',
                keepCurrentValues ? typeSelect.value : typeSelect.getAttribute('data-selected') || ''
            );
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
            return item.room_name ? item.room_name + ' · ' + getQuantityText(item) : getQuantityText(item);
        }

        if (item.type === 'activity') {
            return item.variant_name ? item.variant_name + ' · ' + getQuantityText(item) : getQuantityText(item);
        }

        return getQuantityText(item);
    }

    function getItemDateLabel(item) {
        if (item.type === 'accommodation') {
            return item.check_in_display && item.check_out_display ? item.check_in_display + ' → ' + item.check_out_display : '';
        }

        if (item.type === 'activity') {
            return item.activity_date ? 'Date: ' + item.activity_date : '';
        }

        return '';
    }

    function updateHeaderCartCount(count) {
        if (headerCartCount) {
            headerCartCount.textContent = Number(count || 0);
        }

        if (miniCartCountText) {
            const itemCount = Number(count || 0);
            miniCartCountText.textContent = itemCount === 1 ? '1 item in cart' : itemCount + ' items in cart';
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

        if (cartItems.length === 0) {
            miniCartEmpty.style.display = 'block';
            return;
        }

        miniCartEmpty.style.display = 'none';

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

            const details = document.createElement('div');
            details.className = 'mini-cart-item-details';

            const meta = document.createElement('div');
            meta.className = 'mini-cart-item-meta';
            meta.innerHTML = `<strong>${getItemSubtitle(item)}</strong>${getItemDateLabel(item) ? '<br>' + getItemDateLabel(item) : ''}`;

            const price = document.createElement('div');
            price.className = 'mini-cart-item-price';
            price.textContent = formatMoney(item.net_amount || item.total_price || 0, item.currency || 'USD');

            details.appendChild(meta);
            details.appendChild(price);

            itemWrapper.appendChild(imageWrap);
            itemWrapper.appendChild(details);
            miniCartItems.appendChild(itemWrapper);
        });

        document.getElementById('miniCartSubtotal').textContent = formatMoney(summary.subtotal || 0, summary.currency || 'USD');
        document.getElementById('miniCartDiscount').textContent = formatMoney(summary.total_discount || 0, summary.currency || 'USD');
        document.getElementById('miniCartTaxFees').textContent = formatMoney((summary.total_tax || 0) + (summary.total_fees || 0), summary.currency || 'USD');
        document.getElementById('miniCartTotal').textContent = formatMoney(summary.net_payable || 0, summary.currency || 'USD');
    }

    async function fetchMiniCart(openOnLoad = false, message = '') {
        try {
            const response = await fetch('/booking/cart', {
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
            if (openOnLoad) {
                showMiniCart(message || 'Item added to cart.');
            }
        } catch (error) {
            console.error(error);
            if (openOnLoad) {
                showMiniCart('Item added to cart. Unable to load cart details.', 'error');
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
            showMiniCart(payload.message || 'Item added to cart.');
        } catch (error) {
            showMiniCart(error.message || 'Unable to add item to cart.', 'error');
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
            fetchMiniCart(true);
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

