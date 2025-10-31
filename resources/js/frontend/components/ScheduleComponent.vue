<template>
    <div>
        <FullCalendar ref="fullCalendar" :options="calendarOptions" />
    </div>
</template>

<script>
    import FullCalendar from '@fullcalendar/vue'
    import dayGridPlugin from '@fullcalendar/daygrid'
    import interactionPlugin from '@fullcalendar/interaction'
    import rrulePlugin from '@fullcalendar/rrule'
    import listPlugin from '@fullcalendar/list';
    import axios from 'axios'

    export default {
        components: {
            FullCalendar // make the <FullCalendar> tag available
        },
        data() {
            return {
                calendarOptions: {
                    headerToolbar: { center: 'dayGridMonth,listMonth' }, // ,dayGridWeek,listWeek
                    views: {
                        dayGridMonth: { // name of view
                            titleFormat: { year: 'numeric', month: '2-digit', day: '2-digit' }
                            // other view-specific options here
                        }
                    },
                    plugins: [rrulePlugin, dayGridPlugin, interactionPlugin, listPlugin],
                    initialView: 'dayGridMonth',
                    eventSources: [
                        {
                            url: '/calendar/mycalendar', // use the `url` property
                            color: 'lavender',    // an option!
                            textColor: 'black'  // an option!
                        }
                    ]
                }
            }
        },
        methods: {
            sync: function(arg) {
                let button = document.getElementById('calendar-sync-button-icon');
                if (button) {
                    button.classList.add('fa-spin');
                }

                let calendar = this.$refs.fullCalendar.getApi();
                axios.get('/calendar/sync').then(function () {
                    calendar.refetchEvents();
                    if (button) {
                        button.classList.remove('fa-spin');
                    }
                })

            }
        }
    }
</script>
