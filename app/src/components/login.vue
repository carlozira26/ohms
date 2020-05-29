<template>
	<v-container fluid fill-height scrollable>
		<div class="div-block bg"></div>
		<v-layout align-center justify-center>
			<v-flex xs12 sm8 md4>
				<v-card class="elevation-12 card-border">
					<v-form ref="vForm" v-on:submit.prevent="login">
						<v-toolbar dark color="green darken-4 card-toolbar-border">
							<v-toolbar-title class="headline text-uppercase">
								<span>OHMS</span>
							</v-toolbar-title>
							<v-spacer></v-spacer>
						</v-toolbar>
						<v-card-text>
							<h3 class="subheading red--text text-xs-center" v-if="isInvalildCredential"><v-icon color="red">error_outline</v-icon> Invalid Credentials</h3>
							<v-text-field solo :disabled="loading" :rules="[formRules.required]" prepend-icon="person" v-model="email" label="Email" type="text"></v-text-field>
							<v-text-field solo :disabled="loading" :rules="[formRules.required]" prepend-icon="lock" v-model="password" label="Password" type="password"></v-text-field>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn type="submit" :disabled="loading" color="green darken-4" class="white--text">Login</v-btn>
						</v-card-actions>
					</v-form>
				</v-card>
			</v-flex>
		</v-layout>
		<server-connection></server-connection>
	</v-container>
</template>

<script>
import VueCookies from 'vue-cookies';
import axios from 'axios';
import serverConnection from './serverConnection.vue';

export default {
	components : {
		'server-connection' : serverConnection
	},
	created: function(){

	},
	data: function(){
		return{
			email: "",
			password: "",
			isInvalildCredential : false,
			arrayResponse : [],
			loading : false,
			message : ''
		}
	},
	methods : {
		login : function(){
			if( this.$refs.vForm.validate() ){
				let 
				_this = this,
				formData = new FormData();

				formData.append('email',_this.email);
				formData.append('password',_this.password);

				_this.isInvalildCredential = false;
				_this.loading = true;
				axios.post(_this.apiUrl + '/users/app/login', formData)
				.then(function(res){
					if(res.data.status){
						VueCookies.set(_this.cookieKey, res.data);
						_this.loading = false;
						setTimeout(() => {
							_this.$router.push('/');
						}, 300);
					}else{
						_this.isInvalildCredential = true;
						_this.loading = false;
					}
				})
				.catch(error => {
					_this.message = 'Please connect to wifi where the system\'s server is connected';
					_this.error = error;
					_this.loading = false;
				});
			}
		}
	}
};

</script>

<style scoped>
.logo-sm{
	width:40px;
}
.card-border{
	border-bottom-left-radius: 50px;
	border-top-right-radius: 50px;
}
.card-toolbar-border{
	border-top-right-radius: 50px;
}
.div-block{
	display: inline-block;
	width:100%;
	height:100%;
}
.div-block > span {
	position: absolute;
	bottom: 0;
	right: 0;
	color: #fff;
	font-size:25px;
	padding-bottom:10px;
	padding-right:15px;
} 

.div-block.bg {
	position: fixed; 
	top: 0; 
	left: 0;
	filter: blur(8px);
	min-width: 100%;
	min-height: 100%;
	/*filter: blur(10px);*/
	background: url(../assets/doctor.jpg);
	background-size: cover;
	width: 100vh;
	height: 100vh;
	z-index: 0;
}
</style>
