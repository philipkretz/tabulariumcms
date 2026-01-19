import { Controller } from '@hotwired/stimulus';

/*
 * Search Autocomplete Controller
 *
 * Provides Google/Amazon-style autocomplete search with:
 * - Debounced input (250ms)
 * - Keyboard navigation (arrow keys, enter, escape)
 * - XSS prevention
 * - Loading states
 * - Mobile responsive
 */
export default class extends Controller {
    static targets = ['input', 'results', 'loading'];
    static values = {
        url: String,
        minLength: { type: Number, default: 3 },
        debounce: { type: Number, default: 250 }
    };

    connect() {
        this.debounceTimeout = null;
        this.selectedIndex = -1;
        this.isLoading = false;

        // Close results when clicking outside
        this.boundCloseResults = this.closeResults.bind(this);
        document.addEventListener('click', this.boundCloseResults);
    }

    disconnect() {
        document.removeEventListener('click', this.boundCloseResults);
        if (this.debounceTimeout) {
            clearTimeout(this.debounceTimeout);
        }
    }

    // Handle input event with debouncing
    search(event) {
        event.stopPropagation(); // Prevent closing the dropdown

        const query = this.inputTarget.value.trim();

        // Clear previous timeout
        if (this.debounceTimeout) {
            clearTimeout(this.debounceTimeout);
        }

        // Reset selection
        this.selectedIndex = -1;

        // Hide results if query is too short
        if (query.length < this.minLengthValue) {
            this.hideResults();
            return;
        }

        // Debounce the search request
        this.debounceTimeout = setTimeout(() => {
            this.performSearch(query);
        }, this.debounceValue);
    }

    // Perform the actual search API call
    async performSearch(query) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoading();

        const url = `${this.urlValue}?q=${encodeURIComponent(query)}&limit=5`;

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.success && data.total > 0) {
                this.displayResults(data.data);
            } else {
                this.displayEmptyState();
            }
        } catch (error) {
            console.error('Search error:', error);
            this.displayError();
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }

    // Display search results grouped by type
    displayResults(results) {
        let html = '';

        // Products/Articles
        if (results.articles && results.articles.length > 0) {
            html += this.renderSection('Products', results.articles, 'product');
        }

        // Blog Posts
        if (results.posts && results.posts.length > 0) {
            html += this.renderSection('Blog Posts', results.posts, 'post');
        }

        // Pages
        if (results.pages && results.pages.length > 0) {
            html += this.renderSection('Pages', results.pages, 'page');
        }

        // Categories
        if (results.categories && results.categories.length > 0) {
            html += this.renderSection('Categories', results.categories, 'category');
        }

        this.resultsTarget.innerHTML = html;
        this.showResults();
    }

    // Render a section of results
    renderSection(title, items, type) {
        let html = `<div class="search-section">
            <div class="search-section-title">${this.escapeHtml(title)}</div>`;

        items.forEach(item => {
            html += this.renderItem(item, type);
        });

        html += '</div>';
        return html;
    }

    // Render individual result item
    renderItem(item, type) {
        let html = '<div class="search-result-item">';

        if (type === 'product') {
            html += `<a href="${this.escapeHtml(item.detailUrl)}" class="search-item-link">`;

            if (item.image) {
                html += `<div class="search-item-image">
                    <img src="${this.escapeHtml(item.image)}" alt="${this.escapeHtml(item.imageAlt)}" />
                </div>`;
            }

            html += `<div class="search-item-content">
                <div class="search-item-title">${this.escapeHtml(item.name)}</div>`;

            if (item.description) {
                html += `<div class="search-item-description">${this.escapeHtml(item.description)}</div>`;
            }

            html += `<div class="search-item-meta">
                <span class="search-item-price">${item.grossPrice.toFixed(2)} €</span>`;

            if (item.inStock) {
                html += '<span class="search-item-stock in-stock">In Stock</span>';
            } else {
                html += '<span class="search-item-stock out-of-stock">Out of Stock</span>';
            }

            html += '</div></div></a>';
        } else if (type === 'post') {
            html += `<a href="${this.escapeHtml(item.detailUrl)}" class="search-item-link">
                <div class="search-item-content">
                    <div class="search-item-title">${this.escapeHtml(item.title)}</div>`;

            if (item.excerpt) {
                html += `<div class="search-item-description">${this.escapeHtml(item.excerpt)}</div>`;
            }

            if (item.publishedAt) {
                html += `<div class="search-item-meta">
                    <span class="search-item-date">${item.publishedAt}</span>
                </div>`;
            }

            html += '</div></a>';
        } else if (type === 'page') {
            html += `<a href="${this.escapeHtml(item.detailUrl)}" class="search-item-link">
                <div class="search-item-content">
                    <div class="search-item-title">${this.escapeHtml(item.title)}</div>`;

            if (item.excerpt) {
                html += `<div class="search-item-description">${this.escapeHtml(item.excerpt)}</div>`;
            }

            html += '</div></a>';
        } else if (type === 'category') {
            html += `<a href="${this.escapeHtml(item.detailUrl)}" class="search-item-link">
                <div class="search-item-content">
                    <div class="search-item-title">${this.escapeHtml(item.name)}</div>`;

            if (item.description) {
                html += `<div class="search-item-description">${this.escapeHtml(item.description)}</div>`;
            }

            html += '</div></a>';
        }

        html += '</div>';
        return html;
    }

    // Display empty state
    displayEmptyState() {
        this.resultsTarget.innerHTML = `
            <div class="search-empty-state">
                <svg class="search-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="search-empty-text">No results found</p>
                <p class="search-empty-hint">Try a different search term</p>
            </div>
        `;
        this.showResults();
    }

    // Display error state
    displayError() {
        this.resultsTarget.innerHTML = `
            <div class="search-error-state">
                <p>Error loading search results. Please try again.</p>
            </div>
        `;
        this.showResults();
    }

    // Handle keyboard navigation
    handleKeydown(event) {
        const items = this.resultsTarget.querySelectorAll('.search-result-item');

        switch (event.key) {
            case 'ArrowDown':
                event.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                this.updateSelection(items);
                break;

            case 'ArrowUp':
                event.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection(items);
                break;

            case 'Enter':
                event.preventDefault();
                if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                    const link = items[this.selectedIndex].querySelector('a');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
                break;

            case 'Escape':
                this.hideResults();
                this.inputTarget.blur();
                break;
        }
    }

    // Update visual selection
    updateSelection(items) {
        items.forEach((item, index) => {
            if (index === this.selectedIndex) {
                item.classList.add('selected');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('selected');
            }
        });
    }

    // Show results dropdown
    showResults() {
        this.resultsTarget.classList.remove('search-hidden');
        this.resultsTarget.classList.add('search-visible');
    }

    // Hide results dropdown
    hideResults() {
        this.resultsTarget.classList.remove('search-visible');
        this.resultsTarget.classList.add('search-hidden');
    }

    // Show loading indicator
    showLoading() {
        this.resultsTarget.innerHTML = `
            <div class="search-loading-state">
                <div class="search-loading-spinner"></div>
                <p>Searching...</p>
            </div>
        `;
        this.showResults();
    }

    // Hide loading indicator
    hideLoading() {
        // Loading state is replaced by results
    }

    // Close results when clicking outside
    closeResults(event) {
        if (!this.element.contains(event.target)) {
            this.hideResults();
        }
    }

    // Escape HTML to prevent XSS
    escapeHtml(text) {
        if (!text) return '';

        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
