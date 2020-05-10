<template>
	<div>
		<v-dialog v-model="dialog" content-class="profile" scrollable>
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h1>My Profile</h1>
				</v-card-title>
				<v-card-text>
					<v-form ref="vForm" v-on:submit.prevent="submitForm">
						<template>
							<div style="font-size:20px" class="text-xs-left">
								Patient ID : {{ userData.patient_id }}
							</div>
							<v-divider class="mb-2 mt-2"></v-divider>
							<v-layout row wrap>
								<v-flex xs12 md4 class="pa-1">
									<v-text-field label="First Name" :rules="[formRules.required,formRules.textOnly]" v-model="userData.firstname" type="text"/>
								</v-flex>
								<v-flex xs12 md4 class="pa-1">
									<v-text-field label="Middle Name" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required,formRules.textOnly]" v-model="userData.middlename" type="text"/>
								</v-flex>
								<v-flex xs12 md4 class="pa-1">
									<v-text-field label="Last Name" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required,formRules.textOnly]" v-model="userData.lastname" type="text"/>
								</v-flex>
								<v-flex xs12 md6 class="pa-1">
									<v-text-field label="Username" persistent-hint hint="Cannot be edited" type="text" v-model="userData.username" :rules="[formRules.required]" readonly/>
								</v-flex>
								<v-flex xs12 md6 class="pa-1">
									<v-text-field label="Password will be their birthdate('YYYMMDD')" v-model="userData.password" @click:append="showHide = !showHide" :type="showHide ? 'text' : 'password'" :append-icon="showHide ? 'visibility' : 'visibility_off'" persistent-hint hint="Cannot be edited" readonly/>
								</v-flex>
								<v-flex xs9 md4 class="pa-1">
									<template>
										<v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y full-width min-width="290px">
											<template v-slot:activator="{ on }">
												<v-text-field label="Date of Birth" readonly v-on="on" v-model="userData.dateofbirth" type="text" :rules="[formRules.required]"/>
											</template>
											<v-date-picker color="green darken-4" ref="picker" v-model="dateofbirth" :max="new Date().toISOString().substr(0, 10)" min="1950-01-01">
												<v-btn @click="menu = false" dark block>Close</v-btn>
											</v-date-picker>
										</v-menu>
									</template>
								</v-flex>
								<v-flex xs3 md2 class="pa-1">
									<v-text-field label="Age" v-model="age" type="text" readonly/>
								</v-flex>
								<v-flex xs9 md4 class="pa-1">
									<v-text-field label="Date of Consultation" persistent-hint hint="Cannot be edited" v-model="userData.consultationdate" type="text" readonly/>
								</v-flex>
								<v-flex xs3 md2 class="pa-1">
									<v-select label="Gender" :items="gender" :rules="[formRules.required]" v-model="userData.gender"/>
								</v-flex>
								<v-flex xs4 md4 class="pa-1">
									<v-text-field label="Mobile Number" :rules="[formRules.phoneNumber, formRules.required]" v-model="userData.mobilenumber"/>
								</v-flex>
								<v-flex xs3 md3 class="pa-1">
									<v-text-field label="Status" v-model="userData.status" persistent-hint hint="Cannot be edited" readonly></v-text-field>
								</v-flex>
								<v-flex xs3 md3 class="pa-1">
									<v-select label="DR-TB" :items="presumptive" v-model="userData.drtb" persistent-hint hint="Cannot be edited" readonly/>
								</v-flex>
								<v-flex xs2 md2 class="pa-1">
									<template v-if="userData.drtb=='Yes'">
										<v-select label="TB Category" :items="category" v-model="userData.category" persistent-hint hint="Cannot be edited" readonly></v-select>
									</template>
									<template v-else>
										<v-select label="TB Category" :items="category" disabled></v-select>
									</template>
								</v-flex>
								<v-flex xs12 md12 class="pa-1">
									<v-text-field label="Address" :rules="[formRules.required]" v-model="userData.address"/>
								</v-flex>
								<v-flex xs12 md12 class="pa-1">
									<v-textarea label="Remarks" solo v-model="userData.remarks" persistent-hint hint="Cannot be edited" readonly></v-textarea>
								</v-flex>
							</v-layout>
						</template>
					</v-form>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn large flat @click="submitForm">Save</v-btn>
					<v-btn large flat @click="dialog=false">Cancel</v-btn>
				</v-card-actions>
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
	import VueCookies from 'vue-cookies';
	import axios from "axios";
	export default {
		watch : {
			dateofbirth : function(){
				if(this.dateofbirth){
					this.userData.password = this.dateofbirth.split("-").join("");
					this.age = this.getBirthAge(this.userData.dateofbirth);
				}
			},
		},
		mounted: function(){
			this.eventHub.$on('viewProfile', val => {
				this.token = VueCookies.get(this.cookieKey).token;
				this.userid = VueCookies.get(this.cookieKey).data.id;
				this.dialog = true;
				this.fetchProfile();
			});
		},
		data: function(){
			return{
				userData : [],
				showHide : false,
				category: ["Cat I", "Cat II","MDR"],
				gender: ["Male", "Female"],
				presumptive: ["Yes","No"],
				menu : false,
				dateofbirth : '',
				age : '',
				dialog : false,

				sbar : false,
				message : '',
				icon : '',
				snackBarColor : '',
			}
		}, 
		methods : {
			getAge : function(){
				if(this.userData.dateofbirth){
					this.userData.password = this.userData.dateofBirth.split("-").join("");
					this.age = this.getBirthAge(this.userData.dateofBirth);
				}
			},
			save : function(date) {
				this.getAge();
				this.$refs.menu.save(date);
			},
			fetchProfile : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/profile')
				.then(function(res){
					if(res.data.status){
						_this.userData = res.data.data;
						_this.dateofbirth = _this.userData.dateofbirth;
					}
				});
			},
			submitForm : function(){
				let _this = this,
				formData = new FormData();
				formData.append('id', this.userData.id);
				formData.append('firstname', this.userData.firstname);
				formData.append('middlename', this.userData.middlename);
				formData.append('lastname', this.userData.lastname);
				formData.append('dateofbirth', this.userData.dateofbirth);
				formData.append('mobilenumber', this.userData.mobilenumber);
				formData.append('address', this.userData.address);

				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/patient/profile/submit',formData)
				.then(function(res){
					if(res.data.status){
						_this.message = "Successfully submitted!";
						_this.icon = "check";
						_this.snackBarColor = "green";
						_this.sbar = true;
						_this.dialog = false;
					}else{
						_this.snackbarColor = 'red';
						_this.icon = "close";
						_this.message = "There's an error while submitting.";
						_this.sbar = true;
						_this.dialog = false;
					}
				});
			}
		}
	};
</script>
<style>
	.profile {
		max-width : 60%;
	}
	@media only screen and (max-width: 600px){
		.profile {
			max-width: 100%;
		}
	}
</style>