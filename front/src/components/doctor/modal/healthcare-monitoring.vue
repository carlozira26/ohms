<template>
	<v-dialog v-model="healthtracker" width="80%">
		<v-card>
			<v-card-title primary-title class="green darken-4">
				<h1 class="white--text">Healthcare Monitoring</h1>
			</v-card-title>
			<v-card-text>
				
			</v-card-text>
		</v-card>
	</v-dialog>
</template>
<script>
	export default {
		props : ['mtitle'],
		created : function(){
			this.eventHub.$on('showHealthTracker', val =>{
				this.healthtracker = true;
			});
		},
		data : function(){
			return { 
				healthtracker : false
			}
		}
	};
</script>