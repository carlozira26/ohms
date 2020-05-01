<template>
	<v-dialog v-model="changeCreds" content-class="modalHeight">
		<v-card>
			<v-card-title primary-title class="green darken-4 white--text">
				<h2>Change Credentials</h2>
			</v-card-title>
			<v-card-text>
				<v-layout row wrap>
					<v-flex xs12 md12 class="pa-1">
						<v-text-field label="Email" type="text" v-model="credentials.email" :rules="[formRules.email]"/>
					</v-flex>
					<v-flex xs12 md6 class="pa-1">
						<v-text-field label="Password" v-model="credentials.password" @click:append="showHide = !showHide" :type="showHide ? 'text' : 'password'" :append-icon="showHide ? 'visibility' : 'visibility_off'"/>
					</v-flex>
					<v-flex xs12 md6 class="pa-1">
						<v-text-field label="Confirm Password" v-model="matchPassword" @click:append="showHide1 = !showHide1" :error-messages="errorMsg" @keyup="matchingPassword" :type="showHide1 ? 'text' : 'password'" :append-icon="showHide1 ? 'visibility' : 'visibility_off'"/>
					</v-flex>
				</v-layout>
			</v-card-text>
			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn flat>Save</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>
<script>
	import axios from 'axios';
	export default {
		created: function(){
			this.eventHub.$on('showChangeCredentials', val => {
				this.changeCreds = true;
				this.userid = val;
				this.getAccount();
			});
		},
		data : () => ({
			changeCreds : false,
			credentials : {
				email : '',
				password : ''
			},
			showHide : false,
			showHide1 : false,
			matchPassword : '',
			errorMsg: '',
			userid : '',
		}),
		methods : {
			matchingPassword : function(){
				if(this.credentials.password != this.matchPassword){
					this.errorMsg = "Password doesn't match!";
				}else{
					this.errorMsg = "";
				}
			},
			getAccount : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/account/' + this.userid)
				.then(function(res){
					_this.credentials.email = res.data.data.email;
				});
			},
		}
	};
</script>
<style>
	.modalHeight {
		max-width: 50%;
	}
	@media only screen and (max-width: 600px){
		.modalHeight {
			max-width: 100%;
		}
	}
</style>