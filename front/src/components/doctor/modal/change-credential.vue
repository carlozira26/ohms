<template>
	<div>
		<v-dialog v-model="changeCreds" content-class="modalHeight">
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h2>Change Credentials</h2>
				</v-card-title>
				<v-card-text>
					<v-form ref="vForm" v-on:submit.prevent="saveChanges">
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
					</v-form>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat @click="passwordDialog=true">Save</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog v-model="passwordDialog" persistent max-width="500px" transition="dialog-transition">
			<v-card>
				<v-card-title primary-title class="white--text green darken-4">
					<h2>Password Confirmation Required</h2>
				</v-card-title>
				<template v-if="loading==false">
					<v-card-text>
						<div class="text-xs-left mb-2 mt-2">In order to make changes, please insert your password.</div>
						<v-text-field label="Password" v-model="confirmPassword" solo type="password"></v-text-field>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn flat @click="postConfirmPassword">Confirm</v-btn>
						<v-btn flat @click="passwordDialog=false">Cancel</v-btn>
					</v-card-actions>
				</template>
				<template v-else>
					<v-card-text>
						<v-progress-circular
							:size="70"
							:width="7"
							color="green darken-4"
							indeterminate
						></v-progress-circular>
					</v-card-text>
				</template>
			</v-card>
		</v-dialog>
		<v-dialog v-model="loggingOut" max-width="500px" transition="dialog-transition">
			<v-card>
				<v-card-text>
					<div class="text-lg-center mb-2 mt-2"><h2>Logging out... Please login again</h2></div>
				</v-card-text>
			</v-card>
		</v-dialog>
		<v-snackbar v-model="sbar" :color="snackBarColor" right top>
			<v-icon class="white--text" left>{{ icon }}</v-icon>
			{{ message }}
			<v-btn flat @click.native="sbar = false"> &times; </v-btn>
		</v-snackbar>
	</div>
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
			passwordDialog : false,
			credentials : {
				email : '',
				password : ''
			},
			confirmPassword : '',
			showHide : false,
			showHide1 : false,
			matchPassword : '',
			errorMsg: '',
			userid : '',
			loading : false,
			loggingOut : false,

			message : "",
			snackBarColor: "",
			icon: "",
			sbar : false,
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
			postConfirmPassword : function(){
				let _this = this,
				formData = new FormData();
				formData.append('password',_this.confirmPassword);
				if(_this.confirmPassword!=""){
					axios.create({
						baseURL : _this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/users/confirm/' + this.userid, formData)
					.then(function(res){
						if(res.data.status){
							_this.message = "Successfully Saved";
							_this.snackBarColor = "green";
							_this.icon = "done";
							_this.saveChanges();
						}else{
							_this.message = "Incorrect Password";
							_this.snackBarColor = "red";
							_this.icon = "cancel";
							_this.sbar = true;
						}
					});
				}else{
					_this.message = "Please insert your password!";
					_this.snackBarColor = "red";
					_this.icon = "error";
					_this.sbar = true;
				}
			},
			saveChanges : function(res){
				this.passwordDialog = false;
				let _this = this,
				formData = new FormData();
				formData.append('email',_this.credentials.email);
				formData.append('password',_this.credentials.password);
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/users/change/' + this.userid, formData)
				.then(function(res){
					_this.$refs.vForm.reset();
					setTimeout(() => {
						_this.changeCreds = false;
						_this.loading = false;
						_this.sbar = true;
						_this.loggingOut = true;
						setTimeout(() => {
							_this.$router.push('/logout');
						}, 1500)
					}, 500);

				});
			}
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