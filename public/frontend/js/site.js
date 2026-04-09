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
});
