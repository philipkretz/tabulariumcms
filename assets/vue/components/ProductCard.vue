<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
    <div class="aspect-w-16 aspect-h-9 bg-gray-200">
      <img
        v-if="product.image"
        :src="product.image"
        :alt="product.name"
        class="w-full h-48 object-cover"
      />
      <div v-else class="flex items-center justify-center h-48 bg-gray-100">
        <span class="text-gray-400 text-4xl">📦</span>
      </div>
    </div>

    <div class="p-4">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ product.name }}</h3>
      <p v-if="product.shortDescription" class="text-sm text-gray-600 mb-4 line-clamp-2">
        {{ product.shortDescription }}
      </p>

      <div class="flex items-center justify-between">
        <div class="flex flex-col">
          <span class="text-2xl font-bold text-primary-600">{{ formatPrice(product.price) }}</span>
          <span v-if="product.stock > 0" class="text-xs text-green-600">In Stock</span>
          <span v-else class="text-xs text-red-600">Out of Stock</span>
        </div>

        <button
          @click="addToCart"
          :disabled="product.stock <= 0"
          class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="loading" class="inline-block animate-spin">⏳</span>
          <span v-else>Add to Cart</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['add-to-cart']);
const loading = ref(false);

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR'
  }).format(price);
};

const addToCart = async () => {
  loading.value = true;
  emit('add-to-cart', props.product);

  // Simulate API call
  setTimeout(() => {
    loading.value = false;
  }, 500);
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
