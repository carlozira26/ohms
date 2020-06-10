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
					<v-layout row wrap>
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
				</v-card-text>
				<v-card-text>
					<table v-if="todaysMedicine.length==0" class="theme--light fluid" id="tablemedicine">
						<thead>
							<tr class="grey lighten-4" style="border-bottom:1px solid #333;">
								<th class="font-weight-bold text-md-center grey darken-1 white--text">Medicine Name</th>
								<th class="font-weight-bold text-md-center grey darken-1 white--text">Take Medicine</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="2">No Data Found</td>
							</tr>
						</tbody>
					</table>
					<table v-else class="theme--light fluid" id="tablemedicine">
						<thead>
							<tr class="grey lighten-4" style="border-bottom:1px solid #333;">
								<th class="font-weight-bold text-md-center grey darken-1 white--text">Medicine Name</th>
								<th class="font-weight-bold text-md-center grey darken-1 white--text">Take Medicine</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(tmedicinelist,i) in todaysMedicine" :key="i">
								<td>{{ tmedicinelist.brandname }} : {{ tmedicinelist.genericname }}</td>
								<td v-if="tmedicinelist.is_taken == true">
									{{ tmedicinelist.status }}
								</td>
								<td v-else>
									<v-btn icon small class="green darken-4" color="white--text" @click="takeMedicine(i,true)"><v-icon>check</v-icon></v-btn>
									<v-btn icon small class="error darken-4" color="white--text" @click="takeMedicine(i,false)"><v-icon>close</v-icon></v-btn>
								</td>
							</tr>
						</tbody>
					</table>
				</v-card-text>
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
					<v-btn flat @click="newMedicineVal();reasonDialog=false">Submit</v-btn>
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
				.get('/patient/app/medicine?id='+this.patient.id)
				.then(function(res){
					_this.todaysMedicine = res.data.data;
				});
			},
			newMedicineVal : function(){
				this.checkedMedicines.status = (this.checkedMedicines.is_taken==true) ? 'Done' : 'Declined';
				let _this = this,
				formData = new FormData();
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
					_this.snackbarMessage = (_this.checkedMedicines.status=="Done")? "Medicine accepted!" : "Medicine declined!";
					if(res.data.status){
						_this.eventHub.$emit('snackBar', _this.snackbarMessage)
					}
					if(_this.checkedMedicines.status == 'Declined'){
						_this.checkedMedicines.is_taken = true;
						_this.dialog = true;
					}
				});
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