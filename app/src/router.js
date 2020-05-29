import Vue from 'vue'
import Router from 'vue-router'
import mixins from './mixins';
import VueCookies from 'vue-cookies';

import LoginModule from './components/login.vue';
import NavigationDrawer from './components/navigation.vue';
import Dashboard from './components/dashboard.vue';
import Patient from './components/patient.vue';
import Logout from './components/logout.vue';
Vue.use(Router);

const router =  new Router({
	routes: [
		{
			path: '/',
			name: 'home',
			redirect : '/dashboard',
			component: NavigationDrawer,
			children : [
				{
					path : '/dashboard',
					component : Dashboard,
				},
				{
					path : '/patient',
					component : Patient,
				},
				{
					path : '/logout',
					component : Logout
				},
			]
		},
		{
			path: '/login',
			name: 'login',
			component: LoginModule
		},
	]
})

router.beforeEach((to, from, next) => {
    let cookie = VueCookies.get( mixins.data().cookieKey );
    if(cookie == null && to.name != 'login'){
       router.push('/login');
    }else if(cookie != null && to.name == 'login'){
        router.push('/')
    }else{
        next();
    }
});

export default router;
