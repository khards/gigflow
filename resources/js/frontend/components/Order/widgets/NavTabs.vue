<!-- UNUSED -->

<template>
    <div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a v-on:click="setTab('event-details')" v-bind:class="{active : tab == 'event-details'}" class="nav-link" href="#">Event details</a>
            </li>
            <li class="nav-item">
                <a v-on:click="setTab('summary')" v-bind:class="{active : tab=='summary'}" class="nav-link" href="#">Booking summary</a>
            </li>
        </ul>
    </div>
</template>

<script>

export default {
    data: function() {
        return {
            tab: 'event-details',
        }
    },
    beforeCreate() {

    },
    watch: {
        tab: function() {
            this.$emit('update:tab', this.tab);
        },
    },
    created() {

    },
    mounted() {

    },
    methods: {
        setTab:function(tab) {
            return this.tab = tab;
        },
    }
}
</script>
