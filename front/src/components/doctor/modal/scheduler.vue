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
							<ul v-else v-for="(medicine,index) in prescribedMedicine" :key="index">
								<li>{{medicine}}</li>
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
		mounted : function(){
			this.fetchMedicineSchedule();
		},
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

				medicineList: [],
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
					_this.medicineList = res.data.data;
					_this.checkMedicineDate();
				});
			},
			checkMedicineDate : function(){
				this.prescribedMedicine = [];
				for(let medicineIndex in this.medicineList){
					this.datesList = this.medicineList[medicineIndex].datelist.medicinedatelist.join();
					if(this.datesList.indexOf(this.date) > -1){
						this.prescribedMedicine.push(this.medicineList[medicineIndex].medicine);
					}
				}
			}
		}
	};
</script>