<template>
	<v-container fluid fill-height>
		<v-toolbar color="green darken-4" dark app fixed>
			<v-toolbar-title class="ml-0 pl-3"><h2>OHMS</h2></v-toolbar-title>
			<v-spacer></v-spacer>
			<v-btn large flat round class="font-weight-regular white--text green darken-4" @click="changeTitle('patient')">Login</v-btn>
		</v-toolbar>
		<div class="div-block bg"></div>
		<v-layout align-center justify-center>
			<v-flex xs12 sm12 md6>
				<v-container style="margin-top:20%">
					<div class="title">
						<h1 class="font-weight-regular display-3">Online Healthcare Monitoring System</h1>
					</div>
					<br>
					<div class="subtitle">
						<h4 class="font-weight-light">The doctor's motto : Accept Patients.</h4>
					</div>
				</v-container>
			</v-flex>
		</v-layout>
		<v-dialog v-model="dialog" width="500">
			<v-card>
				<v-form ref="vForm" v-on:submit.prevent="accountLogin">
					<v-card-title class="headline green darken-4 white--text" primary-title>
						<div style="font-size:20px">{{ title }}</div>
					</v-card-title>
					<v-card-text>
						<h3 class="subheading red--text text-xs-center" v-if="isInvalildCredential"><v-icon color="red">error_outline</v-icon> Invalid Credentials</h3>
						
						<v-text-field solo prepend-icon="person" label="Username / Email" v-model="username" type="text"></v-text-field>
						<v-text-field solo prepend-icon="lock" @click:append="showHide = !showHide" v-model="password" :type="showHide ? 'text' : 'password'" :append-icon="showHide ? 'visibility' : 'visibility_off'" label="Password"></v-text-field>
					</v-card-text>

					<v-divider></v-divider>

					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat text type="submit">
							Login
						</v-btn>
						<v-btn flat text @click="dialog = false">
							Cancel
						</v-btn>
					</v-card-actions>
				</v-form>
			</v-card>
		</v-dialog>
	</v-container>
</template>
<script>
	import VueCookies from 'vue-cookies';
	import axios from 'axios';

	export default {
		data: function(){
			return{
				dialog: false,
				title : "",
				showHide: false,
				
				username: null,
				password : null,

				isInvalildCredential : false

			}
		},
		methods: {
			changeTitle : function(user){
				this.dialog = true; 
				this.title = "Login to OHMS";
			},
			accountLogin : function(){
				if( this.$refs.vForm.validate() ){
					let _this = this,
					formData = new FormData();

					formData.append("username", _this.username);
					formData.append("password", _this.password);

					axios.post(_this.apiUrl + '/users/login', formData)
					.then(function(res){
						if(res.data.status){
							VueCookies.set(_this.cookieKey, res.data);
								_this.$router.push('/');
						}else{
							_this.isInvalildCredential = true;
						}
					});
				}
			},
		}
	};
</script>
<style>
.div-block.bg {
    position: fixed; 
    top: 0; 
    left: 0;
    min-width: 100%;
    min-height: 100%;
    filter: blur(8px);
    background: url(../assets/doctor.jpg);
    background-size: cover;
    width: 100vh;
    height: 100vh;
    z-index: 0;
}
.title {
   opacity: 0.99;
   color:darkgreen;
}
.subtitle{
	opacity: 0.99;
	color:green;
	font-size: 20px;	
}
</style>