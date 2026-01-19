<template>
  <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-shadow duration-300">
    <div class="flex items-start space-x-4">
      <div :class="`w-12 h-12 rounded-xl flex items-center justify-center bg-${color}-100`">
        <svg :class="`w-6 h-6 text-${color}-600`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <component :is="getIcon(icon)" />
        </svg>
      </div>
      <div class="flex-1">
        <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ title }}</h3>
        <p class="text-gray-600 leading-relaxed">{{ description }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FeatureCard',
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      required: true
    },
    icon: {
      type: String,
      required: true
    },
    color: {
      type: String,
      default: 'blue'
    }
  },
  methods: {
    getIcon(iconName) {
      const icons = {
        translate: 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129',
        code: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
        edit: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'shopping-cart': 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'
      }
      return icons[iconName] || icons.code
    }
  }
}
</script>