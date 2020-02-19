<template>
	<v-dialog v-model="dialog" content-class="modalAssign">
		<v-card min-height="550px">
			<v-card-title primary-title class="green darken-4 white--text">
				<h2>Messenger</h2>
			</v-card-title>
			<v-card-text>
				<v-layout row wrap>
					<v-flex md12 xs12 class="text-md-left" v-if="pt == 1">
						<div>
							<div style="min-height:380px">
								<v-list-tile avatar @click="test">
									<v-list-tile-avatar>
										<v-icon dark class="green darken-1">fa-user-md</v-icon>
									</v-list-tile-avatar>
									<v-list-tile-content>
										<v-list-tile-title>Doctor One</v-list-tile-title>
										<v-list-tile-sub-title>Hey! How are you?</v-list-tile-sub-title>
										<v-divider></v-divider>
									</v-list-tile-content>
								</v-list-tile>
								<v-divider
									:inset="inset"
								></v-divider>
								<v-list-tile avatar>
									<v-list-tile-avatar>
										<v-icon dark class="green darken-1">fa-user-md</v-icon>
									</v-list-tile-avatar>
									<v-list-tile-content>
										<v-list-tile-title>Doctor Two</v-list-tile-title>
										<v-list-tile-sub-title>Hey?</v-list-tile-sub-title>
									</v-list-tile-content>
								</v-list-tile>
								<v-divider
									:inset="inset"
								></v-divider>
							</div>
						</div>
					</v-flex>
					<v-flex md12 xs12 class="text-md-left" v-else>
						<div>
							<div ref="chatArea" style="min-height:380px" id="scroll-target" v-scroll:#scroll-target="onScroll">
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
							</div>
							<div>
								<v-form ref="vForm" v-on:submit.prevent="submitMessage">
									<v-text-field solo width="100%" light class="text-message" label="Type text here..." v-model="message" @click:append="submitMessage()" append-icon="fa-location-arrow"></v-text-field>
								</v-form>
							</div>
						</div>
					</v-flex>
				</v-layout>
			</v-card-text>
		</v-card>	
	</v-dialog>
</template>
<script>
import axios from "axios";
export default {
	mounted: function(){
		this.eventHub.$on('viewMessage', val => {
			this.dialog = true;
			this.conn = val;
			this.pt = 1;
			
			let _this = this;
			val.onmessage = function(e) {
				_this.messages.push(
					{
						body : e.data,
						author : this.userType
					}
				);
				console.log(e.data); 
			};
			// val.onopen = function(e) {
			// 	console.log("Connection established!");
			// 	val.send('Hello Me!');
			// }
		});
	},
	data: function(){
		return{
			conn : '',
			dialog : false,
			pt : 1,
			inset : true,

			messages: [],
			userType: '',
			message : '',
			receiverid : '',
			scrollTop : 0,
			scrollHeightMinusOffsetHeight: 0,
			page: 1,
		}
	}, 
	methods : {
		test : function(){
			this.pt = 2;
		},
		submitMessage : function(){
				let _this = this,
				formData = new FormData();
				formData.append('message',_this.message);
				formData.append('usertype',_this.userType);
				formData.append('receiverid',_this.receiverid);
				_this.conn.send(this.message); // submit to ratchet websocket
				
				_this.messages.push(
					{
						body : this.message,
						author : this.userType
					}
				);

				// axios.create({
				// 	baseURL : this.apiUrl,
				// 	headers : {
				// 		'Authorization' : `Bearer ${this.token}`
				// 	}
				// })
				// .post('/chat/submit', formData)
				// .then(function(res){

				// });
				_this.$refs.vForm.reset();
			},
			updateMessage : function(){
				let _this = this,
				formData = new FormData();
				formData.append('page', _this.page);

				// axios.create({
				// 	baseURL : _this.apiUrl,
				// 	headers : {
				// 		'Authorization' : `Bearer ${this.token}`
				// 	}
				// })
				// .post('/messages/update/'+_this.receiverid, formData)
				// .then(function(res){
				// 	_this.arrangeMessage(res.data.data);
				// 	if(_this.page == 1){
				// 		setTimeout(function(){
				// 			_this.$refs.chatArea.scrollTop = _this.$refs.chatArea.scrollHeight;
				// 		},500);
				// 	}
				// });
			},
			arrangeMessage : function(messages){
				for(let messageIndex in messages){
					this.messages.unshift({
						author: messages[messageIndex].user_type,
						body: messages[messageIndex].message
					})
				}
			},
			onScroll : function(e){
				this.scrollTop = e.target.scrollTop;
				if(this.scrollTop == 0){
					this.page += 1;
					this.updateMessage();
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