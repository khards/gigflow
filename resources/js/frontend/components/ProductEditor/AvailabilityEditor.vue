<template>
    <div>
        <div class="card">
            <div class="card-header">
                <strong>Availability &amp; Stock</strong>
            </div>
            <div class="card-body">

                <h4 v-if="type=='service'">Staff</h4>
                <!-- Service - Staff required -->
                <div v-if="type=='service'" class="row">
                    <div class="form-group col-sm-4">
                        <label for="staff_required">Staff required?</label>

                        <select
                            v-model="staff_required"
                            name="staff[required]"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('staff.required') }"
                            id="staff_required">

                            <option value="yes">Yes - Staff must attend</option>
                            <option value="no">No - Dry hire</option>
                        </select>
                    </div>

                    <div v-if="staff_required=='yes'" class="form-group col-sm-4">
                        <label>Number of staff required?</label>

                        <select
                            name="staff[quantity]"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('staff.quantity') }"
                            id="staff_quantity"
                            v-model="staff_quantity">

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
                            <option value="15">15</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                </div>

                <h4>Availability</h4>
                <!-- Service - Booking availability schedule and qty available to hire -->
                <div v-if="type=='service'" class="row">

                    <div class="form-group col-sm-3">
                        <label for="pricingscheme">Availability type</label>
                        <select
                            v-model="availability_type"
                            v-bind:class="{ 'is-invalid': hasError('availability_type') }"
                            name="availability_type"
                            class="form-control"
                            id="pricingscheme">

                            <option value="available">Always available</option>
                            <option value="scheduled">Scheduled availability</option>
                        </select>
                    </div>

                    <div v-if="availability_type=='scheduled'" class="form-group col-sm-3">
                        <label for="availability_schedule">Schedule availability</label>
                        <select
                            name="availability_schedule"
                            v-model="availability_schedule"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('availability_schedule') }"
                            id="availability_schedule">
                            <option></option>
                            <option v-for="schedule in schedules" :value="schedule.id">{{ schedule.summary }}</option>
                        </select>
                    </div>


                    <div class="form-group col-sm-3">
                        <label for="num_available_to_hire">Quantity available for hire</label>
                        <input
                            name="available_quantity"
                            v-model="available_quantity"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('available_quantity') }"
                            id="num_available_to_hire"
                            type="number" min="0" max="100000" step="1"
                            placeholder="Quantity available for hire">
                    </div>
                </div>

                <!-- Booking - Quantity available in stock -->
                <div v-if="type=='product'" class="row">
                    <div class="form-group col-sm-3">
                        <label for="quantity_in_stock">Quantity available in stock</label>
                        <input
                            name="available_quantity"
                            v-model="available_quantity"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('available_quantity') }"
                            id="quantity_in_stock"
                            type="number" min="0.00" max="100000.00" step="1"
                            placeholder="Quantity in stock">
                    </div>
                </div>

                <h4 v-if="delivery_methods.indexOf('delivered') !== -1">Travelling</h4>
                <!-- Traveling distance limit -->
                <div v-if="delivery_methods.indexOf('delivered') !== -1" class="row">

                    <div class="form-group col-sm-4">
                        <label for="travelling_limit">Limit travelling distance from business?</label>

                        <!-- Limit travelling yes/no -->
                        <select
                            v-model="travelling.limit"
                            name="travelling[limit]"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('travelling.limit') }"
                            id="travelling_limit">

                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <!-- Limit travelling to -->
                    <div class="form-group col-sm-4">
                        <label for="travelling_value">To</label>
                        <input
                            v-model="travelling.value"
                            name="travelling[value]"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('travelling.value') }"
                            id="travelling_value"
                            type="number" min="0.00" max="100000.00" step="0.5"
                            placeholder="Limit travelling to">
                    </div>

                    <div class="form-group col-sm-4">
                        <label for="travelling_type">Type?</label>

                        <!-- Limit travelling type miles/time -->
                        <select
                            v-model="travelling.type"
                            id="travelling_type"
                            name="travelling[type]"
                            class="form-control"
                            v-bind:class="{ 'is-invalid': hasError('travelling.type') }">

                            <option value="miles">Miles</option>
                            <option value="minutes">Minutes</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                staff_required: '',
                availability_schedule: '',
                available_quantity: '',
                availability_type: '',
                travelling: {
                    limit: 'yes',
                    type: 'miles',
                    value: '0',
                },
                staff_quantity: 1,
            }
        },
        created: function () {
            let initial_product = JSON.parse(window.__INITIAL_PRODUCT__);

            this.staff_required = initial_product.staff_quantity > 0 ? 'yes' : 'no';
            this.staff_quantity = initial_product.staff_quantity;

            this.availability_type = initial_product.availability_type;
            if(this.availability_type !== 'available' || this.availability_type !== 'scheduled') {
                this.availability_type = 'available';
            }
            this.availability_schedule = initial_product.availability_schedule;
            this.available_quantity = initial_product.available_quantity;

            this.travelling.limit = initial_product.travelling_limit;
            this.travelling.type = initial_product.travelling_type;
            this.travelling.value = initial_product.travelling_value;
        },
        mounted() {
            console.log('Availability editor mounted.')
        },
        methods: {
            hasError: function(name) {
                return this.errors[name] != undefined;
            },
        },
        computed: {
            type: function() {
                return this.$parent.type;
            },
            schedules: function() {
                return JSON.parse(window.__INITIAL_SCHEDULES__) || {};
            },
            delivery_methods: function() {
                return this.$parent.delivery_methods;
            },
            errors: function() {
                return this.$parent.errors;
            }
        },
    }
</script>
