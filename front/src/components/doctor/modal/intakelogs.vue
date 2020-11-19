<template>
	<v-dialog v-model="medicineIntakelogs" width="60%" scrollable>
		<v-card>
			<v-card-title class="text-xs-center green darken-4 white--text">
				<h1>Intake Logs</h1>
			</v-card-title>
			<v-card-text>
				<v-layout row wrap>
					<v-flex md8>
						<div class="text-xs-left"><label class="font-weight-bold">Patient ID</label> : # {{ patient.patient_id }} </div>
						<div class="text-xs-left"><label class="font-weight-bold">Patient Name</label> : {{patient.firstname + " " + patient.lastname}}</div>    
					</v-flex>
					<v-flex md4>
						<v-menu ref="menu1" v-model="menu1" :close-on-content-click="false" :nudge-right="40" lazy transition="slide-y-transition" offset-y full-width>
							<template v-slot:activator="{ on }">
								<v-text-field readonly append-icon="event" v-model="date" label="Date Range" solo v-on="on"></v-text-field>
							</template>
							<v-layout row wrap>
								<v-flex sm6>
									<v-date-picker v-model="date1" @change="dateRange" no-title></v-date-picker>
								</v-flex>
								<v-flex sm6>
									<v-date-picker v-model="date2" @change="dateRange" no-title></v-date-picker>
								</v-flex>
							</v-layout>
						</v-menu>
					</v-flex>
				</v-layout>
				<v-layout :class="color" class="text-md-left white--text pa-1" v-if="patient.status=='Discontinuation' || patient.status=='Success'">
					<v-flex md10>
						{{dateFormat(datastatus.created_at)}} : ({{datastatus.status}}) {{datastatus.reason}}
					</v-flex>
					<v-flex md2 class="text-left">{{datastatus.updated_by}}</v-flex>
				</v-layout>
				<v-layout row wrap>
					<v-flex class="text-md-left">
						<table class="theme--light fluid" id="tablemedicine">
							<thead>
								<tr class="text-sm-center">
									<th>Status</th>
									<th>Date/Time</th>
									<th>Scheduled Medicine Date</th>
									<th>Reason</th>
									<th>Distributor</th>
								</tr>
							</thead>
							<tbody>
								<template v-if="logs.length > 0">
									<tr class="text-sm-center" v-for="(log,index) in logs" v-bind:key="index">
										<td>{{ log.status }}</td>
										<td>{{ formatDateTime(log.created_at) }}</td>
										<td>{{ formatDate(log.date) }}</td>
										<td>{{ log.reason }}</td>
										<td>{{ log.distributor }}</td>
									</tr>
								</template>
								<template v-else>
									<tr class="text-sm-center">
										<td colspan="5" class="text-center">
											<h4>No Logs Found!</h4>
										</td>
									</tr>
								</template>
							</tbody>
						</table>
					</v-flex>
				</v-layout>
			</v-card-text>
		</v-card>	
	</v-dialog>
</template>
<script>
	import moment from 'moment';
	import axios from "axios";
	export default {
		mounted: function(){
			this.eventHub.$on('viewIntakeLogs', val => {
				if(val.data){
					this.patient = val.data;
				}else{
					this.getPatientProfile();
				}
				switch (this.patient['status']){
					case 'Success' :
						this.color = "green darken-3";
						this.getStats();
						break;
					case 'Discontinuation':
						this.color = "red darken-3";
						this.getStats();
						break;
					default :
						break;
				} 
				this.getLogs(val.patientID);
				this.medicineIntakelogs = true;
			});
		},
		data: function(){
			return{
				medicineIntakelogs : false,
				logs : [],
				patient : [],
				date: '',
				date1: '',
				date2: '',
				menu1: '',
				color: 'green derken-3',
				datastatus: [],
			}
		}, 
		methods : {
			getPatientProfile : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/profile')
				.then(function(res){
					_this.patient = res.data.data;
				})
			},
			getLogs : function(id){
				let _this = this;
				_this.logs = [];
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/medicinelogs?id='+this.patient.id+'&date='+this.date)
				.then(function(res){
					_this.logs = res.data.data;
				})
			},
			dateRange : function(){
				let d1="",d2="";
				if(this.date1!=''){
					d1 = new Date(this.date1).toDateString().substr(4);
					this.date = d1+" ~ "+d2;
				} if (this.date2!='') {
					d2 = new Date(this.date2).toDateString().substr(4);
					this.date = d1+" ~ "+d2;
				}
				this.getLogs();
			},
			getStats : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/logstat?id='+this.patient.id)
				.then(function(res){
					_this.datastatus = res.data.data;
					console.log(_this.datastatus);
				});
			}
		}
	};
</script>
<style>
	.intakelogs{
		max-width:30%;
	}
	@media only screen and (max-width: 600px){
		.intakelogs {
			max-width: 100%;
		}
	}
</style>