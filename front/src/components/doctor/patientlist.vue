<template>
	<div>
		<v-layout row wrap>
			<v-flex class="pa-3">
				<v-card min-width="700px" style="overflow:auto">
					<v-card-title>
						<h1 class="green--text">Patients List</h1>
						<v-spacer></v-spacer>
						<v-tooltip bottom>
							<template v-slot:activator="{ on }">
								<v-btn color="green darken-4" v-on="on" @click="patientModal('add')" round><v-icon class="white--text" medium>person_add</v-icon></v-btn>
							</template>
							<span>Add Patient</span>
						</v-tooltip>
						<v-tooltip bottom>
							<template v-slot:activator="{ on }">
								<v-btn color="green darken-4" v-on="on" @click="openMedicineViewer" round><v-icon class="white--text" medium>fa fa-pills</v-icon></v-btn>
							</template>
							<span>View Medicines</span>
						</v-tooltip>
					</v-card-title>
					<v-card-text>
						<v-layout row wrap>
							<v-flex md10>
								<v-text-field single-line prepend-icon="search" full-width hide-details @keyup="loadPatients" v-model="search" label="Search here ..."></v-text-field>
							</v-flex>
							<v-flex md2>
								<v-select solo label="Status" :items="status" v-model="statusFilter" @change="loadPatients"></v-select>
							</v-flex>
						</v-layout>
						<table class="v-datatable v-table" style="border:1px solid #ddd">
							<thead>
								<tr class="grey lighten-4" style="border-bottom:1px solid #333">
									<th class="font-weight-bold text-xs-center" style="width:10%;">PATIENT ID</th>
									<th style="width:10%;">
										<v-btn class="font-weight-bold text-xs-center" flat @click="sortTable()">
											NAME
											<template v-if="clickSort == true">
												<v-icon small right>{{ filterIcon }}</v-icon>
											</template>
										</v-btn>
									</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">STATUS</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">TB CATEGORY</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">REMARKS</th>
									<th class="font-weight-bold text-xs-center" style="width:10%;">ACTIONS</th>
								</tr>
							</thead>
							<tbody>
								<template v-if="patientList.length == 0">
									<tr>
										<td colspan="6" class="text-center">No data found</td>
									</tr>
								</template>
								<template v-else>
									<tr v-for="(patient,index) in patientList"  v-bind:key="patient.id">
										<td>{{ patient.patient_id }}</td>
										<td>{{ patient.lastname + ", " + patient.firstname }}</td>
										<td  class="pt-2"><v-select flat box label="Status" :items="status" v-model="patient.status" @change="changeStatus(index)" solo></v-select></td>
										<td>{{ patient.category }}</td>
										<td>{{ patient.remarks }}</td>
										<td>
											<template>
											<v-menu left origin="right top" transition="scale-transition">
												<template v-slot:activator="{ on }">
													<v-btn flat icon v-on="on"><v-icon small>fa fa-ellipsis-v</v-icon></v-btn>
												</template>
												<v-list>
													<v-list-tile @click="patientModal('edit',index)">
														<v-list-tile-avatar>
															<v-icon>fa fa-user-edit</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Edit Details</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('assignDoctor',patient.id)">
														<v-list-tile-avatar>
															<v-icon>fa fa-user-nurse</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title v-if="role==1">Assign Doctor</v-list-tile-title>
														<v-list-tile-title v-else>Reassign Doctor</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showHealthTracker',patient.id)">
														<v-list-tile-avatar>
															<v-icon>fa fa-file-contract</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Healthcare Monitoring</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showLabResults',patient.id)">
														<v-list-tile-avatar>
															<v-icon>fa fa-file-medical-alt</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Laboratory Results</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showScheduler',patient.id)">
														<v-list-tile-avatar>
															<v-icon>fa fa-calendar-alt</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Scheduler</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showMessage',patient.id)">
														<v-list-tile-avatar>
															<v-icon>fa fa-comment-medical</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Message</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showAddMedicine',patient.id)">
														<v-list-tile-avatar>
															<v-icon>fa fa-pills</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Add Medicine</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('',patient.id)">
														<v-list-tile-avatar>
															<v-icon>fa fa-archive</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Archive</v-list-tile-title>
													</v-list-tile>
												</v-list>
											</v-menu>
										</template>
										</td>
									</tr>
								</template>
							</tbody>
						</table>
						<v-pagination circle color="green darken-4" total-visible="8" v-model="pagination.page" :length="pagination.length" light @input="loadPatients"></v-pagination>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<template>Head Count: {{ headCount }}</template>
					</v-card-actions>
				</v-card>
			</v-flex>
		</v-layout>
		<v-dialog v-model="patientDetails" width="700">
			<v-card>
				<v-form ref="vForm" v-on:submit.prevent="patientAccount">
					<v-card-title primary-title class="green darken-4 white--text">
						<h1>{{ title }}</h1>
					</v-card-title>
					<v-card-text>
						<template>
							<div style="font-size:20px" class="text-xs-left">
								Patient ID : {{ patientID }}
							</div>
							<v-divider class="mb-2 mt-2"></v-divider>
							<v-layout row wrap>
								<v-flex xs12 md4 class="pa-1">
									<v-text-field label="First Name" :rules="[formRules.required]" v-model="firstName" type="text"/>
								</v-flex>
								<v-flex xs12 md4 class="pa-1">
									<v-text-field label="Middle Name" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required]" v-model="middleName" type="text"/>
								</v-flex>
								<v-flex xs12 md4 class="pa-1">
									<v-text-field label="Last Name" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required]" v-model="lastName" type="text"/>
								</v-flex>
								<v-flex xs12 md6 class="pa-1">
									<v-text-field label="Username" type="text" v-model="username" :rules="[formRules.required]"/>
								</v-flex>
								<v-flex xs12 md6 class="pa-1">
									<v-text-field label="Password will be their birthdate('YYYMMDD')" v-model="password" @click:append="showHide = !showHide" :type="showHide ? 'text' : 'password'" :append-icon="showHide ? 'visibility' : 'visibility_off'" readonly/>
								</v-flex>
								<v-flex xs9 md4 class="pa-1">
									<template>
										<v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y full-width min-width="290px">
											<template v-slot:activator="{ on }">
												<v-text-field label="Date of Birth" readonly v-on="on" v-model="dateofBirth" @change="save" type="text" :rules="[formRules.required]"/>
											</template>
											<v-date-picker color="green darken-4" ref="picker" v-model="dateofBirth" :max="new Date().toISOString().substr(0, 10)" min="1950-01-01">
												<v-btn @click="menu = false" dark block>Close</v-btn>
											</v-date-picker>
										</v-menu>
									</template>
								</v-flex>
								<v-flex xs3 md2 class="pa-1">
									<v-text-field label="Age" v-model="age" type="text" readonly/>
								</v-flex>
								<v-flex xs9 md4 class="pa-1">
									<v-text-field label="Date of Consultation" v-model="consultationDate" type="text" readonly/>
								</v-flex>
								<v-flex xs3 md2 class="pa-1">
									<v-select label="Gender" :items="gender" :rules="[formRules.required]" v-model="patientgender"/>
								</v-flex>
								<v-flex xs4 md4 class="pa-1">
									<v-text-field label="Mobile Number" :rules="[formRules.phoneNumber, formRules.required]" v-model="mobilenumber"/>
								</v-flex>
								<v-flex xs3 md3 class="pa-1">
									<v-text-field label="Status" v-model="patientstatus" readonly></v-text-field>
									<!-- <v-select label="Status" :items="status" v-model="patientstatus"/> -->
								</v-flex>
								<v-flex xs3 md3 class="pa-1">
									<v-select label="DR-TB" :items="presumptive" v-model="drtb"/>
								</v-flex>
								<v-flex xs2 md2 class="pa-1">
									<template v-if="drtb=='Yes'">
										<v-select label="TB Category" :items="category" v-model="tbcategory"></v-select>
									</template>
									<template v-else>
										<v-select label="TB Category" :items="category" disabled></v-select>
									</template>
								</v-flex>
								<v-flex xs12 md12 class="pa-1">
									<v-text-field label="Address" :rules="[formRules.required]" v-model="address"/>
								</v-flex>
								<v-flex xs12 md12 class="pa-1">
									<v-textarea label="Remarks" solo v-model="remarks"></v-textarea>
								</v-flex>
							</v-layout>
						</template>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<v-btn large flat @click="patientAccount(formType)">Submit</v-btn>
						<v-btn large flat @click="patientDetails=false">Cancel</v-btn>
					</v-card-actions>
				</v-form>
			</v-card>
		</v-dialog>
		<v-dialog v-model="statusChangeModal" width="30%">
			<v-card>
				<v-card-title class="green darken-4 white--text"><h2>Change Status ({{statusType}})</h2></v-card-title>
				<v-card-text>
					<template v-if="reasonTemplate == false">
						<p>Are you sure you want to change the status of this patient?</p>
						<h3>{{ fullname }}</h3>
					</template>
					<template v-else>
						<label>Please input a remarks:</label>
						<v-textarea solo label="Type here ..." v-model="reasons"></v-textarea>
					</template>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat @click="confirmChangeStatus('submit')">Confirm</v-btn>
					<v-btn flat @click="confirmChangeStatus('cancel')">Cancel</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-snackbar v-model="sbar" :color="snackBarColor" right top>
			<v-icon class="white--text" left>{{ icon }}</v-icon>
			{{ message }}
			<v-btn flat @click.native="sbar = false"> &times; </v-btn>
		</v-snackbar>
	<health-tracker></health-tracker>
	<scheduler></scheduler>
	<add-medicine></add-medicine>
	<view-medicine></view-medicine>
	<assign-doctor></assign-doctor>
	<lab-results></lab-results>
	
	</div>
