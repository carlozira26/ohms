<template>
	<v-dialog v-model="medicine" content-class="modalHeight" scrollable>
		<v-card>
				<v-card-title class="green darken-4">
					<h1 class="white--text">Patient Medicine List</h1>
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
						<v-form ref="vForm" v-on:submit.prevent="submitMedicineList">
							<v-layout row wrap v-for="(medicine,index) in medicineList" :key="index">
								<v-flex xs7 md6 class="pa-1">
									<v-select label="Medicine" :items="medicineSelect" item-value="id" item-text="medicinename" v-model="medicine.medicineID" :rules="[formRules.required]" @change="addInstructions(medicine.medicineID,index)"></v-select>
								</v-flex>
								<v-flex xs2 md2 class="pa-1">
									<v-select label="UM" :rules="[formRules.required]" :items="dosage" v-model="medicine.medicineDosage"></v-select>
								</v-flex>
								<v-flex xs2 md3 class="pa-1">
									<v-text-field label="Pieces" :rules="[formRules.required]" v-model="medicine.medicinePieces"></v-text-field>
								</v-flex>
								<v-flex xs1 md1 v-if ="index != 0">
									<v-btn flat icon @click="removeRow(index)"><v-icon small color="red darken-4">close</v-icon></v-btn>
								</v-flex>
							</v-layout>
						</v-form>
					</v-card-text>
					<v-card-actions>
						<v-spacer></v-spacer>
						<template v-if="err==false">
							<v-btn flat large type="submit" @click="submitMedicineList">Save</v-btn>
							<v-btn flat large @click="medicine = false">Cancel</v-btn>
						</template>
					</v-card-actions>
				</template>
				<template v-else>
					<v-card-text>
						<v-layout row wrap>
							<v-flex>
								<h3>Please upload a Laboratory Result first</h3>
								<v-btn flat class="blue-grey darken-4 white--text" @click="openModal('showLabResults',patient.id)">Go to Upload</v-btn>
							</v-flex>
						</v-layout>
					</v-card-text>
				</template>
		</v-card>
	</v-dialog>
</template>
<script>
	import axios from "axios";
	export default {
		created: function(){
			this.eventHub.$on('showAddMedicine', val => {
				this.patient['id'] = val.id;
				this.patient['status'] = val.status;
				this.patient['category'] = val.category;
				this.medicineList = [];
				this.medicine = true;
				this.checkLaboratory();
			});
		},
		data : function(){
			return {
				medicine : false,
				medicineList: [],
				offset : true,
				medicineSelect:[],
				patient : [],
				err : false,
				dosage : ["5 mg", "10 mg", "15 mg"]
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
				.get('/patient/laboratory/'+this.patient.id)
				.then(function(res){
					if(res.data.status){
						_this.viewMedicineList();
						_this.addMedicineRow();
						_this.getPatientMedicine();
						_this.err = false;
					}else{
						_this.err = true;
					}
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
			getPatientMedicine : function(){
				let _this = this,
				medicines = [];
				axios.create({
					baseURL : _this.apiUrl,
					headers	: {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/patient/list?id='+this.patient.id+'&status='+this.patient.status+'&category='+this.patient.category)
				.then(function(res){
					medicines = res.data.data;
					_this.medicineList = [];
					if(res.data.status){
						for(let i in medicines){
							let dosage = (_this.patient.status=='New') ? medicines[i].dosage +' mg/kg' : medicines[i].dosage;
							_this.medicineList.push({
								medicineID : medicines[i].id,
								medicineDosage : dosage,
								medicinePieces : medicines[i].pieces
							})
						}
					}
				});
			},
			submitMedicineList : function(){
				if(this.$refs.vForm.validate()){
					let _this = this,
					formData = new FormData();
					formData.append('id', _this.patient.id);
					formData.append('patientMedicineList', JSON.stringify(_this.medicineList));
					formData.append('category',this.patient.category);
					axios.create({
						baseURL : _this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/medicine/list/submit', formData)
					.then(function(res){
						let returnval = { message : "Patient's medicine has been saved!", icon : "done", color : "green"};
						_this.eventHub.$emit("showSnackBar", returnval);
						_this.medicine = false;
						_this.eventHub.$emit("updatePatientList",{ 'patientID' : _this.patient.id, 'status' : _this.patient.status });
					})
				}else{
					let returnval = { message : "Please fill-up all the required fields", icon : "error", color : "red"};
					this.eventHub.$emit("showSnackBar", returnval);
				}
			},
		}
	};
</script>
<style>
	.modalHeight {
		max-width: 40%;
	}
	@media only screen and (max-width: 600px){
		.modalHeight {
			max-width: 100%;
		}
	}
</style>