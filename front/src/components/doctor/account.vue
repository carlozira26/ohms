<template>
	<div>
		<v-layout row wrap>
			<v-flex class="pa-3">
				<v-card>
					<v-card-title>
						<h1 class="green--text">Personal Account</h1>
						<v-spacer></v-spacer>
						<v-btn flat icon large class="green darken-4 white--text" @click="openModal"><v-icon medium>save</v-icon></v-btn>
					</v-card-title>
					<v-card-text>
						<v-form ref="vForm" v-on:submit.prevent="saveChanges">
							<v-layout row wrap>
								<v-flex sm3 md2>
									<div @click="$refs.file.click()">
										<input type="file" ref="file" style="display: none" @change="imageSelect">
										<div class="text-xs-center" v-if="userData.image_path">
											<img style="cursor:pointer; border:1px solid grey; object-fit: cover; height:180px; width:100%" :src="userData.image_path"/>
										</div>
										<div class="text-xs-center" style="cursor:pointer; margin:5px; border:1px solid grey;" v-else>
											<v-icon dark color="grey" style="margin-top:60px;height:90px">control_point</v-icon>
										</div>
									</div>
								</v-flex>
								<v-flex sm9 md10 style="padding-left:10px">
									<v-layout row wrap>
										<v-flex xs4 md4 class="pa-1">
											<v-text-field label="First Name" v-model="userData.firstname" type="text" :rules="[formRules.required]"/>
										</v-flex>
										<v-flex xs4 md4 class="pa-1">
											<v-text-field label="Middle Name" v-model="userData.middlename" type="text" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required]"/>
										</v-flex>
										<v-flex xs4 md4 class="pa-1">
											<v-text-field label="Last Name" v-model="userData.lastname" type="text" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required]"/>
										</v-flex>
										<v-flex xs5 md3 class="pa-1">
											<v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y full-width min-width="290px">
												<template v-slot:activator="{ on }">
													<v-text-field label="Date of Birth" v-model="userData.birthdate" readonly v-on="on" @change="save" type="text" :rules="[formRules.required]"/>
												</template>
												<v-date-picker color="green darken-4" ref="picker" v-model="userData.birthdate" :max="new Date().toISOString().substr(0, 10)" min="1950-01-01" @change="getAge">
													<v-btn @click="menu = false" dark block>Close</v-btn>
												</v-date-picker>
											</v-menu>
										</v-flex>
										<v-flex xs3 md2 class="pa-1">
											<v-text-field label="Age" type="text" v-model="age" readonly/>
										</v-flex>
										<v-flex xs4 md3 class="pa-1">
											<v-select label="Gender" type="text" v-model="userData.gender" :items="gender" :rules="[formRules.required]"></v-select>
										</v-flex>
										<v-flex xs12 md4 class="pa-1">
											<v-text-field label="Mobile Number" :rules="[formRules.phoneNumber, formRules.required]" v-model="userData.contact_number" type="text"/>
										</v-flex>
									</v-layout>
								</v-flex>
								<template v-if="role == 2">
									<v-flex xs12 md4 class="pa-1">
										<v-text-field label="License Number" v-model="userData.licensenumber" :rules="[formRules.licenseNumber]" type="text"/>
									</v-flex>
									<v-flex xs12 md4 class="pa-1">
										<v-select label="Specialization" :items="specializationList" item-text="type" @change="getSub" v-model="userData.specialization" type="text"/>
									</v-flex>
									<v-flex xs12 md4 class="pa-1">
										<v-select label="Sub-Specialization" :items="subSpecializationList" item-text="sub_type" v-model="userData.subspecialization" type="text"/>
									</v-flex>
									<v-flex xs12 md6 class="pa-1">
										<v-text-field label="Clinic Name" v-model="userData.clinic_name" type="text"/>
									</v-flex>
									<v-flex xs12 md6 class="pa-1">
										<v-text-field label="Clinic Adress" v-model="userData.clinic_address" type="text"/>
									</v-flex>
								</template>
							</v-layout>
						</v-form>
					</v-card-text>
				</v-card>
			</v-flex>
		</v-layout>
		<v-snackbar v-model="sbar" :color="snackBarColor" right top>
			<v-icon class="white--text" left>{{ icon }}</v-icon>
			{{ message }}
			<v-btn flat @click.native="sbar = false"> &times; </v-btn>
		</v-snackbar>

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
	</div>
