<template>
    <div id="summary-page">
        <div class="row">
            <div class="col-md-12">
                <h2>Order summary</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Reference</h3>
            </div>
            <div class="col-sm-9">
                <h3> #<span id="quoteReference">{{ reference }}</span></h3>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Start</h3>
            </div>
            <div class="col-sm-9">
                <h3> {{ start }}</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Finish</h3>
            </div>
            <div class="col-sm-9">
                <h3>{{ finish }}</h3>
                <p>{{ times_note }}</p>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Total price</h3>
            </div>
            <div class="col-sm-9">
                <h3>{{ currency }}{{ total }}</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Deposit</h3>
            </div>
            <div class="col-sm-9">
                <h3>{{ currency }}{{ deposit }}</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Includes</h3>
            </div>
            <div class="col-sm-9">
                <ul class="package-list">
                    <li v-for="item in includes">{{ item }}</li>
                </ul>
            </div>
        </div>

        <div class="row">

            <div class="col-sm-3">
                <h3>Optional</h3>
            </div>

            <div class="col-sm-9">
                <div id="quoteAttributes"><ul class="package-list"></ul></div>

            </div>

        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Venue location:</h3>
            </div>
            <div class="col-sm-9">
                <p>{{ location }}</p>
            </div>
        </div>

    </div>
</template>

<script>

import moment from "moment";

export default {
    data: function () {
        return {

        }
    },
    components: {

    },
    mounted() {
        console.log('Summary mounted.')
    },
    methods: {

    },
    computed: {
        times() {
            return this.$parent.info.times;
        },
        start() {
            return moment(this.$parent.cart.start).format("dddd, DD/MM/YYYY, H:mm:ss a");
        },
        finish() {
            return moment(this.$parent.cart.finish).format("dddd, DD/MM/YYYY, H:mm:ss a");
        },
        location() {
            return this.$parent.cart.location;
        },
        items() {
            return this.$parent.info.items;
        },
        total() {
            return this.$parent.info.total;
        },
        deposit() {
            return this.$parent.info.deposit;
        },
        times_note() {
            return 'Arrival for setup will be before the above start time';
        },
        reference() {
            return this.$parent.info.reference;
        },
        currency: function() {
            return this.$parent.currency_symbol;
        },
        includes() {
            return [
                this.items,
                "Setup prior to start (30 to 90 mins)",
                "Pack-away after finish (45 - 60 mins)",
                "Preparation of your playlist(s) (as required)",
                "Weddings - Preparation of your first dance (as required)",
                "Return travelling to your venue",
            ];
        }
    }

}
</script>
<style>
#summary-page .package-list {
    padding-inline-start: 0;
    list-style: none;
}
</style>
