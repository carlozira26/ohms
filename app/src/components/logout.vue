<template>
	<v-container>
		<div>
			<v-progress-circular
				class="pa-5"
				:size="70"
				:width="7"
				color="green darken-4"
				indeterminate
			></v-progress-circular>
		</div>
	</v-container>
</template>

<script>

import VueCookies from 'vue-cookies';
	
export default {
	data : function(){
		return {
			logoutDialog : true,
		}
	},
	mounted : function(){
		VueCookies.remove(this.cookieKey);

		setTimeout(() => {
            this.$router.push('/login');
        }, 500);
	}

};


</script>