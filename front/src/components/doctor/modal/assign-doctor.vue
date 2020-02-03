<template>
	<v-dialog v-model="assignDoctorModal" content-class="modalAssign">
		<v-card>
			<v-form ref="vForm" v-on:submit.prevent="assignDoctorSubmit">
				<v-card-title class="green darken-4">
					<h1 class="white--text">{{ title }}</h1>
				</v-card-title>
				<v-card-text>
					<p>Please assign a doctor:</p>
					<v-select :items="doctorList" item-value="id" item-text="doctor" v-model="doctorID" :rules="[formRules.required]" solo label="Doctor's Name"></v-select>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat large type="submit">Submit</v-btn>
					<v-btn flat large @click="assignDoctorModal=false">Cancel</v-btn>
				</v-card-actions>
			</v-form>
		</v-card>
	</v-dialog>
</template>
<script>
	import axios from "axios";
	import VueCookies from "vue-cookies";
	export default {
		props : ['mtitle'],
		created : function(){
			this.role = VueCookies.get(this.cookieKey).data.role;
			this.eventHub.$on('assignDoctor', val =>{
				this.assignDoctorModal = true;
				this.getDoctorList();
				this.patientID = val.patientID;
				this.checkDoctorAssigned();
				if(this.role == 2){
					this.title = "Reassign Doctor"
				}
			});
		},
		data : function(){
			return { 
				assignDoctorModal : false,
				doctorList : [],
				doctorID: "",
				patientID : "",
				title : "Assign Doctor"
			}
		},
		methods : {
			getDoctorList : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/users/doctor')
				.then(function(res){
					_this.doctorList = res.data;
				});
			},
			assignDoctorSubmit : function(){
				let _this = this,
				formData = new FormData();
				
				formData.append('patientID',_this.patientID);
				formData.append('doctorid',this.doctorID);
				if(_this.$refs.vForm.validate()){
					axios.create({
						baseURL : _this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/patients/assign', formData)
					.then(function(res){
						let returnval = { message : res.data.message, icon : "done", color : "green"};
						_this.eventHub.$emit("showSnackBar", returnval);
						_this.assignDoctorModal = false;
					});
				}else{
					let returnval = { message : "Please assign a doctor!", icon : "error", color : "red"};
					this.eventHub.$emit("showSnackBar", returnval);
				}
			},
			checkDoctorAssigned : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/doctor/'+_this.patientID)
				.then(function(res){
					if(res.data.status){
						_this.doctorID = res.data.data.doctor_id;
					}
				})
				
			}
		}
	};
</script>
<style>
	.modalAssign {
		max-width: 30%;
	}
	@media only screen and (max-width: 600px){
		.modalAssign {
			max-width: 100%;
		}
	}
</style>