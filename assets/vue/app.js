import {createApp} from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import axios from 'axios';
import router from './router';
import Swal from "sweetalert2";

axios.defaults.baseURL = 'http://localhost:8000/api';
axios.defaults.headers.common['Content-Type'] = 'application/json';
axios.defaults.headers.common['Accept'] = 'application/json';

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.use(router);
app.mount('#app');
app.config.globalProperties.$swal = Swal;