</template>
<script>
	import VueCookies from "vue-cookies";
	import axios from "axios";
	import healthTracker from './modal/healthcare-monitoring.vue';
	import scheduler from './modal/scheduler.vue';
	import addMedicine from './modal/add-patient-medicine.vue';
	import viewMedicine from './modal/view-medicine.vue';
	import assignDoctor from './modal/assign-doctor.vue';
	import labResults from './modal/lab-results.vue';
	export default {
		components : {
			'health-tracker' : healthTracker,
			'scheduler' : scheduler,
			'add-medicine' : addMedicine,
			'view-medicine' : viewMedicine,
			'assign-doctor' : assignDoctor,
			'lab-results' : labResults,
		},
		watch : {
			dateofBirth : function(){
				if(this.dateofBirth){
					this.password = this.dateofBirth.split("-").join("");
					this.age = this.getBirthAge(this.dateofBirth);
				}
			},
			
		},
		created : function(){
			this.token = VueCookies.get(this.cookieKey).token;
			this.role = VueCookies.get(this.cookieKey).data.role;
			if(this.role == 'none'){
				this.$router.push('/dashboard');
			}
			this.getDate();
			this.loadPatients();
			this.eventHub.$on('showSnackBar', val =>{
				this.icon = val.icon;
				this.snackBarColor = val.color;
				this.message = val.message;
				this.sbar = true;
			});
		},
		data : function() {
			return {
				patientDetails : false,
				showHide: false,
				phase : 1,
				menu: false,
				status: ["New", "Ongoing", "Success", "Discontinuation"],
				statusFilter:"",
				gender: ["Male", "Female"],
				presumptive: ["Yes","No"],
				category: ["Cat I", "Cat II","MDR"],
				patientList: [],
				title: "",
				formType : "",

				statusChangeModal:false,
				fullname: "",
				indexChange : "",
				reasons: null,
				reasonTemplate : true,
				statusType: "",

				pagination:{ page: 1, length: 0},
				search: "",
				headCount : 0,

				snackBarColor: "",
				sbar: "",
				message: "",
				icon: "",

				age: 0,
				id: null,
				patientID : "",
				firstName: "",
				middleName: "",
				lastName: "",
				dateofBirth: "",
				consultationDate:"",
				patientgender: "",
				mobilenumber: "",
				patientstatus: "New",
				drtb: "Yes",
				tbcategory: "",
				address: "",
				remarks: "",
				username: "",
				password: "",

				clickSort : false,
				filterIconList : ["fa-sort-up","fa-sort-down"],
				filterIcon : "",
				filterCount: 0,
				sortType : "",

			}
		},
		methods : {
			save : function(date) {
				this.$refs.menu.save(date);
			},
			getDate : function(){
				this.consultationDate = new Date().toISOString().substr(0, 10);
			},
			patientAccount : function(){
				if( this.$refs.vForm.validate() ){
					let _this = this,
					formData = new FormData();

					if(_this.id!=null){
						formData.append('id',_this.id);
					}
					if(_this.formType == 'add'){
						formData.append('patientid', _this.patientID);
					}
					formData.append('type', _this.formType);
					formData.append('firstname', _this.firstName);
					formData.append('middlename', _this.middleName);
					formData.append('lastname', _this.lastName);
					formData.append('dateofbirth', _this.dateofBirth);
					formData.append('consultationdate', _this.consultationDate);
					formData.append('gender', _this.patientgender);
					formData.append('mobilenumber', _this.mobilenumber);
					formData.append('drtb', _this.drtb);
					formData.append('category',_this.tbcategory);
					formData.append('address', _this.address);
					formData.append('remarks', _this.remarks);
					formData.append('username', _this.username);
					formData.append('password', _this.password);

					axios.create({
						baseURL : _this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/patients/addedit', formData)
					.then(function(res){
						_this.loadPatients();
						_this.message = res.data.message;
						_this.icon = "done";
						_this.snackBarColor = "green";
						_this.sbar = true;
						_this.patientDetails = false;
					});
				}else{
					this.message = "There's an error on the form. Please double check.";
					this.icon = "error";
					this.snackBarColor = "red";
					this.sbar = true;
				}
			},
			loadPatients : function(){
				let _this = this;
				
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/list?page='+_this.pagination.page+'&search='+_this.search+'&sort='+_this.sortType+"&status="+_this.statusFilter)
				.then(function(res){
					console.log(res.data.data);
					_this.patientList = res.data.data;
					_this.headCount = res.data.count.count;
					_this.pagination.length = Math.ceil(res.data.count.count / 20);
				})
			},
			openMedicineViewer(){
				this.eventHub.$emit("showMedicineList",true);
			},
			openModal : function(modal,patientID){
				this.eventHub.$emit(modal, {'patientID': patientID});
			},
			resetForm(){
				this.$refs.vForm.reset();
				setTimeout(() => {
					this.getDate();
					this.patientstatus = "New";
					this.drtb = "Yes";
				}, 1);
				
			},
			patientModal(type,index) {
				this.formType = type;
				if(type == "add"){
					this.title = "New Patient";
					this.id = null;
					this.getlastID();
					this.resetForm();
				}else{
					this.title = "Edit Patient";
					this.id = this.patientList[index].id;
					this.patientID = this.patientList[index].patient_id;
					this.firstName = this.patientList[index].firstname;
					this.middleName = this.patientList[index].middlename;
					this.lastName = this.patientList[index].lastname;
					this.username = this.patientList[index].username;
					this.dateofBirth = this.patientList[index].dateofbirth;
					this.consultationDate = this.patientList[index].consultationdate;
					this.patientgender = this.patientList[index].gender;
					this.mobilenumber = this.patientList[index].mobilenumber;
					this.patientstatus = this.patientList[index].status;
					this.drtb = this.patientList[index].drtb;
					this.address = this.patientList[index].address;
					this.remarks = this.patientList[index].remarks;
				}
				this.patientDetails = true;
			},
			getlastID : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/lastid')
				.then(function(res){
					let id = res.data.patient_id;
					let currentYear = new Date().toISOString().substr(2,2);
					let year = String(id).substring(0,2)
					let lastid = String(id).substring(2,6);
					if(year == currentYear){
						lastid = parseInt(lastid) + 1;
					}else{
						lastid = 1;
					}
					_this.patientID = _this.zeroPad(lastid,4);
				});
			},
			changeStatus : function(index){
				this.reasons = "";
				this.fullname = this.patientList[index].lastname+", "+this.patientList[index].firstname;
				this.indexChange = index;
				this.reasonTemplate = false;
				this.statusType = this.patientList[index].status;
				if(this.patientList[index].status == 'Success' || this.patientList[index].status == 'Discontinuation'){
					this.reasonTemplate = true;
				}
				this.statusChangeModal = true;
			},
			confirmChangeStatus : function(btnstatus){
				let id = this.patientList[this.indexChange].id;
				if(btnstatus == 'cancel'){
					this.loadPatients();
					this.statusChangeModal = false;
				}else{
					let _this = this,
					formData = new FormData();
					formData.append('status',_this.patientList[_this.indexChange].status);
					if(_this.reasons != null){
						formData.append('reason',_this.reasons);
					}
					axios.create({
						baseURL : _this.apiUrl,
						headers: {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/patients/status/'+id,formData)
					.then(function(res){
						_this.message = res.data.message;
						_this.icon = "done";
						_this.snackBarColor = "green";
						_this.sbar = true;
						_this.statusChangeModal = false;
					})
				}
			},
			sortTable : function(){
				if(this.filterCount == 0){
					this.clickSort=true;
					this.filterIcon = this.filterIconList[this.filterCount];
					this.sortType = "asc";
					this.filterCount = 1;
				}else if(this.filterCount == 1){
					this.filterIcon = this.filterIconList[this.filterCount];
					this.sortType = "desc";
					this.filterCount = 2;
				}else{
					this.clickSort = false;
					this.sortType = "";
					this.filterCount = 0;
				}
				this.loadPatients();
			},
		}
	};
</script>