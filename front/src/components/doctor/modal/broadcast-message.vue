<template>
	<v-dialog v-model="broadcastMessage" width="450" scrollable  persistent>
		<v-card>
			<v-card-title class="green darken-4 white--text">
				<h1>Broadcast Message</h1>
				<v-spacer></v-spacer>
				<v-btn flat icon class="white--text" @click="broadcastMessage=false">
					<v-icon>close</v-icon>
				</v-btn>
			</v-card-title>
			<v-card-text>
				<v-form ref="vForm" v-on:submit.prevent="submitMessage">
					<v-layout row wrap>
						<v-flex md3 class="text-md-right pa-3">
							To :
						</v-flex>
						<v-flex md9>
							<v-select solo v-model="broadcast.receiver" @change="getPatientCount" :rules="[formRules.required]" :items="receiverList"></v-select>
						</v-flex>
						<v-flex md3 class="text-md-right pa-3">
							Message :
						</v-flex>
						<v-flex md9>
							<v-textarea v-model="broadcast.message" :rules="[formRules.required]" solo></v-textarea>
						</v-flex>
					</v-layout>
				</v-form>
			</v-card-text>
			<v-card-actions>
				<template v-if="patientCount!=''">
					Total Receivers: {{ patientCount }}
				</template>
				<v-spacer></v-spacer>
				<template v-if="btnLoad==false">
					<v-btn color="green darken-4 white--text" @click="submitMessage">Send</v-btn>
				</template>
				<template v-else>
					<v-btn disabled>
						<v-progress-circular indeterminate small color="green darken-4">Sending</v-progress-circular>
					</v-btn>
				</template>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>
<script>
	import axios from "axios";
	import VueCookies from "vue-cookies";
	export default{
		created: function(){
			this.eventHub.$on('showBroadcastMessage', val => {
				this.receiver = '';
				this.message = '';
				this.broadcastMessage = true;
				this.broadcast = [];
				this.patientCount = '';
			});
		},
		data : function(){
			return{
				broadcastMessage : false,
				btnLoad : false,
				receiverList : ['New Patients','Ongoing Patients'],
				broadcast : [],
				patientCount : '',
			}
		},
		methods : {
			getPatientCount : function(){
				let _this = this;
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.get('/patients/message/count?status='+this.receiver)
				.then(function(res){
					_this.patientCount = res.data.data;
				});
			},
			submitMessage : function(){
				if( this.$refs.vForm.validate() ){
					let _this = this,
					formData = new FormData();
					formData.append('receiver', this.receiver);
					formData.append('message', this.message);
					axios.create({
						baseURL : this.apiUrl,
						headers : {
							'Authorization' : `Bearer ${this.token}`
						}
					})
					.post('/patients/message/submit',formData)
					.then(function(res){
						let snackbarMessage = (res.data.data==true) ? "Message sent!" : "Message did not send";
						let returnval = [];
						if(res.data.status){
							returnval = { message : snackbarMessage, icon : "done", color : "green"};
							_this.broadcastMessage = false;
						}else{
							returnval = { message : snackbarMessage, icon : "warning", color : "red"};
						}
						_this.eventHub.$emit('showSnackBar', returnval);
					});
				}
			}
		}
	};
</script>