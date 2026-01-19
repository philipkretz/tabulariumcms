<template>
  <div class="min-h-screen" style="background: linear-gradient(135deg, #f0e8d0 0%, #d1b05f 50%, #d3c29d 100%);">
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(240, 232, 208, 0.3) 0%, rgba(209, 176, 95, 0.3) 50%, rgba(211, 194, 157, 0.3) 100%);"></div>
      <div class="relative container mx-auto px-4 py-20 lg:py-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="text-center lg:text-left space-y-8">
            <div class="space-y-4">
              <h1 class="text-5xl lg:text-7xl font-bold" style="color: #4c505b;">
                {{ t('startpage.hero.welcome') }}
                <span class="block">{{ t('startpage.hero.title') }}</span>
              </h1>
              <p class="text-xl lg:text-2xl text-gray-600 max-w-2xl">
                {{ t('startpage.hero.subtitle') }}
              </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
              <button 
                @click="navigateTo('/admin')"
                class="px-8 py-4 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-lg"
              >
                <span class="flex items-center justify-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>{{ t('startpage.hero.admin_dashboard') }}</span>
                </span>
              </button>

              <button
                @click="navigateTo('/docs')"
                class="px-8 py-4 bg-white text-gray-700 font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transform hover:scale-105 transition-all duration-200 shadow-lg"
              >
                <span class="flex items-center justify-center space-x-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                  <span>{{ t('startpage.hero.documentation') }}</span>
                </span>
              </button>
            </div>

            <!-- Language Switcher -->
            <div class="flex justify-center lg:justify-start">
              <LanguageSwitcher
                :languages="availableLanguages"
                :current-language-code="currentLanguage"
                display-type="buttons"
                @language-changed="handleLanguageChange"
              />
            </div>
          </div>

          <div class="relative">
            <div class="absolute inset-0 rounded-3xl transform rotate-6 opacity-20" style="background: linear-gradient(135deg, #f0e8d0 0%, #d1b05f 50%, #d3c29d 100%);"></div>
            <div class="relative bg-white rounded-3xl shadow-2xl p-8">
              <div class="aspect-video rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, rgba(240, 232, 208, 0.5) 0%, rgba(209, 176, 95, 0.5) 50%, rgba(211, 194, 157, 0.5) 100%);">
                <div class="text-center space-y-4">
                  <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                    </svg>
                  </div>
                  <h3 class="text-2xl font-bold text-gray-800">Modern & Secure</h3>
                  <p class="text-gray-600">Built with Symfony 7, Vue.js 3, and Tailwind CSS</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Grid -->
    <section class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold mb-4" style="color: #4c505b;">{{ t('startpage.features.title') }}</h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            {{ t('startpage.features.subtitle') }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <FeatureCard
            v-for="feature in features"
            :key="feature.title"
            :title="feature.title"
            :description="feature.description"
            :icon="feature.icon"
            :color="feature.color"
          />
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20" style="background: linear-gradient(135deg, #f0e8d0 0%, #d1b05f 50%, #d3c29d 100%);">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
          <div v-for="stat in stats" :key="stat.label">
            <div class="text-4xl lg:text-5xl font-bold mb-2" style="color: #4c505b;">{{ stat.value }}</div>
            <div class="text-gray-700">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Customer Reviews Section -->
    <section class="py-20 bg-white">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold mb-4" style="color: #4c505b;">{{ t('startpage.reviews.title') }}</h2>
          <p class="text-xl text-gray-600">{{ t('startpage.reviews.subtitle') }}</p>
          <div class="flex items-center justify-center mt-2 space-x-1">
            <svg v-for="n in 5" :key="n" class="w-6 h-6 text-yellow-400 fill-current" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
            <span class="ml-3 text-gray-600 font-semibold">5.0 {{ t('startpage.reviews.rating') }}</span>
          </div>
        </div>

        <CustomerReviews :limit="6" />
      </div>
    </section>

    <!-- Recent Content Preview -->
    <section class="py-20 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold mb-4" style="color: #4c505b;">{{ t('startpage.recent_content.title') }}</h2>
          <p class="text-xl text-gray-600">{{ t('startpage.recent_content.subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <ContentPreview
            title="Recent Posts"
            :items="recentPosts"
            type="post"
          />
          <ContentPreview
            title="Recent Pages"
            :items="recentPages"
            type="page"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import LanguageSwitcher from './LanguageSwitcher.vue'
import FeatureCard from './FeatureCard.vue'
import ContentPreview from './ContentPreview.vue'
import CustomerReviews from './CustomerReviews.vue'
import { useTranslations } from '../composables/useTranslations'

export default {
  name: 'StartPage',
  components: {
    LanguageSwitcher,
    FeatureCard,
    ContentPreview,
    CustomerReviews
  },
  setup() {
    const { t, loadTranslations } = useTranslations()
    const currentLanguage = ref('en')
    const availableLanguages = ref([])

    const features = ref([
      {
        title: 'Multi-language Support',
        description: 'Built-in internationalization with configurable language domains and subdirectories',
        icon: 'translate',
        color: 'blue'
      },
      {
        title: 'Modern Tech Stack',
        description: 'Powered by Symfony 7, Vue.js 3, and Tailwind CSS for optimal performance',
        icon: 'code',
        color: 'purple'
      },
      {
        title: 'Content Management',
        description: 'Flexible pages, posts, and categories with rich editing capabilities',
        icon: 'edit',
        color: 'green'
      },
      {
        title: 'E-commerce Ready',
        description: 'Integrated booking system with PayPal and Stripe payment support',
        icon: 'shopping-cart',
        color: 'yellow'
      },
      {
        title: 'User Management',
        description: 'Secure authentication system with role-based access control',
        icon: 'users',
        color: 'red'
      },
      {
        title: 'SEO Optimized',
        description: 'Built-in SEO tools with meta tags, sitemaps, and structured data',
        icon: 'search',
        color: 'indigo'
      }
    ])

    const stats = ref([
      { label: 'Languages Supported', value: '4+' },
      { label: 'Payment Gateways', value: '2' },
      { label: 'Themes Available', value: '∞' },
      { label: 'API Endpoints', value: '50+' }
    ])

    const recentPosts = ref([
      { title: 'Getting Started with TabulariumCMS', excerpt: 'Learn how to set up your first website...', date: '2024-01-02' },
      { title: 'Advanced Customization Guide', excerpt: 'Take your site to the next level with...', date: '2024-01-01' },
      { title: 'Security Best Practices', excerpt: 'Keep your CMS secure with these tips...', date: '2023-12-30' }
    ])

    const recentPages = ref([
      { title: 'About Us', excerpt: 'Learn more about our mission and...', date: '2024-01-02' },
      { title: 'Contact', excerpt: 'Get in touch with our team...', date: '2024-01-01' },
      { title: 'Documentation', excerpt: 'Comprehensive guides and API reference...', date: '2023-12-28' }
    ])

    const navigateTo = (path) => {
      window.location.href = path
    }

    const handleLanguageChange = (language) => {
      console.log('Language changed to:', language.code)
      // Language switching logic handled by LanguageSwitcher component
    }

    const loadLanguages = async () => {
      try {
        const response = await fetch('/public-api/languages')
        const data = await response.json()
        availableLanguages.value = data

        // Set current language from localStorage or default
        const savedLanguage = localStorage.getItem('preferred-language')
        if (savedLanguage) {
          currentLanguage.value = savedLanguage
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

    onMounted(async () => {
      await loadTranslations()
      loadLanguages()
    })

    return {
      t,
      currentLanguage,
      availableLanguages,
      features,
      stats,
      recentPosts,
      recentPages,
      navigateTo,
      handleLanguageChange
    }
  }
}
</script>