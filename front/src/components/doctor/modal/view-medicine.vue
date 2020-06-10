<template>
	<div>
		<v-dialog v-model="medicineListModal" content-class="medicineListModal" scrollable>
			<v-card>
				<v-card-title class="green darken-4">
					<h1 class="white--text">Medicine List</h1>
					<v-spacer></v-spacer>
					<v-tooltip bottom>
						<template v-slot:activator="{ on }">
							<v-btn icon small dark v-on="on" @click="addEditMedicine('new')"><v-icon>fa fa-plus</v-icon></v-btn>
						</template>
					<span>Add New Medicine</span>
					</v-tooltip>
				</v-card-title>
				<v-card-text>
					<v-layout row wrap>
						<v-flex md12>
							<v-text-field single-line prepend-icon="search" v-model="search" @keyup="getMedicineList" full-width hide-details label="Search here ..."></v-text-field>
						</v-flex>
						<v-flex md12>
							<table class="v-datatable v-table" style="border:1px solid #ddd">
								<thead>
									<tr class="grey lighten-4" style="border-bottom:1px solid #333">
										<th class="font-weight-bold text-xs-center" style="width:20%;">MEDICINE NAME</th>
										<th class="font-weight-bold text-xs-center" style="width:10%;">STATUS</th>
										<th class="font-weight-bold text-xs-center" style="width:20%;">ACTIONS</th>
									</tr>
								</thead>
								<tbody>
									<template v-if="medicineList == 0">
										<tr>
											<td colspan="5" class="text-center">No data found</td>
										</tr>
									</template>
									<template v-else>
										<tr v-for="(medicine,index) in medicineList" v-bind:key="index">
											<td>{{ medicine.brandname + " (" + medicine.genericname + ")"}}</td>
											<td>{{ medicine.is_active }}</td>
											<td>
												<v-tooltip bottom>
													<template v-slot:activator="{ on }">
														<v-btn v-on="on" icon dark color="green darken-4" @click="addEditMedicine('edit',index)"><v-icon small>fa fa-pen</v-icon></v-btn>
													</template>
												<span>Edit Medicine</span>
												</v-tooltip>
												<v-tooltip bottom>
													<template v-slot:activator="{ on }">
														<v-btn v-on="on" icon dark color="red darken-4" @click="deleteModal(index)"><v-icon small>fa fa-trash</v-icon></v-btn>
													</template>
												<span>Delete Medicine</span>
												</v-tooltip>
											</td>
										</tr>
									</template>	
								</tbody>
							</table>
							<v-pagination circle color="green darken-4" total-visible="8" v-model="pagination.page" :length="pagination.length" light @input="getMedicineList"></v-pagination>
						</v-flex>
					</v-layout>
				</v-card-text>
			</v-card>
		</v-dialog>
		<v-dialog v-model="addMedicine" content-class="addNewMedicineModal" ref="addMedicineDialogBox" scrollable>
			<v-form ref="vForm" v-on:submit.prevent="addEditMedicineSubmit">
			<v-card>
				<v-card-title class="green darken-4">
					<h1 class="white--text">{{ modalTitle }}</h1>
				</v-card-title>
				<v-card-text>
					<v-layout wrap>
						<v-flex md6 sm12 class="pa-1">
							<v-text-field label="Brand Name" v-model="medicine.brandName" :rules="[formRules.required]"></v-text-field>  
						</v-flex>
						<v-flex md6 sm12 class="pa-1">
							<v-text-field label="Generic Name" v-model="medicine.genericName" :rules="[formRules.required]"></v-text-field>  
						</v-flex>
						<v-flex md6 sm12 class="pa-1">
							<v-text-field label="Manufacturer" v-model="medicine.manufacturer" :rules="[formRules.required]"></v-text-field>  
						</v-flex>
						<v-flex md6 sm12 class="pa-1">
							<v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y full-width min-width="290px">
								<template v-slot:activator="{ on }">
									<v-text-field label="Expiration Date" readonly v-on="on" type="text" :value="formatDate(medicine.expiration)" :rules="[formRules.required]"/>
								</template>
								<v-date-picker color="green darken-4" ref="picker" @input="menu = false" no-title v-model="medicine.expiration"></v-date-picker>
							</v-menu>
						</v-flex>
					</v-layout>
					<v-layout row wrap>
						<v-flex md12 sm12 class="pa-1">
							<v-textarea no-resize label="Description" v-model="medicine.description" :rules="[formRules.required]"></v-textarea>  
						</v-flex>
					</v-layout>

				</v-card-text>
				<v-divider></v-divider>
				<v-card-actions>
						<v-btn flat small icon v-if="medicine.primary" @click="medicine.primary=!medicine.primary"><v-icon small color="green darken-4">check</v-icon></v-btn>
						<v-btn flat small icon v-else @click="medicine.primary=!medicine.primary"><v-icon small color="red darken-4">close</v-icon></v-btn>
						Primary Medicine
					<v-spacer></v-spacer>
					<v-btn flat large @click="addMedicine=false">CANCEL</v-btn>
					<v-btn flat large @click="addEditMedicineSubmit(type)">SUBMIT</v-btn>
				</v-card-actions>
			</v-card>
			</v-form>
		</v-dialog>
		<v-dialog v-model="deleteBox" content-class="warningbox">
			<v-card>
				<v-card-title class="red darken-4">
					<v-icon color="white" class="pa-1">error</v-icon><h1 class="white--text">Warning</h1>
				</v-card-title>
				<v-card-text>
					<h2 class="font-weight-light">Are you sure you want to delete this medicine?</h2>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat large @click="deleteMedicine()">Confirm</v-btn>
					<v-btn flat large @click="deleteBox=false">Cancel</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>
