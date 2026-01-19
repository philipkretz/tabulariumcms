<template>
  <div class="relative w-full max-w-2xl mx-auto">
    <div class="relative">
      <input
        v-model="searchQuery"
        @input="handleInput"
        @focus="showSuggestions = true"
        @blur="handleBlur"
        type="text"
        placeholder="Search products..."
        class="w-full px-4 py-3 pl-12 pr-4 text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
      />
      <div class="absolute inset-y-0 left-0 flex items-center pl-4">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <button
        v-if="searchQuery"
        @click="clearSearch"
        class="absolute inset-y-0 right-0 flex items-center pr-4"
      >
        <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Suggestions Dropdown -->
    <div
      v-if="showSuggestions && suggestions.length > 0"
      class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-96 overflow-y-auto"
    >
      <div
        v-for="(suggestion, index) in suggestions"
        :key="index"
        @mousedown="selectSuggestion(suggestion)"
        class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
      >
        <div class="flex items-center">
          <div v-if="suggestion.image" class="w-12 h-12 flex-shrink-0 mr-3">
            <img :src="suggestion.image" :alt="suggestion.name" class="w-full h-full object-cover rounded" />
          </div>
          <div class="flex-1">
            <h4 class="text-sm font-medium text-gray-900">{{ suggestion.name }}</h4>
            <p v-if="suggestion.price" class="text-sm text-gray-600">{{ formatPrice(suggestion.price) }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  apiEndpoint: {
    type: String,
    default: '/api/search/suggestions'
  }
});

const emit = defineEmits(['search', 'select']);

const searchQuery = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);
const debounceTimer = ref(null);

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR'
  }).format(price);
};

const handleInput = () => {
  clearTimeout(debounceTimer.value);

  if (searchQuery.value.length < 2) {
    suggestions.value = [];
    return;
  }

  debounceTimer.value = setTimeout(async () => {
    try {
      const response = await fetch(`${props.apiEndpoint}?q=${encodeURIComponent(searchQuery.value)}`);
      const data = await response.json();
      suggestions.value = data.data || [];
    } catch (error) {
      console.error('Search error:', error);
      suggestions.value = [];
    }
  }, 300);
};

const handleBlur = () => {
  setTimeout(() => {
    showSuggestions.value = false;
  }, 200);
};

const clearSearch = () => {
  searchQuery.value = '';
  suggestions.value = [];
};

const selectSuggestion = (suggestion) => {
  emit('select', suggestion);
  searchQuery.value = suggestion.name;
  showSuggestions.value = false;
};

watch(searchQuery, (newValue) => {
  emit('search', newValue);
});
</script>
