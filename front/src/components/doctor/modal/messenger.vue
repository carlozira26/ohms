<template>
	<v-dialog v-model="dialog" content-class="modalAssign">
		<v-card min-height="550px">
			<v-card-title primary-title class="green darken-4 white--text">
				<template v-if="pt == 1">
					<h2>Messenger</h2>
				</template>
				<template v-else>
					<v-btn flat icon @click="selectnback" small v-if="userType=='patient'"><v-icon color="white" small>fa-chevron-left</v-icon></v-btn>
					<h3>{{ receivername }}</h3>
				</template>
			</v-card-title>
			<v-card-text style="max-height:400px;overflow-y:auto" v-if="pt == 1">
				<v-flex md12 xs12 class="text-md-left">
					<template v-if="messagelist.length > 0">
						<div v-for="(usermessage,index) in messagelist" :key="index">
							<v-list-tile avatar @click="selectnback(usermessage.doctor_id, usermessage.doctor_name, usermessage.patient_id, usermessage.patient_name)">
								<v-list-tile-avatar>
									<template v-if="userType == 'patient'">
										<v-icon dark class="green darken-1">fa-user-md</v-icon>
									</template>
									<template v-else>
										<v-icon dark class="green darken-1">fa-user</v-icon>
									</template>
								</v-list-tile-avatar>
								<v-list-tile-content>
									<template v-if="userType=='patient'">
										<v-list-tile-title>{{ usermessage.doctor_name }}</v-list-tile-title>
									</template>
									<template v-else>
										<v-list-tile-title>{{ usermessage.patient_name }}</v-list-tile-title>
									</template>
									<v-list-tile-sub-title class="grey--text">{{ usermessage.message }}</v-list-tile-sub-title>
								</v-list-tile-content>
								<template v-if="usermessage.unseen > 0">
									<v-badge color="pink" title="Messages">
										<template v-slot:badge>
											<span>{{ usermessage.unseen }}</span>
										</template>
									</v-badge>
								</template>
							</v-list-tile>
							<v-divider
								:inset="inset"
							></v-divider>
						</div>
					</template>
					<template v-else>
						<h3>No Messages Found!</h3>
					</template>
				</v-flex>
			</v-card-text>
			<v-card-text ref="chatArea" id="scroll-target" v-scroll:#scroll-target="onScroll" style="max-height:400px;overflow-y:auto" v-else>
				<v-flex md12 xs12 class="text-md-left">
					<div v-for="(message,key) in messages" v-bind:key="key">
						<div align="right" v-if="message.author === userType">
							<div class="message-out">
								{{ message.body }}
							</div>
						</div>
						<div align="left" v-else>
							<div class="message-in">
								{{ message.body }}
							</div>
						</div>
					</div>
				</v-flex>
			</v-card-text>
			<section  v-if="pt > 1" style="position:absolute; bottom:0; width:100%">
				<v-form ref="vForm" v-on:submit.prevent="submitMessage">
					<v-text-field solo width="100%" counter class="text-message" label="Type text here..." v-model="message" @click:append="submitMessage()" append-icon="fa-location-arrow"></v-text-field>
				</v-form>
			</section>
		</v-card>
	</v-dialog>
</template>
<script>
import axios from "axios";
import VueCookies from 'vue-cookies';
export default {
	mounted: function(){
		this.eventHub.$on('viewMessage', val => {
			this.role = VueCookies.get(this.cookieKey).data.role;
			this.senderid = VueCookies.get(this.cookieKey).data.id;
			this.dialog = true;
			this.conn = val.wsconnect;
			
			if(this.role == 'none'){
				this.pt = 1;
				this.userType = "patient";
			}else{
				let patientDetails = val.patientDetails;
				let patientname = patientDetails.firstname+" "+patientDetails.lastname; 
				this.selectnback(this.senderid,0,patientDetails.id,patientname);
				this.userType = "doctor";
			}

			this.fetchMessageList();
			let _this = this;
			this.conn.onmessage = function(e) {
				let retmsg = JSON.parse(e.data);
				
				if(_this.senderid == retmsg.receiver){
					_this.messages.push(
						{
							body : retmsg.body,
							author : retmsg.author
						}
					);
				}

				setTimeout(function(){
					_this.$refs.chatArea.scrollTop = _this.$refs.chatArea.scrollHeight;
				}, 50); 
			};
		});
	},
	data: function(){
		return{
			conn : '',
			dialog : false,
			pt : 1,
			inset : true,
			senderid : '',

			messages: [],
			messagelist : [],
			userType: '',
			message : '',
			receiverid : '',
			receivername : '',
			scrollTop : 0,
			scrollHeightMinusOffsetHeight: 0,
			page: 1,
		}
	}, 
	methods : {
		selectnback : function(doctorid, doctorname, patientid, patientname){
			this.receiverid = (this.userType=='patient') ? doctorid : patientid;
			this.receivername = (this.userType=='patient') ? doctorname : patientname;
			
			if(this.userType == 'patient'){
				if(this.pt == 1){
					this.pt = 2;
					this.fetchMessage();
				}else{
					this.pt = 1;
				}
			}else{
				this.pt = 2;
				this.fetchMessage();
			}
		},
		fetchMessageList : function(){
			let _this = this;
			axios.create({
				baseURL : _this.apiUrl,
				headers : {
					'Authorization' : `Bearer ${this.token}`
				}
			})
			.get('/messages/list?usertype='+this.userType)
			.then(function(res){
				_this.messagelist = res.data.data;
			});
		},
		submitMessage : function(){
				let _this = this,
				formData = new FormData();
				formData.append('senderid',_this.senderid);
				formData.append('receiverid',_this.receiverid);
				formData.append('message',_this.message);
				formData.append('usertype',_this.userType);
				let msg = {
						body : _this.message,
						receiver : 1,
						author : _this.userType,
					}
				
				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/messages/submit', formData)
				.then(function(res){
					_this.conn.send(JSON.stringify(msg)); // submit to ratchet websocket
					_this.messages.push( msg );
					setTimeout(function(){
						_this.$refs.chatArea.scrollTop = _this.$refs.chatArea.scrollHeight;
					},50);
					_this.$refs.vForm.reset();
				});
			},
			fetchMessage : function(){
				let _this = this,
				formData = new FormData();
				formData.append('page', _this.page);
				formData.append('userType', _this.userType);
				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/messages/fetch/'+_this.receiverid, formData)
				.then(function(res){
					_this.arrangeMessage(res.data.data);
					// if(_this.page == 1){
						
					// }
				});
			},
			arrangeMessage : function(messages){
				this.messages = [];
				let _this = this;
				for(let messageIndex in messages){
					this.messages.push({
						author: messages[messageIndex].message_from,
						body: messages[messageIndex].message
					})
				}
			},
			onScroll : function(e){
				this.scrollTop = e.target.scrollTop;
				if(this.scrollTop == 0){
					this.page += 1;
					this.fetchMessage();
				}
			}
	}
};
</script>
<style>
	.message-out {
		background: #1b5e20;
		color: white;
		border-radius: 20px;
		padding: .6em;
		font-size: 1em;
		word-wrap:break-word;
		border:1px solid;
		width:auto;
		display: inline-block;
		max-width:200px;
		margin-bottom:0.2em;
	}
	.message-in {
		background: #A0A0A0;
		color: white;
		word-wrap: break-word;
		border-radius: 20px;
		padding: .6em;
		font-size: 1em;
		word-wrap:break-word;
		width:auto;
		display: inline-block;
		max-width:200px;
		margin-bottom:0.2em;
	}
</style>