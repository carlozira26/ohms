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
					<template>
						<v-layout row wrap>
							<v-flex sm4 md3>
								<div>
									<div class="text-xs-center" v-if="userData.image_path">
										<img style="border:1px solid grey; object-fit: cover; height:120px; width:100%" src="http://122.53.152.8/ohms/api/uploads/Doctors/8.jpeg"/>
									</div>
									<div class="text-xs-center" style="border:1px solid grey;" v-else>
										<v-icon dark color="grey" large style="margin-top:40px;height:70px">fa-user-md</v-icon>
									</div>
								</div>
							</v-flex>
							<v-flex sm8 md9 class="text-sm-left">
								<v-flex md12 style="margin-left:10px">
									<div class="title">
										Doctor {{userData.firstname + " " + userData.lastname}}
									</div>
									<div class="subheading">
										{{ userData.specialization }} - {{userData.subspecialization}}
									</div>
									<div class="subheading">
										License: {{ changeLicenseFormat(userData.licensenumber) }}
									</div>
								</v-flex>
								<v-flex md12 style="margin-left:10px; margin-top:10px">
									<div class="subheading">
										{{ userData.clinic_name }}
									</div>
									<div class="body-2">
										{{ userData.clinic_address }}
									</div>
									<div class="body-2">
										{{ userData.contact_number }}
									</div>
								</v-flex>
								<v-flex md12 style="margin-left:10px; margin-top:10px">
									<div class="title">
										Schedule
									</div>
									<div>
										<table width="100%">
											<tbody>
												<tr v-for="(day,i) in days" :key="i">
													<td width="25%">{{ day }}</td>
													<td>{{ doctorSchedule[i]}}</td>
												</tr>
											</tbody>
										</table>
									</div>
								</v-flex>
							</v-flex>
						</v-layout>
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
				userData : {},
				doctorSchedule : [],
				days : ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
				dialog : false,
			}
		}, 
		methods : {
			processSchedule : function(){
				let schedule = this.userData.schedule;
				let sched = schedule.split(',');
				for(let x in sched){
					if(sched[x]!='None'){
						let scheduleDays = sched[x].split('-');
						this.doctorSchedule.push(scheduleDays[0]+" to "+scheduleDays[1]);
					}else{
						this.doctorSchedule.push("None");
					}
				}
			},
			fetchDoctorProfile : function(){
				this.userData.image_path = 'http://122.53.152.8/ohms/api/uploads/Doctors/8.jpeg';
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/profile')
				.then(function(res){
					if(res.data.status){
						_this.userData = res.data.data;
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