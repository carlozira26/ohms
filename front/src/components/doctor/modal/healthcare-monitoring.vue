<template>
	<v-dialog v-model="healthtracker" width="80%">
		<v-card>
			<v-card-title primary-title class="green darken-4">
				<h1 class="white--text">Healthcare Monitoring</h1>
			</v-card-title>
			<v-card-text>
				<apexchart width="500" type="line" :options="options" :series="series"></apexchart>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>
<script>
	import Vue from 'vue';
	import VueApexCharts from 'vue-apexcharts';
	import ApexCharts from "apexcharts";
	export default {
		components : {
			'apexchart' : VueApexCharts,
		},
		props : ['mtitle'],
		created : function(){
			this.eventHub.$on('showHealthTracker', val =>{
				this.healthtracker = true;
			});
		},
		data : function(){
			return { 
				healthtracker : false,
				options: {
					chart: {
						id: 'vuechart-example'
					},
					xaxis: {
						categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
					}
				},
				series: [{
					name: 'series-1',
					data: [30, 40, 45, 50, 49, 60, 70, 91]
				}]
			}
		}
	};
</script>