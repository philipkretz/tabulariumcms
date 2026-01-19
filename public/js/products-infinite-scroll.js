// Infinite Scroll and Lazy Loading for Products with Filter Integration
(function() {
    'use strict';

    let currentPage = 1;
    let isLoading = false;
    let hasMoreProducts = true;
    const productsGrid = document.getElementById('products-grid');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noMoreProducts = document.getElementById('no-more-products');
    const productCountElem = document.getElementById('product-count');
    const productCountCurrent = document.getElementById('product-count-current');
    const productCountTotal = document.getElementById('product-count-total');
    const locale = document.documentElement.lang || 'en';

    if (!productsGrid) {
        console.error('Products grid not found');
        return;
    }

    // Lazy Loading for Images using Intersection Observer
    const lazyLoadObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                const src = img.getAttribute('data-src');
                if (src) {
                    img.src = src;
                    img.classList.remove('lazy-load');
                    img.classList.add('lazy-loaded');
                    observer.unobserve(img);
                }
            }
        });
    }, {
        rootMargin: '50px'
    });

    // Observe all lazy-load images
    function observeLazyImages() {
        document.querySelectorAll('.lazy-load').forEach(img => {
            lazyLoadObserver.observe(img);
        });
    }

    // Initial lazy load observation
    observeLazyImages();

    // Format price with tax info
    function formatPrice(grossPrice, taxRate, locale) {
        const formatter = new Intl.NumberFormat(locale === 'de' ? 'de-DE' : 'en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        const formatted = formatter.format(grossPrice);
        const priceGross = '<span class="price-gross text-2xl font-bold text-green-600">' + formatted + ' €</span>';
        const taxNote = '<span class="price-tax-note text-xs text-gray-600">(incl. ' + taxRate + '% VAT)</span>';
        return priceGross + ' ' + taxNote;
    }

    // Create product card HTML
    function createProductCard(product) {
        const inStock = product.inStock;
        const stockBadge = inStock
            ? '<span class="text-xs text-green-600 font-semibold flex items-center gap-1">' +
              '<span class="w-2 h-2 bg-green-600 rounded-full"></span> In Stock</span>'
            : '<span class="text-xs text-red-600 font-semibold flex items-center gap-1">' +
              '<span class="w-2 h-2 bg-red-600 rounded-full"></span> Out of Stock</span>';

        const imageHtml = product.image
            ? '<img data-src="' + product.image + '" alt="' + product.imageAlt + '" ' +
              'class="w-full h-full object-cover lazy-load" ' +
              'src="data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 400 300\'%3E%3Crect fill=\'%23f3f4f6\' width=\'400\' height=\'300\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-family=\'sans-serif\' font-size=\'24\' fill=\'%239ca3af\'%3ELoading...%3C/text%3E%3C/svg%3E">'
            : '<span class="text-6xl">📦</span>';

        const shortDesc = product.shortDescription
            ? '<p class="text-gray-600 text-sm mb-3 line-clamp-2">' + product.shortDescription + '</p>'
            : '';

        return '<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 border border-amber-100 product-card">' +
            '<div class="bg-gray-100 h-56 flex items-center justify-center">' + imageHtml + '</div>' +
            '<div class="p-5">' +
            '<h3 class="text-lg font-semibold text-gray-800 mb-2 line-clamp-2 min-h-[3.5rem]">' + product.name + '</h3>' +
            shortDesc +
            '<div class="flex items-center justify-between mb-4">' +
            '<div>' + formatPrice(product.grossPrice, product.taxRate, locale) + '</div>' +
            stockBadge +
            '</div>' +
            '<a href="' + product.detailUrl + '" ' +
            'class="block w-full bg-gradient-to-r from-blue-600 to-blue-800 text-white text-center py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-blue-900 transition shadow-md">' +
            'View Details</a>' +
            '</div></div>';
    }

    // Show empty state
    function showEmptyState() {
        productsGrid.innerHTML = `
            <div class="col-span-full text-center py-16">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-4 text-xl font-semibold text-gray-700">No products found</h3>
                <p class="mt-2 text-gray-500">Try adjusting your filters to see more results</p>
                <button onclick="window.productFilters && window.productFilters.reset()"
                        class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Clear All Filters
                </button>
            </div>
        `;
        noMoreProducts.style.display = 'none';
    }

    // Update product count display
    function updateProductCount(current, total) {
        if (productCountCurrent) productCountCurrent.textContent = current;
        if (productCountTotal) productCountTotal.textContent = total;
    }

    // Get filter query string from ProductFilters instance
    function getFilterQueryString() {
        if (window.productFilters) {
            // Update page number in filters state
            window.productFilters.state.page = currentPage;
            return window.productFilters.buildQueryString();
        }
        return 'page=' + currentPage + '&limit=12&sort=newest';
    }

    // Reload products (clear grid and load page 1 with filters)
    async function reloadProducts() {
        console.log('Reloading products with filters');

        // Reset state
        currentPage = 1;
        hasMoreProducts = true;
        isLoading = true;

        // Show loading state
        productsGrid.innerHTML = `
            <div class="col-span-full text-center py-16">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600">Loading products...</p>
            </div>
        `;
        noMoreProducts.style.display = 'none';

        try {
            const queryString = getFilterQueryString();
            const response = await fetch('/api/products?' + queryString);
            const data = await response.json();

            console.log('API response:', data);

            // Clear grid
            productsGrid.innerHTML = '';

            if (data.success && data.data.length > 0) {
                // Add products
                data.data.forEach(product => {
                    const cardHtml = createProductCard(product);
                    productsGrid.insertAdjacentHTML('beforeend', cardHtml);
                });

                // Observe new lazy-load images
                observeLazyImages();

                // Update product count
                updateProductCount(data.data.length, data.pagination.total);

                hasMoreProducts = data.pagination.hasMore;

                if (!hasMoreProducts) {
                    noMoreProducts.style.display = 'block';
                }
            } else {
                // Show empty state
                showEmptyState();
                updateProductCount(0, 0);
            }
        } catch (error) {
            console.error('Error loading products:', error);
            productsGrid.innerHTML = `
                <div class="col-span-full text-center py-16">
                    <svg class="mx-auto h-24 w-24 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-4 text-xl font-semibold text-gray-700">Error loading products</h3>
                    <p class="mt-2 text-gray-500">Please try again later</p>
                    <button onclick="location.reload()"
                            class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Reload Page
                    </button>
                </div>
            `;
            updateProductCount(0, 0);
        } finally {
            isLoading = false;
        }
    }

    // Load more products (infinite scroll)
    async function loadMoreProducts() {
        if (isLoading || !hasMoreProducts) {
            console.log('Skipping load: isLoading=' + isLoading + ', hasMoreProducts=' + hasMoreProducts);
            return;
        }

        console.log('Loading page ' + (currentPage + 1));
        isLoading = true;
        loadingIndicator.style.display = 'block';

        try {
            currentPage++;
            const queryString = getFilterQueryString();
            const response = await fetch('/api/products?' + queryString);
            const data = await response.json();

            console.log('API response:', data);

            if (data.success && data.data.length > 0) {
                data.data.forEach(product => {
                    const cardHtml = createProductCard(product);
                    productsGrid.insertAdjacentHTML('beforeend', cardHtml);
                });

                // Observe new lazy-load images
                observeLazyImages();

                // Update product count
                const totalShown = document.querySelectorAll('.product-card').length;
                updateProductCount(totalShown, data.pagination.total);

                hasMoreProducts = data.pagination.hasMore;
                console.log('Has more products:', hasMoreProducts);

                if (!hasMoreProducts) {
                    noMoreProducts.style.display = 'block';
                }
            } else {
                hasMoreProducts = false;
                noMoreProducts.style.display = 'block';
            }
        } catch (error) {
            console.error('Error loading products:', error);
            hasMoreProducts = false;
            currentPage--; // Revert page increment on error
        } finally {
            isLoading = false;
            loadingIndicator.style.display = 'none';
        }
    }

    // Check if we should load more (scroll position check)
    function checkScrollPosition() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;

        // Trigger when within 300px of bottom
        const scrollThreshold = 300;
        const distanceFromBottom = documentHeight - (scrollTop + windowHeight);

        if (distanceFromBottom < scrollThreshold && !isLoading && hasMoreProducts) {
            console.log('Scroll threshold reached, loading more products');
            loadMoreProducts();
        }
    }

    // Throttle function to limit scroll event frequency
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // Listen for filter changes
    window.addEventListener('filtersChanged', function(e) {
        console.log('Filters changed:', e.detail);
        reloadProducts();
    });

    // Add scroll listener with throttling
    window.addEventListener('scroll', throttle(checkScrollPosition, 200));

    // Also check on page load in case content is short
    window.addEventListener('load', function() {
        setTimeout(checkScrollPosition, 500);
    });

    // Check immediately after DOM content loaded
    if (document.readyState === 'complete') {
        setTimeout(checkScrollPosition, 500);
    }

    console.log('Infinite scroll initialized with filter integration');
})();
