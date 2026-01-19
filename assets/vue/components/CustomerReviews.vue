<template>
  <div class="customer-reviews-container">
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="reviews.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div
        v-for="review in reviews"
        :key="review.id"
        class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
      >
        <!-- Rating Stars -->
        <div class="flex items-center mb-4">
          <div class="flex space-x-1 text-yellow-400">
            <svg v-for="n in 5" :key="n" class="w-5 h-5 fill-current" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
          <span v-if="review.isVerified" class="ml-3 text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-semibold">
            ✓ {{ t('reviews.verified_customer') }}
          </span>
        </div>

        <!-- Review Text -->
        <p class="text-gray-700 mb-6 leading-relaxed italic">
          "{{ review.reviewText }}"
        </p>

        <!-- Customer Info -->
        <div class="flex items-center space-x-4">
          <img
            v-if="review.customerImage"
            :src="review.customerImage"
            :alt="review.customerName"
            class="w-14 h-14 rounded-full object-cover ring-2 ring-gray-200"
          />
          <div v-else class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center ring-2 ring-gray-200">
            <span class="text-white text-xl font-bold">
              {{ review.customerName.charAt(0) }}
            </span>
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-gray-900">{{ review.customerName }}</h4>
            <p v-if="review.customerTitle || review.customerLocation" class="text-sm text-gray-500">
              {{ review.customerTitle }}
              <span v-if="review.customerTitle && review.customerLocation"> • </span>
              {{ review.customerLocation }}
            </p>
          </div>
        </div>

        <!-- Featured Badge -->
        <div v-if="review.isFeatured" class="mt-4 pt-4 border-t border-gray-100">
          <span class="inline-flex items-center text-xs font-medium text-yellow-600">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            {{ t('reviews.featured_review') }}
          </span>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-12 text-gray-500">
      <p class="text-lg">{{ t('startpage.reviews.no_reviews') }}</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useTranslations } from '../composables/useTranslations'

export default {
  name: 'CustomerReviews',
  props: {
    limit: {
      type: Number,
      default: 6
    }
  },
  setup(props) {
    const { t } = useTranslations()
    const reviews = ref([])
    const loading = ref(true)

    const fetchReviews = async () => {
      try {
        const response = await fetch(`/public-api/customer-reviews?limit=${props.limit}`)
        if (response.ok) {
          reviews.value = await response.json()
        }
      } catch (error) {
        console.error('Error fetching customer reviews:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      fetchReviews()
    })

    return {
      t,
      reviews,
      loading
    }
  }
}
</script>

<style scoped>
.customer-reviews-container {
  max-width: 1200px;
  margin: 0 auto;
}
</style>
