<template>
  <div class="language-switcher">
    <!-- Dropdown Style -->
    <div v-if="displayType === 'dropdown'" class="relative">
      <button 
        @click="toggleDropdown" 
        class="flex items-center space-x-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
        :class="{ 'ring-2 ring-blue-500': isOpen }"
      >
        <span v-if="currentLanguage" class="text-xl">{{ currentLanguage.flagEmoji }}</span>
        <span class="font-medium text-gray-700">{{ currentLanguage?.nativeName || 'Language' }}</span>
        <svg 
          class="w-4 h-4 transition-transform duration-200" 
          :class="{ 'rotate-180': isOpen }"
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div 
          v-if="isOpen" 
          class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto"
        >
          <div class="py-2">
            <button
              v-for="language in activeLanguages"
              :key="language.code"
              @click="selectLanguage(language)"
              class="w-full px-4 py-3 text-left hover:bg-blue-50 transition-colors duration-150 flex items-center space-x-3"
              :class="{ 'bg-blue-50 text-blue-700': language.code === currentLanguageCode }"
            >
              <span class="text-xl">{{ language.flagEmoji }}</span>
              <div class="flex-1">
                <div class="font-medium">{{ language.nativeName }}</div>
                <div class="text-sm text-gray-500">{{ language.name }}</div>
              </div>
              <div v-if="language.isDefault" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                Default
              </div>
            </button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Buttons Style -->
    <div v-else-if="displayType === 'buttons'" class="flex space-x-2">
      <button
        v-for="language in activeLanguages"
        :key="language.code"
        @click="selectLanguage(language)"
        class="flex items-center space-x-2 px-3 py-2 rounded-lg transition-all duration-200"
        :class="{
          'bg-blue-600 text-white shadow-md': language.code === currentLanguageCode,
          'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50': language.code !== currentLanguageCode
        }"
      >
        <span class="text-lg">{{ language.flagEmoji }}</span>
        <span class="font-medium text-sm">{{ language.nativeName }}</span>
      </button>
    </div>

    <!-- Compact Style -->
    <div v-else-if="displayType === 'compact'" class="flex space-x-1">
      <button
        v-for="language in activeLanguages"
        :key="language.code"
        @click="selectLanguage(language)"
        class="flex items-center justify-center w-10 h-10 rounded-lg transition-all duration-200"
        :class="{
          'bg-blue-600 text-white shadow-md': language.code === currentLanguageCode,
          'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50': language.code !== currentLanguageCode
        }"
        :title="language.nativeName"
      >
        <span class="text-lg">{{ language.flagEmoji }}</span>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'LanguageSwitcher',
  props: {
    languages: {
      type: Array,
      required: true,
      default: () => []
    },
    currentLanguageCode: {
      type: String,
      default: 'en'
    },
    displayType: {
      type: String,
      default: 'dropdown',
      validator: (value) => ['dropdown', 'buttons', 'compact'].includes(value)
    }
  },
  emits: ['language-changed'],
  setup(props, { emit }) {
    const isOpen = ref(false)

    const activeLanguages = computed(() => {
      return props.languages
        .filter(lang => lang.isActive)
        .sort((a, b) => a.sortOrder - b.sortOrder)
    })

    const currentLanguage = computed(() => {
      return activeLanguages.value.find(lang => lang.code === props.currentLanguageCode)
    })

    const toggleDropdown = () => {
      isOpen.value = !isOpen.value
    }

    const selectLanguage = (language) => {
      emit('language-changed', language)
      isOpen.value = false

      // Store language preference in localStorage and cookie
      localStorage.setItem('preferred-language', language.code)
      document.cookie = `locale=${language.code}; path=/; max-age=31536000`

      // Build the new URL with the selected language
      const currentPath = window.location.pathname
      const currentSearch = window.location.search
      const supportedLocales = ['en', 'de', 'es', 'fr', 'ca', 'it', 'pt', 'nl', 'pl', 'ru', 'ja', 'zh', 'ar', 'sv', 'no', 'da', 'fi', 'cs', 'sk', 'hu', 'ro', 'el', 'tr', 'uk', 'ko', 'hi', 'th', 'vi']

      // Remove existing locale prefix if present
      let pathWithoutLocale = currentPath
      const pathParts = currentPath.split('/').filter(part => part.length > 0)
      if (pathParts.length > 0 && supportedLocales.includes(pathParts[0])) {
        pathWithoutLocale = '/' + pathParts.slice(1).join('/')
      }

      // Normalize path
      if (!pathWithoutLocale || pathWithoutLocale === '/') {
        pathWithoutLocale = ''
      } else if (!pathWithoutLocale.startsWith('/')) {
        pathWithoutLocale = '/' + pathWithoutLocale
      }

      // Build new URL with language prefix
      // For non-English languages, ALWAYS add the language prefix
      // For English (default), NO prefix
      let newUrl
      if (language.code === 'en') {
        // English: no prefix, just the path
        newUrl = pathWithoutLocale || '/'
      } else {
        // Other languages: always add prefix
        newUrl = '/' + language.code + pathWithoutLocale
      }

      // Add query string if present
      if (currentSearch) {
        newUrl += currentSearch
      }

      // Navigate to the new URL
      window.location.href = newUrl
    }

    const handleClickOutside = (event) => {
      if (isOpen.value && !event.target.closest('.language-switcher')) {
        isOpen.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      isOpen,
      activeLanguages,
      currentLanguage,
      toggleDropdown,
      selectLanguage
    }
  }
}
</script>

<style scoped>
.language-switcher {
  position: relative;
  display: inline-block;
}

/* Custom scrollbar for dropdown */
.language-switcher .absolute::-webkit-scrollbar {
  width: 6px;
}

.language-switcher .absolute::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.language-switcher .absolute::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.language-switcher .absolute::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>