<template>
	<v-dialog v-model="medicine" content-class="modalHeight">
		<v-card>
			<v-form ref="vForm" v-on:submit.prevent="submitMedicineList">
				<v-card-title class="green darken-4">
					<h1 class="white--text">Add Medicine</h1>
					<v-spacer></v-spacer>
					<v-tooltip bottom v-if="err == false">
						<template v-slot:activator="{ on }">
							<v-btn flat icon v-on="on" @click="addMedicineRow"><v-icon color="white">fa fa-plus-circle</v-icon></v-btn>
						</template>
					<span>Add Medicine</span>
					</v-tooltip>
				</v-card-title>
				<template v-if="err==false">
					<v-card-text>
						<v-layout row wrap v-for="(medicine,index) in medicineList" :key="index">
							<v-flex xs7 md6 class="pa-1">
								<v-select label="Medicine" :items="medicineSelect" item-value="id" item-text="medicinename" v-model="medicine.medicineID" :rules="[formRules.required]" @change="addInstructions(medicine.medicineID,index)"></v-select>
							</v-flex>
							<v-flex xs2 md2 class="pa-1">
								<v-text-field label="ML" :rules="[formRules.required]" v-model="medicine.medicineDosage"></v-text-field>
							</v-flex>
							<v-flex xs2 md3 class="pa-1">
								<v-text-field label="Pieces" :rules="[formRules.required]" v-model="medicine.medicinePieces"></v-text-field>
							</v-flex>
							<!-- <v-flex xs1 md1>
								<v-menu open-on-hover right bottom :offset-x="offset">
									<template v-slot:activator="{ on }">
										<v-btn fab small icon outline class="green darken-4" v-on="on"><v-icon color="green darken-4">fa fa-file-prescription</v-icon></v-btn>
									</template>
									<template v-if="medicine.medicineInstructions">
										<v-card left width="200px">
											<v-card-text>
												<h3>Medicine Instructions</h3>
												{{ medicine.medicineInstructions }}
											</v-card-text>
										</v-card>
									</template>
									<template v-else>
										<v-card>
											<v-card-text>
												Please select a medicine first
											</v-card-text>
										</v-card>
									</template>
								</v-menu>
							</v-flex> -->
							<v-flex xs1 md1 v-if ="index != 0">
								<v-btn fab small icon outline class="red darken-4" @click="removeRow(index)"><v-icon color="red darken-4">fa fa-minus</v-icon></v-btn>
							</v-flex>
						</v-layout>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<template v-if="err==false">
							<v-btn flat large type="submit">Save</v-btn>
							<v-btn flat large @click="medicine = false">Cancel</v-btn>
						</template>
					</v-card-actions>
				</template>
				<template v-else>
					<v-card-text>
						<v-layout row wrap>
							<v-flex>
								<h3>Please upload a Laboratory Result first</h3>
								<v-btn flat class="blue-grey darken-4 white--text" @click="openModal('showLabResults',patientID)">Go to Upload</v-btn>
							</v-flex>
						</v-layout>
					</v-card-text>
				</template>
			</v-form>
		</v-card>
	</v-dialog>
</template>
<script>
	import axios from "axios";
	export default {
		created: function(){
			this.eventHub.$on('showAddMedicine', val => {
				
				this.patientID = val.patientID;
				this.medicineList = [];
				this.medicine = true;
				this.checkLaboratory(this.patientID)
			});
		},
		data : function(){
			return {
				medicine : false,
				medicineList: [],
				offset : true,
				medicineSelect:[],
				patientID : '',
				err : false,
			}
		}, 
		methods : {
			openModal : function(modal,patientID){
				this.medicine = false;
				this.eventHub.$emit(modal, {'patientID': patientID});
			},
			checkLaboratory: function(id){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patient/laboratory/'+id)
				.then(function(res){
					if(res.data.status){
						_this.viewMedicineList();
						_this.addMedicineRow();
						_this.getPatientMedicine();
						_this.err = false;
					}else{
						_this.err = true;
					}
					console.log(_this.err);
				});
			},
			addMedicineRow : function(){
				this.medicineList.push({
					medicineID : "",
					medicineDosage : "",
					medicinePieces : "",
					medicineInstructions : ""
				});
			},
			viewMedicineList : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/get')
				.then(function(res){
					_this.medicineSelect  = res.data.data;
				});
			},
			removeRow : function(index){
				let newArr = [];
				for(let i in this.medicineList){
					if(index != i){
						newArr.push(this.medicineList[i]);
					}
				}
				this.medicineList = newArr;
			},
			addInstructions : function(id,index){
				for(let i in this.medicineSelect){
					if(id == this.medicineSelect[i].id){
						this.medicineList[index].medicineInstructions = this.medicineSelect[i].instructions;
					} 
				}
			},
			getPatientMedicine : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers	: {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/patient/list/'+_this.patientID)
				.then(function(res){
					if(res.data.length>0){
						_this.medicineList = [];
					}
					for(let i in res.data){
						_this.medicineList.push({
							medicineID : res.data[i].medicineid,
							medicineDosage : res.data[i].dosage,
							medicinePieces : res.data[i].pieces,
							medicineInstructions : res.data[i].instructions
						})
					}
				});
			},
			submitMedicineList : function(){
				if(this.$refs.vForm.validate()){
					let _this = this,
					formData = new FormData();
					formData.append('patientMedicineList', JSON.stringify(_this.medicineList));
					axios.create({
						baseURL : _this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/medicine/list/submit/'+_this.patientID,formData)
					.then(function(res){
						let returnval = { message : "Patient's medicine has been saved!", icon : "done", color : "green"};
						_this.eventHub.$emit("showSnackBar", returnval);
						_this.medicine = false;
					})
				}else{
					let returnval = { message : "Please fill-up all the required fields", icon : "error", color : "red"};
					this.eventHub.$emit("showSnackBar", returnval);
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