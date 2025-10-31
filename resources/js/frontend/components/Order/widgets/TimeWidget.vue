<template>
    <div class="d-inline-block">

        <select :name="name"
            v-model="selected"
            class="form-control"
            id="time">
            <option v-for="time in times" :value="time.value">{{ time.name }}</option>
        </select>
    </div>
</template>

<script>

import moment from "moment";

export default {
    props: ['name', 'default', 'minimum', 'maximum'],
    emits: ['selected'],
    components: {

    },
    methods: {
        timeline: (desiredStartTime, interval, period, desiredEndTime) => {
            let desiredStartTimeM = moment(desiredStartTime, 'YYYY-MM-DD HH:mm');
            let desiredEndTimeM = moment(desiredEndTime, 'YYYY-MM-DD HH:mm');

            const duration = moment.duration(desiredEndTimeM.diff(desiredStartTimeM));
            const maxHours = duration.asHours();
            const periodsInADay = moment.duration(maxHours, 'hours').as(period);

            const times = [];
            const startTimeMoment = desiredStartTimeM;//moment(desiredStartTime, 'YYYY-MM-DD HH:mm:ss');

            for (let i = 0; i <= periodsInADay; i += interval) {
                startTimeMoment.add(i === 0 ? 0 : interval, period);
                times.push({
                    name: startTimeMoment.format('h:mm A'),
                    value: startTimeMoment.format('YYYY-MM-DD HH:mm:ss')
                });
            }
            return times;
        },
    },
    computed: {
        times() {
            return this.timeline(this.minimum, 15, 'm', this.maximum);
        },
        selected: {
            get() {
                return this.default;
            },
            set(value) {
                this.$emit('update', value)

            }
        }
    }
}
</script>
<style>
</style>
