<template>
    <div>
        <v-layout row wrap>
            <v-flex xs12 class="pa-3">
                <v-card>
                    <v-card-title primary-title>
                        <h1 class="green--text">Dashboard</h1>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-text>
                        <v-layout row wrap>
                            <v-flex md4 class="pa-1">
                                <v-layout>
                                    <v-flex>
                                        <v-card>
                                            <v-card-title class="green darken-4 white--text title">
                                                Medicine Schedule
                                            </v-card-title>
                                            <v-card-text>  
                                                <v-date-picker
                                                    full-width
                                                    no-title
                                                    v-model="date"
                                                    :events="arrayEvents"
                                                    color="green darken-4"
                                                    event-color="green darken-2"
                                                    @change="fetchMedicine"
                                                ></v-date-picker>
                                            </v-card-text>
                                        </v-card>
                                    </v-flex>
                                </v-layout>
                            </v-flex>
                            <v-flex md4 class="pa-1">
                                <v-card>
                                    <v-card-title class="green darken-4 white--text title">
                                        Today's Checklist
                                    </v-card-title>
                                </v-card>
                                <v-card max-height="373px" min-height="373px" style="overflow:auto">
                                    <v-card-text>
                                        <template v-if="prescribedMedicine.length > 0">
                                            <template v-for="(medicine,i) in prescribedMedicine">
                                                <v-list dense :key="`medicine-${i}`">
                                                    <v-list-tile>
                                                        <v-list-tile-action>
                                                            <v-icon v-if="medicine.status == 'Done'" color="green darken-3">fas fa-dot-circle</v-icon>
                                                            <v-icon v-else color="red darken-3">far fa-dot-circle</v-icon>
                                                        </v-list-tile-action>
                                                        {{medicine.brandname}} : {{medicine.genericname}}

                                                        <v-spacer></v-spacer>
                                                        <template v-if="medicine.status == 'Declined'">
                                                            <v-icon small color="red">fa-exclamation-circle</v-icon>
                                                        </template>
                                                    </v-list-tile>
                                                </v-list>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <v-icon color="primary">fa-info-circle</v-icon>
                                            <h3>No scheduled medicine today.</h3>
                                        </template>
                                    </v-card-text>
                                </v-card>
                            </v-flex>
                            <v-flex md4 class="pa-1">
                                <template>
                                    <v-card>
                                        <v-card-title class="green darken-4 white--text title">
                                            Diagnostic Results
                                        </v-card-title>
                                    </v-card>
                                    <v-card max-height="320px" min-height="373px" style="overflow:auto">
                                        <v-card-text>
                                            <v-treeview class="text-xs-left" :open="open" item-key="name" open-on-click :items="diagnosticLogs">
                                                <template slot-scope="{ item,open }" slot="label">
                                                    <a v-if="!item.file" class="black--text"><v-icon>{{ open ? 'fa-folder-open' : 'fa-folder' }}</v-icon> {{ item.name }}</a>
                                                    <a v-else @click="viewImage(item.location,item.remarks)" class="black--text"><v-icon>{{item.file}}</v-icon> {{ item.name }}</a>
                                                </template>
                                            </v-treeview>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-flex>
                        </v-layout>
                    </v-card-text>
                </v-card>
            </v-flex>
        </v-layout>
        <v-dialog v-model="viewResultImage" content-class="modalHeight">
            <v-card class="text-md-left">
                <v-layout>
                    <v-flex md8 xs12 style="overflow:auto">
                        <v-img :src="url"></v-img>
                    </v-flex>
                    <v-flex md4 class="grey pa-2">
                        <label><h3>Remarks:</h3></label>
                        <p>{{remarks}}</p>
                    </v-flex>
                </v-layout>
            </v-card>
        </v-dialog>
    </div>
</template>
<script>
import axios from 'axios';
import VueCookies from "vue-cookies";
export default {
    created : function(){
        this.token = VueCookies.get(this.cookieKey).token;
        this.patient = VueCookies.get(this.cookieKey).data;
        this.fetchPatientFile();
        this.fetchMedicineSchedule();
    },
    data: () => ({
        viewResultImage : false,
        diagnosis : ["Sputum Result", "CXR Result", "TST Result", "Other Examination Result"],
        date: new Date().toISOString().substr(0, 10),
        open : [],
        model : "",
        url : "",
        remarks : "",
        files: {
            image: 'fa-file-image',
        },
        diagnosticLogs : [],
        prescribedMedicine : [],
        arrayEvents : [],
        patient : [],
    }),

    methods: {
        viewImage : function(file,remarks){
            this.remarks = "";
            if(file != null){
                var http = new XMLHttpRequest();
                http.open('head', file, false);
                http.send();
                this.remarks = remarks;
                if(http.status){
                    this.url = file;
                    this.viewResultImage = true;
                }
            }
        },
        fetchPatientFile: function(){
            let _this = this;
            axios.create({
                baseURL : this.apiUrl,
                headers : {
                    'Authorization' : `Bearer ${this.token}`
                }
            })
            .get('/patients/diagnostic/file')
            .then(function(res){
                _this.diagnosticLogs = res.data;
            });
        },

        fetchMedicineSchedule : function(){
            let _this = this,
            category = this.patient.category,
            date = this.patient.datestart;
            axios.create({
                baseURL : _this.apiUrl,
                headers : {
                    'Authorization' : `Bearer ${this.token}`
                },
            })
            .get('/medicine/patient/schedule?date='+date+"&category="+category)
            .then(function(res){
                _this.arrayEvents = res.data;
                _this.fetchMedicine();
            });
        },
        fetchMedicine : function(){
            if(this.arrayEvents.indexOf(this.date) !== -1){
                let _this = this;
                axios.create({
                    baseURL : this.apiUrl,
                    headers : {
                        'Authorization' : `Bearer ${this.token}`
                    },
                })
                .get('/patient/app/medicine?id='+this.patient.id+'&date='+this.date)
                .then(function(res){
                    _this.prescribedMedicine = res.data.data;
                });
            }else{
                this.prescribedMedicine = [];
            }
        },
    }
};
</script>
<style lang="stylus" scoped>
.my-event {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    border-radius: 2px;
    background-color: #1867c0;
    color: #ffffff;
    border: 1px solid #1867c0;
    width: 100%;
    font-size: 12px;
    padding: 3px;
    cursor: pointer;
    margin-bottom: 1px;
}
.table {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
.table td{border: 1px solid #ddd;padding: 8px;}
.table tr:nth-child(even){background-color: #f2f2f2;}
.table tr:hover {background-color: #ddd;}
@media only screen and (max-width: 600px){
    .modalHeight {
        max-width: 100%;
    }
}
</style>