</template>
<script>
	import VueCookies from 'vue-cookies';
	import axios from 'axios';

	export default{
		watch: {
			menu (val) {
				val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
			},
		},
		created: function(){
			this.token = VueCookies.get(this.cookieKey).token;
			this.userid = VueCookies.get(this.cookieKey).data.id;
			this.role = VueCookies.get(this.cookieKey).data.role;
			if(this.role == 'none'){
				this.$router.push('/dashboard');
			}
			this.getSpecializations();
			this.getAccount();
		},
		data : function(){
			return {
				userData : [],
				specializationList : [],
				subSpecializationList : [],
				sub : [],
				
				age: 0,
				menu : false,
				sbar: false,
				showHide: false,
				showHide1: false,
				passwordDialog: false,
				confirmPassword: "",
				
				message : "",
				snackBarColor: "",
				icon: "",

				loading: false,

				password: "",
				matchPassword: "",
				errorMsg: "",

				image: null,
			}
		},
		methods : {
			getSub : function(sub){
				for(let x in this.specializationList){
					if(this.userData.specialization == this.specializationList[x].type){
						for(let y in this.sub){
							if(this.specializationList[x].id == this.sub[y].uid){
								this.subSpecializationList.push(this.sub[y].sub_type);
							}
						}
						this.userData.subspecialization = sub;
					}
				}
			},
			imageSelect(e){
                this.image = e.target.files[0];
                if(this.image.type === "image/png" || this.image.type === "image/jpeg"){
                    this.userData.image_path = URL.createObjectURL(this.image);
                }else{
					this.message = "Please select a valid image!";
					this.snackBarColor = "red";
					this.icon = "cancel";
					this.sbar = true;
                    this.userData.image_path = null;
                    this.image = null;
                }
            },
			getSpecializations : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers	 : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/specializations')
				.then(function(res){
					_this.specializationList = res.data.data.specializations;
					_this.sub = res.data.data.subspecializations;
				})
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
					_this.userData = res.data.data;
					_this.age = _this.getBirthAge(_this.userData.birthdate);
					_this.getSub(_this.userData.subspecialization);
				});
			},
			openModal : function(){
				this.confirmPassword='';
				if( this.$refs.vForm.validate() ){
					if(this.password != ""){
						var err = false;
						if(this.password != this.matchPassword){
							this.message = "There's an error on the form. Please double check.";
							this.snackBarColor = "red";
							this.icon = "error";
							this.sbar = true;
							err = true;
						}
					}else{ err = false; }
					if(err == false){
						this.passwordDialog = true;
					}
				}else{

					this.message = "There's an error on the form. Please double check.";
					this.snackBarColor = "red";
					this.icon = "error";
					this.sbar = true;
				}
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
			saveChanges : function(){
				let _this = this,
				formData = new FormData();
				_this.loading = true;
				formData.append('firstname',_this.userData.firstname);
				formData.append('middlename',_this.userData.middlename);
				formData.append('lastname',_this.userData.lastname);
				formData.append('birthdate',_this.userData.birthdate);
				formData.append('gender',_this.userData.gender);
				formData.append('contact_number',_this.userData.contact_number);
				formData.append('licensenumber', _this.userData.licensenumber);
				formData.append('specialization', _this.userData.specialization);
				formData.append('subspecialization', _this.userData.subspecialization);
				formData.append('clinic_name',_this.userData.clinic_name);
				formData.append('clinic_address',_this.userData.clinic_address);
				if(_this.image!==null){
                    formData.append('imageFile', _this.image, _this.image.name);
                }
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post(_this.apiUrl + '/users/change/' + this.userid, formData)
				.then(function(res){
					setTimeout(() => {
						_this.loading = false;
						_this.passwordDialog = false;
						_this.sbar = true;
					}, 500);
				});
			},
			save : function(date) {
				this.$refs.menu.save(date);
			},
			getAge : function(){
				this.age = this.getBirthAge(this.userData.birthdate);
			},
		}
	};
</script>
