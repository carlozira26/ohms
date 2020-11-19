<template>
	<v-dialog v-model="healthtracker" width="80%" scrollable>
		<v-card>
			<v-card-title primary-title class="green darken-4">
				<h1 class="white--text">Healthcare Monitoring</h1>
			</v-card-title>
			<v-card-text>
				<v-layout row wrap>
					<v-flex md8>
						<div v-if="loadData != false" class="loadDiv">
							<v-progress-circular :width="7" :size="70" color="green" indeterminate></v-progress-circular>
							<br>
							<br>
							<h3>Please Wait..</h3>
						</div>
						<div v-else>
							<div v-if="length > 0">
								<apexchart type="line" :options="options" :series="series"></apexchart>
							</div>
							<div v-else class="loadDiv">
								<v-icon color="red" large>fa-times</v-icon>
								<br>
								<br>
								<h3>No Data Found</h3>
							</div>
						</div>
					</v-flex>
					<v-flex md4>
						<v-layout row wrap class="text-xs-left">
							<v-flex md12>
								<h3>Patient Profile</h3>
							</v-flex>
							<v-flex md4>
								Patient ID :
							</v-flex>
							<v-flex md8>
								# {{patient.patient_id}}
							</v-flex>
							<v-flex md4>
								Patient Name :
							</v-flex>
							<v-flex md8>
								{{patient.lastname}}, {{patient.firstname}}
							</v-flex>
							<v-flex md4>
								Category :
							</v-flex>
							<v-flex md8>
								{{patient.category}}
							</v-flex>
							<v-flex md4>
								Status :
							</v-flex>
							<v-flex md8>
								{{patient.status}}
							</v-flex>
							<v-flex md4>
								Date Started :
							</v-flex>
							<v-flex md8>
								{{dateFormat(patient.datestart)}}
							</v-flex>
						</v-layout>
					</v-flex>
				</v-layout>
				<div>
					
				</div>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>
<script>
	import Vue from 'vue';
	import VueApexCharts from 'vue-apexcharts';
	import ApexCharts from "apexcharts";
	import axios from "axios";

	export default {
		components : {
			'apexchart' : VueApexCharts,
		},
		props : ['mtitle'],
		created : function(){
			this.eventHub.$on('showHealthTracker', val =>{
				this.patient = val.data;
				this.loadData = true;
				this.getChartData();
				this.healthtracker = true;
			});
		},
		data : function(){
			return { 
				patient : [],
				length: 0,
				loadData: true,
				healthtracker : false,
				options: {
					chart: {
						id: 'lineGraphChart',
						toolbar : {
							show : true,
							tools: {
								download: false,
								selection: true,
								zoom: true,
								zoomin: true,
								zoomout: true,
								pan: true,
							}
						}
					},
					fill: {
						type: "gradient",
						gradient: {
							shadeIntensity: 1,
							opacityFrom: 0.7,
							opacityTo: 0.9,
							stops: [0, 90, 100]
						}
					},
					markers: {
						size: 4,
						colors: ["#1bc821"],
						strokeColors: "#fff",
						strokeWidth: 2,
						hover: {
							size: 7,
						}
					},
					xaxis: {
						categories: []
					},
					yaxis: {
						forceNiceScale: false,
						max: 100,
						labels: {
							formatter: (value) => value.toFixed(2) +'%',
						},
					}
				},
				series: [{
					name: 'Percentage',
					data: []
				}]

			}
		},
		methods : {
			getChartData : function(){
				let _this = this;
				_this.length = 0;
				_this.series[0].data = [];
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/healthcare/monitoring?id='+this.patient.id+'&category='+this.patient.category+'&date='+this.patient.datestart)
				.then(function(res){
					if(res.data.status == false){
						_this.loadData = false
					}else{
						_this.length = res.data.series.length;
						for(let i in res.data.series){
							_this.series[0].data.push(res.data.series[i].data);
						}
						setTimeout(function(){ 
							_this.loadData = false; 
						}, 1000);
						_this.options.xaxis.categories = res.data.xaxis;
						_this.updateChart();
					}
				});
			},
			updateChart : async function(axis){
				ApexCharts.exec("lineGraphChart", "updateOptions",{
					xaxis: {
						categories: axis
					}
				});
			},
		}
	};
</script>
<style>
.loadDiv {
	min-height:450px;
	padding: 180px 0;
	text-align: center;
}
</style>