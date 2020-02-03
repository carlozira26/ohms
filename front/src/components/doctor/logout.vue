<template>
	<v-container fluid fill-height>
		<v-layout align-center justify-center  class="ma-5" >
			<v-flex xs12 offset-sm2 offset-md4 sm8 md4 class="text-xs-center">
				<v-progress-circular
					:size="70"
					:width="7"
					color="green darken-4"
					indeterminate
				></v-progress-circular>
			</v-flex>
			<v-flex xs12 sm2 md4></v-flex>
		</v-layout>
	</v-container>
</template>

<script>

import VueCookies from 'vue-cookies';
	
export default {

	mounted : function(){
		VueCookies.remove(this.cookieKey);

		setTimeout(() => {
            this.$router.push('/login');
        }, 500);
	}

};


</script>