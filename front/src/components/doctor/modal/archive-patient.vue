<template>
	<v-dialog v-model="archive" content-class="modalHeight">
		<v-card>
			
		</v-card>
	</v-dialog>
</template>
<script>
	import axios from "axios";
	export default {
		created: function(){
			this.eventHub.$on('archivePatient', val => {
				this.patientID = val.patientID;
				this.archive = true;
			});
		},
		data : function(){
			return {
				patientID : '',
				archive : false,
			}
		}, 
		methods : {
			
		}
	};
</script>
<style>
	.modalHeight {
		max-width: 50%;
	}
	@media only screen and (max-width: 600px){
		.modalHeight {
			max-width: 100%;
		}
	}
</style>