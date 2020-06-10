<template>
	<div>
		<v-dialog v-model="labResults" content-class="modalHeight">
			<v-card>
				<v-card-title class="green darken-4">
					<h1 class="white--text">Laboratory Results</h1>
					<v-spacer></v-spacer>
					<v-tooltip bottom>
						<template v-slot:activator="{ on }">
							<v-btn flat icon @click="examinationModal = !examinationModal" v-on="on"><v-icon color="white">fa fa-plus-circle</v-icon></v-btn>
						</template>
						<span>Add Result</span>
					</v-tooltip>
				</v-card-title>
				<v-card-text style="overflow:auto">
					<div class="text-xs-left"><label class="font-weight-bold">Patient ID</label> : # {{ patient.patient_id }} </div>
					<div class="text-xs-left"><label class="font-weight-bold">Patient Name</label> : {{patient.firstname + " " + patient.lastname}}</div>
					<table class="v-datatable v-table mt-3" style="border:1px solid #ddd">
						<thead>
							<tr class="grey lighten-4" style="border-bottom:1px solid #333">
								<th class="font-weight-bold text-xs-center" style="width:10%;">DATE UPLOADED</th>
								<th class="font-weight-bold text-xs-center" style="width:10%;">DIAGNOSTIC TYPE</th>
								<th class="font-weight-bold text-xs-center" style="width:10%;">EXAMINATION DATE</th>
								<th class="font-weight-bold text-xs-center" style="width:10%;">RESULTS</th>
							</tr>
						</thead>
						<tbody>
							<template v-if="diagnosticData == 0">
								<tr>
									<td colspan="4" class="text-center">No data found</td>
								</tr>
							</template>
							<template v-else>
								<tr v-for="(diagnosis,index) in diagnosticData" v-bind:key="index">
									<td>{{diagnosis.created_at}}</td>
									<td>{{diagnosis.diagnostic_type}}</td>
									<td>{{diagnosis.examination_date}}</td>
									<td>
										<v-btn flat @click="checkFileIfExist(diagnosis.image_location, diagnosis.remarks)">View</v-btn>
									</td>
								</tr>
							</template>
						</tbody>
					</table>
					<v-pagination circle color="green darken-4" total-visible="8" v-model="pagination.page" :length="pagination.length" light @input="fetchDiagnostics"></v-pagination>
				</v-card-text>
			</v-card>
		</v-dialog>
		<v-dialog max-width="300px" v-model="examinationModal">
			<v-form ref="vForm" v-on:submit.prevent="submitTestResult">
				<v-card>
					<v-card-text>
						<v-layout row wrap class="text-sm-left">
							<v-flex>
							<label>Diagnostic type:</label>
								<v-select solo :items="examinationType" v-model="diagnostic.diagnosticType" :rules="[formRules.required]"></v-select>
							</v-flex>
							<template v-if="diagnostic.diagnosticType == 'Other Diagnostic Test'">
								<v-flex>
									<label>Please specify:</label>
									<v-text-field solo :rules="[formRules.required]" v-model="diagnostic.specific"></v-text-field>
								</v-flex>
							</template>
							<v-flex>
								<label>Date of Examination:</label>
								<v-menu ref="menu" v-model="menu" :close-on-content-click="false">
								<template v-slot:activator="{ on }">
									<v-text-field :value="formatDate(diagnostic.examinationdate)" solo readonly v-on="on"></v-text-field>
								</template>
								<v-date-picker @input="menu=!menu" v-model="diagnostic.examinationdate" :max="new Date().toISOString().substr(0, 10)" no-title scrollable></v-date-picker>
						</v-menu>
							</v-flex>
							<v-flex>
							<label>File to upload:</label>
								<input type="file" ref="file" style="display: none" @change="imageSelect">
								<v-text-field solo readonly v-model="image.name" label="Select an image..." @click="$refs.file.click()" :rules="[formRules.required]"></v-text-field>
							</v-flex>
							<v-flex>
							<label >Remarks:</label>
								<v-textarea solo v-model="diagnostic.remarks" :rules="[formRules.required]"></v-textarea>
							</v-flex>
							<v-flex>
								<v-btn type="submit" class="green darken-4 white--text" block>Upload</v-btn>
							</v-flex>
						</v-layout>
					</v-card-text>
				</v-card>
			</v-form>
		</v-dialog>
		<v-dialog v-model="viewResultImage" content-class="modalHeight">
			<v-card class="text-md-left">
				<v-layout>
					<v-flex md8 xs12 style="overflow:auto">
						<v-img :src="url"></v-img>
					</v-flex>
					<v-flex md4 class="grey lighten-3 pa-2">
						<v-layout row wrap>
							<v-flex xs12>
								<label><h3>Remarks:</h3></label>
								<p>{{diagnostic.remarks}}</p>
							</v-flex>
						</v-layout>
						<div style="position: absolute; bottom: 10px;">
							# {{patient.patient_id}} : {{patient.firstname + " " + patient.lastname}}
						</div>
					</v-flex>
				</v-layout>
			</v-card>
		</v-dialog>
	</div>
