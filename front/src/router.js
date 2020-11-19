import Vue from 'vue';
import Router from 'vue-router';
import mixins from './mixins';
import VueCookies from 'vue-cookies';
import LoginModule from './components/login.vue';

import NavigationModule from './components/doctor/navigation.vue';
import Dashboard from './components/doctor/dashboard.vue';
import PatientList from './components/doctor/patientlist.vue';
import DoctorList from './components/doctor/doctorlist.vue';
import Account from './components/doctor/account.vue';
import Logout from './components/doctor/logout.vue';


Vue.use(Router)

const router =  new Router({
	routes: [
		{
			path: '/',
			name: 'root',
			redirect : '/dashboard',
			component: NavigationModule,
			children : [
				{
					path : '/dashboard',
					name : 'dashboard',
					component: Dashboard,
				},
				{
					path : '/patient-list',
					name : 'patientlist',
					component : PatientList
				},
				{
					path : '/doctors-list',
					name : 'doctorslist',
					component : DoctorList
				},
				{
					path : '/account',
					name : 'account',
					component : Account
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
