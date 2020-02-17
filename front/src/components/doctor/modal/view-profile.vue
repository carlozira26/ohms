<template>
	<v-dialog v-model="dialog" content-class="profile">
		<v-card>
			<v-form ref="vForm" v-on:submit.prevent="patientAccount">
				<v-card-title primary-title class="green darken-4 white--text">
					<h1>My Profile</h1>
				</v-card-title>
				<v-card-text>
					<template>
						<div style="font-size:20px" class="text-xs-left">
							Patient ID : {{ patientID }}
						</div>
						<v-divider class="mb-2 mt-2"></v-divider>
						<v-layout row wrap>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="First Name" :rules="[formRules.required]" v-model="firstName" type="text"/>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Middle Name" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required]" v-model="middleName" type="text"/>
							</v-flex>
							<v-flex xs12 md4 class="pa-1">
								<v-text-field label="Last Name" hint="This field uses maxlength attribute" counter maxlength="20" :rules="[formRules.required]" v-model="lastName" type="text"/>
							</v-flex>
							<v-flex xs12 md6 class="pa-1">
								<v-text-field label="Username" type="text" v-model="username" :rules="[formRules.required]"/>
							</v-flex>
							<v-flex xs12 md6 class="pa-1">
								<v-text-field label="Password will be their birthdate('YYYMMDD')" v-model="password" @click:append="showHide = !showHide" :type="showHide ? 'text' : 'password'" :append-icon="showHide ? 'visibility' : 'visibility_off'" readonly/>
							</v-flex>
							<v-flex xs9 md4 class="pa-1">
								<template>
									<v-menu ref="menu" v-model="menu" :close-on-content-click="false" transition="scale-transition" offset-y full-width min-width="290px">
										<template v-slot:activator="{ on }">
											<v-text-field label="Date of Birth" readonly v-on="on" v-model="dateofBirth" @change="save" type="text" :rules="[formRules.required]"/>
										</template>
										<v-date-picker color="green darken-4" ref="picker" v-model="dateofBirth" :max="new Date().toISOString().substr(0, 10)" min="1950-01-01">
											<v-btn @click="menu = false" dark block>Close</v-btn>
										</v-date-picker>
									</v-menu>
								</template>
							</v-flex>
							<v-flex xs3 md2 class="pa-1">
								<v-text-field label="Age" v-model="age" type="text" readonly/>
							</v-flex>
							<v-flex xs9 md4 class="pa-1">
								<v-text-field label="Date of Consultation" v-model="consultationDate" type="text" readonly/>
							</v-flex>
							<v-flex xs3 md2 class="pa-1">
								<v-select label="Gender" :items="gender" :rules="[formRules.required]" v-model="patientgender"/>
							</v-flex>
							<v-flex xs4 md4 class="pa-1">
								<v-text-field label="Mobile Number" :rules="[formRules.phoneNumber, formRules.required]" v-model="mobilenumber"/>
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-text-field label="Status" v-model="patientstatus" readonly></v-text-field>
								<!-- <v-select label="Status" :items="status" v-model="patientstatus"/> -->
							</v-flex>
							<v-flex xs3 md3 class="pa-1">
								<v-select label="DR-TB" :items="presumptive" v-model="drtb"/>
							</v-flex>
							<v-flex xs2 md2 class="pa-1">
								<template v-if="drtb=='Yes'">
									<v-select label="TB Category" :items="category" v-model="tbcategory"></v-select>
								</template>
								<template v-else>
									<v-select label="TB Category" :items="category" disabled></v-select>
								</template>
							</v-flex>
							<v-flex xs12 md12 class="pa-1">
								<v-text-field label="Address" :rules="[formRules.required]" v-model="address"/>
							</v-flex>
							<v-flex xs12 md12 class="pa-1">
								<v-textarea label="Remarks" solo v-model="remarks"></v-textarea>
							</v-flex>
						</v-layout>
					</template>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn large flat @click="patientAccount(formType)">Save</v-btn>
					<v-btn large flat @click="dialog=false">Cancel</v-btn>
				</v-card-actions>
			</v-form>
		</v-card>
	</v-dialog>
</template>
<script>
	import axios from "axios";
	export default {
		mounted: function(){
			this.eventHub.$on('viewProfile', val => {
				this.dialog=true;
			});
		},
		data: function(){
			return{
				showHide : false,
				category: ["Cat I", "Cat II","MDR"],
				gender: ["Male", "Female"],
				presumptive: ["Yes","No"],
				menu : false,

				dialog : false,
				patientID : '',
				firstName : '',
				middleName : '',
				lastName : '',
				username : '',
				password : '',
				dateofBirth : '',
				age : '',
				consultationDate : '',
				patientgender : '',
				mobilenumber : '',
				patientstatus : '',
				drtb: "Yes",
				tbcategory : '',
				address : '',
				remarks : ''
			}
		}, 
		methods : {
			save : function(date) {
				this.$refs.menu.save(date);
			},
		}
	};
</script>
<style>
	.profile {
		max-width : 60%;
	}
	@media only screen and (max-width: 600px){
		.profile {
			max-width: 100%;
		}
	}
</style>