</template>
<script>
	import axios from "axios";
	import moment from 'moment';
	export default {
		mounted: function(){
			this.eventHub.$on('showLabResults', val => {
				this.patientID = val.id;
				this.labResults = true;
				this.patient = val.data;
				this.fetchDiagnostics();
			});
		},
		data : function(){
			return {
				patient : [],
				menu : false,
				labResults : false,
				examinationModal:false,
				examinationType : [
					"Sputum Examination",
					"TST Examination",
					"CXR Examination",
					"Other Diagnostic Test"
				],
				pagination : { page: 1, length : 0 },
				image: [],
				diagnostic : [],
				diagnosticData : [],
				url : '',
				viewResultImage: false,
			}
		}, 
		methods : {
			imageSelect(e){
				if(e.target.files[0].type === "image/png" || e.target.files[0].type === "image/jpeg"){
					this.image = e.target.files[0];
				}else{
					this.image = [];
					let returnval = { message : "The selected file is not an image!", icon : "error", color : "red"};
					this.eventHub.$emit("showSnackBar", returnval);
				}
			},
			fetchDiagnostics : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/diagnostic?id='+_this.patientID+'&page='+_this.pagination.page)
				.then(function(res){
					_this.diagnosticData = res.data.data;
					for(let i in _this.diagnosticData){
						_this.diagnosticData[i].created_at = moment(_this.diagnosticData[i].created_at).subtract(10, 'days').calendar();
						_this.diagnosticData[i].examination_date = moment(_this.diagnosticData[i].examination_date).subtract(10, 'days').calendar();

					}
					_this.pagination.length = Math.ceil(res.data.count.count / 8);
				})
			},
			submitTestResult : function(){
				if( this.$refs.vForm.validate() ){
					let _this = this,
					formData = new FormData();

					if(this.diagnostic.diagnosticType == 'Other Diagnostic Test'){
						formData.append('diagnostic', this.specific);
					}else{
						formData.append('diagnostic', this.diagnostic.diagnosticType);
					}
					formData.append('diagnostictype',this.diagnostic.diagnosticType);
					formData.append('patientid', this.patientID);
					formData.append('examinationdate', this.diagnostic.examinationdate);
					formData.append('imageFile', this.image, this.image.name);
					formData.append('remarks', this.diagnostic.remarks);
					axios.create({
						baseURL : _this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						},
					})
					.post('/patient/result',formData)
					.then(function(res){
						let returnval = { message : res.data.message , icon : "done", color : "green"};
						_this.eventHub.$emit("showSnackBar", returnval);
						_this.examinationModal = false;
						_this.fetchDiagnostics();
					});
				}
			},
			checkFileIfExist(file,remarks){
				if(file != null){
					var http = new XMLHttpRequest();
					http.open('head', file, false);
					http.send();
					this.diagnostic.remarks = remarks;
					if(http.status){
						this.url = file;
						this.viewResultImage = true;
					}
				}
			}
		}
	};
</script>
<style>
	.modalHeight {
		max-width: 50%;
	}
	@media only screen and (max-width: 600px){
		.modalHeight {
			max-width: 100%;
		}
	}
</style>