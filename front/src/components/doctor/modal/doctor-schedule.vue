<template>
	<div>
		<v-dialog scrollable v-model="dialog" content-class="scheduleClass">
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h2>Schedule</h2>
				</v-card-title>
				<v-card-text >
					<v-layout row wrap>
						<v-flex md12 sm12 class="text-md-left">
							<label v-if="id==''">Please set your schedule here</label>
						</v-flex>
						<v-flex md12 sm12 class="text-md-left">
							<v-form ref="vForm" v-on:submit.prevent="submitForm">
								<table style="width:100%" id="table">
									<thead>
										<tr>
											<th class="text-md-center">Days</th>
											<th class="text-md-center">From</th>
											<th class="text-md-center">To</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="pl-2">Monday</td>
											<template v-if="id==userid">
												<td><v-select solo hide-details v-model="schedule.from1" :items="arrayTime"></v-select></td>
												<td><v-select solo hide-details v-model="schedule.to1" :items="arrayTime"></v-select></td>
											</template>
											<template v-else>
												<td class="text-md-center">{{schedule.from1}}</td>
												<td class="text-md-center">{{schedule.to1}}</td>
											</template>
										</tr>
										<tr>
											<td class="pl-2">Tuesday</td>
											<template v-if="id==userid">
												<td><v-select solo hide-details v-model="schedule.from2" :items="arrayTime"></v-select></td>
												<td><v-select solo hide-details v-model="schedule.to2" :items="arrayTime"></v-select></td>
											</template>
											<template v-else>
												<td class="text-md-center">{{schedule.from2}}</td>
												<td class="text-md-center">{{schedule.to2}}</td>
											</template>
										</tr>
										<tr>
											<td class="pl-2">Wednesday</td>
											<template v-if="id==userid">
												<td><v-select solo hide-details v-model="schedule.from3" :items="arrayTime"></v-select></td>
												<td><v-select solo hide-details v-model="schedule.to3" :items="arrayTime"></v-select></td>
											</template>
											<template v-else>
												<td class="text-md-center">{{schedule.from3}}</td>
												<td class="text-md-center">{{schedule.to3}}</td>
											</template>
										</tr>
										<tr>
											<td class="pl-2">Thursday</td>
											<template v-if="id==userid">
												<td><v-select solo hide-details v-model="schedule.from4" :items="arrayTime"></v-select></td>
												<td><v-select solo hide-details v-model="schedule.to4" :items="arrayTime"></v-select></td>
											</template>
											<template v-else>
												<td class="text-md-center">{{schedule.from4}}</td>
												<td class="text-md-center">{{schedule.to4}}</td>
											</template>
										</tr>
										<tr>
											<td class="pl-2">Friday</td>
											<template v-if="id==userid">
												<td><v-select solo hide-details v-model="schedule.from5" :items="arrayTime"></v-select></td>
												<td><v-select solo hide-details v-model="schedule.to5" :items="arrayTime"></v-select></td>
											</template>
											<template v-else>
												<td class="text-md-center">{{schedule.from5}}</td>
												<td class="text-md-center">{{schedule.to5}}</td>
											</template>
										</tr>
										<tr>
											<td class="pl-2">Saturday</td>
											<template v-if="id==userid">
												<td><v-select solo hide-details v-model="schedule.from6" :items="arrayTime"></v-select></td>
												<td><v-select solo hide-details v-model="schedule.to6" :items="arrayTime"></v-select></td>
											</template>
											<template v-else>
												<td class="text-md-center">{{schedule.from6}}</td>
												<td class="text-md-center">{{schedule.to6}}</td>
											</template>
										</tr>
										<tr>
											<td class="pl-2">Sunday</td>
											<template v-if="id==userid">
												<td><v-select solo hide-details v-model="schedule.from7" :items="arrayTime"></v-select></td>
												<td><v-select solo hide-details v-model="schedule.to7" :items="arrayTime"></v-select></td>
											</template>
											<template v-else>
												<td class="text-md-center">{{schedule.from7}}</td>
												<td class="text-md-center">{{schedule.to7}}</td>
											</template>
										</tr>
									</tbody>
								</table>
							</v-form>
						</v-flex>
					</v-layout>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat @click="compileSchedule" v-if="id==userid">SAVE</v-btn>
					<v-btn flat @click="dialog=false">CANCEL</v-btn>
				</v-card-actions>
			</v-card>	
		</v-dialog>
		<v-snackbar v-model="sbar" :color="snackBarColor" right top>
			<v-icon class="white--text" left>{{ icon }}</v-icon>
			{{ message }}
			<v-btn flat @click.native="sbar = false"> &times; </v-btn>
		</v-snackbar>
	</div>
