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
							<v-flex md4>
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
              <v-flex offset-md4 md4>
                <template>
  <v-expansion-panel>
    <v-expansion-panel-content
      v-for="(item,i) in 5"
      :key="i"
    >
      <template v-slot:header>
        <div>Item</div>
      </template>
      <v-card>
        <v-card-text>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</v-card-text>
      </v-card>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>
              </v-flex>
						</v-layout>
					</v-card-text>
				</v-card>
			</v-flex>
		</v-layout>
	</div>
</template>
<script>
  export default {
    data: () => ({
      today: '2019-01-08',
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
      open (event) {
        alert(event.title)
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
</style>