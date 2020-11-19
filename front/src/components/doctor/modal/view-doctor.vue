<template>
	<div>
		<v-dialog v-model="dialog" content-class="profile" scrollable>
			<v-card>
				<v-card-title primary-title class="green darken-4 white--text">
					<h1>Doctor's Profile</h1>
					<v-spacer></v-spacer>
					<v-btn flat icon><v-icon color="white" @click="dialog=false">fa-times</v-icon></v-btn>
				</v-card-title>
				<v-card-text>
					<template v-if="stat">
						<v-layout row wrap>
							<v-flex xs4 sm4 md3>
								<div>
									<div class="text-xs-center" v-if="userData.image_path">
										<img style="border:1px solid grey; object-fit: cover; height:120px; width:100%" :src="userData.image_path"/>
									</div>
									<div class="text-xs-center" style="border:1px solid grey;" v-else>
										<v-icon dark color="grey" large style="margin-top:40px;height:70px">fa-user-md</v-icon>
									</div>
								</div>
							</v-flex>
							<v-flex xs8 sm8 md9 class="text-sm-left text-xs-left">
								<v-flex md12 style="margin-left:10px">
									<div class="title">
										Dr. {{userData.firstname + " " + userData.lastname}}
									</div>
									<div class="subheading text-xs-left">
										{{ userData.specialization }} {{(userData.subspecialization!='') ? '('+userData.subspecialization+")" : ''}}
									</div>
									<div class="subheading text-xs-left">
										License: {{ changeLicenseFormat(userData.licensenumber) }}
									</div>
								</v-flex>
								<v-flex md12 style="margin-left:10px; margin-top:10px">
									<div class="subheading text-xs-left">
										{{ userData.clinic_name }}
									</div>
									<div class="body-2 text-xs-left">
										{{ userData.clinic_address }}
									</div>
									<div class="body-2 text-xs-left">
										{{ userData.contact_number }}
									</div>
								</v-flex>
							</v-flex>
							<v-flex md12 style="margin-left:10px; margin-top:10px">
								<div class="title">
									Schedule
								</div>
								<div>
									<table width="100%">
										<tbody>
											<template v-if="doctorSchedule.length>0">
												<tr v-for="(day,i) in days" :key="i">
													<td width="40%" class="text-sm-right">{{ day }}</td>
													<td class="text-xs-left pl-3">{{ doctorSchedule[i]}}</td>
												</tr>
											</template>
											<template v-else>
												<tr>
													<td class="text-md-center">The doctod didn't set the schedule.</td>
												</tr>
											</template>
										</tbody>
									</table>
								</div>
							</v-flex>
						</v-layout>
					</template>
					<template v-else >
						<h2>No Doctor assigned yet. Please ask the clinic to assign your doctor.</h2>
					</template>
				</v-card-text>
			</v-card>
		</v-dialog>
	</div>
</template>
<script>
	import VueCookies from 'vue-cookies';
	import axios from "axios";
	export default {
		mounted: function(){
			this.eventHub.$on('viewDoctor', val => {
				this.token = VueCookies.get(this.cookieKey).token;
				this.userid = VueCookies.get(this.cookieKey).data.id;
				this.dialog = true;
				this.fetchDoctorProfile();
			});
		},
		data: function(){
			return{
				userData : [],
				doctorSchedule : [],
				days : ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
				dialog : false,
				stat : false,
			}
		}, 
		methods : {
			processSchedule : function(){
				let schedule = this.userData.schedule;
				if(schedule!=''){
					let sched = schedule.split(',');
					for(let x in sched){
						if(sched[x]!='None'){
							let scheduleDays = sched[x].split('-');
							this.doctorSchedule.push(scheduleDays[0]+" to "+scheduleDays[1]);
						}else{
							this.doctorSchedule.push("None");
						}
					}
				}
			},
			fetchDoctorProfile : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/profile')
				.then(function(res){
					_this.stat = res.data.status;
					if(_this.stat){
						_this.userData = res.data.data;
						_this.userData.licensenumber = (_this.userData.licensenumber) ? _this.userData.licensenumber : "0000000";
						_this.processSchedule();
					}
				});
			},
		}
	};
</script>
<style>
	.profile {
		max-width : 40%;
	}
	@media only screen and (max-width: 600px){
		.profile {
			max-width: 100%;
		}
	}
</style>