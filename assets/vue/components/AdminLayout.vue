<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Admin Sidebar -->
    <aside :class="sidebarClasses">
      <div class="p-6">
        <h2 class="text-2xl font-bold text-white mb-8">Admin Panel</h2>
        
        <nav class="space-y-2">
          <router-link 
            v-for="item in menuItems" 
            :key="item.path"
            :to="item.path"
            class="flex items-center space-x-3 px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white rounded-lg transition-colors"
            active-class="bg-gray-700 text-white"
          >
            <i :class="item.icon"></i>
            <span>{{ item.label }}</span>
          </router-link>
        </nav>
      </div>
    </aside>

    <!-- Mobile Menu Toggle -->
    <button 
      @click="toggleSidebar" 
      class="md:hidden fixed top-4 left-4 z-50 bg-gray-900 text-white p-2 rounded-lg"
    >
      <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content Area -->
    <main :class="mainClasses">
      <header class="bg-white shadow-sm">
        <div class="px-6 py-4 flex justify-between items-center">
          <h1 class="text-2xl font-semibold">{{ currentPageTitle }}</h1>
          
          <div class="flex items-center space-x-4">
            <button class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-bell"></i>
            </button>
            
            <div class="flex items-center space-x-2">
              <img :src="user.avatar || '/default-avatar.png'" class="w-8 h-8 rounded-full">
              <span class="text-sm font-medium">{{ user.firstName }} {{ user.lastName }}</span>
            </div>
          </div>
        </div>
      </header>

      <div class="p-6">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  name: 'AdminLayout',
  setup() {
    const sidebarOpen = ref(true)
    const user = ref({
      firstName: 'Admin',
      lastName: 'User',
      avatar: null
    })

    const menuItems = ref([
      { path: '/admin', label: 'Dashboard', icon: 'fas fa-home' },
      { path: '/admin/posts', label: 'Posts', icon: 'fas fa-file-text' },
      { path: '/admin/pages', label: 'Pages', icon: 'fas fa-file' },
      { path: '/admin/media', label: 'Media', icon: 'fas fa-image' },
      { path: '/admin/users', label: 'Users', icon: 'fas fa-user' },
      { path: '/admin/bookings', label: 'Bookings', icon: 'fas fa-calendar' },
      { path: '/admin/orders', label: 'Orders', icon: 'fas fa-shopping-cart' },
      { path: '/admin/settings', label: 'Settings', icon: 'fas fa-cog' },
    ])

    const sidebarClasses = computed(() => {
      return `admin-sidebar fixed md:relative transform transition-transform duration-300 ease-in-out ${
        sidebarOpen.value ? 'translate-x-0' : '-translate-x-full'
      }`
    })

    const mainClasses = computed(() => {
      return `admin-main ${sidebarOpen.value ? 'md:ml-64' : ''}`
    })

    const currentPageTitle = computed(() => {
      const route = window.location.pathname
      const item = menuItems.value.find(item => route.includes(item.path))
      return item ? item.label : 'Admin Panel'
    })

    const toggleSidebar = () => {
      sidebarOpen.value = !sidebarOpen.value
    }

    return {
      sidebarOpen,
      user,
      menuItems,
      sidebarClasses,
      mainClasses,
      currentPageTitle,
      toggleSidebar
    }
  }
}
</script>