<template>
	<div>
		<template v-if="role != 'none'">
			<v-navigation-drawer v-model="appnav" app dark floating width="260">
				<v-list>
					<v-list-tile>
						<v-list-tile-title class="title" style="text-align:center;height:50px;padding-top:20px">
							<h2 class="white--text">OHMS</h2>
						</v-list-tile-title>
					</v-list-tile>
					<br>
					<v-list-tile avatar active-class="highlighted green darken-2" class="v-list-item" to="/dashboard">
						<v-list-tile-action>
							<v-icon>dashboard</v-icon>
						</v-list-tile-action>
						<v-list-tile-title>Dashboard</v-list-tile-title>
					</v-list-tile>
					<v-list-tile avatar active-class="highlighted green darken-2" class="v-list-item" to="/patient-list">
						<v-list-tile-action>
							<v-icon>person</v-icon>
						</v-list-tile-action>
						<v-list-tile-title>My Patients</v-list-tile-title>
					</v-list-tile>
					<v-list-tile avatar active-class="highlighted green darken-2" class="v-list-item" to="/doctors-list" v-if="role == 1">
						<v-list-tile-action>
							<v-icon>fa-user-md</v-icon>
						</v-list-tile-action>
						<v-list-tile-title>Doctor's List</v-list-tile-title>
					</v-list-tile>
					<v-list-tile avatar active-class="highlighted green darken-2" class="v-list-item" to="/account">
						<v-list-tile-action>
							<v-icon>account_circle</v-icon>
						</v-list-tile-action>
						<v-list-tile-title>Account Settings</v-list-tile-title>
					</v-list-tile>
					<v-list-tile avatar active-class="highlighted green darken-2" class="v-list-item" to="/logout">
						<v-list-tile-action>
							<v-icon>backspace</v-icon>
						</v-list-tile-action>
						<v-list-tile-title :to="'/logout'">Log Out</v-list-tile-title>
					</v-list-tile>
				</v-list>
			</v-navigation-drawer>
			<v-toolbar color="green darken-4" dark app :clipped-left="$vuetify.breakpoint.mdAndUp" fixed>
				<v-toolbar-side-icon @click.stop="appnav = !appnav"></v-toolbar-side-icon>
				<v-toolbar-title class="ml-0 pl-3">
					<h2>OHMS</h2>
				</v-toolbar-title>
			</v-toolbar>
		</template>
		<template v-else>
			<v-toolbar color="green darken-4" dark app :clipped-left="$vuetify.breakpoint.mdAndUp" fixed>
				<v-toolbar-title class="ml-0 pl-3">
					<h2>OHMS</h2>
				</v-toolbar-title>
				<v-spacer></v-spacer>
				<v-btn flat icon @click="openMessage">
					<v-badge left color="pink" title="Messages">
						<template v-slot:badge>
							<span>6</span>
						</template>
						<v-icon>fa-envelope</v-icon>
					</v-badge>
				</v-btn>
				<v-btn flat icon title="Medicine History" @click="openLogs">
					<v-icon>fa-clipboard-list</v-icon>
				</v-btn>
				<v-menu :nudge-width="200" transition="slide-y-transition" bottom left z-index="99">
                    <v-btn slot="activator" icon>
                        <v-icon>fa-angle-down</v-icon>
                    </v-btn>
                    <v-card>
                        <v-list>
                            <v-list-tile avatar>
                                <v-list-tile-avatar>
                                    <v-icon large>person</v-icon>
                                </v-list-tile-avatar>

                                <v-list-tile-content>
                                    <v-list-tile-title>{{user.fullname}}</v-list-tile-title>
                                    <v-list-tile-sub-title :class="color">{{status}}</v-list-tile-sub-title>
                                </v-list-tile-content>
                            </v-list-tile>
                        </v-list>
                        <v-divider></v-divider>
                        <v-list>
                            <v-list-tile @click="openProfile">
                                <v-list-tile>
                                    <v-icon>fa-user-circle</v-icon>
                                </v-list-tile>
                                <v-list-tile-title>
                                    <span>My Profile</span>
                                </v-list-tile-title>
                            </v-list-tile>
                            <v-list-tile>
                                <v-list-tile>
                                    <v-icon>fa-user-md</v-icon>
                                </v-list-tile>
                                <v-list-tile-title>
                                    <span>Doctor's Profile</span>
                                </v-list-tile-title>
                            </v-list-tile>
                            <v-list-tile to="/logout">
                                <v-list-tile>
                                    <v-icon>fa-power-off</v-icon>
                                </v-list-tile>
                                <v-list-tile-title>
                                    <span>Logout</span>
                                </v-list-tile-title>
                            </v-list-tile>
                        </v-list>
                    </v-card>
                </v-menu>
			</v-toolbar>
		</template>
		<v-dialog v-model="globalLoading" persistent width="300">
			<v-card color="green darken-4" dark>
				<v-card-text>
					Loading...
					<v-progress-linear
					indeterminate
					color="white"
					class="mb-0"
					hide-overlay
					></v-progress-linear>
				</v-card-text>
			</v-card>
		</v-dialog>
		<v-content>
			<router-view></router-view>
		</v-content>
		<view-profile></view-profile>
		<view-message></view-message>
		<view-logs></view-logs>
	</div>
</template>
<script>
import VueCookies from 'vue-cookies';
import openMessage from './modal/chatbox.vue';
import openLogs from './modal/intakelogs.vue';
// import axios from 'axios';
import viewProfile from './modal/view-profile.vue';
import viewMessage from './modal/messenger.vue';

export default {
	created : function(){
		this.role = VueCookies.get(this.cookieKey).data.role;
		this.user = VueCookies.get(this.cookieKey).data;
		if(this.role == "none"){
			this.checkTreatment(this.user.id)
			this.wsconnect = new WebSocket(this.websocket);
		}
		let _this = this;
	},
	components : {
		'view-profile' : viewProfile,
		'view-message' : viewMessage,
		'view-logs'    : openLogs
	},
	data : () => ({
		connect : '',
		appnav : false,
		globalLoading : false,
		treatment : [
			{ 
				name : "New",
				type : "New Patient",
				color: "primary--text"
			},
			{ 
				name : "Ongoing",
				type : "Ongoing Treatment",
				color: "orange--text"
			},
			{ 
				name : "Success",
				type : "Treatment Success",
				color: "success--text"
			},
			{ 
				name : "Discontinuation",
				type : "Treatment Discontinued",
				color: "red--text"
			},
		],
		status: "New Patient",
		color : "primary",
	}),


	methods : {
		checkTreatment: function(patientid){
			for(let stat in this.treatment){
				if(this.user.status == this.treatment[stat].name){
					this.status = this.treatment[stat].type;
					this.color = this.treatment[stat].color;
				}
			}
		},
		openProfile : function(){
			this.eventHub.$emit('viewProfile', true);
		},
		openMessage : function(){
			this.eventHub.$emit('viewMessage', { 'wsconnect' : this.wsconnect});
		},
		openLogs : function(){
			this.eventHub.$emit('viewLogs', true);
		},
	}
};	
</script>