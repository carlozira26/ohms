<template>
	<div>
		<v-dialog v-model="medicineListModal" content-class="test">
			<v-card>
				<v-card-title class="green darken-4">
					<h1 class="white--text">Medicine List</h1>
					<v-spacer></v-spacer>
					<v-tooltip bottom>
						<template v-slot:activator="{ on }">
							<v-btn fab small v-on="on" @click="addEditMedicine('new')"><v-icon color="green darken-4">fa fa-plus</v-icon></v-btn>
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
										<th class="font-weight-bold text-xs-center" style="width:20%;">INSTRUCTIONS</th>
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
											<td>{{ medicine.instructions }}</td>
											<td>{{ medicine.is_active }}</td>
											<td>
												<v-tooltip bottom>
													<template v-slot:activator="{ on }">
														<v-btn v-on="on" fab dark small color="green darken-4" @click="addEditMedicine('edit',index)"><v-icon dark>fa fa-pen</v-icon></v-btn>
													</template>
												<span>Edit Medicine</span>
												</v-tooltip>
												<v-tooltip bottom>
													<template v-slot:activator="{ on }">
														<v-btn v-on="on" fab dark small color="red darken-4" @click="deleteModal(index)"><v-icon dark>fa fa-trash</v-icon></v-btn>
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
		<v-dialog v-model="addMedicine" content-class="test" ref="addMedicineDialogBox">
			<v-form ref="vForm" v-on:submit.prevent="addEditMedicineSubmit">
			<v-card>
				<v-card-title class="green darken-4">
					<h1 class="white--text">{{ modalTitle }}</h1>
				</v-card-title>
				<v-card-text>
					<v-layout wrap>
						<v-flex md3 sm12 class="pa-1">
							<v-text-field label="Brand Name" v-model="brandName" :rules="[formRules.required]"></v-text-field>  
						</v-flex>
						<v-flex md3 sm12 class="pa-1">
							<v-text-field label="Generic Name" v-model="genericName" :rules="[formRules.required]"></v-text-field>  
						</v-flex>
						<v-flex md6 sm12 class="pa-1">
							<v-text-field label="Instructions" v-model="instructions" :rules="[formRules.required]"></v-text-field>
						</v-flex>
					</v-layout>
				</v-card-text>
				<v-divider></v-divider>
				<v-card-title>
					<h1 class="green--text">Medicine Time Intake</h1>
					<v-spacer></v-spacer>
					<v-tooltip bottom>
						<template v-slot:activator="{ on }">
							<v-btn fab small v-on="on" @click="newDayTime" class="green darken-4"><v-icon color="white">fa fa-plus</v-icon></v-btn>
						</template>
					<span>Add Day/Time</span>
					</v-tooltip>
				</v-card-title>
				<v-card-text>
					<v-layout row wrap v-for="(count,index) in models.timeIntake" :key="index">
						<v-flex md6 xs6 class="pa-1">
							<v-select :items="dayList" v-model="count.dayList" label="Days of the Week" multiple>
								<template v-slot:selection="{ item, index }">
									<v-chip small v-if="index === 0">
										<span>{{ item }}</span>
									</v-chip>
									<template v-if="count.dayList.length <= 2">
										<span v-if="index === 1" class="grey--text caption">
											(+{{ count.dayList.length - 1 }} other)
										</span>
									</template>
									<template v-if="count.dayList.length > 2">
										<span v-if="index === 1" class="grey--text caption">
											(+{{ count.dayList.length - 1 }} others)
										</span>
									</template>
								</template>
							</v-select>
						</v-flex>
						<v-flex md5 xs4 class="pa-1">
							<v-dialog lazy v-model="count.modalTime" :close-on-content-click="false" transition="scale-transition" offset-y full-width max-width="350px" min-width="350px">
								<template v-slot:activator="{ on }">
									<v-select append-icon="alarm_add" @click:append="count.modalTime= !count.modalTime" label="Times of the day" v-on="on" :items="count.timeList" v-model="count.timeList" multiple>
										<template v-slot:selection="{ item, index }">
											<v-chip small v-if="index <= 1" close>
												<span>{{ item }}</span>
											</v-chip>
											<template v-if="count.timeList.length <= 3">
												<span v-if="index === 2" class="grey--text caption">
													(+{{ count.timeList.length - 2 }} other)
												</span>
											</template>
											<template v-if="count.timeList.length > 3">
												<span v-if="index === 3" class="grey--text caption">
													(+{{ count.timeList.length - 2 }} others)
												</span>
											</template>
										</template>
									</v-select>
								</template>
								<v-card v-model="count.alarmTime">
									<v-card-text>
										<v-layout row wrap>
											<v-flex xs3>
												<v-btn flat @click="changeTime('hour','up')"><v-icon>keyboard_arrow_up</v-icon></v-btn>
											</v-flex>
											<v-flex xs3 offset-xs1>
												<v-btn flat @click="changeTime('minutes','up')"><v-icon>keyboard_arrow_up</v-icon></v-btn>
											</v-flex>
											
											<v-flex xs4></v-flex>
											
											<v-flex xs3>
												<v-text-field class="centered-input" v-model="hour"></v-text-field>
											</v-flex>
											<v-flex xs1 class="pa-2" style="font-size:30px">
											:
											</v-flex>
											<v-flex xs3>
												<v-text-field class="centered-input" v-model="minutes"></v-text-field>
											</v-flex>

											<v-flex xs4 class="pa-2">
												<v-btn class="green darken-4 white--text" @click="addTime(index)">OK</v-btn>
											</v-flex>
											
											<v-flex xs3>
												<v-btn flat @click="changeTime('hour','down')"><v-icon>keyboard_arrow_down</v-icon></v-btn>
											</v-flex>
											<v-flex xs3 offset-xs1>
												<v-btn flat @click="changeTime('minutes','down')"><v-icon>keyboard_arrow_down</v-icon></v-btn>
											</v-flex>
										</v-layout>
									</v-card-text>
								</v-card>
							</v-dialog>
						</v-flex>
						<v-flex md1 xs2 v-if="index != 0">
							<v-btn flat icon outline class="red darken-4" @click="removeRow(index)"><v-icon color="red darken-4">fa fa-minus</v-icon></v-btn>
						</v-flex>
					</v-layout>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn flat large @click="addEditMedicineSubmit(type)">SUBMIT</v-btn>
					<v-btn flat large @click="addMedicine=false">CANCEL</v-btn>
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
				timeCount : [1],
				count : 1,
				addMedicine : false,
				medicineListModal : false,
				dayList : ["Daily","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
				medicineList : [],
				search : "",
				pagination : { page: 1, length : 0 },

				id : "",
				brandName : "",
				genericName : "",
				instructions: "",
				models : {
					timeIntake : []
				},

				modalTitle : "",
				type : "",
				deleteBox: false,
				index: "",
				hour: this.timePad(0,2),
				minutes : this.timePad(0,2),

			}
		},
		methods : {
			addTime : function(index){
				this.models.timeIntake[index].alarmTime = this.hour + ":" + this.minutes;
				if(this.models.timeIntake[index].alarmTime !='' && !this.models.timeIntake[index].timeList.includes(this.models.timeIntake[index].alarmTime)){
					if(this.models.timeIntake[index].timeList.length < 4){
						this.models.timeIntake[index].timeList.push(this.models.timeIntake[index].alarmTime);
						this.models.timeIntake[index].alarmTime = "";
						this.models.timeIntake[index].modalTime = false;
					}else{
						let returnval = { message : "You've reached the maximum limit!", icon : "error", color : "red"};
						this.eventHub.$emit("showSnackBar", returnval);
					}
				}
			}, 
			newDayTime : function(){
				this.models.timeIntake.push({
					dayList : [],
					timeList : [],
					modalTime : false,
					alarmTime : ""
				})
			},
			addEditMedicineSubmit : function(type){
				if( this.$refs.vForm.validate() ){
					let _this = this,
					formData = new FormData();

					if(type=="edit"){
						formData.append('id',_this.id);
					}
					formData.append("brandName", _this.brandName);
					formData.append("genericName", _this.genericName);
					formData.append("instructions", _this.instructions);
					formData.append("timeIntake",JSON.stringify(_this.models.timeIntake));

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
			getMedicineTimeIntake : function(id){
				let _this = this;
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/medicine/details/'+id)
				.then(function(res){
					var medicineTimeIntake = res.data.data;
					_this.models.timeIntake = [];
					for(let timecount=0;timecount< medicineTimeIntake.length;timecount++){
						let atimeList = medicineTimeIntake[timecount].intaketime.split(",");
						let adayList = medicineTimeIntake[timecount].intakedays.split(","); 
						_this.newDayTime();
						for(let dataTimeCount=0; dataTimeCount<atimeList.length; dataTimeCount++){
							_this.models.timeIntake[timecount].timeList.push(atimeList[dataTimeCount]);
						}
						for(let dataDayCount=0; dataDayCount< adayList.length; dataDayCount++){
							_this.models.timeIntake[timecount].dayList.push(adayList[dataDayCount]);
						}
					}
				});
			},
			addEditMedicine : function(type,id){
				this.type = type;
				if(type == 'new'){
					this.modalTitle = "New Medicine";	
					this.id = "";
					this.brandName = "";
					this.genericName = "";
					this.instructions = "";
					this.models.timeIntake = [];
					this.newDayTime();
				}else{
					this.modalTitle = "Edit Medicine";
					this.getMedicineTimeIntake(this.medicineList[id].id);
					this.id = this.medicineList[id].id;
					this.brandName = this.medicineList[id].brandname;
					this.genericName = this.medicineList[id].genericname;
					this.instructions = this.medicineList[id].instructions;
				}
				this.addMedicine = true;
			},
			removeRow : function(index){
				let newArr = [];
				for(let i in this.models.timeIntake){
					if(index != i){
						newArr.push(this.models.timeIntake[i]);
					}
				}
				this.models.timeIntake = newArr;
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
			changeTime : function(type,arrow){
				if(type == 'hour'){
					if(arrow == "up"){
						this.hour = parseInt(this.hour) + 1;
						if(this.hour > 23){
							this.hour = 0;
						}
					}else{
						this.hour = parseInt(this.hour) - 1;
						if(this.hour < 0){
							this.hour = 23;
						}
					}

					this.hour = this.timePad(this.hour, 2);
				
				}else{
					if(arrow == "up"){
						this.minutes = parseInt(this.minutes) + 15;
						if(this.minutes>59){
							this.minutes = 0;
						}
					}else{
						this.minutes = parseInt(this.minutes) - 15;
						if(this.minutes<0){
							this.minutes = 45;
						}
					}
					this.minutes = this.timePad(this.minutes,2);
				}
			}
		}
	};
</script>
<style>
	.test {
		max-width: 60%;
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