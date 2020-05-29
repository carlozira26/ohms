import "@babel/polyfill";
import Vue from 'vue';
import App from './App.vue';
import router from './router';
import Vuetify from 'vuetify';
import 'vuetify/dist/vuetify.min.css';
import 'roboto-fontface/css/roboto/roboto-fontface.css'
import 'material-design-icons-iconfont/dist/material-design-icons.css';
import '@fortawesome/fontawesome-free/css/all.css';
import mixins from './mixins';
Vue.use(Vuetify);
Vue.mixin(mixins);
Vue.config.productionTip = false

new Vue({
	router,
	render: h => h(App),
}).$mount('#app')
