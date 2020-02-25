<template>
	<v-dialog v-model="medscheduler" width="60%">
		<v-card style="overflow:auto">
			<v-card-title primary-title>
				<h1 class="green--text">Scheduler</h1>
			</v-card-title>
			<v-divider></v-divider>
			<v-card-text>
				<v-layout row wrap>
					<v-flex xs12 sm12 md8>
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
							
							<template v-else v-for="(timeintake,index) in prescribedMedicine">
                                <v-list dense two-line subheader :key="index">
                                    <v-subheader class="font-weight-bold">{{timeintake.time}}</v-subheader>
                                </v-list>
                                <template v-for="(medicine,i) in timeintake.medicine">
                                    <v-list dense :key="`medicine-${index}-${i}`">
                                        <v-list-tile>
                                            <v-list-tile-action>
                                                <v-checkbox @change="takeMedicine(index,i,medicine)" v-model="medicine.selected"></v-checkbox>
                                            </v-list-tile-action>
                                            {{medicine}}
                                        </v-list-tile>
                                    </v-list>
                                </template>
                                <v-divider :key="`divider-${index}`"></v-divider>
                            </template>
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
				todaysMedicine : [],
				checkedMedicines : []
			}
		},
		methods : {
			todaysCheckList : function(){
				this.todaysMedicine = [];
				let _this = this,
				datelist = [],
				val = [],
				y = 0,
				instr = "";
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
								list.push({ name : _this.medicineSchedule[_this.today][key][medicine], selected : sel });
							}else{
								val.push("N"),
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
					}
				}
			},
			takeMedicine : function(index, i,medicinename){
				this.checkedMedicines = [];
				for(let timetake in this.todaysMedicine){
					console.log(this.todaysMedicine[timetake]);
					for(let med in this.todaysMedicine[timetake].medicine){
						let val = "N";
						if(this.todaysMedicine[timetake].medicine[med].selected == true){
							val = "Y";
						}
						this.checkedMedicines.push(val);
					}
				}
				// this.newMedicineVal('update', medicinename);
			},
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
			},
			getMedicineVal : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/value')
				.then(function(res){
					if(res.data.status){
						_this.checkedMedicines = res.data.data.intake_value.split(",");
					}
					_this.todaysCheckList();
				});
			},
		}
	};
</script>