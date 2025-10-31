<template xmlns="http://www.w3.org/1999/html">
    <div>
        <div class="card">
            <div class="card-header"><strong>Booking exclusions</strong> <small></small></div>
            <div class="card-body">

                <div>
                    <h4>Do <u>not</u> allow bookings in the following Postcodes / Zipcodes</h4>
                    <div class="row">
                        <div class="form-group col-sm-5">
                            <label>Postcodes/Zipcodes: (Separate each with a comma)
                                <textarea
                                    name="blocked_postcodes"
                                    class="form-control"
                                    type="textarea"
                                    v-model="blocked_postcodes"></textarea>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <h4>Block same say bookings?</h4>
                    <p>This will block 'surprise' bookings that are booked on the same day as the event.</p>
                    <div class="row">
                        <div class="form-group col-sm-5">
                            <label>Block?
                                <select name="block_same_day_bookings"
                                        class="form-control"
                                        v-model="block_same_day_bookings">
                                    <option value="yes">Yes</option>
                                    <option value="yes">No</option>
                                </select>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <h4>Block bookings over number of day in the future?</h4>
                    <p>This will block bookings that are too far in the future. (0 = no blocking)</p>
                    <div class="row">
                        <div class="form-group col-sm-5">
                            <label>Block?
                                <input name="block_number_days_future"
                                        class="form-control"
                                        v-model="block_number_days_future" />
                            </label>
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

            if (typeof initial_product.settings.block_same_day_bookings !== 'undefined') {
                this.block_same_day_bookings = initial_product.settings.block_same_day_bookings;
            }
            if (typeof initial_product.settings.block_number_days_future !== 'undefined') {
                this.block_number_days_future = initial_product.settings.block_number_days_future;
            }
            if (typeof initial_product.settings.blocked_postcodes !== 'undefined') {
                this.blocked_postcodes = initial_product.settings.blocked_postcodes;
            }

        },
        methods: {
            hasError: function(name) {
                return this.errors[name] != undefined;
            },
        },
        data: function() {
            return {
                block_number_days_future: 0,
                block_same_day_bookings: 'no',
                blocked_postcodes: '',
            }
        },
        computed: {
            errors: function() {
                return this.$parent.errors;
            }
        },
        mounted() {

        }
    }
</script>
