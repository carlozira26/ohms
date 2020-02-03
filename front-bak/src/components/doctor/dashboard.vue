<template>
	<div>
		<template v-if="role == 'none'">
			<patient-dashboard></patient-dashboard>
		</template>
		<template v-else>
			<doctor-dashboard></doctor-dashboard>
		</template>
	</div>
</template>
<script>
import VueCookies from 'vue-cookies';
import patientDashboard from './patient-dashboard.vue';
import doctorDashboard from './doctor-dashboard.vue';
		export default {
			components : {
				'patient-dashboard' : patientDashboard,
				'doctor-dashboard' : doctorDashboard
			},
			created: function(){
				this.role = VueCookies.get(this.cookieKey).data.role;
			},
			data : function(){
				return {

				}
			}
	};
</script>