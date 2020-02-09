<template>
	<v-dialog v-model="medscheduler" width="60%">
		<v-card>
			<v-card-title primary-title>
				<h1 class="green--text">Scheduler</h1>
			</v-card-title>
			<v-divider></v-divider>
			<v-card-text>
				<v-layout row wrap>
					<v-flex xs12 md8>
						<v-date-picker
							full-width
							landscape
							v-model="date"
							:events="arrayEvents"
							color="green darken-4"
							event-color="green darken-2"
							@change="checkMedicineDate"
						></v-date-picker>
					</v-flex>
					<v-flex xs12 md4 class="pa-3">
						<div>
							<h2 class="green--text">Prescribed Medicine</h2>
						</div>
						<div class="text-xs-left">
							<div class="text-xs-center" v-if="prescribedMedicine == 0">
								<h4 class="red--text">No Medicine Found</h4>
							</div>
							<ul v-else v-for="(timeintake,index) in prescribedMedicine" :key="index">
								<li class="font-weight-bold">{{timeintake.time}}</li>
								<ul v-for="(medicine,index) in timeintake.medicine" :key="index">
									<li class="font-italic">{{ medicine }}</li>
								</ul>
							</ul>
						</div>
					</v-flex>
				</v-layout>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>
<script>
import VueCookies from "vue-cookies";
import axios from 'axios';

	export default {

		props : ['mtitle'],
		created : function(){
			this.token = VueCookies.get(this.cookieKey).token;
			this.eventHub.$on('showScheduler', val =>{
				this.medscheduler = true;
				this.fetchMedicineSchedule(val.patientID);
			});
		},
		data : function(){
			return { 
				arrayEvents: [],
				datesList : null,
				medscheduler : false,
				date: new Date().toISOString().substr(0, 10),

				medicineSchedule: [],
				prescribedMedicine : [],
			}
		},
		methods : {
			fetchMedicineSchedule : function(id){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					},
				})
				.get('/medicine/patient/schedule/'+id)
				.then(function(res){
					_this.arrayEvents = res.data.dates;
					_this.medicineSchedule = res.data.data;
					_this.checkMedicineDate();
				});
			},
			checkMedicineDate : function(){
				this.prescribedMedicine = [];
				const schedule = [];
				let _this = this;
				let x = 0;
				let datelist = [];
				Object.keys(_this.medicineSchedule).forEach(function (key) {
					datelist.push(key);
				});
				if(datelist.includes(_this.date)){
					Object.keys(_this.medicineSchedule[_this.date]).sort().forEach(function(key) {
						_this.prescribedMedicine[x] ={
							time : key,
							medicine : _this.medicineSchedule[_this.date][key],
						};
						x++;
					});
				}
			}
		}
	};
</script>