<template>
	<div>
		<v-toolbar color="green darken-4" dark app fixed>
			<div class="mr-4 headline font-weight-bold">OHMS</div>
			<v-spacer></v-spacer>
			<v-btn flat icon :to="'/logout'">
				<v-icon>fa-sign-out-alt</v-icon>
			</v-btn>
		</v-toolbar>
		<v-content>
			<router-view></router-view>
		</v-content>
	</div>
</template>
<script>
import VueCookies from 'vue-cookies';
import axios from "axios";

export default {
	created : function(){
		
	},
	data : () => ({
		
	}),

	methods : {
		
	}
};	
</script>