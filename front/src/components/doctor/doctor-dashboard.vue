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
									<v-card-title class="title">
										Infected per Age Group
										<v-spacer></v-spacer>
										<v-btn icon title="Download" small color="green darken-4"><v-icon color="white" small>file_download</v-icon></v-btn>
									</v-card-title>
									<v-divider></v-divider>
									<v-card-text>
										<apexchart type="pie" :options="pieOptions" :series="pieSeries"></apexchart>
									</v-card-text>
								</v-card>
							</v-flex>
							<v-flex sm7 class="pa-1">
								<v-card>
									<v-card-title class="title">
										Patient Status Outcomes Monthly
										<v-spacer></v-spacer>
										<v-btn icon title="Download" small color="green darken-4"><v-icon color="white" small>file_download</v-icon></v-btn>
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
	import ApexCharts from 'vue-apexcharts';
	import axios from 'axios';
	export default {
		components : {
			'apexchart' : ApexCharts
		},
		mounted : function(){
			this.getInfected();
			this.getStatusOutcomes();
		},
		data: function() {
			return {
				date : "",
				date1: "",
				date2 : "",
				menu1 : false,
				options: {
					chart: {
						id: 'Chart',
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

				pieOptions : {
					labels : ['17 below','18 to 25', '26 to 35','36 to 40','41 above']
				},
				pieSeries : [],
				barSeries : []
			}
		},
		methods : {
			dateRange : async function(){
				let d1="",d2="";
				this.ApexCharts.exec("Chart", "updateOptions", {
					xaxis: {
					categories: ["A", "B", "C"]
					}
					});
				this.options.xaxis.categories =[];
				if(this.date1!=''){
					d1 = new Date(this.date1).toDateString().substr(4);
					this.date = d1+" ~ "+d2;
				} if (this.date2!='') {
					d2 = new Date(this.date2).toDateString().substr(4);
					this.date = d1+" ~ "+d2;
					this.getInfected();
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
			getStatusOutcomes : function(){
				let _this = this;
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
						_this.options.xaxis.categories.push(res.data.months[i]);
					}
				})
			}
		},
	};

	</script>