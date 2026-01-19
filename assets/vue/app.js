import { createApp } from 'vue';
import App from './App.vue';

// Main Vue application
const app = createApp(App);

// Mount the app (without router - Symfony handles routing)
app.mount('#vue-app');