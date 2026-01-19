// Product Filters and Sort Management
(function() {
    'use strict';

    class ProductFilters {
        constructor() {
            this.state = {
                priceMin: null,
                priceMax: null,
                categories: [],
                type: null,
                inStock: false,
                sort: 'newest',
                page: 1
            };

            this.categoriesCache = null;
            this.typesCache = null;

            this.loadFromURL();
            this.initializeFilters();
        }

        // Load filter state from URL parameters
        loadFromURL() {
            const params = new URLSearchParams(window.location.search);

            if (params.has('price_min')) {
                this.state.priceMin = params.get('price_min');
            }
            if (params.has('price_max')) {
                this.state.priceMax = params.get('price_max');
            }
            // Check for both categories and categories[] formats
            if (params.has('categories[]')) {
                this.state.categories = params.getAll('categories[]');
            } else if (params.has('categories')) {
                this.state.categories = params.getAll('categories');
            }
            if (params.has('type')) {
                this.state.type = params.get('type');
            }
            if (params.has('in_stock')) {
                this.state.inStock = params.get('in_stock') === 'true';
            }
            if (params.has('sort')) {
                this.state.sort = params.get('sort');
            }
        }

        // Initialize filters by fetching categories and types, then binding events
        async initializeFilters() {
            try {
                // Fetch categories and types in parallel
                await Promise.all([
                    this.fetchCategories(),
                    this.fetchTypes()
                ]);

                // Populate UI
                this.populateCategoriesDropdown();
                this.populateTypesDropdown();

                // Restore filter state from URL
                this.restoreFilterUI();

                // Bind event listeners
                this.bindEvents();

                // Update active filters display
                this.updateActiveFilters();

                console.log('Product filters initialized');
            } catch (error) {
                console.error('Error initializing filters:', error);
            }
        }

        // Fetch categories from API
        async fetchCategories() {
            if (this.categoriesCache) {
                return this.categoriesCache;
            }

            const response = await fetch('/api/products/categories');
            const data = await response.json();

            if (data.success) {
                this.categoriesCache = data.data;
                return data.data;
            }
            throw new Error('Failed to fetch categories');
        }

        // Fetch product types from API
        async fetchTypes() {
            if (this.typesCache) {
                return this.typesCache;
            }

            const response = await fetch('/api/products/types');
            const data = await response.json();

            if (data.success) {
                this.typesCache = data.data;
                return data.data;
            }
            throw new Error('Failed to fetch types');
        }

        // Populate categories dropdown with checkboxes
        populateCategoriesDropdown() {
            const menu = document.getElementById('category-dropdown-menu');
            if (!menu || !this.categoriesCache) return;

            menu.innerHTML = this.categoriesCache.map(category => `
                <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
                    <input type="checkbox"
                           class="form-checkbox h-4 w-4 text-blue-600 rounded category-checkbox"
                           value="${category.id}"
                           data-name="${category.name}">
                    <span class="ml-2 text-sm text-gray-700">${category.name}</span>
                    <span class="ml-auto text-xs text-gray-500">(${category.productCount})</span>
                </label>
            `).join('');
        }

        // Populate types dropdown
        populateTypesDropdown() {
            const select = document.getElementById('type-select');
            if (!select || !this.typesCache) return;

            const options = this.typesCache.map(type =>
                `<option value="${type.value}">${type.label}</option>`
            ).join('');

            select.innerHTML = '<option value="">All Types</option>' + options;
        }

        // Restore filter UI from current state
        restoreFilterUI() {
            // Price inputs
            if (this.state.priceMin) {
                document.getElementById('price-min').value = this.state.priceMin;
            }
            if (this.state.priceMax) {
                document.getElementById('price-max').value = this.state.priceMax;
            }

            // Categories checkboxes
            this.state.categories.forEach(catId => {
                const checkbox = document.querySelector(`.category-checkbox[value="${catId}"]`);
                if (checkbox) checkbox.checked = true;
            });
            this.updateCategoryButtonText();

            // Type select
            if (this.state.type) {
                document.getElementById('type-select').value = this.state.type;
            }

            // In stock checkbox
            document.getElementById('in-stock-checkbox').checked = this.state.inStock;

            // Sort select
            document.getElementById('sort-select').value = this.state.sort;
        }

        // Bind event listeners to filter controls
        bindEvents() {
            // Sort dropdown
            document.getElementById('sort-select').addEventListener('change', (e) => {
                this.state.sort = e.target.value;
                this.applyFilters();
            });

            // Price inputs with debouncing
            let priceDebounceTimer;
            const priceMinInput = document.getElementById('price-min');
            const priceMaxInput = document.getElementById('price-max');

            priceMinInput.addEventListener('input', () => {
                clearTimeout(priceDebounceTimer);
                priceDebounceTimer = setTimeout(() => {
                    this.state.priceMin = priceMinInput.value || null;
                    this.applyFilters();
                }, 300);
            });

            priceMaxInput.addEventListener('input', () => {
                clearTimeout(priceDebounceTimer);
                priceDebounceTimer = setTimeout(() => {
                    this.state.priceMax = priceMaxInput.value || null;
                    this.applyFilters();
                }, 300);
            });

            // Category dropdown toggle
            const categoryBtn = document.getElementById('category-dropdown-btn');
            const categoryMenu = document.getElementById('category-dropdown-menu');

            categoryBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                categoryMenu.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('#category-filter')) {
                    categoryMenu.classList.add('hidden');
                }
            });

            // Category checkboxes
            categoryMenu.addEventListener('change', (e) => {
                if (e.target.classList.contains('category-checkbox')) {
                    const checkboxes = document.querySelectorAll('.category-checkbox:checked');
                    this.state.categories = Array.from(checkboxes).map(cb => cb.value);
                    this.updateCategoryButtonText();
                    this.applyFilters();
                }
            });

            // Type dropdown
            document.getElementById('type-select').addEventListener('change', (e) => {
                this.state.type = e.target.value || null;
                this.applyFilters();
            });

            // In stock checkbox
            document.getElementById('in-stock-checkbox').addEventListener('change', (e) => {
                this.state.inStock = e.target.checked;
                this.applyFilters();
            });

            // Clear filters button
            document.getElementById('clear-filters-btn').addEventListener('click', () => {
                this.reset();
            });
        }

        // Update category button text
        updateCategoryButtonText() {
            const btn = document.getElementById('category-btn-text');
            if (!btn) return;

            const count = this.state.categories.length;
            if (count === 0) {
                btn.textContent = 'All Categories';
            } else if (count === 1) {
                const checkbox = document.querySelector(`.category-checkbox[value="${this.state.categories[0]}"]`);
                btn.textContent = checkbox ? checkbox.dataset.name : '1 Category';
            } else {
                btn.textContent = `${count} Categories`;
            }
        }

        // Apply filters - update URL and reload products
        applyFilters() {
            this.state.page = 1;
            this.updateURL();
            this.updateActiveFilters();

            // Trigger product reload via window event
            window.dispatchEvent(new CustomEvent('filtersChanged', {
                detail: this.buildQueryString()
            }));
        }

        // Reset all filters
        reset() {
            this.state = {
                priceMin: null,
                priceMax: null,
                categories: [],
                type: null,
                inStock: false,
                sort: 'newest',
                page: 1
            };

            // Clear UI
            document.getElementById('price-min').value = '';
            document.getElementById('price-max').value = '';
            document.querySelectorAll('.category-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('type-select').value = '';
            document.getElementById('in-stock-checkbox').checked = false;
            document.getElementById('sort-select').value = 'newest';

            this.updateCategoryButtonText();
            this.updateURL();
            this.updateActiveFilters();

            // Trigger product reload
            window.dispatchEvent(new CustomEvent('filtersChanged', {
                detail: this.buildQueryString()
            }));
        }

        // Update URL with current filter state
        updateURL() {
            const params = new URLSearchParams();

            if (this.state.priceMin) params.set('price_min', this.state.priceMin);
            if (this.state.priceMax) params.set('price_max', this.state.priceMax);
            this.state.categories.forEach(cat => params.append('categories[]', cat));
            if (this.state.type) params.set('type', this.state.type);
            if (this.state.inStock) params.set('in_stock', 'true');
            if (this.state.sort !== 'newest') params.set('sort', this.state.sort);

            const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.replaceState({}, '', url);
        }

        // Build query string for API calls
        buildQueryString() {
            const params = new URLSearchParams();

            if (this.state.priceMin) params.set('price_min', this.state.priceMin);
            if (this.state.priceMax) params.set('price_max', this.state.priceMax);
            this.state.categories.forEach(cat => params.append('categories[]', cat));
            if (this.state.type) params.set('type', this.state.type);
            if (this.state.inStock) params.set('in_stock', 'true');
            params.set('sort', this.state.sort);
            params.set('page', this.state.page);
            params.set('limit', 12);

            return params.toString();
        }

        // Get active filters for badge display
        getActiveFilters() {
            const filters = [];

            if (this.state.priceMin || this.state.priceMax) {
                const priceLabel = this.state.priceMin && this.state.priceMax
                    ? `Price: €${this.state.priceMin} - €${this.state.priceMax}`
                    : this.state.priceMin
                        ? `Min Price: €${this.state.priceMin}`
                        : `Max Price: €${this.state.priceMax}`;
                filters.push({
                    label: priceLabel,
                    remove: () => {
                        this.state.priceMin = null;
                        this.state.priceMax = null;
                        document.getElementById('price-min').value = '';
                        document.getElementById('price-max').value = '';
                        this.applyFilters();
                    }
                });
            }

            this.state.categories.forEach(catId => {
                const checkbox = document.querySelector(`.category-checkbox[value="${catId}"]`);
                if (checkbox) {
                    filters.push({
                        label: checkbox.dataset.name,
                        remove: () => {
                            checkbox.checked = false;
                            this.state.categories = this.state.categories.filter(id => id !== catId);
                            this.updateCategoryButtonText();
                            this.applyFilters();
                        }
                    });
                }
            });

            if (this.state.type && this.typesCache) {
                const typeObj = this.typesCache.find(t => t.value === this.state.type);
                if (typeObj) {
                    filters.push({
                        label: typeObj.label,
                        remove: () => {
                            this.state.type = null;
                            document.getElementById('type-select').value = '';
                            this.applyFilters();
                        }
                    });
                }
            }

            if (this.state.inStock) {
                filters.push({
                    label: 'In Stock Only',
                    remove: () => {
                        this.state.inStock = false;
                        document.getElementById('in-stock-checkbox').checked = false;
                        this.applyFilters();
                    }
                });
            }

            return filters;
        }

        // Update active filter badges display
        updateActiveFilters() {
            const container = document.getElementById('active-filters');
            if (!container) return;

            const filters = this.getActiveFilters();

            if (filters.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'flex';
            container.innerHTML = filters.map((filter, index) => `
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800
                             rounded-full text-sm font-medium">
                    ${filter.label}
                    <button onclick="window.productFilters.removeFilterAtIndex(${index})"
                            class="hover:bg-blue-200 rounded-full p-0.5 ml-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </span>
            `).join('');
        }

        // Remove filter at specific index (called from badge buttons)
        removeFilterAtIndex(index) {
            const filters = this.getActiveFilters();
            if (filters[index]) {
                filters[index].remove();
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.productFilters = new ProductFilters();
        });
    } else {
        window.productFilters = new ProductFilters();
    }
})();
