<template>
	<v-dialog v-model="connectToServerDialog" full-width persistent>
		<v-card>
			<v-card-text class="text-xs-center" v-if="errorMessage.length == 0">
				<v-progress-circular
					:size="70"
					:width="7"
					color="purple"
					indeterminate
				></v-progress-circular>

				<p class="subheading ma-2">checking connectivity...</p>
			</v-card-text>

			<v-card-text class="text-xs-center subheading" v-if="errorMessage.length > 0">
				{{errorMessage}}
			</v-card-text>
		</v-card>
	</v-dialog>
</template>

<script>

import axios from 'axios';

export default {

	props : {
		intervalCheck : Boolean
	},

	created : function(){
		let _this = this;
		this.getSystemDate()
		.then((res) => {
			_this.connectToServerDialog = false;
		})
		.catch((err) => {
			console.log(err);
			_this.errorMessage = err.message;
		});
	},

	mounted : function(){
		if(this.intervalCheck){
			this.intnervalFnx();
		}
	},

	data : () => ({

		connectToServerDialog : true,
		errorMessage : '',
		interval : {}

	}),

	methods : {

		getSystemDate : function(){
			return axios.get(this.apiUrl + '/get/system/date');
		},

		intnervalFnx : function(){
			let _this = this;
			_this.interval = setInterval(() => {
				_this.getSystemDate()
				.catch((err) => {
					clearInterval(_this.interval);
					_this.connectToServerDialog = true;
					_this.errorMessage = err.message;
				});
			}, 120000);

		}

	}

};

</script>