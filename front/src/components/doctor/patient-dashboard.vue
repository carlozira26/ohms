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
											<v-card-text>  
												<v-calendar :now="today" :value="today" color="primary">
													<template v-slot:day="{ date }">
														<template v-for="event in eventsMap[date]">
															<v-menu :key="event.title" v-model="event.open" full-width offset-x>
																<template v-slot:activator="{ on }">
																	<div v-if="!event.time" v-ripple class="my-event" v-on="on" v-html="event.title"></div>
																</template>
																<v-card color="grey lighten-4" min-width="350px" flat>
																	<v-toolbar color="primary" dark>
																		<v-btn icon>
																			<v-icon>edit</v-icon>
																		</v-btn>
																		<v-toolbar-title v-html="event.title"></v-toolbar-title>
																		<v-spacer></v-spacer>
																		<v-btn icon>
																			<v-icon>favorite</v-icon>
																		</v-btn>
																		<v-btn icon>
																			<v-icon>more_vert</v-icon>
																		</v-btn>
																	</v-toolbar>
																	<v-card-title primary-title>
																		<span v-html="event.details"></span>
																	</v-card-title>
																	<v-card-actions>
																		<v-btn flat color="secondary">
																			Cancel
																		</v-btn>
																	</v-card-actions>
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
                <v-card min-height="320px">
                  <v-card-text>
                    
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
                  <v-card max-height="320px" min-height="320px" style="overflow:auto">
                    <v-card-text>
                      <v-treeview class="text-xs-left" :open="open" activatable item-key="name" open-on-click :items="diagnosticLogs">
                        <template slot-scope="{ item }" slot="label">
                          <a v-if="!item.file" class="black--text"><v-icon>{{ open ? 'fa-folder-open' : 'fa-folder' }}</v-icon> {{ item.name }}</a>
                          <a v-else @click="viewImage" class="black--text"><v-icon>{{item.file}}</v-icon> {{ item.name }}</a>
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
          <!-- <v-img :src="url"></v-img> -->
        </v-card-text>
      </v-card>
    </v-dialog>
	</div>
</template>
<script>
  import axios from 'axios';
  export default {
    data: () => ({
      viewResultImage : false,
      diagnosis : ["Sputum Result", "CXR Result", "TST Result", "Other Examination Result"],
      today: '2019-01-08',
      open : [],
      files: {
        image: 'fa-file-image',
      },
      diagnosticLogs : [
        {
          name: 'Sputum Result',
          children: [
            {
              name: 'test',
              file: 'image',
            },
          ],
        },
        {
          name: 'CXR Result',
          children: [
            {
              name:'test',
              file: 'image'
            }
          ]
        },
        {
          name: 'TST Result',
          children: [
            {
              name:'test',
              file: 'image'
            }
          ]
        },
        {
          name: 'Other Examination Result',
          children : [
            {
              name: 'Test1',
              file: 'image'
            },{
              name: 'Test',
              file: 'image'
            },
          ]
        }
      ],
      events: [
        {
          title: 'Vacation',
          details: 'Going to the beach!',
          date: '2018-12-30',
          open: false
        },
        {
          title: 'Vacation',
          details: 'Going to the beach!',
          date: '2018-12-31',
          open: false
        },
        {
          title: 'Vacation',
          details: 'Going to the beach!',
          date: '2019-01-01',
          open: false
        },
        {
          title: 'Meeting',
          details: 'Spending time on how we do not have enough time',
          date: '2019-01-07',
          open: false
        },
        {
          title: '30th Birthday',
          details: 'Celebrate responsibly',
          date: '2019-01-03',
          open: false
        },
        {
          title: 'New Year',
          details: 'Eat chocolate until you pass out',
          date: '2019-01-01',
          open: false
        },
        {
          title: 'Conference',
          details: 'Mute myself the whole time and wonder why I am on this call',
          date: '2019-01-21',
          open: false
        },
        {
          title: 'Hackathon',
          details: 'Code like there is no tommorrow',
          date: '2019-02-01',
          open: false
        }
      ]
    }),
    computed: {
      // convert the list of events into a map of lists keyed by date
      eventsMap () {
        const map = {}
        this.events.forEach(e => (map[e.date] = map[e.date] || []).push(e))
        return map
      }
    },
    methods: {
      viewImage : function(){
        this.viewResultImage = true;
      },
      fetchPatientFile: function(){
        axios.create({
          baseURL : this.apiUrl,
          headers : {
            'Authorization' : `Bearer ${this.token}`
          }
        })
        .get('/patients/diagnostic/file')
        .then(function(res){
          
        });
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