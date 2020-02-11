<template>
	<v-dialog v-model="dialog" content-class="message">
		<v-card>
			<v-card-title primary-title class="green darken-4 white--text">
				<h2>Messenger</h2>
			</v-card-title>
			<v-card-text>
				<v-layout row wrap>
					<v-flex sm4 class="text-md-left">
						<v-card>
							<v-card-text></v-card-text>
						</v-card>
					</v-flex>
					<v-flex sm8  class="text-md-left">
						<v-card>
							<v-card-text></v-card-text>
						</v-card>
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
			});
		},
		data: function(){
			return{
				dialog : false
			}
		}, 
		methods : {

		}
	};
</script>
<style>
	.message {
		max-width : 60%;
	}
	@media only screen and (max-width: 600px){
		.message {
			max-width: 100%;
		}
	}
</style>