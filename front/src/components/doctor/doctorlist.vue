<template>
	<div>
		<v-layout row wrap>
			<v-flex class="pa-3">
				<v-card min-width="700px">
					<v-card-title>
						<h1 class="green--text">List of Doctors Account</h1>
						<v-spacer></v-spacer>
						<v-tooltip bottom>
							<template v-slot:activator="{ on }">
								<v-btn color="green darken-4" v-on="on" @click="doctorModal('add')" round><v-icon class="white--text" medium>person_add</v-icon></v-btn>
							</template>
							<span>Add New Doctor</span>
						</v-tooltip>
					</v-card-title>
					<v-card-text>
						<v-text-field single-line prepend-icon="search" full-width hide-details v-model="search" @keyup="loadDoctors" label="Search here ..."></v-text-field>
						<table class="v-datatable v-table" style="border:1px solid #ddd">
							<thead>
								<tr class="grey lighten-4" style="border-bottom:1px solid #333">
									<th class="font-weight-bold text-xs-center" style="width:10%;">NAME</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">SPECIALIZATION</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">CLINIC NAME</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">CLINIC ADDRESS</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">ACTIONS</th>
								</tr>
							</thead>
							<tbody>
								<template v-if="doctorsList.length == 0">
									<tr>
										<td colspan="6" class="text-center">No data found</td>
									</tr>
								</template>
								<template v-else>
									<tr v-for="(model,index) in doctorsList"  v-bind:key="index">
										<td class="font-weight-medium">Dr. {{model.lastname}}, {{model.firstname}}</td>
										<td>{{model.specialization}}</td>
										<td>{{model.clinic_name}}</td>
										<td>{{model.clinic_address}}</td>
										<td>
											<v-tooltip bottom>
												<template v-slot:activator="{ on }">
													<v-btn v-on="on" icon dark color="green darken-4" @click="doctorModal('edit',index)"><v-icon small dark>fa fa-id-card</v-icon></v-btn>
												</template>
												<span>Doctor's Information</span>
											</v-tooltip>
											<v-tooltip bottom>
												<template v-slot:activator="{ on }">
													<template v-if="model.is_active == 'Y'">
														<v-btn v-on="on" icon dark color="red darken-4" @click="deactivateDoctorAccount(model.id,'deactivate')"><v-icon small dark>fa fa-user-times</v-icon></v-btn>
													</template>
													<template v-else>
														<v-btn v-on="on" icon dark color="red darken-4" @click="deactivateDoctorAccount(model.id,'reactivate')"><v-icon small dark>fa fa-user-check</v-icon></v-btn>
													</template>
												</template>
											<span v-if="model.is_active == 'Y'">Deactivate Account</span>
											<span v-else>Reactivate Account</span>
											</v-tooltip>
										</td>
									</tr>
								</template>
							</tbody>
						</table>
						<v-pagination circle color="green darken-4" total-visible="8" v-model="pagination.page" :length="pagination.length" light @input="loadDoctors"></v-pagination>
					</v-card-text>
				</v-card>
			</v-flex>
		</v-layout>
		<v-dialog width="700" v-model="doctorAccountModal" scrollable>
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h1>{{ title }}</h1>
					<template v-if="formType=='edit'">
						<v-spacer></v-spacer>
						<v-btn flat class="white--text" outline round @click="openDoctorSchedule()">Schedule</v-btn>
					</template>
				</v-card-title>
				<v-card-text>
					<v-form ref="vForm" v-on:submit.prevent="submitDoctorAccount">
						<v-layout row wrap>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="First Name" :rules="[formRules.required]" v-model="profile.firstName" type="text"/>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Middle Name" :rules="[formRules.required]" v-model="profile.middleName" type="text"/>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Last Name" :rules="[formRules.required]" v-model="profile.lastName" type="text"/>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<template>
									<v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y full-width min-width="290px">
										<template v-slot:activator="{ on }">
											<v-text-field label="Date of Birth" readonly v-on="on" v-model="profile.dateofBirth" @change="save" type="text" :rules="[formRules.required]"/>
										</template>
										<v-date-picker color="green darken-4" ref="picker" v-model="profile.dateofBirth" :max="new Date().toISOString().substr(0, 10)" @input="menu = false,changeDOB()" min="1950-01-01">
										</v-date-picker>
									</v-menu>
								</template>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-select label="Gender" :items="gender" :rules="[formRules.required]" v-model="profile.doctorsgender" type="text"/>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Mobile Number" :rules="[formRules.phoneNumber, formRules.required]" v-model="profile.mobileNumber" type="text"/>
							</v-flex>
							<v-flex xs12 md6 class="pa-1">
								<v-text-field label="Email" :rules="[formRules.email]" v-model="profile.email" type="text"/>
							</v-flex>
							<v-flex xs12 md6 class="pa-1">
								<v-text-field label="Default Password (Birth Date)" v-model="profile.password" @click:append="showHide = !showHide" :type="showHide ? 'text' : 'password'" :append-icon="showHide ? 'visibility' : 'visibility_off'" readonly/>
							</v-flex>
							<template v-if="isadmin == true"></template>
							<template v-else>
								<v-flex xs12 md6 class="pa-1">
									<v-text-field label="License Number" v-model="profile.license" :rules="[formRules.licenseNumber]" type="text"/>
								</v-flex>
								<v-flex xs12 md6 class="pa-1">
									<v-select label="Specialization" :items="specializationList" item-text="type" v-model="profile.specialization"/>
								</v-flex>
								<v-flex xs12 md12 class="pa-1">
									<v-text-field label="Clinic Name" v-model="profile.clinicName" type="text"/>
								</v-flex>
								<v-flex xs12 md12 class="pa-1">
									<v-text-field label="Clinic Address" v-model="profile.clinicAddress" type="text"/>
								</v-flex>
							</template>
						</v-layout>
					</v-form>
				</v-card-text>
				<v-card-actions class="pa-3">
					<v-checkbox label="Is Admin" v-model="isadmin"></v-checkbox>
					<v-spacer></v-spacer>
					<v-btn large flat @click="submitDoctorAccount(formType)" v-if="formType == 'add'">Submit</v-btn>
					<v-btn large flat @click="doctorAccountModal=false">Cancel</v-btn>
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
	import VueCookies from "vue-cookies";
	import axios from "axios";
	export default {
		created : function(){
			this.token = VueCookies.get(this.cookieKey).token;
			this.role = VueCookies.get(this.cookieKey).data.role;
			this.loadDoctors();
			this.getSpecializations();
			if(this.role == 'none'){
				this.$router.push('/dashboard');
			}else if(this.role == '2'){
				this.$router.push('/');
			}
		},
		data : function() {
			return {
				showHide : false,
				menu: false,
				adminSelect : ['Y','N'],
				pagination:{ page: 1, length: 0},
				search: "",

				snackBarColor: "",
				sbar: "",
				message: "",
				icon: "",

				doctorAccountModal : false,
				title: "",
				formType : "",

				doctorsList : [],
				specializationList : [],

				profile : [],

				isadmin:false
			}
		},
		methods : {
			changeDOB : function(){
				if(this.profile.dateofBirth){
					this.profile.password = this.profile.dateofBirth.split("-").join("");
					this.profile.age = this.getBirthAge(this.profile.dateofBirth);
				}
			},
			save : function(date) {
				console.log(date);
				this.$refs.menu.save(date);
			},
			getSpecializations(){
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
				})
			},
			doctorModal(type,index) {
				this.formType = type;
				if(type == "add"){
					this.title = "Add New Doctor";
					this.$refs.vForm.reset();
				}else{
					this
					this.title = "Doctor's Profile";
					this.profile.id = this.doctorsList[index].id;
					this.profile.firstName = this.doctorsList[index].firstname;
					this.profile.middleName = this.doctorsList[index].middlename;
					this.profile.lastName = this.doctorsList[index].lastname;
					this.profile.dateofBirth = this.doctorsList[index].birthdate;
					this.profile.license = (this.doctorsList[index].licensenumber) ? this.doctorsList[index].licensenumber : "N/A";
					this.profile.specialization = (this.doctorsList[index].specialization) ? this.doctorsList[index].specialization : "";
					this.profile.doctorsgender = this.doctorsList[index].gender;
					this.profile.email = (this.doctorsList[index].email) ? this.doctorsList[index].email : "N/A";
					this.profile.mobileNumber = this.doctorsList[index].contact_number;
					this.profile.clinicName = (this.doctorsList[index].clinic_name) ? this.doctorsList[index].clinic_name : "N/A";
					this.profile.clinicAddress = (this.doctorsList[index].clinic_address) ? this.doctorsList[index].clinic_address : "N/A";
				}
				this.doctorAccountModal = true;
			},
			loadDoctors : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/doctor/account?page='+_this.pagination.page+'&search='+_this.search)
				.then(function(res){
					_this.doctorsList = res.data.data;
					_this.pagination.length = Math.ceil(res.data.count.count / 20);
				});
			},
			submitDoctorAccount : function(){
				let _this = this,
				formData = new FormData();

				formData.append('firstname',_this.profile.firstName);
				formData.append('middlename',_this.profile.middleName);
				formData.append('lastname',_this.profile.lastName);
				formData.append('birthdate',_this.profile.dateofBirth);
				formData.append('license',_this.profile.license);
				formData.append('specialization',_this.profile.specialization);
				formData.append('gender',_this.profile.doctorsgender);
				formData.append('email',_this.profile.email);
				formData.append('password',_this.profile.password);
				formData.append('contactnumber',_this.profile.mobileNumber);
				formData.append('clinicname',_this.profile.clinicName);
				formData.append('clinicaddress',_this.profile.clinicAddress);
				formData.append('isadmin',_this.profile.isadmin);
				if(this.$refs.vForm.validate()){
					axios.create({
						baseURL : this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/users/doctor/create',formData)
					.then(function(res){
						_this.message = res.data.message;
						if(res.data.status){
							_this.icon = "done";
							_this.snackBarColor = "green";
							_this.doctorAccountModal = false;
						}else{
							_this.icon = "error";
							_this.snackBarColor = "red";
						}
						_this.sbar = true;
						_this.loadDoctors();
					})
				}
			},
			deactivateDoctorAccount : function(id,type){
				let _this = this,
				formData = new FormData();

				formData.append('type',type);
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/users/activate/'+id,formData)
				.then(function(res){
					_this.message = res.data.message;
					if(res.data.status){
						_this.icon = "done";
						_this.snackBarColor = "green";
						_this.doctorAccountModal = false;
					}
					_this.sbar = true;
					_this.loadDoctors();
				})
			},
			openDoctorSchedule : function(){
				this.eventHub.$emit('viewDoctorSchedule', { id: this.profile.id});
			},
		}
	};
</script>