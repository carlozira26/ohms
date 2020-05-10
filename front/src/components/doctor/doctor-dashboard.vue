<template>
	<div>
		<v-layout row wrap>
			<v-flex xs12 class="pa-3">
				<v-card>
					<v-card-title primary-title>
						<h1 class="green--text">Dashboard</h1>
						<v-spacer></v-spacer>
						<v-layout>
							<v-flex offset-sm7 sm5>
								<v-menu ref="menu1" v-model="menu1" :close-on-content-click="false" :nudge-right="40" lazy transition="slide-y-transition" offset-y full-width>
									<template v-slot:activator="{ on }">
										<v-text-field readonly append-icon="event" v-model="date" label="Date Range" solo v-on="on"></v-text-field>
									</template>
									<v-layout row wrap>
										<v-flex sm6>
											<v-date-picker v-model="date1" @change="dateRange" no-title></v-date-picker>
										</v-flex>
										<v-flex sm6>
											<v-date-picker v-model="date2" @change="dateRange" no-title></v-date-picker>
										</v-flex>
									</v-layout>
								</v-menu>
							</v-flex>
						</v-layout>
					</v-card-title>
					<v-card-text>
						<v-layout row wrap>
							<v-flex sm5 class="pa-1">
								<v-card>
									<v-card-title class="title white--text green darken-4">
										Total Count 
										<v-spacer></v-spacer>
										<download-excel :data="totalCountData" icon style="cursor:pointer" :title="title" small color="green darken-4" name="Total Count.xls">
											<v-icon color="white">file_download</v-icon>
										</download-excel>
									</v-card-title>
									<v-divider></v-divider>
									<v-card-text>
										<apexchart height="300em" type="bar" :options="barOptions" :series="barSeries"></apexchart>
									</v-card-text>
								</v-card>
							</v-flex>
							<v-flex sm7 class="pa-1">
								<v-card>
									<v-card-title class="title white--text green darken-4">
										Patient Status Outcomes Monthly
										<v-spacer></v-spacer>
										<download-excel :data="patientListData" :fields="patientListFields" style="cursor:pointer" name="Patient Status Logs.xls" icon title="Patient Status Logs" small color="green darken-4">
											<v-icon color="white">file_download</v-icon>
										</download-excel>
									</v-card-title>
									<v-divider></v-divider>
									<v-card-text>
										<apexchart height="300em" type="bar" :options="options" :series="series"></apexchart>
									</v-card-text>
								</v-card>
							</v-flex>
						</v-layout>
					</v-card-text>
				</v-card>
			</v-flex>
		</v-layout>
	</div>
</template>
<script>
	import VueApexCharts from 'vue-apexcharts';
	import Vue from 'vue';
	import ApexCharts from "apexcharts";
	import axios from 'axios';
	import JsonExcel from 'vue-json-excel';

	export default {
		components : {
			'apexchart' : VueApexCharts,
			'downloadExcel' : JsonExcel
		},
		mounted : function(){
			this.getInfected();
			this.getStatusOutcomes();
			this.getTotalCount();
		},
		data: function() {
			return {
				date : "",
				date1: "",
				date2 : "",
				menu1 : false,
				barOptions: {
					chart: {
						id: 'bargraph',
						stacked: false,
						toolbar : {
							show : false,
						}
					},
					xaxis: {
						categories: ["New","Ongoing","Success","Discontinuation"],
					}
				},
				options: {
					chart: {
						id: 'bargraphChart',
						stacked: true,
						toolbar : {
							show : false,
						}
					},
					xaxis: {
						categories: [],
					}
				},
				series: [],
				barSeries : [],
				totalCount : [],
				categoriesOfTotal : [
					'New',
					'Ongoing',
					'Success',
					'Discontinuation'
				],
				totalCountData : [],
				patientListData : [],
				patientListFields : {
					'Patient ID' : 'patient_id',
					'First Name' : 'firstname',
					'Middle Name' : 'middlename',
					'Last Name' : 'lastname',
					'Date/Time' : 'created_at',
					'Status' : 'status'
				},
				title : ''
			}
		},
		methods : {
			dateRange : function(){
				let d1="",d2="";
				if(this.date1!=''){
					d1 = new Date(this.date1).toDateString().substr(4);
					this.date = d1+" ~ "+d2;
				} if (this.date2!='') {
					d2 = new Date(this.date2).toDateString().substr(4);
					this.date = d1+" ~ "+d2;
					this.getTotalCount();
					this.getStatusOutcomes();
				}
			},
			getInfected : function(){
				let _this = this;
				_this.pieSeries = [];
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/infected?date='+_this.date)
				.then(function(res){
					_this.pieSeries = res.data;
				})
			},
			updateChart : async function(axis){
				ApexCharts.exec("bargraphChart", "updateOptions",{
					xaxis: {
						categories: axis
					}
				});
			},
			getStatusOutcomes : function(){	
				let _this = this,
				axis = [];
				_this.series = [];
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/outcomes?date='+_this.date)
				.then(function(res){
					let x=0;
					for(let i in res.data.data){
						_this.series.push({
							name : i,
							data : res.data.data[i]
						});
					}
					
					for(let i in res.data.months){
						axis.push(res.data.months[i]);
					}
				})
				this.updateChart(axis);
				this.fetchPatients();
			},
			fetchPatients : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/outcomes/fetch?date='+_this.date)
				.then(function(res){
					_this.patientListData = res.data.data;
				});
			},
			getTotalCount : async function(){
				let _this = this;

				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/count?date='+_this.date)
				.then(function(res){
					for(let x in res.data.data){
						_this.totalCount[_this.barOptions.xaxis.categories[x]] = res.data.data[x];
					}
					_this.totalCountData.push(_this.totalCount);
					_this.title = res.data.date_range;
					ApexCharts.exec("bargraph", "updateSeries",[{
						data: res.data.data
					}]);
				});
			}

		},
	};

	</script>