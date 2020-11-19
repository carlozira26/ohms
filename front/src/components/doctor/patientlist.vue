<template>
	<div>
		<v-layout row wrap>
			<v-flex class="pa-3">
				<v-card min-width="700px" style="overflow:auto" scrollable>
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
							<v-flex md2 class="pb-2 text-xs-left">
								<v-select solo hide-details :items="status" v-model="statusFilter" @change="loadPatients"></v-select>
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
									<tr v-for="(patient,index) in patientList" v-bind:key="patient.id">
										<td>{{ patient.patient_id }}</td>
										<td>{{ patient.lastname + ", " + patient.firstname }}</td>
										<td  class="pt-1 pb-1">
											<template v-if="patient.status!='Success' && patient.status!='Discontinuation'">
												<v-select flat hide-details box label="Status" :items="status" v-model="patient.status" @change="changeStatus(index)" solo></v-select>
											</template>
											<template v-else>
												{{patient.status}}
											</template>
										</td>
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

													<v-list-tile @click="openModal('showLabResults',index)">
														<v-list-tile-avatar>
															<v-icon>fa fa-file-medical-alt</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Laboratory Results</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showAddMedicine',index)">
														<v-list-tile-avatar>
															<v-icon>fa fa-pills</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Add Medicine</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showScheduler',index)">
														<v-list-tile-avatar>
															<v-icon>fa fa-calendar-alt</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Scheduler</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('showHealthTracker',index)">
														<v-list-tile-avatar>
															<v-icon>fa fa-file-contract</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Healthcare Monitoring</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openMessage('viewMessage',index)">
														<v-list-tile-avatar>
															<v-icon>fa fa-comment-medical</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Message</v-list-tile-title>
													</v-list-tile>

													<v-list-tile @click="openModal('viewIntakeLogs',index)">
														<v-list-tile-avatar>
															<v-icon>fa fa-history</v-icon>
														</v-list-tile-avatar>
														<v-list-tile-title>Intake History</v-list-tile-title>
													</v-list-tile>
												</v-list>
											</v-menu>
										</template>
										</td>
									</tr>
								</template>
							</tbody>
						</table>
						<div>
						<v-pagination circle color="green darken-4" total-visible="8" v-model="pagination.page" :length="pagination.length" light @input="loadPatients"></v-pagination>
					</div>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<template>Head Count: {{ headCount }}</template>
					</v-card-actions>
				</v-card>
			</v-flex>
		</v-layout>
		<v-dialog v-model="patientDetails" width="700" scrollable>
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h1>{{ title }}</h1>
				</v-card-title>
				<v-card-text>
					<v-form ref="vForm" v-on:submit.prevent="patientAccount">
						<div style="font-size:20px" class="text-xs-left" v-if="formType != 'add'">
							Patient ID : {{ patientID }}
							<v-divider class="mb-2 mt-2"></v-divider>
						</div>
						<v-layout row wrap>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="First Name" :rules="[formRules.required,formRules.textOnly]" @change="setUsername" v-model="patient.firstname" @input="patient.firstname=ucWords(patient.firstname)" type="text"></v-text-field>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Middle Name" v-model="patient.middlename" @input="patient.middlename=ucWords(patient.middlename)" type="text" hint="Optional" persistent-hint></v-text-field>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Last Name" @input="patient.lastname=ucWords(patient.lastname)" :rules="[formRules.required,formRules.textOnly]" @change="setUsername" v-model="patient.lastname" type="text"></v-text-field>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Username" type="text" v-model="patient.username" :rules="[formRules.required]"></v-text-field>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Password" hint="Password is defaulted to the Patient's Birthday" persistent-hint v-model="patient.password" @click:append="showHide = !showHide" :type="showHide ? 'text' : 'password'" :append-icon="showHide ? 'visibility' : 'visibility_off'" readonly></v-text-field>
							</v-flex>
							<v-flex md4 class="pa-1">
								<v-select :items="doctorList" item-value="id" item-text="doctor" v-model="patient.doctorid" label="Doctor's Name"></v-select>
							</v-flex>
							<v-flex xs9 md4 class="pa-1">
								<template>
									<v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y full-width min-width="290px">
										<template v-slot:activator="{ on }">
											<v-text-field label="Date of Birth" readonly v-on="on" :value="formatDate(patient.dateofbirth)" type="text" :rules="[formRules.required]"></v-text-field>
										</template>
										<v-date-picker color="green darken-4" ref="picker" @input="changeData" v-model="patient.dateofbirth" :max="new Date().toISOString().substr(0, 10)" min="1950-01-01">
										</v-date-picker>
									</v-menu>
								</template>
							</v-flex>
							<v-flex xs3 md2 class="pa-1">
								<v-text-field label="Age" v-model="age" type="text" readonly></v-text-field>
							</v-flex>
							<v-flex xs9 md4 class="pa-1">
								<v-text-field label="Date of Consultation" :value="formatDate(patient.consultationdate)" type="text" readonly></v-text-field>
							</v-flex>
							<v-flex xs3 md2 class="pa-1">
								<v-select label="Gender" :items="gender" :rules="[formRules.required]" v-model="patient.gender"></v-select>
							</v-flex>
							<v-flex xs4 md4 class="pa-1">
								<v-text-field label="Mobile Number" :rules="[formRules.phoneNumber, formRules.required]" v-model="patient.mobilenumber"></v-text-field>
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-text-field label="Status" v-model="patient.status" readonly></v-text-field>
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-select label="DR-TB" :items="presumptive" v-model="patient.drtb"></v-select>
							</v-flex>
							<v-flex xs2 md2 class="pa-1">
								<v-select label="TB Category" :items="category" :rules="[formRules.required]" v-model="patient.category"></v-select>
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-text-field label="House Number" @input="patient.address=ucWords(patient.address)" :rules="[formRules.required]" v-model="patient.address"></v-text-field>
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-text-field label="Street" @input="patient.street=ucWords(patient.street)"  :rules="[formRules.required]" v-model="patient.street"></v-text-field>
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-select label="Barangay" :items="barangayList" item-text="barangay" :rules="[formRules.required]" v-model="patient.barangay"></v-select>
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-text-field label="Municipality/City" readonly :rules="[formRules.required]" v-model="patient.city"></v-text-field>
							</v-flex>
							<v-flex xs12 md12 class="pa-1">
								<v-textarea label="Remarks" solo v-model="patient.remarks"></v-textarea>
							</v-flex>
						</v-layout>
					</v-form>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn large flat @click="patientAccount(formType)">Submit</v-btn>
					<v-btn large flat @click="patientDetails=false">Cancel</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog v-model="statusChangeModal" width="30%" persistent>
			<v-card>
				<v-card-title class="green darken-4 white--text"><h2>Change Status ({{statusType}})</h2></v-card-title>
				<template v-if="checkPrerequisite==true">
					<v-card-text>
						<template v-if="reasonTemplate == false">
							<p>Are you sure you want to change the status of this patient?</p>
							<h3>{{ fullname }}</h3>
						</template>
						<template v-else>
							<template v-if="statusType == 'Success'">
								<template v-if="intakeStat==false">
									<v-icon color="yellow darken-3" large>warning</v-icon> <h3>Patient must finish medicine intake!</h3>
								</template>
								<template v-else>
									<label>Please upload the final examination of patient:</label>
									<input type="file" ref="file" style="display: none" @change="imageSelect">
									<v-text-field solo readonly v-model="image.name" label="Select an image..." @click="$refs.file.click()" :rules="[formRules.required]"></v-text-field>
									<label>Remarks:</label>
								<v-textarea solo label="Type here ..." v-model="reasons"></v-textarea>
								</template>
							</template>
							<template v-else>
								<label>Please input a remarks:</label>
								<v-textarea solo label="Type here ..." v-model="reasons"></v-textarea>
							</template>
						</template>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<template v-if="intakeStat || statusType == 'Discontinuation' || statusType == 'New'">
							<v-btn flat @click="confirmChangeStatus('submit')">Confirm</v-btn>
						</template>
						<v-btn flat @click="confirmChangeStatus('cancel')">Close</v-btn>
					</v-card-actions>
				</template>
				<template v-else>
					<v-card-text>
						<v-layout row wrap>
							<v-flex>
								<template v-if="prerequisite=='diagnostic'">
									<h3>Please upload a Laboratory Result first</h3>
									<v-btn flat class="blue-grey darken-4 white--text" @click="statusChangeModal=false;openModal('showLabResults',indexChange)">Go to Upload</v-btn>
								</template>
								<template v-else>
									<h3>Please set the medicines first.</h3>
									<v-btn flat class="blue-grey darken-4 white--text" @click="statusChangeModal=false;openModal('showAddMedicine',indexChange)">Go to Add Medicine</v-btn>
								</template>
							</v-flex>
						</v-layout>
					</v-card-text>
				</template>
			</v-card>
		</v-dialog>
		<v-dialog v-model="patientPrivacy" width="500" persistent>
			<v-card>
				<v-card-title class="headline green darken-4 white--text">
					Privacy Policy
					<v-spacer></v-spacer>
					<v-btn flat icon @click="patientPrivacy=false"><v-icon class="white--text">fa-times</v-icon></v-btn>
				</v-card-title>
				<v-card-text class="text-xs-left">
					<b>Republic Act 10173</b> or known as the "<b>Data Privacy Act of 2012</b>"<br><br>
					- It is the policy of the State to protect the fundamental human right of privacy, of communication while ensuring free flow of information to promote innovation and growth. The State recognizes the vital role of information and communications technology in nation-building and its inherent obligation to ensure that personal information in information and communications systems in the government and in the private sector are secured and protected.<br>
					<br>
					Has the patient agreed his or her personal information to be submitted to this form?
				</v-card-text>
				<v-divider></v-divider>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat @click="patientDetails=true;patientPrivacy=false">Accepted</v-btn>
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
	<lab-results></lab-results>
	<intake-logs></intake-logs>
	</div>
