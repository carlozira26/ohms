<template>
	<v-dialog v-model="medicineIntakelogs" width="60%">
		<v-card style="overflow:auto" min-height="373px">
			<v-card-title primary-title class="text-xs-center green darken-4 white--text">
				<h2>Intake Logs</h2>
			</v-card-title>
			<v-card-text >
				<v-layout row wrap>
					<v-flex class="text-md-left">
						<table class="theme--light fluid" id="tablemedicine">
							<thead>
								<tr class="text-sm-center">
									<th>Status</th>
									<th>Date/Time</th>
									<th>Scheduled Medicine Date</th>
									<th>Medicine</th>
									<th>Reason</th>
								</tr>
							</thead>
							<tbody>
								<template v-if="logs.length > 0">
									<tr class="text-sm-center" v-for="(log,index) in logs" v-bind:key="index">
										<td>{{ log.status }}</td>
										<td>{{ formatDateTime(log.created_at) }}</td>
										<td>{{ formatDate(log.date) }}</td>
										<td>{{ log.medicine }}</td>
										<td>{{ log.reason }}</td>
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
				this.patient = val.data;
				this.getLogs(val.patientID);
				this.medicineIntakelogs = true;
			});
		},
		data: function(){
			return{
				medicineIntakelogs : false,
				logs : [],
				patient : [],
			}
		}, 
		methods : {
			getLogs : function(id){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/medicinelogs?id='+this.patient.id)
				.then(function(res){
					console.log(res.data);
					_this.logs = res.data.data;
				})
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