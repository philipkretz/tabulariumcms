<template>
  <div class="relative">
    <button
      @click="toggleCart"
      class="relative p-2 text-gray-700 hover:text-primary-600 transition-colors"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
      <span
        v-if="itemCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
      >
        {{ itemCount }}
      </span>
    </button>

    <!-- Cart Dropdown -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-xl z-50"
      >
        <div class="p-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Shopping Cart</h3>
        </div>

        <div v-if="items.length === 0" class="p-8 text-center">
          <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <p class="text-gray-500">Your cart is empty</p>
        </div>

        <div v-else class="max-h-96 overflow-y-auto">
          <div
            v-for="item in items"
            :key="item.id"
            class="p-4 border-b border-gray-100 hover:bg-gray-50"
          >
            <div class="flex items-center space-x-4">
              <img
                v-if="item.article.image"
                :src="item.article.image"
                :alt="item.article.name"
                class="w-16 h-16 object-cover rounded"
              />
              <div class="flex-1 min-w-0">
                <h4 class="text-sm font-medium text-gray-900 truncate">{{ item.article.name }}</h4>
                <p class="text-sm text-gray-500">{{ formatPrice(item.price) }} x {{ item.quantity }}</p>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="updateQuantity(item.id, item.quantity - 1)"
                  class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-gray-900"
                >
                  -
                </button>
                <span class="text-sm font-medium">{{ item.quantity }}</span>
                <button
                  @click="updateQuantity(item.id, item.quantity + 1)"
                  class="w-6 h-6 flex items-center justify-center text-gray-600 hover:text-gray-900"
                >
                  +
                </button>
                <button
                  @click="removeItem(item.id)"
                  class="ml-2 text-red-500 hover:text-red-700"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="items.length > 0" class="p-4 border-t border-gray-200 bg-gray-50">
          <div class="flex justify-between mb-4">
            <span class="font-medium text-gray-900">Subtotal:</span>
            <span class="font-bold text-xl text-gray-900">{{ formatPrice(subtotal) }}</span>
          </div>
          <a
            href="/checkout"
            class="block w-full py-3 px-4 bg-primary-600 text-white text-center font-medium rounded-lg hover:bg-primary-700 transition-colors"
          >
            Proceed to Checkout
          </a>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const isOpen = ref(false);
const items = ref([]);
const subtotal = ref(0);

const itemCount = computed(() => {
  return items.value.reduce((total, item) => total + item.quantity, 0);
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR'
  }).format(price);
};

const toggleCart = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    loadCart();
  }
};

const loadCart = async () => {
  try {
    const response = await fetch('/api/cart');
    const data = await response.json();
    items.value = data.items || [];
    subtotal.value = data.totals?.subtotal || 0;
  } catch (error) {
    console.error('Error loading cart:', error);
  }
};

const updateQuantity = async (itemId, newQuantity) => {
  if (newQuantity < 1) return;

  try {
    const response = await fetch(`/api/cart/update/${itemId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ quantity: newQuantity })
    });

    if (response.ok) {
      await loadCart();
    }
  } catch (error) {
    console.error('Error updating quantity:', error);
  }
};

const removeItem = async (itemId) => {
  try {
    const response = await fetch(`/api/cart/remove/${itemId}`, {
      method: 'DELETE'
    });

    if (response.ok) {
      await loadCart();
    }
  } catch (error) {
    console.error('Error removing item:', error);
  }
};

onMounted(() => {
  loadCart();
});
</script>
