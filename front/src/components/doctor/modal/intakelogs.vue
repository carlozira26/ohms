<template>
	<v-dialog v-model="dialog" content-class="intakelogs">
		<v-card max-height="320px" min-height="373px" style="overflow:auto">
			<v-card-title primary-title class="green darken-4 white--text">
				<h2>Intake Logs</h2>
			</v-card-title>
			<v-card-text >
				<v-layout row wrap>
					<v-flex class="text-md-left">
						<table style="width:100%;border-collapse:collapse">
							<tr>
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
		mounted: function(){
			this.eventHub.$on('viewLogs', val => {
				this.dialog = true;
				this.getLogs();
			});
		},
		data: function(){
			return{
				dialog : false,
				logs : [],
			}
		}, 
		methods : {
			getLogs : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/medicinelogs')
				.then(function(res){
					_this.logs = res.data.data;
					console.log(_this.logs);
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