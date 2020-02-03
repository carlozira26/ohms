<template>
	<v-layout wrap>
		<v-navigation-drawer v-model="drawer"  app dark right floating style="overflow:hidden">
			<v-list class="pa-1">
				<v-list-tile avatar tag="div">
					<v-list-tile-avatar>
						<img src="https://randomuser.me/api/portraits/men/85.jpg">
					</v-list-tile-avatar>

					<v-list-tile-content>
						<v-list-tile-title></v-list-tile-title>
					</v-list-tile-content>

					<v-list-tile-action>
						<v-btn icon @click.stop="drawer = !drawer">
							<v-icon>chevron_right</v-icon>
						</v-btn>
					</v-list-tile-action>
				</v-list-tile>
			</v-list>
			<v-container class="chat-area ma-1" ref="chatArea" id="scroll-target" v-scroll:#scroll-target="onScroll">
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
			</v-container>
			<section class="ma-1">
				<v-form ref="vForm" v-on:submit.prevent="submitMessage">
					<v-text-field solo light :autofocus="focus" class="text-message" label="Type text here..." v-model="message" @click:append="submitMessage()" append-icon="fa-location-arrow"></v-text-field>
				</v-form>
			</section>
		</v-navigation-drawer>
	</v-layout>
</template>
<script>
	import axios from "axios";
	import VueCookies from 'vue-cookies';
	export default {
		mounted: function(){
			this.role = VueCookies.get(this.cookieKey).data.role;
			this.eventHub.$on('showMessage', val => {
				
				this.drawer=true;
				this.receiverid = val.patientID;
				if(this.role == 'none'){
					this.userType = "patient";
				}else{
					this.userType = "doctor";
				}
				this.messages = [];
				this.updateMessage();
			});
		},
		data : function(){
			return {
				focus : false,
				drawer: false,
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
			submitMessage : function(){
				let _this = this,
				formData = new FormData();
				formData.append('message',_this.message);
				formData.append('usertype',_this.userType);
				formData.append('receiverid',_this.receiverid);
				
				_this.messages.push(
					{
						body : this.message,
						author : this.userType
					}
				);

				axios.create({
					baseURL : this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/chat/submit', formData)
				.then(function(res){

				});
				_this.$refs.vForm.reset();
			},
			updateMessage : function(){
				let _this = this,
				formData = new FormData();
				formData.append('page', _this.page);

				axios.create({
					baseURL : _this.apiUrl,
					headers : {
						'Authorization' : `Bearer ${this.token}`
					}
				})
				.post('/messages/update/'+_this.receiverid, formData)
				.then(function(res){
					_this.arrangeMessage(res.data.data);
					if(_this.page == 1){
						setTimeout(function(){
							_this.$refs.chatArea.scrollTop = _this.$refs.chatArea.scrollHeight;
						},500);
					}
				});
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
			},

		}
	};
</script>
<style>
	.chat-area {
		border: 1px solid #ccc; 
		background: white;
		height: 80%;
		padding: 1em;
		overflow: auto;
		max-width: 350px;
		margin: 0 auto 2em auto;
		box-shadow: 2px 2px 5px 2px rgba(0, 0, 0, 0.3);
		border-top-left-radius: 5px;
		border-top-right-radius: 5px;
	}
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