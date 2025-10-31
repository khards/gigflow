<template>
    <div>
        <div class="card">
            <div class="card-header"><strong>Special Pricing</strong> <small></small></div>
            <div class="card-body">

                <!-- Scheduled Price row(s) -->
                <div
                    v-for="(price, index) in prices"
                    :id="index"
                    class="row"
                    v-bind:class="{ 'is-invalid': hasError('price_scheduled') }">

                    <!-- Schedule -->
                    <div class="form-group col-sm-4">
                        <label :for="'schedule_price_schedule_' + index">Schedule</label>
                        <!-- v-model="schedule" -->
                        <select
                            :name="'special_price_scheduled[' + index + '][schedule]'"
                            v-model="prices[index].schedule"
                            class="form-control"
                            :id="'schedule_price_schedule_' + index">
                            <option v-for="schedule in schedules" :value="schedule.id">{{ schedule.summary }}</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div class="form-group col-sm-2">
                        <label :for="'schedule_price_price_' + index">Price</label>
                        <input
                            :name="'special_price_scheduled[' + index + '][price]'"
                            v-model="prices[index].price"
                            class="form-control"
                            :id="'schedule_price_price_' + index"
                            type="number"
                            min="0.00"
                            max="100000.00"
                            step=".01"
                            placeholder="Enter price">

                        <input :name="'special_price_scheduled[' + index + '][per]'" value="special" type="hidden">
                    </div>


                    <!-- Buttons -->
                    <div class="form-group col-sm-4">
                        <div style="margin-top: 35px;">
                            <button
                                v-on:click="deletePrice(index)"
                                type="button"
                                class="btn-sm btn-pill btn-danger">
                                <span class="cil-trash btn-icon mr-2"></span>Delete</button>

                            <button type="button"
                                    v-on:click="editSchedule(index)"
                                    class="btn-sm btn-pill btn-secondary">
                                <span class="cil-cog btn-icon mr-2"></span>Edit</button>

                            <button type="button"
                                    v-on:click="addPrice(index)"
                                    class="btn-sm btn-pill btn-success">
                                <span class="cil-plus btn-icon mr-2"></span>Add</button>

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

            initial_product.schedules = initial_product.schedules.filter(function(item) {
                return item.rrule === null;
            });

            if (initial_product.schedules.length) {
                var i = 0;
                let prices = [];
                for (; i < initial_product.schedules.length; i++) {
                    let price = {
                        per: initial_product.schedules[i]['pivot']['key'],
                        price: initial_product.schedules[i]['pivot']['value'] / 100, // Model is json encoded so need to convert to pennies
                        schedule: initial_product.schedules[i]['pivot']['schedule_id'],
                    };
                    prices.push(price);
                }
                this.prices = prices;
            }
        },
        methods: {
            hasError: function(name) {
                return this.errors[name] != undefined;
            },
            deletePrice: function(index) {
                this.prices.splice(index, 1);
            },
            addPrice: function(index) {
                this.prices.splice(index + 1, 0, {schedule: this.schedules[0].id || 0, price: 0.00, per: 'booking'});
            },
            editSchedule(id) {
                // Save this product and redirect
                this.$parent.save().then(result => {
                    window.location = '/schedule/' + this.prices[id].schedule;
                });
            },
        },
        data: function() {
            return {
                schedule: 'test',
                price_type: 'scheduled',
                price_fixed_price: '0.00',
                prices: [
                    {schedule: 0, price: 0.00, per: 'hour'},
                ],
            }
        },
        computed: {
            type: function() {
                return this.$parent.type;
            },
            schedules: function() {
                let filtered = [];
                let schedules =  JSON.parse(window.__INITIAL_SCHEDULES__) || [];
                if (schedules.length > 0) {
                    filtered = schedules.filter(function(item) {
                        return item.rrule === null;
                    });
                }
                return filtered;
            },
            errors: function() {
                return this.$parent.errors;
            }
        },
        mounted() {

        }
    }
</script>
