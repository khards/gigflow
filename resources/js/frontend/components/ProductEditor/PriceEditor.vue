<template>
    <div>
        <div class="card">
            <div class="card-header"><strong>Regular Pricing</strong> <small></small></div>
            <div class="card-body">

                <div class="row">
                    <div class="form-group col-sm-4">
                        <label for="price_type">Pricing type</label>
                        <select
                            name="price_type"
                            v-model="price_type"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('price_type') }"
                            id="price_type">
                            <option value="fixed">Fixed price</option>
                            <option value="scheduled">Scheduled</option>
                        </select>
                    </div>
                </div>

                <!-- Scheduled Price row(s) -->
                <div
                    v-if="price_type=='scheduled'"
                    v-for="(price, index) in prices"
                    :id="index"
                    class="row"
                    v-bind:class="{ 'is-invalid': hasError('price_scheduled') }">

                    <!-- Schedule -->
                    <div class="form-group col-sm-4" v-if="price_type=='scheduled'">
                        <label :for="'schedule_price_schedule_' + index">Schedule</label>
                        <!-- v-model="schedule" -->
                        <select
                            :name="'price_scheduled[' + index + '][schedule]'"
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
                            :name="'price_scheduled[' + index + '][price]'"
                            v-model="prices[index].price"
                            class="form-control"
                            :id="'schedule_price_price_' + index"
                            type="number"
                            min="0.00"
                            max="100000.00"
                            step=".01"
                            placeholder="Enter price">
                    </div>

                    <!-- Per -->
                    <div v-if="type=='service'" class="form-group col-sm-2">
                        <label :for="'schedule_price_per_' + index">Per</label>

                        <select
                            :name="'price_scheduled[' + index + '][per]'"
                            v-model="prices[index].per"
                            class="form-control"
                            :id="'schedule_price_per_' + index">

                            <option value="booking">Booking</option>
                            <option value="day">Day</option>
                            <option value="hour">Hour</option>
<!--                            <option value="person">Person</option>-->
                        </select>
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
                                v-on:click="addPrice(index)"
                                class="btn-sm btn-pill btn-success">
                                <span class="cil-plus btn-icon mr-2"></span>Add</button>
                        </div>
                    </div>

                </div>

                <!-- Fixed Price -->
                <div v-if="price_type=='fixed'" class="row">
                    <!-- Price -->
                    <div class="form-group col-sm-4">
                        <label for="price_fixed_price">Price</label>
                        <input
                            v-model="price_fixed_price"
                            name="price_fixed_price"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('price_fixed_price') }"
                            id="price_fixed_price"
                            type="number"
                            min="0.00"
                            max="100000.00"
                            step=".01"
                            placeholder="Enter price">
                    </div>

                    <!-- Service pricing per (Hour, booking, day..) -->
                    <div v-if="type=='service'" class="form-group col-sm-4">
                        <label for="price_fixed_per">Per</label>
                        <select
                            name="price_fixed_per"
                            class="form-control"
                            id="price_fixed_per">
                            <option>Booking</option>
                            <option>Day</option>
                            <option>Hour</option>
<!--                            <option>Person</option>-->
                        </select>
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

            this.price_type = initial_product.price_type;
            this.price_fixed_price = initial_product.price_fixed_price;

            if (initial_product.price_type == 'scheduled') {
                var i = 0;
                let prices = [];

                initial_product.schedules = initial_product.schedules.filter(function(item) {
                    return item.rrule !== null;
                });

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
                        return item.rrule !== null;
                    });
                }
                return filtered;
            },
            errors: function() {
                return this.$parent.errors;
            }
        },
        mounted() {
            console.log('Price editor component mounted.');
        }
    }
</script>
