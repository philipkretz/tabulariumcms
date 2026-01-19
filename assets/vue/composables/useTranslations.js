import { ref, computed } from 'vue'

// Global translations storage
const translations = ref({})
const currentLocale = ref('en')
const isLoading = ref(false)
const isLoaded = ref(false)

export function useTranslations() {
  // Get current locale from URL or default to 'en'
  const detectLocale = () => {
    const path = window.location.pathname
    const supportedLocales = ['en', 'de', 'es', 'fr', 'ca', 'it', 'pt', 'nl', 'pl', 'ru', 'ja', 'zh', 'ar', 'sv', 'no', 'da', 'fi', 'cs', 'sk', 'hu', 'ro', 'el', 'tr', 'uk', 'ko', 'hi', 'th', 'vi']

    const pathParts = path.split('/').filter(part => part.length > 0)
    if (pathParts.length > 0 && supportedLocales.includes(pathParts[0])) {
      return pathParts[0]
    }

    // Check cookie
    const cookies = document.cookie.split(';')
    for (let cookie of cookies) {
      const [name, value] = cookie.trim().split('=')
      if (name === 'locale' && supportedLocales.includes(value)) {
        return value
      }
    }

    return 'en'
  }

  // Load translations from API
  const loadTranslations = async (locale = null) => {
    const targetLocale = locale || detectLocale()

    // Don't reload if already loaded for this locale
    if (isLoaded.value && currentLocale.value === targetLocale) {
      return
    }

    isLoading.value = true

    try {
      const response = await fetch(`/public-api/translations?locale=${targetLocale}`)
      if (response.ok) {
        const data = await response.json()
        translations.value = data
        currentLocale.value = targetLocale
        isLoaded.value = true
      }
    } catch (error) {
      console.error('Error loading translations:', error)
      // Fallback to English if there's an error
      if (targetLocale !== 'en') {
        await loadTranslations('en')
      }
    } finally {
      isLoading.value = false
    }
  }

  // Translation function
  const t = (key, params = {}) => {
    let value = translations.value[key] || key

    // Replace parameters in the translation
    Object.keys(params).forEach(param => {
      value = value.replace(`%${param}%`, params[param])
    })

    return value
  }

  // Check if a translation key exists
  const has = (key) => {
    return translations.value.hasOwnProperty(key)
  }

  return {
    t,
    has,
    loadTranslations,
    currentLocale: computed(() => currentLocale.value),
    isLoading: computed(() => isLoading.value),
    isLoaded: computed(() => isLoaded.value)
  }
}
