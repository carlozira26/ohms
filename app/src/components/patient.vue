<template>
	<div>
		<v-dialog v-model="dialog" scrollable  max-width="500px" transition="dialog-transition">
			<v-card>
				<v-card-title class="green darken-4 white--text title">
					Patient
					<v-spacer></v-spacer>
					<v-btn small flat icon class="white--text" @click="dialog=false"><v-icon>close</v-icon></v-btn>
				</v-card-title>
				<v-card-text>
					<v-layout row wrap class="mb-2">
						<v-flex xs4 class="font-weight-bold text-xs-right">
							Patient ID :
						</v-flex>
						<v-flex xs8 class="text-xs-left pl-2">
							{{patient.patient_id}}
						</v-flex>
						<v-flex xs4 class="font-weight-bold text-xs-right">
							Patient Name :
						</v-flex>
						<v-flex xs8 class="text-xs-left pl-2">
							{{ patient.firstname + " " + patient.lastname }}
						</v-flex>
						<v-flex xs4 class="font-weight-bold text-xs-right">
							TB Category :
						</v-flex>
						<v-flex xs8 class="text-xs-left pl-2">
							{{ patient.category }}
						</v-flex>
					</v-layout>
					<template class="text-xs-center" v-if="todaysMedicine.length==0">
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
								Medicine List
							</v-card-title>
						</v-card>
						<v-card max-height="233" style="overflow-y:auto" scrollable>
							<v-card-text>
								<v-list dense>
									<template v-for="(medicine, i) in todaysMedicine">
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
			</v-card>
		</v-dialog>
		<v-dialog v-model="reasonDialog">
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
		<v-dialog v-model="dialogApprove">
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
import VueCookies from 'vue-cookies';
import axios from 'axios';
	export default {
		mounted : function(){
			this.eventHub.$on('viewPatient', val => {
				this.token = VueCookies.get(this.cookieKey).token;
				this.patient = val.patient;
				this.date = val.date
				this.dialog = true;
				this.fetchPatientMedicine();
			});
		},
		data: function(){
			return {
				patient : [],
				dialog : false,
				reasonDialog : false,
				todaysMedicine : [],
				checkedMedicines : [],
				reason : "",
				reasonList : [],
				data : [],
				dialogApprove : false,
				date : '',
				prescribedMedicineStatus : '',
			}
		},
		methods : {
			fetchPatientMedicine : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					},
				})
				.get('/patient/app/medicine?id='+this.patient.id+"&date="+this.date)
				.then(function(res){
					_this.todaysMedicine = res.data.data.list;
					_this.prescribedMedicineStatus = res.data.data.status;
				});
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
			takeMedicine : function(i,is_taken){
				this.checkedMedicines = this.todaysMedicine[i];
				if(is_taken == true){
					this.todaysMedicine[i].is_taken = true;
					this.todaysMedicine[i].status = "Done";
					this.checkedMedicines = this.todaysMedicine[i];
					this.newMedicineVal();
				}else{
					this.dialog = false;
					this.reasonDialog = true;
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