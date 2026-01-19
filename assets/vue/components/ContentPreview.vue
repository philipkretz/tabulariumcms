<template>
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="p-6 border-b border-gray-200">
      <h3 class="text-2xl font-bold text-gray-900">{{ title }}</h3>
    </div>
    
    <div class="p-6">
      <div v-if="items.length === 0" class="text-center py-8">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-gray-500">No {{ type === 'post' ? 'posts' : 'pages' }} found</p>
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="item in items" 
          :key="item.title"
          class="group cursor-pointer hover:bg-gray-50 rounded-lg p-4 transition-colors duration-200"
          @click="navigateToItem(item)"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <h4 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200 mb-2">
                {{ item.title }}
              </h4>
              <p class="text-gray-600 text-sm leading-relaxed mb-2">{{ item.excerpt }}</p>
              <div class="flex items-center text-xs text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(item.date) }}
              </div>
            </div>
            
            <div class="ml-4 flex-shrink-0">
              <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="items.length > 0" class="mt-6 pt-6 border-t border-gray-200">
        <button 
          @click="viewAll"
          class="w-full py-3 px-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center space-x-2"
        >
          <span>View All {{ type === 'post' ? 'Posts' : 'Pages' }}</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContentPreview',
  props: {
    title: {
      type: String,
      required: true
    },
    items: {
      type: Array,
      default: () => []
    },
    type: {
      type: String,
      default: 'post',
      validator: (value) => ['post', 'page'].includes(value)
    }
  },
  methods: {
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'short', day: 'numeric' }
      return new Date(dateString).toLocaleDateString(undefined, options)
    },
    
    navigateToItem(item) {
      // Generate appropriate URL based on type
      const url = this.type === 'post' ? `/blog/${item.slug}` : `/pages/${item.slug}`
      window.location.href = url
    },
    
    viewAll() {
      const url = this.type === 'post' ? '/blog' : '/pages'
      window.location.href = url
    }
  }
}
</script>