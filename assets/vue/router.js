import { createRouter, createWebHistory } from 'vue-router'
import StartPage from './components/StartPage.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: StartPage
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
