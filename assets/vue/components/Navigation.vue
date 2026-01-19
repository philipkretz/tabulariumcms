<template>
  <nav class="bg-gray-50 sticky top-0 z-50">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center h-16">
        <!-- Logo/Brand -->
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
            </svg>
          </div>
          <a href="/" class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            TabulariumCMS
          </a>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden lg:flex items-center space-x-1">
          <template v-for="item in menuItems" :key="item.id">
            <div v-if="item.children && item.children.length > 0" class="relative group">
              <button class="px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 flex items-center space-x-1">
                <i v-if="item.icon" :class="item.icon" class="text-sm"></i>
                <span class="font-medium text-gray-700">{{ item.title }}</span>
                <svg class="w-4 h-4 transform group-hover:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Dropdown -->
              <div class="absolute left-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform group-hover:translate-y-0 translate-y-2">
                <div class="py-2">
                  <a
                    v-for="child in item.children"
                    :key="child.id"
                    :href="child.url"
                    :target="child.openInNewTab ? '_blank' : '_self'"
                    class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-150"
                  >
                    <i v-if="child.icon" :class="child.icon" class="text-sm mr-2"></i>
                    {{ child.title }}
                  </a>
                </div>
              </div>
            </div>

            <a
              v-else
              :href="item.url"
              :target="item.openInNewTab ? '_blank' : '_self'"
              class="px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 flex items-center space-x-2"
            >
              <i v-if="item.icon" :class="item.icon" class="text-sm"></i>
              <span class="font-medium text-gray-700">{{ item.title }}</span>
            </a>
          </template>
        </div>

        <!-- Right side: Cart Icon + Language Switcher + Mobile Menu Button -->
        <div class="flex items-center space-x-4">
          <!-- Cart Icon with Count -->
          <a
            :href="cartUrl"
            class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200 group"
            :title="`${cartCount} item(s) in cart`"
          >
            <svg class="w-6 h-6 text-gray-700 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <!-- Count Badge -->
            <span
              v-if="cartCount > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
            >
              {{ cartCount > 99 ? '99+' : cartCount }}
            </span>
          </a>

          <LanguageSwitcher
            v-if="languages.length > 0"
            :languages="languages"
            :current-language-code="currentLanguage"
            display-type="compact"
            @language-changed="handleLanguageChange"
          />

          <!-- Mobile Menu Button -->
          <button
            @click="toggleMobileMenu"
            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div v-if="mobileMenuOpen" class="lg:hidden py-4 border-t border-gray-200">
          <div class="space-y-1">
            <template v-for="item in menuItems" :key="item.id">
              <div v-if="item.children && item.children.length > 0">
                <button
                  @click="toggleSubmenu(item.id)"
                  class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-100 rounded-lg transition-colors duration-200"
                >
                  <span class="flex items-center space-x-2">
                    <i v-if="item.icon" :class="item.icon" class="text-sm"></i>
                    <span class="font-medium text-gray-700">{{ item.title }}</span>
                  </span>
                  <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-180': openSubmenus.includes(item.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>

                <div v-if="openSubmenus.includes(item.id)" class="ml-4 mt-1 space-y-1">
                  <a
                    v-for="child in item.children"
                    :key="child.id"
                    :href="child.url"
                    :target="child.openInNewTab ? '_blank' : '_self'"
                    class="block px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors duration-150"
                  >
                    <i v-if="child.icon" :class="child.icon" class="text-sm mr-2"></i>
                    {{ child.title }}
                  </a>
                </div>
              </div>

              <a
                v-else
                :href="item.url"
                :target="item.openInNewTab ? '_blank' : '_self'"
                class="block px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200"
              >
                <i v-if="item.icon" :class="item.icon" class="text-sm mr-2"></i>
                {{ item.title }}
              </a>
            </template>
          </div>
        </div>
      </transition>
    </div>
  </nav>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import LanguageSwitcher from './LanguageSwitcher.vue'
import { useCart } from '../composables/useCart'

export default {
  name: 'Navigation',
  components: {
    LanguageSwitcher
  },
  props: {
    position: {
      type: String,
      default: 'header'
    }
  },
  setup(props) {
    const { cartCount, loadCart } = useCart()
    const menuItems = ref([])
    const languages = ref([])
    const currentLanguage = ref('en')
    const mobileMenuOpen = ref(false)
    const openSubmenus = ref([])

    // Detect current locale from URL
    const detectLocale = () => {
      const path = window.location.pathname
      const supportedLocales = ['en', 'de', 'es', 'fr', 'ca', 'it', 'pt', 'nl', 'pl', 'ru', 'ja', 'zh', 'ar', 'sv', 'no', 'da', 'fi', 'cs', 'sk', 'hu', 'ro', 'el', 'tr', 'uk', 'ko', 'hi', 'th', 'vi']
      const pathParts = path.split('/').filter(part => part.length > 0)
      if (pathParts.length > 0 && supportedLocales.includes(pathParts[0])) {
        return pathParts[0]
      }
      return 'en'
    }

    // Compute cart URL based on current locale
    const cartUrl = computed(() => {
      const locale = detectLocale()
      return locale === 'en' ? '/cart' : `/${locale}/cart`
    })

    const loadMenu = async () => {
      try {
        const response = await fetch(`/public-api/menus/${props.position}`)
        const data = await response.json()
        menuItems.value = data.items || []
      } catch (error) {
        console.error('Failed to load menu:', error)
      }
    }

    const loadLanguages = async () => {
      try {
        const response = await fetch('/public-api/languages')
        const data = await response.json()
        languages.value = data

        // Set current language from localStorage or default
        const savedLang = localStorage.getItem('preferred-language')
        if (savedLang) {
          currentLanguage.value = savedLang
        } else {
          const defaultLang = data.find(lang => lang.isDefault)
          if (defaultLang) {
            currentLanguage.value = defaultLang.code
          }
        }
      } catch (error) {
        console.error('Failed to load languages:', error)
      }
    }

    const toggleMobileMenu = () => {
      mobileMenuOpen.value = !mobileMenuOpen.value
    }

    const toggleSubmenu = (itemId) => {
      const index = openSubmenus.value.indexOf(itemId)
      if (index > -1) {
        openSubmenus.value.splice(index, 1)
      } else {
        openSubmenus.value.push(itemId)
      }
    }

    const handleLanguageChange = (language) => {
      currentLanguage.value = language.code
    }

    onMounted(async () => {
      loadMenu()
      loadLanguages()
      await loadCart()
    })

    return {
      menuItems,
      languages,
      currentLanguage,
      mobileMenuOpen,
      openSubmenus,
      toggleMobileMenu,
      toggleSubmenu,
      handleLanguageChange,
      cartCount,
      cartUrl
    }
  }
}
</script>
