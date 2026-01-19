<template>
  <div class="product-list">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h3 class="text-lg font-semibold mb-4">Filters</h3>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            type="text"
            placeholder="Search products..."
            class="form-input"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
          <select v-model="filters.category" @change="loadProducts" class="form-input">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
          <select v-model="filters.sort" @change="loadProducts" class="form-input">
            <option value="newest">Newest</option>
            <option value="price_asc">Price: Low to High</option>
            <option value="price_desc">Price: High to Low</option>
            <option value="name_asc">Name: A-Z</option>
            <option value="featured">Featured</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">In Stock Only</label>
          <label class="flex items-center cursor-pointer">
            <input
              v-model="filters.inStock"
              @change="loadProducts"
              type="checkbox"
              class="w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
            />
            <span class="ml-2 text-sm">Show only available items</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Products Grid -->
    <div v-else-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <product-card
        v-for="product in products"
        :key="product.id"
        :product="product"
        @add-to-cart="addToCart"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow-md p-12 text-center">
      <div class="text-6xl mb-4">🔍</div>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
      <p class="text-gray-600">Try adjusting your filters or search query</p>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.totalPages > 1" class="flex justify-center items-center gap-2 mt-8">
      <button
        @click="changePage(pagination.page - 1)"
        :disabled="pagination.page === 1"
        class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Previous
      </button>

      <button
        v-for="page in visiblePages"
        :key="page"
        @click="changePage(page)"
        :class="[
          'px-4 py-2 rounded-lg',
          page === pagination.page
            ? 'bg-primary-600 text-white'
            : 'border hover:bg-gray-50'
        ]"
      >
        {{ page }}
      </button>

      <button
        @click="changePage(pagination.page + 1)"
        :disabled="pagination.page === pagination.totalPages"
        class="px-4 py-2 border rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
      >
        Next
      </button>
    </div>

    <!-- Toast Notification -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div
        v-if="toast.show"
        :class="[
          'fixed bottom-4 right-4 px-6 py-4 rounded-lg shadow-lg text-white z-50',
          toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'
        ]"
      >
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import ProductCard from './ProductCard.vue';

const props = defineProps({
  apiEndpoint: {
    type: String,
    default: '/api/articles/public'
  }
});

const emit = defineEmits(['cart-updated']);

const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const searchTimeout = ref(null);

const filters = reactive({
  search: '',
  category: '',
  sort: 'newest',
  inStock: false
});

const pagination = reactive({
  page: 1,
  limit: 12,
  total: 0,
  totalPages: 0
});

const toast = reactive({
  show: false,
  message: '',
  type: 'success'
});

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, pagination.page - Math.floor(maxVisible / 2));
  let end = Math.min(pagination.totalPages, start + maxVisible - 1);

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

const loadProducts = async () => {
  loading.value = true;

  try {
    const params = new URLSearchParams({
      page: pagination.page,
      limit: pagination.limit,
      sort: filters.sort
    });

    if (filters.search) params.append('search', filters.search);
    if (filters.category) params.append('category', filters.category);
    if (filters.inStock) params.append('in_stock', 'true');

    const response = await fetch(`${props.apiEndpoint}?${params}`);
    const data = await response.json();

    products.value = data.data || [];
    pagination.total = data.pagination.total;
    pagination.totalPages = data.pagination.pages;
  } catch (error) {
    console.error('Error loading products:', error);
    showToast('Failed to load products', 'error');
  } finally {
    loading.value = false;
  }
};

const loadCategories = async () => {
  try {
    const response = await fetch('/api/articles/public/categories');
    const data = await response.json();
    categories.value = data;
  } catch (error) {
    console.error('Error loading categories:', error);
  }
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout.value);
  searchTimeout.value = setTimeout(() => {
    pagination.page = 1;
    loadProducts();
  }, 500);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.totalPages) {
    pagination.page = page;
    loadProducts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const addToCart = async (product) => {
  try {
    const response = await fetch('/api/cart/add', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        article_id: product.id,
        quantity: 1
      })
    });

    const data = await response.json();

    if (data.success) {
      showToast(`${product.name} added to cart`, 'success');
      emit('cart-updated', data.cart);
    } else {
      showToast(data.error || 'Failed to add to cart', 'error');
    }
  } catch (error) {
    console.error('Error adding to cart:', error);
    showToast('Error adding to cart', 'error');
  }
};

const showToast = (message, type = 'success') => {
  toast.message = message;
  toast.type = type;
  toast.show = true;

  setTimeout(() => {
    toast.show = false;
  }, 3000);
};

onMounted(() => {
  loadProducts();
  loadCategories();
});
</script>
