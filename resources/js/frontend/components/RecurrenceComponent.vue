<template>
    <div class="card">
        <div class="card-header"><strong>Recurrence</strong> <small>editor</small></div>
        <div class="card-body">

            <!-- timezone -->
            <div class="px-3">
                <div class="form-group row">
                    <div class="col-sm-2 text-sm-right">
                        <label for="startDate" class="col-form-label">
                            <strong>Timezone</strong>
                        </label>
                    </div>
                    <div class="col-6 col-sm-3">
                        <select
                            v-model="selectedTimezone"
                            name="timezone"
                            aria-label="Timezone"
                            class="form-control"
                            style="height: 30px;padding: 0px;margin: 0px;border: 1px solid darkgray;">
                            <option v-for="name in timezones" :value="name">{{ name }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <hr>

            <!-- startDate -->
            <div>
                <div class="px-3">
                    <div class="form-group row">
                        <div class="col-sm-2 text-sm-right">
                            <label for="startDate" class="col-form-label">
                                <strong>Start date and time</strong>
                            </label>
                        </div>
                        <div class="col-6 col-sm-2">
                            <Datepicker
                                format="yyyy-MM-dd"
                                name="startDate"
                                id="startDate"
                                :value="startDate"
                                v-model="startDate"></Datepicker>
                        </div>
                        <div class="col-sm-2">
                            <select
                                    name="startTime"
                                    v-model="startTime"
                                    aria-label="Start time"
                                    class="form-control"
                                    style="height: 30px;padding: 0px;margin: 0px;border: 1px solid darkgray;">
                                <option v-for="n in allTimes " :value="n.raw">{{ n.text }}</option>
                             </select>
                        </div>
                    </div>
                </div>
                <hr>
            </div>

            <!-- endDate -->
            <div>
                <div class="px-3">
                    <div class="form-group row">
                        <div class="col-sm-2 text-sm-right">
                            <label for="endDate" class="col-form-label">
                                <strong>End date and time</strong>
                            </label>
                        </div>
                        <div class="col-6 col-sm-2">
                            <Datepicker
                                format="yyyy-MM-dd"
                                name="endDate"
                                id="endDate"
                                :value="endDate"
                                v-model="endDate"></Datepicker>
                        </div>
                        <div class="col-sm-2">
                            <select
                                    name="endTime"
                                    aria-label="End time"
                                    v-model="endTime"
                                    class="form-control"
                                    style="height: 30px;padding: 0px;margin: 0px;border: 1px solid darkgray;">
                                <option v-for="n in allTimes " :value="n.raw">{{ n.text }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr />
                <div class="px-3">
                    <div class="form-group row">
                        <div class="col-sm-2 text-sm-right">
                            <strong>Duration</strong>
                        </div>
                        <div class="col-6 col-sm-6">
                            <h4>{{ duration }}</h4>
                        </div>
                    </div>
                </div>
                <hr>
            </div>

            <!-- repeatFrequency -->
            <div>
                <div class="px-3">
                    <div class="form-group row">
                        <div class="col-sm-2 text-sm-right">
                            <label for="repeatFrequency" class="col-form-label">
                                <strong>Repeat</strong>
                            </label>
                        </div>
                        <div class="col-sm-6">
                            <select v-model="repeatFrequency" id="repeatFrequency"  class="form-control" >
                                <optgroup label="Simple">
                                    <option value="never">Never</option>
                                    <option value="weekends">Weekends</option>
                                    <option value="weekdays">Weekdays</option>
                                </optgroup>
                                <optgroup label="Advanced">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <!-- repeatWeeklyDays -->
                    <div class="px-3">
                        <div v-if="showWeekdays" class="form-group row">
                            <div class="btn-group btn-group-toggle offset-sm-2" >
                                <label for="repeat-weekly-mon" class="btn btn-light" :class="repeatWeeklyActive('MO')">
                                <input v-model="repeatWeeklyDays" id="repeat-weekly-mon" type="checkbox" value="MO" class="form-control">Mon</label>

                                <label for="repeat-weekly-tue" class="btn btn-light" :class="repeatWeeklyActive('TU')">
                                <input v-model="repeatWeeklyDays" id="repeat-weekly-tue" type="checkbox" value="TU" class="form-control">Tue</label>

                                <label for="repeat-weekly-wed" class="btn btn-light" :class="repeatWeeklyActive('WE')">
                                <input v-model="repeatWeeklyDays" id="repeat-weekly-wed" type="checkbox" value="WE" class="form-control">Wed</label>

                                <label for="repeat-weekly-thu" class="btn btn-light" :class="repeatWeeklyActive('TH')">
                                <input v-model="repeatWeeklyDays" id="repeat-weekly-thu" type="checkbox" value="TH" class="form-control">Thu</label>

                                <label for="repeat-weekly-fri" class="btn btn-light" :class="repeatWeeklyActive('FR')">
                                <input v-model="repeatWeeklyDays" id="repeat-weekly-fri" type="checkbox" value="FR" class="form-control">Fri</label>

                                <label for="repeat-weekly-sat" class="btn btn-light" :class="repeatWeeklyActive('SA')">
                                <input v-model="repeatWeeklyDays" id="repeat-weekly-sat" type="checkbox" value="SA" class="form-control">Sat</label>

                                <label for="repeat-weekly-sun" class="btn btn-light" :class="repeatWeeklyActive('SU')">
                                <input v-model="repeatWeeklyDays" id="repeat-weekly-sun" type="checkbox" value="SU" class="form-control">Sun</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <!-- repeatInterval-->
                <div v-if="showEvery" class="form-group row d-flex align-items-sm-center">
                    <div class="col-sm-1 offset-sm-2">
<input v-model="repeatMode" id="rrule-1-repeat-off" type="radio" aria-label="Only" value="only">
                    </div>
                    <div class="col-sm-2">Repeat every</div>
                    <div class="col-sm-3">
                        <input v-model="repeatInterval" class="form-control" type="number" min="1" max="999" step="1">
                    </div>
                    <div class="col-sm-2">{{ everyTxt }}</div>
                </div>

                <!-- repeatMode on x (of x) -->
                <div v-if="repeatFrequency=='monthly' || repeatFrequency=='yearly'" class="form-group row d-flex align-items-sm-center false">
                    <div class="col-sm-1 offset-sm-2">
<input v-model="repeatMode" id="rrule-1-repeat-on" type="radio" aria-label="Repeat on" value="on">
                    </div>
                    <div class="col-sm-1">on day</div>

                    <!-- repeatOnMonth -->
                    <div  v-if="repeatFrequency=='yearly'" class="col-sm-2">
                        <select v-model="repeatOnMonth" aria-label="Repeat yearly on month" class="form-control" :disabled="repeatMode!='on'">
                            <option value="JAN">Jan</option>
                            <option value="FEB">Feb</option>
                            <option value="MAR">Mar</option>
                            <option value="APR">Apr</option>
                            <option value="MAY">May</option>
                            <option value="JUN">Jun</option>
                            <option value="JUL">Jul</option>
                            <option value="AUG">Aug</option>
                            <option value="SEP">Sep</option>
                            <option value="OCT">Oct</option>
                            <option value="NOV">Nov</option>
                            <option value="DEC">Dec</option>
                        </select>
                    </div>

                    <!-- repeatOnDay -->
                    <div class="col-sm-2">
                        <select v-model="repeatOnDay" aria-label="Repeat on a day" class="form-control" :disabled="repeatMode!='on'">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option>
                            <option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option>
                            <option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option>
                            <option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
                        </select>
                    </div>
                </div>

                <!-- Repeat on the  -->
                <div v-if="repeatFrequency=='monthly' || repeatFrequency=='yearly'" class="form-group row d-flex align-items-sm-center opacity-50">
                    <div class="col-sm-1 offset-sm-2">
<input v-model="repeatMode" id="rrule-1-repeat-monthly-onThe" type="radio" aria-label="Repeat on the" value="on the">
                    </div>
                    <div class="col-sm-1">on the</div>
                    <div class="col-sm-2">
                        <select v-model="repeatBySetPos" aria-label="Repeat on the which" class="form-control" :disabled="repeatMode!='on the'">
                            <option value="1">First</option>
                            <option value="2">Second</option>
                            <option value="3">Third</option>
                            <option value="4">Fourth</option>
                            <option value="-1">Last</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <select v-model="repeatOnTheDay" aria-label="Repeat on the day" class="form-control" :disabled="repeatMode!='on the'">
                            <option value="MO">Monday</option>
                            <option value="TU">Tuesday</option>
                            <option value="WE">Wednesday</option>
                            <option value="TH">Thursday</option>
                            <option value="FR">Friday</option>
                            <option value="SA">Saturday</option>
                            <option value="SU">Sunday</option>

                            <option value="day">Day</option>
                            <option value="weekday">Weekday</option>
                            <option value="weekend">Weekend day</option>
                        </select>
                    </div>

                    <div v-if="repeatFrequency=='yearly'" class="col-sm-1">of</div>

                    <div v-if="repeatFrequency=='yearly'" class="col-sm-2">
                        <select v-model="repeatOnMonth" aria-label="Repeat yearly on the month" class="form-control" :disabled="repeatMode!='on the'">
                            <option value="JAN">Jan</option>
                            <option value="FEB">Feb</option>
                            <option value="MAR">Mar</option>
                            <option value="APR">Apr</option>
                            <option value="MAY">May</option>
                            <option value="JUN">Jun</option>
                            <option value="JUL">Jul</option>
                            <option value="AUG">Aug</option>
                            <option value="SEP">Sep</option>
                            <option value="OCT">Oct</option>
                            <option value="NOV">Nov</option>
                            <option value="DEC">Dec</option>
                        </select>
                    </div>

                </div>
            </div>

            <!-- End recurrence ? -->
            <div v-if="repeatFrequency!='never'">
                <hr>
                <div class="px-3">
                    <div class="form-group row">
                        <div class="col-sm-2 text-sm-right">
                            <label for="rrule-1-end" class="col-form-label"><strong>End</strong></label>
                        </div>

                        <!-- Stop recurrence option -->
                        <div class="col-sm-3">
                            <select v-model="endMode" class="form-control">
                            <option value="never">Never</option>
                            <option value="after">After</option>
                            <option value="ondate">On date</option>
                            </select>
                        </div>

                        <!-- Stop recurrence after -->
                        <div v-if="endMode=='after'" class="col-sm-4">
                            <div class="form-group m-0 row d-flex align-items-center">
                                <div class="col-3 col-sm-6 pl-0">
                                    <input v-model="endAfterNumberOfExecutions" aria-label="End after" class="form-control" type="number" min="1" max="999" step="1">
                                </div>
                                <div class="col-9 col-sm-6">executions.</div>
                            </div>
                        </div>

                        <!-- Stop recurrence On date -->
                        <div v-if="endMode=='ondate'" class="col-6 col-sm-3">
                            <Datepicker :value="recurUntilDate" v-model="recurUntilDate"></Datepicker>
                        </div>

                    </div>
                </div>
            </div>
            <textarea class="d-none" type="text" style="width:100%" name="rrule" v-model="rule" />
        </div> <!-- End card -->
    </div>
</template>

<script>
    /* https://fafruch.github.io/react-rrule-generator/ */

    import Datepicker from 'vuejs-datepicker';
    import axios from 'axios'
    import { RRule, RRuleSet, rrulestr } from 'rrule'
    import moment from 'moment';
    import tz from 'moment-timezone';

    export default {
         props: ['rrule', 'dtstart', 'dtend', 'timezone'],

        created: function () {
            function pad(n) {
                return (n < 10) ? ("0" + n) : n;
            }

            this.generateTimes();
            this.generateTimezones();

            let rrule = this.$options.propsData.rrule;
            let dtstart = this.$options.propsData.dtstart;
            let dtend = this.$options.propsData.dtend;
            let options = RRule.parseString(rrule);

            // Timezone
            let timezone = this.$options.propsData.timezone;
            if(timezone == '') {
                timezone = moment.tz.guess(true);
            }
            this.selectedTimezone = timezone;

            // Start and end date picker.
            this.startDate = new Date(dtstart);
            this.endDate =  new Date(dtend);

            // Start & endtime (local to timezone)
            let mDtStart = new moment(dtstart).tz(timezone);
            let mDtEnd = new moment(dtend).tz(timezone);
            this.startTime = pad(mDtStart.hour()) + ':' + pad(mDtStart.minute()) + ':00';
            this.endTime = pad(mDtEnd.hour()) + ':' + pad(mDtEnd.minute()) + ':00';

            //-----------------------------------------------
            // Repeat frequency dropdown
            //-----------------------------------------------
            this.repeatFrequency = 'never';
            switch(options.freq) {
                case 0: //yearly
                    this.repeatFrequency = 'yearly';
                    break;
                case 1: // monthly
                    this.repeatFrequency = 'monthly';
                    break;
                case 2: //weekly, weekends, weekdays
                    this.repeatFrequency = 'weekly';
                    break;
                case 3: // daily
                    this.repeatFrequency = 'daily';
                    break;
            }

            //-----------------------------------------------
            // Process weekly repeat days.
            //-----------------------------------------------
            let repeatWeeklyDays = [];
            let weekdayCount = 0;
            let weekendCount = 0;
            if(options.byweekday) {
                options.byweekday.forEach(function (a) {
                    switch (a.weekday) {
                        case 0:
                            repeatWeeklyDays.push('MO');
                            weekdayCount++;
                            break;
                        case 1:
                            repeatWeeklyDays.push('TU');
                            weekdayCount++;
                            break;
                        case 2:
                            repeatWeeklyDays.push('WE');
                            weekdayCount++;
                            break;
                        case 3:
                            repeatWeeklyDays.push('TH');
                            weekdayCount++;
                            break;
                        case 4:
                            repeatWeeklyDays.push('FR');
                            weekdayCount++;
                            break;
                        case 5:
                            repeatWeeklyDays.push('SA');
                            weekendCount++;
                            break;
                        case 6:
                            repeatWeeklyDays.push('SU');
                            weekendCount++;
                            break;
                    }
                });
            }
            this.repeatWeeklyDays = repeatWeeklyDays;

            //-----------------------------------------------
            // Check weekly repeat days to populate dropdown
            //-----------------------------------------------
            if (this.repeatFrequency == 'weekly' ) {
                if (repeatWeeklyDays.length) {
                    if (weekendCount && !weekdayCount) {
                        this.repeatFrequency = 'weekends';
                    }
                    if (!weekendCount && weekdayCount) {
                        this.repeatFrequency = 'weekdays';
                    }
                }
            }
            //-----------------------------------------------

            this.repeatInterval = options.interval;

            //-----------------------------------------------
            // End mode
            //-----------------------------------------------
            this.endMode = 'never';
            if(options.count) {
                this.endMode = 'after';
                this.endAfterNumberOfExecutions = options.count;
            } else if(options.until) {
                this.endMode = 'ondate';
                this.recurUntilDate = options.until;
            }

            //-------------------------------------------------
            // repeat By month day (radio + select)
            //-------------------------------------------------
            if(options.bymonthday) {
                this.repeatOnDay = options.bymonthday;
                this.repeatMode = 'on';
            } else if(options.bysetpos) {
                this.repeatBySetPos = options.bysetpos;
                this.repeatMode = 'on the';

                if(this.repeatFrequency === 'monthly' || this.repeatFrequency === 'yearly') {
//
                    let repeatOnTheDay = null;
                    let weekdayCount = 0;
                    let weekendCount = 0;

                    options.byweekday.forEach(function (a) {
                        switch (a.weekday) {
                            case 0:
                                repeatOnTheDay = 'MO';
                                weekdayCount++;
                                break;
                            case 1:
                                repeatOnTheDay = 'TU';
                                weekdayCount++;
                                break;
                            case 2:
                                repeatOnTheDay =  'WE';
                                weekdayCount++;
                                break;
                            case 3:
                                repeatOnTheDay = 'TH';
                                weekdayCount++;
                                break;
                            case 4:
                                repeatOnTheDay = 'FR';
                                weekdayCount++;
                                break;
                            case 5:
                                repeatOnTheDay = 'SA';
                                weekendCount++;
                                break;
                            case 6:
                                repeatOnTheDay = 'SU';
                                weekendCount++;
                                break;
                        }
                    });

                    this.repeatOnTheDay = repeatOnTheDay;

                    // - weekend, day, weekday select options.
                    if(weekendCount == 2 && weekdayCount == 5) {
                        this.repeatOnTheDay = 'day';
                    } else if(weekendCount == 2 && weekdayCount == 0) {
                        this.repeatOnTheDay = 'weekend';
                    } else if(weekdayCount == 5) {
                        this.repeatOnTheDay = 'weekday';
                    }
                } // end if monthly or yearly
            } // end by set pos

            if(options.bymonth) {
                this.repeatOnMonth = options.bymonth;
            }
        },

         data() {
            return {
                // Dates
                startDate: new Date(),
                endDate: new Date(),
                recurUntilDate: new Date(),
                selectedTimezone: '',
                startTime: '00:45',
                endTime: '04:15',
                repeatFrequency: 'weekly',
                endMode: 'never',
                repeatMode: 'only',//on
                repeatWeeklyDays: ['SA', 'SU'],
                repeatInterval: 1,
                repeatOnMonth: 'JAN',
                repeatOnDay: 1,
                repeatBySetPos: '-1',
                repeatOnTheDay: 'FR',
                endAfterNumberOfExecutions: 1,

                // Generated.
                allTimes: [],
                timezones: [],
            }
        },
        components: {
            Datepicker
        },

        methods: {
            generateTimezones: function() {
                this.timezones = moment.tz.names();//timezones;
            },
            generateTimes: function() {
                var x = 15; //minutes interval
                var tt = 0; // start time
                var ap = ['AM', 'PM']; // AM-PM

                //loop to increment the time and push results in array
                for (var i=0;tt<24*60; i++) {
                    // getting hours of day in 0-24 format
                    var hh = Math.floor(tt/60);

                    // getting minutes of the hour in 0-55 format
                    var mm = (tt % 60);

                    let hour = ("0" + (hh % 12)).slice(-2);
                    let rawhour = ("0" + hh).slice(-2);
                    let min = ("0" + mm).slice(-2);
                    let raw = rawhour + ':' + min + ':00';//h:i:s
                    let timeTxt = hour + ':' + min + ' ' + ap[Math.floor(hh/12)]; // pushing data in array in [00:00 - 12:00 AM/PM format]
                    this.allTimes[i] = {text: timeTxt, raw: raw};
                    tt = tt + x;
                }
            },
            save: function(arg) {
                let button = document.getElementById('calendar-sync-button-icon');
                axios.get('/recurrence/save').then(function () {
                    //calendar.refetchEvents();
                    if (button) {
                        button.classList.remove('fa-spin');
                    }
                })
            },
            repeatWeeklyActive: function (day) {
                if(this.repeatWeeklyDays.includes(day)) {
                    return 'active';
                }

                return '';
            }
        },
        computed: {

             duration: function() {

                 let startDate = new Date(this.startDate.getTime() - (this.startDate.getTimezoneOffset() * 60000 )).toISOString().split("T")[0];
                 let endDate = new Date(this.endDate.getTime() - (this.endDate.getTimezoneOffset() * 60000 )).toISOString().split("T")[0];

                 let start = moment(startDate + ' ' + this.startTime, 'YYYY-MM-DD HH:mm:ss').tz(this.selectedTimezone);
                 let end = moment(endDate + ' ' + this.endTime, 'YYYY-MM-DD HH:mm:ss').tz(this.selectedTimezone);

                 let duration = moment.duration(end.diff(start));

                 return duration.months() + ' month(s) ' + duration.days() + ' day(s), ' + duration.hours() + ' hour(s), ' + duration.minutes() + ' minute(s)';
             },

            showEvery: function () {
                if(this.repeatFrequency == 'weekly' || this.repeatFrequency =='daily' || this.repeatFrequency =='yearly' || this.repeatFrequency =='monthly') {
                    return true;
                }
                return false;
            },
            showWeekdays: function () {
                if(this.repeatFrequency == 'weekly') {
                    return true;
                }
                return false;
            },
            everyTxt: function () {
                switch (this.repeatFrequency) {
                    case "yearly":
                        return 'year(s)';
                    case "monthly":
                        return 'month(s)';
                    case "weekly":
                        return 'week(s)';
                    case "daily":
                        return 'day(s)';
                    default:
                        return '';

                }
            },
            rule: function () {
                if(this.repeatFrequency === 'never') {
                    return '';
                }

                let interval = this.repeatInterval;
                let count = this.endMode == 'after' ? this.endAfterNumberOfExecutions : null;
                let weekdays = [];
                let freq = '';
                let bymonth = (this.repeatFrequency === 'yearly' && this.repeatMode !== 'only') ? this.repeatOnMonth : null;
                let bymonthday = (this.repeatMode == 'on' && (this.repeatFrequency === 'monthly' || this.repeatFrequency === 'yearly')) ? this.repeatOnDay : null;
                let bysetpos = [];
                if((this.repeatMode == 'on the' && (this.repeatFrequency === 'monthly' || this.repeatFrequency === 'yearly'))) {
                    bysetpos.push(this.repeatBySetPos);
                } else {
                    bysetpos = null;
                }


                switch (this.repeatFrequency) {
                    case 'daily':
                        freq = RRule.DAILY;
                        break;
                    case 'weekly':
                        freq = RRule.WEEKLY;
                        break;
                    case 'weekends':
                        weekdays.push(RRule.SA);
                        weekdays.push(RRule.SU);
                        freq = RRule.WEEKLY;
                        interval = 1;
                        break;
                    case 'weekdays':
                        weekdays.push(RRule.MO);
                        weekdays.push(RRule.TU);
                        weekdays.push(RRule.WE);
                        weekdays.push(RRule.TH);
                        weekdays.push(RRule.FR);
                        freq = RRule.WEEKLY;
                        interval = 1;
                        break;
                    case 'monthly':
                        freq = RRule.MONTHLY;
                        break;
                    case 'yearly':
                        freq = RRule.YEARLY;
                        break;
                }

                if(this.repeatMode == 'on the' && (this.repeatFrequency === 'monthly' || this.repeatFrequency === 'yearly')) {
                    if(this.repeatOnTheDay == 'MO') {
                        weekdays.push(RRule.MO);
                    } else if(this.repeatOnTheDay == 'TU') {
                        weekdays.push(RRule.TU);
                    } else if(this.repeatOnTheDay == 'WE') {
                        weekdays.push(RRule.WE);
                    } else if(this.repeatOnTheDay == 'TH') {
                        weekdays.push(RRule.TH);
                    } else if(this.repeatOnTheDay == 'FR') {
                        weekdays.push(RRule.FR);
                    } else if(this.repeatOnTheDay == 'SA') {
                        weekdays.push(RRule.SA);
                    } else if(this.repeatOnTheDay == 'SU') {
                        weekdays.push(RRule.SU);
                    } else if(this.repeatOnTheDay == 'day') {
                        weekdays.push(RRule.MO);
                        weekdays.push(RRule.TU);
                        weekdays.push(RRule.WE);
                        weekdays.push(RRule.TH);
                        weekdays.push(RRule.FR);
                        weekdays.push(RRule.SA);
                        weekdays.push(RRule.SU);
                    } else if(this.repeatOnTheDay == 'weekday') {
                        weekdays.push(RRule.MO);
                        weekdays.push(RRule.TU);
                        weekdays.push(RRule.WE);
                        weekdays.push(RRule.TH);
                        weekdays.push(RRule.FR);
                    } else if(this.repeatOnTheDay == 'weekend') {
                        weekdays.push(RRule.SA);
                        weekdays.push(RRule.SU);
                    }
                }

                if(this.repeatFrequency == 'weekly') {
                    this.repeatWeeklyDays.forEach(function (key) {
                        switch (key) {
                            case 'MO':
                                weekdays.push(RRule.MO);
                                break;
                            case 'TU':
                                weekdays.push(RRule.TU);
                                break;
                            case 'WE':
                                weekdays.push(RRule.WE);
                                break;
                            case 'TH':
                                weekdays.push(RRule.TH);
                                break;
                            case 'FR':
                                weekdays.push(RRule.FR);
                                break;
                            case 'SA':
                                weekdays.push(RRule.SA);
                                break;
                            case 'SU':
                                weekdays.push(RRule.SU);
                                break;
                        }
                    });
                }

                /* https://github.com/jakubroztocil/rrule */

                let rule = new RRule({
                    dtstart: this.startDate,
                    until: this.endMode === 'ondate' ? this.recurUntilDate : null,
                    freq: freq,
                    byweekday: weekdays,
                    interval: interval,
                    count: count,
                    bymonth: bymonth,
                    bymonthday: bymonthday,
                    bysetpos: bysetpos,
                });

                return rule.toString();

            }
        }
    }
</script>
