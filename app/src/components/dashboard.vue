<template>
	<div>
		<v-container>
			<v-text-field single-line full-width hide-details prepend-icon="search" v-model="search" @keyup="fetchPatients" label="Search id or name here.."></v-text-field>
			<!-- <div>
				<v-btn flat icon @click="backDate"><v-icon color="grey darken-2">fa-chevron-left</v-icon></v-btn>
				<label class="title">{{ formatDate(date) }}</label>
				<v-btn flat icon @click="forwardDate"><v-icon color="grey darken-2">fa-chevron-right</v-icon></v-btn>
			</div> -->
			<table class="theme--light fluid" id="tabletracker">
				<thead>
					<tr class="grey lighten-4" style="border-bottom:1px solid #333;">
						<th class="font-weight-bold text-md-center grey darken-1 white--text">Patient ID</th>
						<th class="font-weight-bold text-md-center grey darken-1 white--text">Full Name</th>
					</tr>
				</thead>
				<tbody>
					<tr v-if="patientList.length == 0">
						<td colspan="2">No Patient Found</td>
					</tr>
					<tr v-else v-for="(patient,id) in patientList" v-bind:key="id" @click="selectPatient(id)">
						<td>{{patient.patient_id}}</td>
						<td>{{patient.firstname + " " + patient.lastname}}</td>
					</tr>
				</tbody>
			</table>
		</v-container>
		<patient></patient>
		<v-snackbar v-model="snackbar" color="green darken-4">
			{{ snackbarMessage }}
			<v-btn icon @click="snackbar = false">
				<v-icon>close</v-icon>
			</v-btn>
		</v-snackbar>
	</div>
</template>

<script>
import VueCookies from 'vue-cookies';
import axios from 'axios';
import Patient from './patient.vue';
import moment from 'moment';

export default {
	components : {
		'patient' : Patient
	},
	mounted : function(){
		this.token = VueCookies.get(this.cookieKey).token;
		this.fetchPatients();
		this.eventHub.$on('snackBar', val => {
			this.snackbarMessage = val;
			this.snackbar = true;
		});
	},
	data : function(){
		return {
			patientList : [],
			dialog : false,
			snackbar : false,
			search : "",
			snackbarMessage : "",
			date : new Date().toISOString().substr(0, 10),
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
			.get('/patients/app/list?search='+this.search+"&date="+this.date)
			.then(function(res){
				_this.patientList = res.data.data;
			});
		},
		selectPatient : function(id){
			this.eventHub.$emit('viewPatient', { patient: this.patientList[id], date : this.date } );
		},
		formatDate : function(date){
			return moment(date).format('l');
		},
		// forwardDate : function(){
		// 	this.date = moment(this.date).add(1, 'days').format('l');
		// 	this.fetchPatients();
		// },
		// backDate : function(){
		// 	this.date = moment(this.date).subtract(1, 'days').format('l');
		// 	this.fetchPatients();
		// }
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