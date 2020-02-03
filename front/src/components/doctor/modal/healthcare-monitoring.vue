<template>
	<v-dialog v-model="healthtracker" width="80%">
		<v-card>
			<v-card-title primary-title>
				<h1 class="green--text">Healthcare Monitoring</h1>
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