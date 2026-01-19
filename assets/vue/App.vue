<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Dynamic Navigation from API -->
    <Navigation position="header" />

    <!-- Main Content -->
    <main>
      <StartPage />
    </main>

    <!-- Footer with dynamic menu -->
    <footer class="bg-gray-800 text-white py-12 mt-16">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
              <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                </svg>
              </div>
              <span>TabulariumCMS</span>
            </h3>
            <p class="text-gray-300">Modern, flexible content management system for the web with multi-language support.</p>
          </div>

          <!-- Footer Menu -->
          <div v-for="(menuItem, index) in footerMenuItems" :key="index">
            <h3 class="text-lg font-semibold mb-4">{{ menuItem.title }}</h3>
            <ul class="space-y-2 text-gray-300">
              <li v-for="child in menuItem.children" :key="child.id">
                <a :href="child.url" :target="child.openInNewTab ? '_blank' : '_self'" class="hover:text-white transition-colors duration-200">
                  {{ child.title }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Contact Info -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Contact</h3>
            <div class="text-gray-300 space-y-2">
              <p class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>info@tabulariumcms.com</span>
              </p>
              <p class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>+1 234 567 890</span>
              </p>
            </div>
          </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
          <p>&copy; 2024-{{ new Date().getFullYear() }} TabulariumCMS. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import Navigation from './components/Navigation.vue'
import StartPage from './components/StartPage.vue'

export default {
  name: 'App',
  components: {
    Navigation,
    StartPage
  },
  setup() {
    const footerMenuItems = ref([])

    const loadFooterMenu = async () => {
      try {
        const response = await fetch('/public-api/menus/footer')
        const data = await response.json()
        footerMenuItems.value = data.items || []
      } catch (error) {
        console.error('Failed to load footer menu:', error)
        // Fallback to static menu
        footerMenuItems.value = [
          {
            title: 'Quick Links',
            children: [
              { id: 1, title: 'Documentation', url: '/docs' },
              { id: 2, title: 'Support', url: '/support' },
              { id: 3, title: 'Terms', url: '/terms' }
            ]
          },
          {
            title: 'Services',
            children: [
              { id: 4, title: 'Web Development', url: '/services/web' },
              { id: 5, title: 'Consulting', url: '/services/consulting' },
              { id: 6, title: 'Hosting', url: '/services/hosting' }
            ]
          }
        ]
      }
    }

    onMounted(() => {
      loadFooterMenu()
    })

    return {
      footerMenuItems
    }
  }
}
</script>