</template>
<script>
	import VueCookies from 'vue-cookies';
	import axios from "axios";
	export default {
		mounted: function(){
			this.eventHub.$on('viewDoctorSchedule', val => {
				this.token = VueCookies.get(this.cookieKey).token;
				this.userid = VueCookies.get(this.cookieKey).data.id;
				this.id = (val['id']) ? val['id'] : this.userid;
				this.dialog=true;
				this.timeList();
				this.fetchSchedule();
			});
		},
		data: function(){
			return{
				id : '',
				userid : '',
				snackBarColor : '',
				icon : '',
				message : '',
				sbar : false,

				dialog : false,
				schedule: {
					from1 : 'None',
					from2 : 'None',
					from3 : 'None',
					from4 : 'None',
					from5 : 'None',
					from6 : 'None',
					from7 : 'None',
					to1 : 'None',
					to2 : 'None',
					to3 : 'None',
					to4 : 'None',
					to5 : 'None',
					to6 : 'None',
					to7 : 'None',
				},
				doctorSched : '',
				arrayTime : []
			}
		}, 
		methods : {
			fetchSchedule : function(){
				let _this = this;

				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/schedule/fetch/'+_this.id)
				.then(function(res){
					_this.processSchedule(res.data.data.schedule);
				});
			},
			processSchedule : function(schedule){
				if(schedule!='undefined'){
					let sched = schedule.split(',');
					for(let x in sched){
						if(sched[x]!='None'){
							let scheduleDays = sched[x].split('-');
							this.schedule['from'+(+x+1)] = scheduleDays[0];
							this.schedule['to'+(+x+1)] = scheduleDays[1];
						}else{
							this.schedule['from'+(+x+1)] = 'None';
							this.schedule['to'+(+x+1)] = 'None';
						}
					}
				}
			},
			timeList : function(){
				this.arrayTime.push("None");
				for(let x=0; x<24; x++){
					if(x>5 && x<12){
						this.arrayTime.push(x+":00 AM");
					}else if(x==12){
						this.arrayTime.push(x+":00 PM");
					}else if(x>12 && x<21){
						this.arrayTime.push((x-12)+":00 PM");
					}
				}
			},
			compileSchedule : function(){
				let sched1 = (this.schedule.from1 == 'None' || this.schedule.to1 == "None") ? "None" : this.schedule.from1 + "-" + this.schedule.to1;
				let sched2 = (this.schedule.from2 == 'None' || this.schedule.to2 == "None") ? "None" : this.schedule.from2 + "-" + this.schedule.to2;
				let sched3 = (this.schedule.from3 == 'None' || this.schedule.to3 == "None") ? "None" : this.schedule.from3 + "-" + this.schedule.to3;
				let sched4 = (this.schedule.from4 == 'None' || this.schedule.to4 == "None") ? "None" : this.schedule.from4 + "-" + this.schedule.to4;
				let sched5 = (this.schedule.from5 == 'None' || this.schedule.to5 == "None") ? "None" : this.schedule.from5 + "-" + this.schedule.to5;
				let sched6 = (this.schedule.from6 == 'None' || this.schedule.to6 == "None") ? "None" : this.schedule.from6 + "-" + this.schedule.to6;
				let sched7 = (this.schedule.from7 == 'None' || this.schedule.to7 == "None") ? "None" : this.schedule.from7 + "-" + this.schedule.to7;
				this.doctorSched = sched1 + "," + sched2 + "," + sched3 + "," + sched4 + "," + sched5 + "," + sched6 + "," + sched7;
				this.submitForm();
			},
			submitForm : function(){
				let _this = this,
				formData = new FormData();

				formData.append('doctorSchedule',this.doctorSched);
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/users/schedule',formData)
				.then(function(res){
					if(res.data.status){
						_this.snackBarColor = 'green';
						_this.icon = "check";
						_this.message = "Schedule has been saved!";
						_this.sbar = true;
						_this.dialog = false;
					}else{
						_this.snackBarColor = 'red';
						_this.icon = "close";
						_this.message = "There's an error while submitting.";
						_this.sbar = true;
						_this.dialog = false;
					}
				});
			}
		},
	};
</script>
<style>
	.scheduleClass{
		max-width:35%;
	}
	@media only screen and (max-width: 700px){
		.scheduleClass {
			max-width: 100%;
		}
	}
	#table,
	#table th,
	#table td {
		border: 1px solid black;
		border-collapse: collapse;
		border-color:grey;
		width:33%;
	}
</style>