<script>
	import axios from "axios";
	export default{
		created : function(){
			this.eventHub.$on('showMedicineList', val =>{
				this.medicineListModal = true;
				this.getMedicineList();
			});
		},

		data : function(){
			return {
				menu : false,
				addMedicine : false,
				medicineListModal : false,
				dayList : ["Daily","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
				medicineList : [],
				search : "",
				pagination : { page: 1, length : 0 },

				medicine: {
					primary : false,
				},
				models : {
					timeIntake : []
				},

				modalTitle : "",
				type : "",
				deleteBox: false,
				index: "",

			}
		},
		methods : {
			getMedicineList : function(){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/list?page='+_this.pagination.page+'&search='+_this.search)
				.then(function(res){
					_this.medicineList = res.data.data;
					_this.pagination.length = Math.ceil(res.data.count.count / 6);
				});
			},
			deleteModal : function(index){
				this.deleteBox = true;
				this.index = index;	
			},
			deleteMedicine : function(){
				let _this = this;
				var index = this.index;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/medicine/delete/'+_this.medicineList[index].id)
				.then(function(res){
					_this.deleteBox = false;
					_this.getMedicineList();
				})
			},
			addEditMedicineSubmit : function(type){
				if( this.$refs.vForm.validate() ){
					let _this = this,
					formData = new FormData();

					if(type=="edit"){
						formData.append('id',_this.medicine.id);
					}
					formData.append("brandName", this.medicine.brandName);
					formData.append("genericName", this.medicine.genericName);
					formData.append("manufacturer", this.medicine.manufacturer);
					formData.append("expiration", this.medicine.expiration);
					formData.append("description", this.medicine.description);
					formData.append("is_primary", this.medicine.primary);
					axios.create({
						baseURL :  _this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/medicine/addedit/'+type, formData)
					.then(function(res){
						_this.addMedicine = false;
						let returnval = { message : res.data.message, icon : "done", color : "green"};
						_this.eventHub.$emit("showSnackBar", returnval);
						_this.getMedicineList();
					});
				}else{
					let returnval = { message : "Please fill up all the required fields", icon : "error", color : "red"};
					this.eventHub.$emit("showSnackBar", returnval);
				}
			},
			addEditMedicine : function(type,id){
				this.type = type;
				if(type == 'new'){
					this.modalTitle = "New Medicine";
					this.$refs.vForm.reset();
				}else{
					this.modalTitle = "Edit Medicine";
					this.medicine.id = this.medicineList[id].id;
					this.medicine.brandName = this.medicineList[id].brandname;
					this.medicine.genericName = this.medicineList[id].genericname;
					this.medicine.manufacturer = this.medicineList[id].manufacturer;
					this.medicine.expiration = this.medicineList[id].expiration;
					this.medicine.description = this.medicineList[id].description;
					this.medicine.primary = (this.medicineList[id].is_primary=="Y") ? true : false;
				}
				this.addMedicine = true;
			},
			
		}
	};
</script>
<style>
	.medicineListModal{
		max-width: 60% ;
	}
	.addNewMedicineModal {
		max-width: 40%;
	}
	.warningbox{
		max-width: 40%;
	}
	@media only screen and (max-width: 600px){
		.test {
			max-width: 100%;
		}
		.warningbox {
			max-width: 100%;
		}
	}
	.centered-input input {
		text-align: center;
		font-size: 30px;
	}
</style>