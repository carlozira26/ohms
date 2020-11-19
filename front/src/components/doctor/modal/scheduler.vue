<template>
	<div>
		<v-dialog v-model="medscheduler" width="60%" scrollable>
			<v-card>
				<v-card-title primary-title class="green darken-4">
					<h1 class="white--text">Scheduler</h1>
				</v-card-title>
				<v-divider></v-divider>
				<template v-if="patient.status!='New'">
					<v-card-text>
						<div class="text-xs-left"><label class="font-weight-bold">Patient ID</label> : # {{ patient.patient_id }} </div>
						<div class="text-xs-left pb-2"><label class="font-weight-bold">Patient Name</label> : {{patient.firstname + " " + patient.lastname}}</div>
						<v-layout row wrap>
							<v-flex xs12 sm12 md7 class="pa-1">
								<v-date-picker
									full-width
									landscape
									v-model="date"
									:events="arrayEvents"
									color="green darken-4"
									:event-color="eventColor"
									@change="fetchMedicine"
								></v-date-picker>
							</v-flex>
							<v-flex xs12 md5 class="pa-1">
								<div class="text-xs-left">
									<template class="text-xs-center" v-if="prescribedMedicine == 0">
										<v-card>
											<v-card-title class="font-weight-bold grey darken-1 white--text">
												Medicine Name
											</v-card-title>
										</v-card>
										<v-card max-height="233" style="overflow-y:auto" scrollable>
											<v-card-text>
												<v-list dense>
													<v-list-tile>
														<v-list-tile-content>
															No Scheduled Medicine
														</v-list-tile-content>
													</v-list-tile>
												</v-list>
											</v-card-text>
										</v-card>
									</template>
									<template v-else>
										<v-card>
											<v-card-title class="font-weight-bold grey darken-1 white--text">
												Medicine Name
											</v-card-title>
										</v-card>
										<v-card max-height="233" style="overflow-y:auto" scrollable>
											<v-card-text>
												<v-list dense>
													<template v-for="(medicine, i) in prescribedMedicine">
														<v-list-tile :key="medicine.brandname">
															<v-list-tile-content dense>
																{{ medicine.brandname }} : {{ medicine.genericname }}
															</v-list-tile-content>
														</v-list-tile>
														<v-divider :key="i"></v-divider>
													</template>
												</v-list>
											</v-card-text>
										</v-card>
									</template>
								</div>
							</v-flex>
						</v-layout>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<template v-if="prescribedMedicineStatus=='None'">
							<v-btn color="error darken-4" flat class="white--text" @click="newMedicineVal(false)"><v-icon left small>fa-times</v-icon>Decline</v-btn>
							<v-btn color="green darken-4" flat class="white--text" append-icon="check" @click="newMedicineVal(true)"><v-icon left small>fa-check</v-icon> Check</v-btn>
						</template>
						<template v-if="prescribedMedicineStatus=='Declined'">
							<v-btn color="error darken-4" outline class="white--text" @click="dialogApprove=true">Declined</v-btn>
						</template>
						<template v-if="prescribedMedicineStatus=='Done'">
							<v-btn color="green darken-4" outline class="white--text">Done</v-btn>
						</template>
					</v-card-actions>
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
					<v-btn flat @click="reasonSubmit=true;newMedicineVal(false)">Submit</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog v-model="dialogApprove" width="30%">
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h2>Medicine Accept</h2>
				</v-card-title>
				<v-card-text>
					Do you want to change the status of this medicine list to <b>"Done"</b>?
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat @click="dialogApprove=false">Cancel</v-btn>
					<v-btn flat @click="newMedicineVal(true)">Confirm</v-btn>
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
				this.eventColor = this.setEventColor(this.patient.status);
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
				reasonSubmit : false,
				medscheduler : false,
				date: new Date().toISOString().substr(0, 10),
				reason: "",
				prescribedMedicine : [],
				prescribedMedicineStatus : '',
				checkedMedicines : [],
				eventColor : '',
			}
		},
		methods : {
			openModal : function(modal,patient){
				this.medscheduler = false;
				this.eventHub.$emit(modal, {data : patient});
			},
			newMedicineVal : function(is_taken){
				let _this = this,
				submit = false,
				formData = new FormData();
				formData.append('date',this.date);
				formData.append('reason',this.reason);
				formData.append('patientid',this.patient.id);
				
				if(is_taken == false){
					this.dialog = false;
					this.reasonDialog = true;
					formData.append('status', "Declined");
					if(this.reasonSubmit == true){
						this.reasonDialog=false;
						submit = true;
					}
				}else{
					formData.append('status', "Done");
					submit = true;
				}

				if(submit==true){
					axios.create({
						baseURL : this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/medicine/newvalue',formData)
					.then(function(res){
						let snackbarMessage = (is_taken==true)? "Medicine accepted!" : "Medicine declined!";
						let returnval = { message : snackbarMessage, icon : "done", color : "green"};

						if(is_taken == false){
							_this.prescribedMedicineStatus = "Declined";
							_this.reasonSubmit = false;
						}else{
							_this.prescribedMedicineStatus = "Done";
						}

						if(res.data.status){
							_this.eventHub.$emit('showSnackBar', returnval)
						}
						_this.dialogApprove=false;
					});
				}

				this.reason = "";
			},
			fetchMedicineSchedule : function(){
				let _this = this,
				category = this.patient.category,
				date = this.patient.datestart;
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
						_this.prescribedMedicine = res.data.data.list;
						_this.prescribedMedicineStatus = res.data.data.status;
					});
				}else{
					this.prescribedMedicine = [];
					this.prescribedMedicineStatus = "";
				}
			},
			setEventColor : function(status){
				let color = "";
				if(status == "Success"){
					color = "yellow darken-3";
				}else if(status == "Discontinuation"){
					color = "red";
				}else if(status == "Ongoing"){
					color = "cyan";
				}
				return color;
			}
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