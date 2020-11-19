<template>
	<div>
		<v-layout row wrap>
			<v-flex xs12 class="pa-3">
				<v-card>
					<v-card-title primary-title>
						<h1 class="green--text">Dashboard</h1>
						<v-spacer></v-spacer>
						<v-menu max-width="20%" left v-model="menu" :close-on-content-click="false" offset-y>
							<template v-slot:activator="{ on }">
								<v-btn flat icon v-on="on">
									<v-icon small>fa fa-ellipsis-v</v-icon>
								</v-btn>
							</template>
							<v-card>
								<v-card-text>
									<v-layout row wrap>
										<v-flex md12 class="pa-1">
											<v-select label="Status" v-model="selectedStatus" :items="statusList" hide-details></v-select>
										</v-flex>
										<v-flex md12 class="pa-1">
											<v-select hide-details v-model="selectedCategory" label="Category" :items="categoryList"></v-select>
										</v-flex>
										<v-flex md6 class="pa-1">
											<v-menu ref="menu1" v-model="menu1" :close-on-content-click="false" :nudge-right="40" lazy transition="slide-y-transition" offset-y full-width>
												<template v-slot:activator="{ on }">
													<v-text-field readonly v-model="date1" label="From" v-on="on" hide-details></v-text-field>
												</template>
												<v-date-picker v-model="datePicker1" @change="dateRange" no-title></v-date-picker>
											</v-menu>
										</v-flex>
										<v-flex md6 class="pa-1">
											<v-menu ref="menu2" v-model="menu2" :close-on-content-click="false" :nudge-right="40" lazy transition="slide-y-transition" offset-y full-width>
												<template v-slot:activator="{ on }">
													<v-text-field readonly v-model="date2" label="To" v-on="on" hide-details></v-text-field>
												</template>
												<v-date-picker v-model="datePicker2" @change="dateRange" no-title></v-date-picker>
											</v-menu>
										</v-flex>
										<v-flex md12>
											<v-btn block @click="filter" class="green darken-4 white--text">Filter</v-btn>
										</v-flex>
									</v-layout>
								</v-card-text>
							</v-card>
						</v-menu>
					</v-card-title>
					<v-card-text>
						<v-layout row wrap>
							<v-flex sm7 class="pa-1">
								<v-card>
									<v-card-title class="title white--text green darken-4">
										Total Count
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
							<v-flex sm5 class="pa-1">
								<v-card>
									<v-card-title class="title white--text green darken-4">
										Patient Status Outcomes Monthly
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
						</v-layout>
					</v-card-text>
				</v-card>
			</v-flex>
		</v-layout>
	</div>
</template>
<script>
	import Vue from 'vue';
	import VueApexCharts from 'vue-apexcharts';
	import ApexCharts from "apexcharts";
	import axios from 'axios';
	import JsonExcel from 'vue-json-excel';

	export default {
		components : {
			'apexchart' : VueApexCharts,
			'downloadExcel' : JsonExcel
		},
		mounted : function(){
			this.getStatusOutcomes();
			this.getTotalCount();
		},
		data: function() {
			return {
				date : "",
				date1 : "",
				date2 : "",
				datePicker1: "",
				datePicker2 : "",
				selectedStatus : 'All',
				selectedCategory : 'All',
				menu : false,
				menu1 : false,
				menu2 : false,
				barOptions: {
					chart: {
						id: 'bargraph',
						stacked: true,
						toolbar : {
							show : false,
						}
					},
					xaxis: {
						categories: ["New","Ongoing","Success","Discontinuation"],
					}
				},
				series: [],

				options: {
					chart: {
						id: 'bargraphChart',
						type: 'bar',
						toolbar : {
							show : false,
						}
					},
					xaxis: {
						categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
					}
				},
				barSeries : [{ name : 'Count', data : [] }],
				totalCount : [],
				statusList : [
					'All',
					'New',
					'Ongoing',
					'Success',
					'Discontinuation'
				],
				categoryList : ["All","Cat I","Cat II", "MDR"],
				totalCountData : [],
				patientListData : [],
				patientListFields : {
					'Patient ID' : 'patient_id',
					'First Name' : 'firstname',
					'Middle Name' : 'middlename',
					'Last Name' : 'lastname',
					'Consultation Date' : 'consultationdate',
					'Status' : 'status',
					'Category' : 'category'
				},
				title : ''
			}
		},
		methods : {
			filter :function(){
				this.getTotalCount();
				this.getStatusOutcomes();
			},
			dateRange : function(){
				if(this.datePicker1!=''){
					this.date1 = new Date(this.datePicker1).toDateString().substr(4);
					this.date = this.date1+" ~ "+this.date2;
				} if (this.datePicker2!='') {
					this.date2 = new Date(this.datePicker2).toDateString().substr(4);
					this.date = this.date1+" ~ "+this.date2;
				}
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
				.get('/patients/outcomes?date='+this.date+'&status='+this.selectedStatus+"&category="+this.selectedCategory)
				.then(function(res){
					let dataList = [];
					for(let i in res.data.data){
						dataList.push(res.data.data[i]);
					}
					_this.series = [{
						name : "Patient Count",
						data : dataList
					}];
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
				.get('/patients/outcomes/fetch?date='+this.date+'&status='+this.selectedStatus+"&category="+this.selectedCategory)
				.then(function(res){
					_this.patientListData = res.data.data;
					for(let i in _this.patientListData){
						_this.patientListData[i].consultationdate = new Date(_this.patientListData[i].consultationdate).toDateString().substr(4)
					}
				});
			},
			getTotalCount : async function(){
				let _this = this;
				
				_this.totalCountData = [];
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/count?date='+this.date+'&status='+this.selectedStatus+"&category="+this.selectedCategory)
				.then(function(res){
					for(let x in res.data.data){
						_this.totalCount[_this.barOptions.xaxis.categories[x]] = res.data.data[x];
					}
					_this.totalCountData.push(_this.totalCount);
					_this.title = res.data.date_range;
					_this.barSeries = [
						{
							name : 'New',
							data : [res.data.data[0],0,0,0]
						},
						{
							name : 'Ongoing',
							data : [0,res.data.data[1],0,0]
						},
						{
							name : 'Success',
							data : [0,0,res.data.data[2],0]
						},
						{
							name : 'Discontinued',
							data : [0,0,0,res.data.data[3]]
						}
					];
				});
			}

		},
	};

	</script>