</template>
<script>
	import VueCookies from "vue-cookies";
	import axios from "axios";
	import healthTracker from './modal/healthcare-monitoring.vue';
	import scheduler from './modal/scheduler.vue';
	import addMedicine from './modal/add-patient-medicine.vue';
	import viewMedicine from './modal/view-medicine.vue';
	import labResults from './modal/lab-results.vue';
	import intakeLogs from './modal/intakelogs.vue';
	export default {
		components : {
			'health-tracker' : healthTracker,
			'scheduler' : scheduler,
			'add-medicine' : addMedicine,
			'view-medicine' : viewMedicine,
			'intake-logs' : intakeLogs,
			'lab-results' : labResults,
		},
		watch : {
			patient : function(){
				if(this.patient.dateofbirth){
					this.patient.password = this.patient.dateofbirth.split("-").join("");
					this.age = this.getBirthAge(this.patient.dateofbirth);
				}
			},
		},
		created : function(){
			this.token = VueCookies.get(this.cookieKey).token;
			this.role = VueCookies.get(this.cookieKey).data.role;
			this.wsconnect = new WebSocket(this.websocket);
			if(this.role == 'none'){
				this.$router.push('/dashboard');
			}
			this.loadPatients();
			this.fetchBarangayList();
			this.getDoctorList();
			this.eventHub.$on('showSnackBar', val =>{
				this.icon = val.icon;
				this.snackBarColor = val.color;
				this.message = val.message;
				this.sbar = true;
			});

			this.eventHub.$on('updatePatientList', val => {
				for(let i in this.patientList){
					if(this.patientList[i].id == val.patientID){
						if(val.status == 'New'){
							this.patientList[i].status = "Ongoing";
							this.patientList[i].datestart = val.datestart;
							break;
						}
					}
				}
			});
		},
		data : function() {
			return {
				patientData : '',
				patientID : '',
				patientDetails : false,
				patientPrivacy : false,
				showHide: false,
				phase : 1,
				menu1: false,
				status: ["","New", "Ongoing", "Success", "Discontinuation"],
				statusFilter:"",
				gender: ["Male", "Female"],
				presumptive: ["Yes","No"],
				category: ["Cat I", "Cat II","MDR"],
				patientList: [],
				title: "",
				formType : "",
				prerequisite : "",
				menu : false,

				statusChangeModal: false,
				checkPrerequisite: true,
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
				patient : [],

				clickSort : false,
				filterIconList : ["fa-sort-up","fa-sort-down"],
				filterIcon : "",
				filterCount: 0,
				sortType : "",
				barangayList : [],
				doctorList : [],
				date : new Date().toISOString().substr(0, 10),

				image : [],
				diagnostic : [],
				intakeStat : 0,
			}
		},
		methods : {
			countIntake : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/count/intake?id='+this.patientData.id+'&category='+this.patientData.category)
				.then(function(res){
					_this.intakeStat = res.data.status;
				});

			},
			getDoctorList : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/doctor')
				.then(function(res){
					_this.doctorList = res.data;
				});
			},
			setUsername : function(){
				if(this.patient.firstname!=undefined && this.patient.lastname !=undefined){
					let firstname = this.patient.firstname.split(" ");
					let lastname = this.patient.lastname.replace(" ","");
					this.patient.username = firstname[0].toLowerCase() + lastname.toLowerCase();
				}
			},
			changeData : function(){
				this.patient.password = this.patient.dateofbirth.split("-").join("");
				this.age = this.getBirthAge(this.patient.dateofbirth);
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
					formData.append('firstname', _this.patient.firstname);
					formData.append('middlename', _this.patient.middlename);
					formData.append('lastname', _this.patient.lastname);
					formData.append('doctorid', _this.patient.doctorid);
					formData.append('dateofbirth', _this.patient.dateofbirth);
					formData.append('consultationdate', _this.patient.consultationdate);
					formData.append('gender', _this.patient.gender);
					formData.append('mobilenumber', _this.patient.mobilenumber);
					formData.append('drtb', _this.patient.drtb);
					formData.append('category',_this.patient.category);
					formData.append('address', _this.patient.address);
					formData.append('street', _this.patient.street);
					formData.append('barangay', _this.patient.barangay);
					formData.append('city', _this.patient.city);
					formData.append('remarks', _this.patient.remarks);
					formData.append('username', _this.patient.username);
					formData.append('password', _this.patient.password);

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
					_this.patientList = res.data.data;
					_this.headCount = res.data.count.count;
					_this.pagination.length = Math.ceil(res.data.count.count / 20);
				})
			},
			openMedicineViewer(){
				this.eventHub.$emit("showMedicineList",true);
			},
			openMessage : function(modal, index){
				let patientdetails = this.patientList[index];
				this.eventHub.$emit(modal, {'wsconnect': this.wsconnect ,'patientDetails': patientdetails});
			},
			openModal : function(modal,index){
				this.eventHub.$emit(modal, {'id': this.patientList[index].id, 'status' : this.patientList[index].status, 'category' : this.patientList[index].category, 'data' : this.patientList[index]});
			},
			resetForm(){
				this.$refs.vForm.reset();
				setTimeout(() => {
					this.patient.consultationdate = new Date().toISOString().substr(0, 10);
					this.patient.status = "New";
					this.patient.drtb = "Yes";
					this.patient.city = "Mandaluyong City";
				}, 1);
				
			},
			patientModal(type,index) {
				this.formType = type;
				if(type == "add"){
					this.title = "New Patient";
					this.id = null;
					this.getlastID();
					this.resetForm();
					this.patientPrivacy = true;
				
				}else{
					this.title = "Edit Patient";
					this.id = this.patientList[index].id;
					this.patient = this.patientList[index];
					this.patient['city'] = "Mandaluyong City";
					this.patientDetails = true;
				}
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
					let id = res.data.data.patient_id;
					let currentYear = new Date().toISOString().substr(2,2);
					if(res.data.data.status == false){
						_this.patientID = _this.zeroPad(1,4);
					}else{
						let year = String(id).substring(0,2);
						let lastid = String(id).substring(2,6);
						if(year == currentYear){
							lastid = parseInt(lastid) + 1;
						}else{
							lastid = 1;
						}
						_this.patientID = _this.zeroPad(lastid,4);
					}
				});
			},
			changeStatus : function(index){
				this.patientData = this.patientList[index];
				this.reasons = "";
				this.image = [];
				this.fullname = this.patientList[index].lastname+", "+this.patientList[index].firstname;
				this.indexChange = index;
				this.reasonTemplate = false;
				this.statusType = this.patientList[index].status;
				this.countIntake();
				
				let id = this.patientList[index].id;
				let _this = this;

				axios.create({
					baseURL : _this.apiUrl,
					headers: {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/check?id='+id)
				.then(function(res){
					if(res.data.status){
						if(_this.patientList[index].status == 'Success' || _this.patientList[index].status == 'Discontinuation'){
							_this.reasonTemplate = true;
						}
						_this.statusChangeModal = true;
					}else{
						_this.patientList[index].status = res.data.patient;
						_this.prerequisite = res.data.data.prerequisite;
						_this.checkPrerequisite = false;
						_this.statusChangeModal = true;
					}
				});

			},
			confirmChangeStatus : function(btnstatus){
				let id = this.patientList[this.indexChange].id;
				if(btnstatus == 'cancel'){
					this.loadPatients();
					this.statusChangeModal = false;
				}else{
					let _this = this,
					formData = new FormData();
					formData.append('status',this.patientList[this.indexChange].status);
					
					if(this.image.name != undefined){
						formData.append('imageFile', this.image, this.image.name);
					}
					if(this.reasons != null){
						formData.append('reason',this.reasons);
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
						_this.loadPatients();
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
			fetchBarangayList : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/barangay')
				.then(function(res){
					_this.barangayList = res.data.data;
				});
			},
			imageSelect(e){
				if(e.target.files[0].type === "image/png" || e.target.files[0].type === "image/jpeg"){
					this.image = e.target.files[0];
				}else{
					this.image = [];
					let returnval = { message : "The selected file is not an image!", icon : "error", color : "red"};
					this.eventHub.$emit("showSnackBar", returnval);
				}
			},
		}
	};
</script>