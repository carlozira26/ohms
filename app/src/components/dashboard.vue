<template>
	<div>
		<v-container>
			<v-text-field single-line full-width hide-details prepend-icon="search" v-model="search" @keyup="fetchPatients" label="Search id or name here.."></v-text-field>
			<table class="theme--light fluid" id="tabletracker">
				<thead>
					<tr class="grey lighten-4" style="border-bottom:1px solid #333;">
						<th class="font-weight-bold text-md-center grey darken-1 white--text">Patient ID</th>
						<th class="font-weight-bold text-md-center grey darken-1 white--text">Full Name</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(patient,id) in patientList" v-bind:key="id" @click="selectPatient(id)">
						<td>{{patient.patient_id}}</td>
						<td>{{patient.firstname + " " + patient.lastname}}</td>
					</tr>
				</tbody>
			</table>
		</v-container>
		<patient></patient>
	</div>
</template>

<script>
import VueCookies from 'vue-cookies';
import axios from 'axios';
import Patient from './patient.vue';

export default {
	components : {
		'patient' : Patient
	},
	mounted : function(){
		this.token = VueCookies.get(this.cookieKey).token;
		this.fetchPatients();
	},
	data : function(){
		return {
			patientList : [],
			dialog : false,
			search : "",
		}
	},
	methods : {
		fetchPatients : function(){
			let _this = this;
			axios.create({
				baseURL : this.apiUrl,
				headers: {
					'Authorization' : `Bearer ${this.token}`
				}
			})
			.get('/patients/app/list?search='+this.search)
			.then(function(res){
				_this.patientList = res.data.data;
			});
		},
		selectPatient : function(id){
			this.eventHub.$emit('viewPatient', { patient: this.patientList[id] } );
		}
	}
};
</script>
<style>
	#tabletracker {
		font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
		border-collapse: collapse;
		width: 100%;
	}

	#tabletracker td, #tabletracker th {
		border: 1px solid #ddd;
		padding: 8px;
	}

	#tabletracker tr:nth-child(even){background-color: #f2f2f2;}

	#tabletracker tr:hover {background-color: #ddd;}
</style>