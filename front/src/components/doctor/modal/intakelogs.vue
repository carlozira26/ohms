<template>
	<v-dialog v-model="medicinelogs" content-class="intakelogs">
		<v-card max-height="320px" min-height="373px" style="overflow:auto">
			<v-card-title primary-title class="green darken-4 white--text">
				<h2>Intake Logs</h2>
			</v-card-title>
			<v-card-text >
				<v-layout row wrap>
					<v-flex class="text-md-left">
						<table style="width:100%;border-collapse:collapse">
							<tr v-if="logs.length > 0">
								<td width="50%" style="padding:3px">
									<table>
										<tr v-for="(log,index) in logs" v-bind:key="index">
											<td>{{ log.created_at }}</td>
										</tr>
									</table> 
								</td>
								<td style="padding:3px">
									<table>
										<tr v-for="(log,index) in logs" v-bind:key="index">
											<td>{{ log.medicine }}</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr v-else>
								<td class="text-center">
									<h4>No Logs Found!</h4>
								</td>
							</tr>
						</table>
					</v-flex>
				</v-layout>
			</v-card-text>
		</v-card>	
	</v-dialog>
</template>
<script>
	import axios from "axios";
	export default {
		created: function(){
			this.eventHub.$on('viewLogs', val => {
				this.getLogs(val.patientID);
			});
		},
		data: function(){
			return{
				medicinelogs : false,
				logs : [],
			}
		}, 
		methods : {
			getLogs : function(id){
				this.medicinelogs = true;
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/medicinelogs?id='+id)
				.then(function(res){
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