<template>
    <div>
        <div class="card">
            <div class="card-header"><strong>Price Adjustments</strong> <small></small></div>
            <div class="card-body">

                <div v-for="(price, index) in prices">
                    <h4>Advance booking charge / discount</h4>
                    <div class="row">
                        <div class="form-group col-sm-2">
                            <label>Bookings from:<input
                                :name="'advance_charge[' + index + '][from]'"
                                class="form-control"
                                type="number"
                                v-model="price.from"
                            /></label>
                        </div>
                        <div class="form-group col-sm-3">
                            <label>To: (Days in advance).
                            <input
                                :name="'advance_charge[' + index + '][to]'"
                                class="form-control"
                                type="number"
                                v-model="price.to"
                            />
                            </label>
                        </div>
                        <div class="form-group col-sm-3">
                            <label>Adjust price by
                                <input
                                    :name="'advance_charge[' + index + '][value]'"
                                    class="form-control"
                                    type="number"
                                    v-model="price.value"
                                />
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <h4>Over standard duration charge</h4>
                    <p>Extra charge for bookings over that are longer than the usual duration.</p>
                    <div class="row">
                        <div class="form-group col-sm-4">
                            <label>If total booking time exceeds (hours):
                                <input
                                name="extra_hours_charge_max_hours"
                                class="form-control"
                                v-model="extra_hours_charge_max_hours"
                            /></label>
                        </div>
                        <div class="form-group col-sm-4">
                            An additional charge of <b>{{ extra_charge_percent }}%</b> per hour is applied
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [],

        created: function () {
            let initial_product = JSON.parse(window.__INITIAL_PRODUCT__);

            if (typeof initial_product.settings.advance_charges !== 'undefined'
                && initial_product.settings.advance_charges.length) {
                this.prices = initial_product.settings.advance_charges;
            }


            if (typeof initial_product.settings.extra_hours_charge_max_hours !== 'undefined') {
                this.extra_hours_charge_max_hours = initial_product.settings.extra_hours_charge_max_hours;
            }

        },
        methods: {
            hasError: function(name) {
                return this.errors[name] != undefined;
            },
        },
        data: function() {
            return {
                prices: [
                    {from: 0, to: 0, value: 0.00},
                    {from: 0, to: 0, value: 0.00},
                ],
                extra_hours_charge_max_hours: 0,
            }
        },
        computed: {
            extra_charge_percent: function () {
                if (this.extra_hours_charge_max_hours) {
                    return ((1 / this.extra_hours_charge_max_hours) * 100).toFixed(1);
                }
                return 0.0;
            },
            errors: function() {
                return this.$parent.errors;
            }
        },
        mounted() {

        }
    }
</script>
