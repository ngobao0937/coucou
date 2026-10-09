import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// 1. CSS Thư viện ngoài (Nạp trước)
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'admin-lte/dist/css/adminlte.min.css';

// 2. CSS mặc định của hệ thống
import '../css/app.css';

// 3. CSS tùy biến (Nạp CUỐI CÙNG để ghi đè mọi thứ ở trên)
import '../css/custom.css';

import $ from 'jquery';
window.$ = window.jQuery = $;

import('bootstrap/dist/js/bootstrap.bundle.min.js');
import('admin-lte/dist/js/adminlte.min.js');

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
});
