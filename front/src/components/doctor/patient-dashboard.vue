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
                                                <v-calendar :now="today" :value="today" color="primary">
                                                    <template v-slot:day="{ date }">
                                                        <template v-for="event in eventsMap[date]">
                                                            <v-menu :key="event.title" v-model="event.open" full-width offset-x>
                                                                <template v-slot:activator="{ on }">
                                                                    <div v-if="!event.time" v-ripple class="my-event" style="background-color:#424242" v-on="on" v-html="event.title"></div>
                                                                </template>
                                                                <v-card color="grey lighten-4" min-width="370px" flat>
                                                                    <v-toolbar color="green darken-4" dark>
                                                                        <v-btn icon>
                                                                            <v-icon>fa-pills</v-icon>
                                                                        </v-btn>
                                                                        <v-toolbar-title v-html="event.title"></v-toolbar-title>
                                                                        <v-spacer></v-spacer>
                                                                        <v-btn flat icon color="white" @click="event.open == !event.open">
                                                                            <v-icon small>fa-times</v-icon>
                                                                        </v-btn>
                                                                    </v-toolbar>
                                                                    <v-card-title primary-title>
                                                                        <span v-html="event.details"></span>
                                                                    </v-card-title>
                                                                </v-card>
                                                            </v-menu>
                                                        </template>
                                                    </template>
                                                </v-calendar>
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
                                        <template v-if="todaysMedicine.length > 0">
                                            <template v-for="(timeintake,index) in todaysMedicine">
                                                <v-list dense two-line subheader :key="index">
                                                    <v-subheader class="font-weight-bold">{{timeintake.time}}</v-subheader>
                                                </v-list>
                                                <template v-for="(medicine,i) in timeintake.medicine">
                                                    <v-list dense :key="`medicine-${index}-${i}`">
                                                        <v-list-tile>
                                                            <v-list-tile-action>
                                                                <template v-if="medicine.selected == false">
                                                                    <v-checkbox @change="takeMedicine(index,i,medicine.name)" v-model="medicine.selected"></v-checkbox>
                                                                </template>
                                                                <template v-else>
                                                                    <v-checkbox disabled v-model="medicine.selected"></v-checkbox>
                                                                </template>
                                                            </v-list-tile-action>
                                                            {{medicine.name}}
                                                            <v-spacer></v-spacer>
                                                            <v-menu open-on-hover right bottom>
                                                                <template v-slot:activator="{ on }">
                                                                    <v-btn fab small icon><v-icon v-on="on" color="green darken-4">fa-info-circle</v-icon></v-btn>
                                                                </template>
                                                                <v-card left width="200px">
                                                                    <v-card-text>
                                                                        <h4>Medicine Instructions</h4>
                                                                        {{ medicine.instructions }}
                                                                    </v-card-text>
                                                                </v-card>
                                                            </v-menu>
                                                        </v-list-tile>
                                                    </v-list>
                                                </template>
                                                <v-divider :key="`divider-${index}`"></v-divider>
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
                                                    <a v-else @click="viewImage(item.location)" class="black--text"><v-icon>{{item.file}}</v-icon> {{ item.name }}</a>
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
            <v-card>
                <v-card-text>
                    <v-img :src="url"></v-img>
                </v-card-text>
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
        this.patientid = VueCookies.get(this.cookieKey).data.id;
        this.fetchPatientFile();
        this.fetchMedicineSchedule(this.patientid);
    },
    data: () => ({
        viewResultImage : false,
        diagnosis : ["Sputum Result", "CXR Result", "TST Result", "Other Examination Result"],
        today: new Date().toISOString().substr(0, 10),
        open : [],
        dateeee : new Date().toISOString(),
        model : "",
        url : "",
        files: {
            image: 'fa-file-image',
        },
        diagnosticLogs : [],
        events: [],
        todaysMedicine : [],
        checkedMedicines : [],
    }),
    computed: {
        eventsMap () {
            const map = {}
            this.events.forEach(e => (map[e.date] = map[e.date] || []).push(e))
            return map
        }
    },
    methods: {
        viewImage : function(file){
            if(file != null){
                var http = new XMLHttpRequest();
                http.open('head', file, false);
                http.send();
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

        fetchMedicineSchedule : function(id){
            let _this = this;
            axios.create({
                baseURL : _this.apiUrl,
                headers : {
                    'Authorization' : `Bearer ${this.token}`
                },
            })
            .get('/medicine/patient/schedule/'+id)
            .then(function(res){
                _this.arrayEvents = res.data.dates;
                _this.medicineSchedule = res.data.data;
                _this.plotCalendarEvents();
                _this.getMedicineVal();
            });
        },

        plotCalendarEvents : function(){
            let evt = [];
            let _this = this;
            Object.keys(_this.medicineSchedule).forEach(function (key) {
                let prescribedMedicine = "<ul>",
                newdate = key;
                Object.keys(_this.medicineSchedule[newdate]).sort().forEach(function(key) {
                    prescribedMedicine += "<li class='font-weight-bold text-xs-left'>"+key+"</li>";
                    prescribedMedicine += "<ul>";
                    for(let i in _this.medicineSchedule[newdate][key]){
                        prescribedMedicine += "<li class='text-xs-left'>"+_this.medicineSchedule[newdate][key][i]+"</li>";
                    }
                    prescribedMedicine += "</ul>";
                });
                prescribedMedicine += "</ul>";
                evt = {
                    title: 'Medicine Time Intake',
                    details: prescribedMedicine,
                    date: key,
                    open: false
                };
                _this.events.push(evt);  
            });
        },
        todaysCheckList : function(){
            this.todaysMedicine = [];
            let _this = this,
            datelist = [],
            val = [],
            y = 0,
            instr = "";
            Object.keys(_this.medicineSchedule).forEach(function (key) {
                datelist.push(key);
            });

            if(datelist.includes(_this.today)){
                Object.keys(_this.medicineSchedule[_this.today]).sort().forEach(async function(key) {
                    let list = [];
                    for(let medicine in _this.medicineSchedule[_this.today][key]){ // tagged
                        let sel = false;
                        instr = await _this.getInstructions(_this.medicineSchedule[_this.today][key][medicine], _this.apiUrl, _this.token);
                        if(_this.checkedMedicines.length > 0){
                            if(_this.checkedMedicines[y] == "Y"){
                                sel = true;
                            }
                            list.push({ name : _this.medicineSchedule[_this.today][key][medicine], instructions: instr.data, selected : sel });
                        }else{
                            val.push("N"),
                            list.push({ name : _this.medicineSchedule[_this.today][key][medicine], instructions: instr.data, selected : sel });
                        }
                        y = y+1;
                    }
                    _this.todaysMedicine.push({
                        time : key,
                        medicine : list,
                    });
                }); 

                if(_this.checkedMedicines.length == 0){
                    _this.checkedMedicines = val;
                    _this.newMedicineVal('create');
                }

            }
        },
        getInstructions : async (medicine, url, token) =>{
            return axios.create({
                baseURL : url,
                headers : {
                    'Authorization' : `Bearer ${token}`
                }
            })
            .get('/medicine/instructions/'+medicine);
        },
        getMedicineVal : function(){
            let _this = this;
            axios.create({
                baseURL : this.apiUrl,
                headers : {
                    'Authorization' : `Bearer ${this.token}`
                }
            })
            .get('/medicine/value')
            .then(function(res){
                if(res.data.status){
                    _this.checkedMedicines = res.data.data.intake_value.split(",");
                }
                
                _this.todaysCheckList();
            });
        },
        takeMedicine : function(index, i,medicinename){
            this.checkedMedicines = [];
            for(let timetake in this.todaysMedicine){
                for(let med in this.todaysMedicine[timetake].medicine){
                    let val = "N";
                    if(this.todaysMedicine[timetake].medicine[med].selected == true){
                        val = "Y";
                    }
                    this.checkedMedicines.push(val);
                }
            }
            this.newMedicineVal('update', medicinename);
        },
        newMedicineVal : function(val, medicinename){
            let _this = this,
            formData = new FormData();
            formData.append('newVal',JSON.stringify(_this.checkedMedicines));
            formData.append('medicinename',medicinename);
            formData.append('type',val);
            axios.create({
                baseURL : this.apiUrl,
                headers : {
                    'Authorization' : `Bearer ${this.token}`
                }
            })
            .post('/medicine/newvalue',formData);
        }
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
.modalHeight {
    max-width: 50%;
}
@media only screen and (max-width: 600px){
    .modalHeight {
        max-width: 100%;
    }
}
</style>