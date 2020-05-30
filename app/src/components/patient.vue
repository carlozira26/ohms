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
							<tr v-for="(tmedicinelist,i) in todaysMedicine[0].medicine" :key="i">
								<td>{{ tmedicinelist.name }}</td>
								<td v-if="tmedicinelist.selected == true">
									<v-btn icon small class="grey" disabled><v-icon class="white--text">check</v-icon></v-btn>
									<v-btn icon small class="grey" disabled><v-icon class="white--text">close</v-icon></v-btn>
								</td>
								<td v-else>
									<v-btn icon small class="green darken-4" color="white--text" @click="takeMedicine(i,tmedicinelist.name,true)"><v-icon>check</v-icon></v-btn>
									<v-btn icon small class="error darken-4" color="white--text" @click="takeMedicine(i,tmedicinelist.name,false)"><v-icon>close</v-icon></v-btn>
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
					<v-btn flat @click="newMedicineVal('update',data.medicinename);reasonDialog=false">Submit</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-snackbar v-model="sbar" boottom color="success">
			Action has been done!
			<v-btn flat color="white" @click.native="sbar = false"><v-icon>close</v-icon></v-btn>
		</v-snackbar>
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
				sbar : false,
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
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					},
				})
				.get('/medicine/patient/schedule/'+this.patient.id)
				.then(function(res){
					_this.medicineSchedule = res.data.data;
					_this.getMedicineVal();
				});
			},
			getMedicineVal : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/value?id='+this.patient.id)
				.then(function(res){
					_this.checkedMedicines = [];
					if(res.data.status){
						_this.checkedMedicines = res.data.data.intake_value.split(",");
					}
					_this.todaysCheckList();
					_this.fetchMedicineReason();
				});
			},
			todaysCheckList : function(){
				this.todaysMedicine = [];
				let _this = this,
				datelist = [],
				val = [],
				y = 0;
				Object.keys(_this.medicineSchedule).forEach(function (key) {
					datelist.push(key);
				});
				if(datelist.includes(_this.today)){
					Object.keys(_this.medicineSchedule[_this.today]).sort().forEach(async function(key) {
						let list = [];
						for(let medicine in _this.medicineSchedule[_this.today][key]){ // tagged
							let sel = false;
							if(_this.checkedMedicines.length > 0){
								if(_this.checkedMedicines[y] == "Y"){
									sel = true;
								}
								let data = { name : _this.medicineSchedule[_this.today][key][medicine], selected : sel };
								list.push(data);
							}else{
								val.push("N");
								list.push({ name : _this.medicineSchedule[_this.today][key][medicine], selected : sel });
							}
							y = y+1;
						}
						_this.todaysMedicine.push({
							time : key,
							medicine : list,
						});
					}); 

					if(_this.checkedMedicines.length == 0){
						_this.checkedMedicines = val;
						_this.newMedicineVal('create');
					}
				}
			},
			newMedicineVal : function(val, medicinename){
				let _this = this,
				formData = new FormData();
				formData.append('id',JSON.stringify(this.patient.id));
				formData.append('medicineindex',this.data.index);
				formData.append('newVal',JSON.stringify(this.checkedMedicines));
				formData.append('medicinename',medicinename);
				formData.append('type', val);
				formData.append('reason', this.reason);
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/medicine/newvalue',formData);
				this.reason = "";
			},
			takeMedicine : function(i,medicinename,status){
				this.data = []
				this.data['index'] = i;
				this.data['medicinename'] = medicinename;
				this.data['status'] = status;
				if(status == true){
					this.todaysMedicine[0].medicine[i].selected = true;
					this.checkedMedicines[i] = "Y";
					this.newMedicineVal('update', medicinename);
				}else{
					this.dialog = false;
					this.reasonDialog = true;
				}
			},
			fetchMedicineReason : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/logs/reason?patientid='+this.patient.id)
				.then(function(res){
					let reason = res.data.data;
					for(let x in _this.todaysMedicine[0].medicine){
						if(reason[x]){
							_this.todaysMedicine[0].medicine[reason[x].medicine_index].selected = true;
						}
					}
				});
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