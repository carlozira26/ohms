<template>
	<div>
		<v-dialog v-model="medscheduler" width="60%">
			<v-card style="overflow:auto">
				<v-card-title primary-title class="green darken-4">
					<h1 class="white--text">Scheduler</h1>
				</v-card-title>
				<v-divider></v-divider>
				<template v-if="patient.status!='New'">
					<v-card-text>
						<v-layout row wrap>
							<v-flex xs12 sm12 md7>
								<v-date-picker
									full-width
									landscape
									v-model="date"
									:events="arrayEvents"
									color="green darken-4"
									event-color="green darken-2"
									@change="fetchMedicine"
								></v-date-picker>
							</v-flex>
							<v-flex xs12 md5 class="pl-3">
								<div class="text-xs-left">
									<div class="text-xs-center" v-if="prescribedMedicine == 0">
										<table class="theme--light fluid" id="tablemedicine">
											<thead>
												<tr class="grey lighten-4" style="border-bottom:1px solid #333;">
													<th class="font-weight-bold text-md-center grey darken-1 white--text">Medicine Name</th>
													<th class="font-weight-bold text-md-center grey darken-1 white--text">Take Medicine</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td colspan="2" class="red--text">No Scheduled Medicine</td>
												</tr>
											</tbody>
										</table>
									</div>
									<template v-else>
										<table class="theme--light fluid" id="tablemedicine">
											<thead>
												<tr class="grey lighten-4" style="border-bottom:1px solid #333;">
													<th class="font-weight-bold text-md-center grey darken-1 white--text">Medicine Name</th>
													<th class="font-weight-bold text-md-center grey darken-1 white--text">Take Medicine</th>
												</tr>
											</thead>
											<tbody>
												<tr v-for="(medicine, i) in prescribedMedicine" :key="i" class="text-sm-center">
													<td>{{ medicine.brandname }} : {{ medicine.genericname }}</td>
													<template v-if="medicine.is_taken == true">
														<template v-if="medicine.status != 'Declined'">
															<td class="text-xs-center">
																{{ medicine.status }}
															</td>
														</template>
														<template v-else>
															<td class="text-xs-center" @click="dialogApprove=true; checkedMedicines=medicine" style="cursor:pointer">
																<v-icon small color="red">fa-exclamation-circle</v-icon>
																{{ medicine.status }}
															</td>
														</template>
													</template>
													<template v-else> 
														<td>
															<v-btn flat icon small color="green darken-4" @click="takeMedicine(i,true)"><v-icon>check</v-icon></v-btn>
															<v-btn flat icon small color="error darken-4" @click="takeMedicine(i,false)"><v-icon>close</v-icon></v-btn>
														</td>
													</template>
												</tr>
											</tbody>
										</table>
									</template>
								</div>
							</v-flex>
						</v-layout>
					</v-card-text>
				</template>
				<template v-else>
					<v-card-text>
						<v-layout row wrap>
							<v-flex>
								<h3>Please set the medicines first.</h3>
								<v-btn flat class="blue-grey darken-4 white--text" @click="openModal('showAddMedicine',patient)">Go to Add Medicine</v-btn>
							</v-flex>
						</v-layout>
					</v-card-text>
				</template>
			</v-card>
		</v-dialog>
		<v-dialog v-model="reasonDialog" max-width="500px" transition="dialog-transition">
			<v-card>
				<v-card-title class="green darken-4 white--text title">
					Reason
				</v-card-title>
				<v-card-text>
					<v-layout row wrap>
						<v-flex xs12>
							Please specify a reason:
						</v-flex>
						<v-flex xs12>
							<v-textarea solo v-model="reason"></v-textarea>
						</v-flex>
					</v-layout>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat @click="dialog=true;reasonDialog=false">Cancel</v-btn>
					<v-btn flat @click="newMedicineVal();reasonDialog=false">Submit</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog v-model="dialogApprove" width="30%">
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h2>Medicine Accept</h2>
				</v-card-title>
				<v-card-text>
					Do you want to change the status of this medicine to <b>"Done"</b>?
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat @click="dialogApprove=false">Cancel</v-btn>
					<v-btn flat @click="newMedicineVal">Confirm</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>
<script>
import axios from 'axios';
	export default {
		created : function(){
			this.eventHub.$on('showScheduler', val =>{
				this.patient = val.data;
				this.medscheduler = true;
				this.fetchMedicineSchedule();
			});
		},
		data : function(){
			return {
				dialogApprove : false,
				patient : [],
				arrayEvents: [],
				datesList : null,
				reasonDialog : false,
				medscheduler : false,
				date: new Date().toISOString().substr(0, 10),
				reason: "",
				prescribedMedicine : [],
				checkedMedicines : []
			}
		},
		methods : {
			openModal : function(modal,patient){
				this.medscheduler = false;
				this.eventHub.$emit(modal, patient);
			},
			takeMedicine : function(i,is_taken){
				this.checkedMedicines = this.prescribedMedicine[i];
				if(is_taken == true){
					this.prescribedMedicine[i].is_taken = true;
					this.prescribedMedicine[i].status = "Done";
					this.checkedMedicines = this.prescribedMedicine[i];
					this.newMedicineVal();
				}else{
					this.dialog = false;
					this.reasonDialog = true;
				}
			},
			newMedicineVal : function(){
				this.checkedMedicines.status = (this.checkedMedicines.is_taken==true) ? 'Done' : 'Declined';
				let _this = this,
				formData = new FormData();
				formData.append('date',this.date);
				formData.append('patientid',this.patient.id);
				formData.append('medicineid', this.checkedMedicines.id);
				formData.append('reason',this.reason);
				formData.append('status', this.checkedMedicines.status);
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/medicine/newvalue',formData)
				.then(function(res){
					let snackbarMessage = (_this.checkedMedicines.status=="Done")? "Medicine accepted!" : "Medicine declined!";
					let returnval = { message : snackbarMessage, icon : "done", color : "green"};
					if(res.data.status){
						_this.eventHub.$emit('showSnackBar', returnval)
					}
					if(_this.checkedMedicines.status == 'Declined'){
						_this.checkedMedicines.is_taken = true;
						_this.dialog = true;
					}
					_this.dialogApprove=false;
				});
				this.reason = "";
			},
			fetchMedicineSchedule : function(){
				let _this = this,
				id = this.patient.id,
				category = this.patient.category,
				date = this.patient.consultationdate;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					},
				})
				.get('/medicine/patient/schedule?date='+date+"&category="+category)
				.then(function(res){
					_this.arrayEvents = res.data;
					_this.fetchMedicine();
				});
			},
			fetchMedicine : function(){
				if(this.arrayEvents.indexOf(this.date) !== -1){
					let _this = this;
					axios.create({
						baseURL : this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						},
					})
					.get('/patient/app/medicine?id='+this.patient.id+'&date='+this.date)
					.then(function(res){
						_this.prescribedMedicine = res.data.data;
					});
				}else{
					this.prescribedMedicine = [];
				}
			},
		}
	};
</script>
<style>
	#tablemedicine {
		font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
		border-collapse: collapse;
		width: 100%;
	}

	#tablemedicine td, #tablemedicine th {
		border: 1px solid #ddd;
		padding: 8px;
	}

	#tablemedicine tr:nth-child(even){background-color: #f2f2f2;}
</style>