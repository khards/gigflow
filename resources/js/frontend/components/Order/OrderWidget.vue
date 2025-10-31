<template>
    <div>
        <div class="row" v-if="this.errors">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <h2>{{ this.errors }}</h2>
                </div>
            </div>

        </div>

        <div class="row" v-if="!requiredFieldsFilled">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <div v-if="cart.location === ''">Please enter a location</div>
                    <div v-if="cart.start === ''">Please select a start date</div>
                    <div v-if="cart.finish === ''">Please select a finish date</div>
                </div>
            </div>
        </div>

        <!--
        <nav-tabs v-on:update:tab="updateTab"></nav-tabs>
        -->

        <div class="row" v-if="!requiredFieldsFilled || step === this.cartSteps.PRODUCTS">
            <div class="col-sm-3">
                <label>Location:
                    <input v-model="cart.location"
                           type="text"
                           class="form-control"
                           placeholder="Location">
                </label>
            </div>
            <div class="col-sm-3">
                <label>Date:
                    <input
                        v-model="eventdate"
                        :min="mindate"
                        type="date"
                        class="form-control"
                        placeholder="Start date time">
                </label>
            </div>
            <div class="col-md-6">
                <label>Times:
                    <div>
                        <time-widget
                            @update="updateStartTime"
                            name="start-time"
                            :default="this.cart.start"
                            :minimum="this.minimumstart"
                            :maximum="this.maximumstart"
                        ></time-widget>
                         to:
                        <time-widget
                            @update="updateFinishTime"
                            name="finish-time"
                            :default="this.cart.finish"
                            :minimum="this.cart.start"
                            :maximum="this.maximumfinish"
                        ></time-widget>
                    </div>
                </label>
            </div>

        </div>

        <!-- mini summary -->
        <div class="mini-summary" v-if="requiredFieldsFilled && step !== this.cartSteps.PRODUCTS && step !== this.cartSteps.SUMMARY">
            <div class="row">
                <div class="col-md-3"><b>Times</b></div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6"><b> Start:</b> {{ this.info.times.start}}</div>
                        <div class="col-md-6"><b> Finish:</b> {{ this.info.times.finish}}</div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-3"><b>Location</b></div>
                <div class="col-sm-9">{{ this.cart.location}}</div>
            </div>
            <div class="row">
                <div class="col-sm-3"><b>Order</b></div>
                <div class="col-sm-9">{{ this.info.items }}</div>
            </div>
            <div class="row">
                <div class="col-sm-3"><b>Total</b></div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-6">{{ this.currency_symbol }}{{ this.info.total }}</div>
                        <div class="col-md-6"><b> Includes travelling:</b> {{ this.currency_symbol }}{{ this.info.dispatch_price }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" v-if="!this.hasBookableProducts">
            <div class="col-md-12">
                <div class="alert alert-danger" role="alert">
                    <h2>No products available</h2>
                    <p>Advice: {{ advice }}</p>
                </div>
            </div>
        </div>

        <hr />

        <order-items
            v-on:update:selected_products="updateSelectedProducts"
            v-if="step === this.cartSteps.PRODUCTS">Select Products</order-items>

        <order-options
            :parent_product_id="getNextOptionalProductId"
            :page="optionsPageIdx"
            v-on:update:product_options="updateSelectedProductOptions"
            name="select_option"
            v-if="step === this.cartSteps.VARIANTS"></order-options>

        <order-items
            product_type="extra"
            v-on:update:selected_products="updateSelectedProducts"
            v-if="step === this.cartSteps.EXTRAS">Optional Extras</order-items>

        <order-custom
            ref="custom_form"
            :current_form_data="formData"
            v-if="step === this.cartSteps.CUSTOM_FORM"></order-custom>

        <order-payment
            ref="payment_form"
            v-if="step === this.cartSteps.PAYMENT"></order-payment>

        <order-summary v-if="step === this.cartSteps.SUMMARY"></order-summary>

        <navigation
            v-if="!redirectingToPayment"
            :step="step"
            :disabled="this.navigationDisabled"
            ref="navigation"
            v-on:update:formData="updateFormData"
            v-on:update:step="stepChange"></navigation>

        <div v-if="redirectingToPayment">
            <h1>Please wait while we redirect you to the secure payment screen</h1>
        </div>

    </div>
</template>

<script>
import OrderItems from "./widgets/OrderItems";
import OrderOptions from "./widgets/OrderOptions";
import OrderCustom from "./widgets/OrderCustom";
import OrderPayment from "./widgets/OrderPayment";
import OrderDetails from "./widgets/OrderDetails";
import OrderSummary from "./widgets/OrderSummary";
import Navigation from "./widgets/Navigation";
import NavTabs from "./widgets/NavTabs";
import TimeWidget from "./widgets/TimeWidget";

import debounce from 'lodash/debounce';
import axios from "axios";
import moment from "moment";

export default {
    data: function() {

        //Class constants
        this.cartSteps = {
            PRODUCTS: "Products",
            VARIANTS: "Variants",
            EXTRAS: "Extras",
            CUSTOM_FORM: "Custom",
            SUMMARY: "Summary",
            PAYMENT: 'Payment',
        };

        return {

            minimumstart : '',
            maximumstart : '',
            minimumfinish : '',
            maximumfinish : '',
            mindate: '',

            errors: '',
            advice: '',

            //Update via emit from navigation
            step: this.cartSteps.PRODUCTS,
            tab: '',
            optionsPageIdx: 0,

            //order
            productsPerRow: 3,


            token: window.config.token,

            sessionId: '',

            last_navigation_direction: '',

            sumbittingForm: false,
            formRequestInProgress: false,
            redirectingToPayment: false,

            formId : null,
            formData : null,
            //lastFormErrors: null,//TODO -re move

            // Cart data.
            cart: {
                start: '',
                finish: '',
                location: '',
                selectedProducts: [],
            },
            info: {
                times: {start: '', finish: ''},
                items: '',
                deposit: '',
                adjustments: '',
                dispatch_price: '',
                total: '',
                reference: '',
            },
            //Products data
            products: [],
            currency_symbol: '',
        }
    },
    computed: {

        hasBookableProducts() {
            if( this.products.length === 0) {
                return false;
            }

            let count = 0;
            for (let y=0; y < this.products.length; y++) {
                if (this.products[y].extra === false) {
                    count++;
                }
            }
            if (!count) {
                return false;
            }

            return true;
        },

        navigationDisabled() {

            if (this.cart.selectedProducts.length === 0) {
                return true;
            }

            return (
                this.formRequestInProgress ||
                this.errors ||
                !this.requiredFieldsFilled
            ) ? true : false
        },
        eventdate: {
            get() {
                return this.cart.start.substring(0, 10);
            },
            set(value) {
                // Update start date only.
                let original_start = moment(this.cart.start, 'YYYY-MM-DD HH:mm:ss');
                let dateParts = value.split("-");
                original_start.set('year', dateParts[0]);
                original_start.set('month', dateParts[1]-1); // Month is zero indexed!! (Slag)
                original_start.set('date', dateParts[2]);
                this.cart.start = original_start.format('YYYY-MM-DD HH:mm:ss');

                // Update finish date
                let original_finish = moment(this.cart.finish, 'YYYY-MM-DD HH:mm:ss');

                //  finish will be the next day if time is < start time.
                let finish_date = moment(value, 'YYYY-MM-DD');
                if(original_finish.hours() < original_start.hours()) {
                    //finish_date = finish_date.add(1, 'day');
                    finish_date.add(1, 'days');
                }

                original_finish.year(finish_date.format('YYYY'));
                original_finish.month(finish_date.month()); // Careful, both get and set are zero offset.
                original_finish.date(finish_date.date());
                this.cart.finish = original_finish.format('YYYY-MM-DD HH:mm:ss');

                this.updateTimeSelect();
            },
        },

// @todo - there can be more than 1 optional product - Need to setup a second product (photobooth) with options.
        getNextOptionalProductId() {
            return this._getProductsWithOptionalProducts()[this.optionsPageIdx];
        },
        defaultDateTime() {
            let now = new Date();
            now.setMinutes(0);
            now.setSeconds(0);
            now.setHours(19);
            now.setMinutes(0);//now.getTimezoneOffset()
            return moment(now).format("YYYY-MM-DD HH:mm:ss");
        },
        defaultDateTimeFinish() {
            let now = new Date();
            now.setMinutes(0);
            now.setSeconds(0);
            now.setHours(24);
            now.setMinutes(0);//now.getTimezoneOffset()
            return moment(now).format("YYYY-MM-DD HH:mm:ss");
        },

        requiredFieldsFilled() {
            return this.cart.location !== '' && this.cart.start !== '' && this.cart.finish !== '';
        },
        requiredFieldsHash() {
            return this.cart.location + this.cart.start + this.cart.finish;
        }
    },
    watch: {
        cart: {
            // Will fire as soon as the component is created
            //immediate: true,

            // Watch all the cart properties for change.
            deep: true,

            handler() {
                localStorage.cart_location = this.cart.location;
                localStorage.cart_start = this.cart.start;
                localStorage.cart_finish = this.cart.finish;
                this.debouncedSync();
            }
        },
        requiredFieldsFilled: {
            handler() {
                this.productsSync();
            }
        },
        requiredFieldsHash: {
            handler() {
                this.productsSync();
            }
        }
    },
    methods: {
        getQueryParams(qs) {
            qs = qs.split('+').join(' ');
            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;

                while (tokens = re.exec(qs)) {
                    params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
                }
            return params;
        },
        updateTimeSelect() {
            // start and finish drop-downs are based on start date.
            this.minimumstart = this.cart.start.substring(0, 10) + ' 11:00:00';
            this.maximumstart = this.cart.start.substring(0, 10) + ' 23:30:00';
            this.minimumfinish = this.cart.start.substring(0, 10) + ' 11:00:00';

            let maxfinish = moment(this.cart.start, 'YYYY-MM-DD HH:mm:ss');
            maxfinish.add(1, 'day');
            this.maximumfinish = maxfinish.format('YYYY-MM-DD ') + '02:00:00';
        },
        updateStartTime: function(hours) {
            this.cart.start = hours;
        },
        updateFinishTime: function(hours) {
            this.cart.finish = hours;
        },
        updateTab: function(tab) {
            this.tab = tab;
        },
        stepChange: function(step) {
            this.step = step;
        },
        updateFormData: function (data) {
            this.formData = data.formData;
            this.last_navigation_direction = data.navigation_direction;
            this.sumbittingForm = true;
            this.sync();
        },
        isEqual: function(obj1, obj2) {
            if (JSON.stringify(obj1) === JSON.stringify(obj2)) {
                return true;
            }
            return false;
        },
        sync: function() {
            this.formRequestInProgress = true;

            const submittingFormThisRequest = this.sumbittingForm;
            this.sumbittingForm = false;
            const config = {
                 headers: { Authorization: `Bearer ${this.token}` }
            };

            const requestData = {
                'businessId': window.config.businessId,
                'products': [
                //     {'id': 11, 'quantity': 1}
                ],
                'start': moment(this.cart.start).format("YYYY-MM-DD HH:mm:ss"),
                'end': moment(this.cart.finish).format("YYYY-MM-DD HH:mm:ss"),
                'timezone': Intl.DateTimeFormat().resolvedOptions().timeZone,
                'location': this.cart.location,
                'sessionId': this.sessionId,
                'formData': submittingFormThisRequest ? JSON.stringify(this.formData) : null,
                'currentFormId': submittingFormThisRequest ? this.formId : null,
                'navAction': this.last_navigation_direction,
            }

//@TODO - quantity's

            for (const product of this.cart.selectedProducts) {
                requestData.products.push({'id': product.id, 'quantity': 1});
            }

            this.info.extras = '';

            const self = this;

            // Dont update cart if no products.
            // Added to prevent a failed http request on startup
            //
            //  This could break 'empty cart' If so will need a counter / toggle.
            //
            if(!requestData.products.length) {
                return;
            }

            let makeRequest = false;

            // First time make and store request.
            if ( typeof this.last_request == 'undefined' ) {
                this.last_request = requestData;
                makeRequest = true;

            // Next time if it's not equal store and make request
            } else if (!this.isEqual(this.last_request, requestData)) {
                this.last_request = requestData;
                makeRequest = true;
            } else {
                makeRequest = false;
            }

            if (!makeRequest) {
                return;
            }

            // Clear error messages from last request
            this.errors = '';

            axios.patch('/api/v1/cart', requestData, config)
                .then(response => {
                    const hasFormErrors = typeof response.data.data.errors === 'object' && typeof response.data.data.errors.form === 'object';

                    // Update summary
                    self.info.deposit = response.data.data.price.deposit;
                    self.info.adjustments = response.data.data.price.adjustments;
                    self.info.total = response.data.data.price.total_price;
                    self.info.dispatch_price = response.data.data.price.dispatch_price;
                    self.info.reference = response.data.data.reference;
                    self.info.items = this._info_order();
                    self.info.times.start = moment(self.cart.start).format("dddd, DD/MM/YYYY, H:mm:ss a");
                    self.info.times.finish = moment(self.cart.finish).format("dddd, DD/MM/YYYY, H:mm:ss a");
                    self.sessionId = response.data.data.sessionId;

                    if (hasFormErrors) {
                        // Don't refresh the form (don't update Formdata) if there have been errors.
                        // This way we can update the DOM with the errors

                        const errors = response.data.data.errors.form;
                        for (const key in errors) {
                            document.querySelectorAll(`*[name="${key}"]`).forEach(function(item){
                                item.parentElement.style.background = '#f1ff1f';
                            })
                        }
                    }
                    else {
                        self.formId = response.data.data.formId;
                        self.formData = JSON.parse(response.data.data.formData);

                        const allFormsCompleted = !self.formData.length;
                        if (submittingFormThisRequest) {
                            if (allFormsCompleted && self.last_navigation_direction === 'forward') {
                                //self.step = self.cartSteps.PAYMENT;
                                axios.post('/api/v1/checkout/order', requestData, config)
                                    .then(response => {
                                        this.redirectingToPayment = true;

                                        // console.log('checkout/order response is:');
                                        // console.log(response);
                                        // console.log('Redirect to payment screen.');
                                        window.location.href = response.data.redirectUrl;
                                    })
                                    .catch(error => {
                                        console.log('checkout/order ERROR is:');
                                        console.log(error);
                                    });

                            }
                            else if (self.last_navigation_direction === 'backward') {
                                self.step = self.cartSteps.SUMMARY;
                            }
                        }
                    }
                    this.formRequestInProgress = false;
                })
                .catch(error => {
                    if (typeof error.response.data === 'object' && error.response.data.errors !== undefined) {
                        const str = _.flatten(_.toArray(error.response.data.errors));
                        console.log(str);
                    } else {
                        this.errors = 'Something went wrong. Please try again.';
                        // if (typeof error.response.data.exception == 'string') {
                        //     this.errors += ' : ' + error.response.data.exception;
                        // }
                    }
                });

        },

        /**
         * This function generates the products text for the top summary
         */
        _info_order: function() {
            let items = '';
            let selected_products = this.cart.selectedProducts;

            for (const selected_product of selected_products) {
                for (const parent_product of this.products) {
                    if(selected_product.id == parent_product.id) {

                        const divider = (items.length ? ', ' : '');
                        items += divider + parent_product.title;

                        if(parent_product.products && parent_product.products.length) {
                            for (const child_product of parent_product.products) {
                                for (const [search_product_index, search_product] of selected_products.entries()) {
                                    if (search_product.id == child_product.id) {
                                        items += ' (' + child_product.title + ') ';
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return items;
        },

        /**
         * This method fetches the available products from the server.
         *
         * It passes the Business ID, Search dates, Location etc. and recieves a list of available products.
         *
         */
        fetchProducts: function() {
            const config = {
                headers: { Authorization: `Bearer ${this.token}` },
                params: {
                    'business': window.config.businessId,
                    'start': moment(this.cart.start).format("YYYY-MM-DD HH:mm:ss"),
                    'end': moment(this.cart.finish).format("YYYY-MM-DD HH:mm:ss"),
                    'timezone': Intl.DateTimeFormat().resolvedOptions().timeZone,
                    'location': this.cart.location,
                    'available': true ? 1 : 0,
                    'state': 'active',
                    'opt_fields': 'price',
                },
            };

            axios.get('/api/v1/orderform', config)
                .then(response => {
                    this.products = response.data.data.products;
                    //this.cart.forms = response.data.data.forms;
                    this.currency_symbol = response.data.data.currency_symbol;

                    //Default checked products && Forced selection products before rendering DOM
                    for(let y=0; y < this.products.length; y++) {
                        const isDefault = (this.products[y].default && this.products[y].default == true);
                        const isForced = (this.products[y].force && this.products[y].force == true);

                        if( isDefault || isForced) {
                            //this.cart.selectedProducts.push({id: this.products[y].id, quantity: 1});
                            this.updateSelectedProducts({id: this.products[y].id, checked: true});

                            //@todo - code review: Should be recursive, code duplication 11pm!

                            //If has default or forced child product, then select them...
                            if(this.products[y].products) {
                                for(const child_product of this.products[y].products) {
                                    const isDefault = (child_product.default && child_product.default == true);
                                    const isForced = (child_product.force && child_product.force == true);

                                    if( isDefault || isForced) {
                                        // this.cart.selectedProducts.push({id: child_product.id, quantity: 1});
                                        this.updateSelectedProducts({id: child_product.id, checked: true});
                                    }
                                }
                            }
                        }
                    }

                    // If product is in cart, but no longer returned, then remove it.
                    let initial_Length = this.cart.selectedProducts.length;
                    for (let x = 0; x < initial_Length; x++) {
                        let found = false;
                        for (let idx in response.data.data.products) {
                            if (this && this.cart.selectedProducts[x].id == response.data.data.products[idx].id) {
                                found = true;
                                break;
                            }
                        }
                        if (!found) {
                            this.cart.selectedProducts.splice(x, 1);
                        }
                    }

                    if(this && response.data.data.info.length && !this.hasBookableProducts) {
                        this.advice = response.data.data.info[0].reason_message;
                    }
                })
                .catch(error => {
                    if (typeof error.response.data === 'object' && error.response.data.errors !== undefined) {
                        const str = _.flatten(_.toArray(error.response.data.errors));
                        console.log(str);
                    } else {
                        this.errors = 'Something went wrong. Please try again.';
                        if (typeof error.response.data.exception == 'string') {
                            this.errors += ' : ' + error.response.data.exception;
                        }

                    }
                });
        },
        updateSelectedProductOptions: function(selectOptions) {
            //Note: the incoming selected is an array of items selected and unselected.
            for(let x=0;x<selectOptions.length;x++) {
                this.updateSelectedProducts(selectOptions[x]);
            }
        },

        /**
         * For a given selected product, add or remove it from the cart's selected products
         *
         * @param selected {int id, boolchecked}
         */
        updateSelectedProducts: function(selected) {

            // Forced selected products for any given forced id.
            for(let y=0; y < this.products.length; y++) {
                if(this.products[y].force && this.products[y].force == true) {
                    if(selected.id == this.products[y].id) {
                        selected.checked = true;
                        break;
                    }
                }
            }

            //Remove unchecked products from cart..
            if(!selected.checked) {
                let initial_Length = this.cart.selectedProducts.length;
                for (let x = 0; x < initial_Length; x++) {
                    if (this.cart.selectedProducts[x].id == selected.id) {
                        this.cart.selectedProducts.splice(x, 1);
                        break;
                    }
                }

                //Optional products - If a parent product is de-selected then child products are removed.
                let childProducts = this._getChildProducts(selected.id);
                for(let x=0; x < childProducts.length; x++) {
                    this.updateSelectedProducts({selected: false, id:childProducts[x].id});
                }
            }

            // Add checked product and any default child products
            if(selected.checked) {

                // If product is already in the cart, then don't add it.
                // Note, this will need revising if we add quantity.
                if(this.cart.selectedProducts.findIndex(product => product.id === selected.id) >= 0) {
                    return;
                }

                //Add selected product
                this.cart.selectedProducts.push({id: selected.id});

                //Add any default child products as parent has just been added
                let childProducts = this._getChildProducts(selected.id);
                for(let x=0; x < childProducts.length; x++) {
                    if(childProducts[x].default && childProducts[x].default == true) {
                        this.cart.selectedProducts.push({id: childProducts[x].id});
                        break; // Only 1 default option is supported. (radio buttons)
                    }
                }
            }

        },

        _getChildProducts: function(parent_id) {
            for(let y=0; y < this.products.length; y++) {
                if(this.products[y].products && this.products[y].id == parent_id) {
                    return this.products[y].products;
                }
            }
            return [];
        },

        _getParentProduct: function(child_id) {
            for(let y=0; y < this.products.length; y++) {
                if(this.products[y].products ) {
                    for (let z=0; z < this.products[y].products.length; z++) {
                        if(this.products[y].products[z].id == child_id) {
                            return this.products[y].id;
                        }
                    }
                }
            }
        },

        _getProductsWithOptionalProducts: function () {
            let selectedProductsWithOptionalProducts = [];

            // Check if selected 'top level product' has optional products
            for(let x=0; x < this.cart.selectedProducts.length; x++) {
                for(let y=0; y < this.products.length; y++) {
                    if(this.products[y].id == this.cart.selectedProducts[x].id) {
                        if(this.products[y].products) {
                            selectedProductsWithOptionalProducts.push(this.products[y].id);
                        }
                    }
                }
            }

            return selectedProductsWithOptionalProducts;
        },
    },

    components: {
        OrderItems,
        OrderOptions,
        OrderCustom,
        OrderDetails,
        OrderPayment,
        OrderSummary,
        Navigation,
        NavTabs,
        TimeWidget,
    },


    beforeMount: function() {

        let params = this.getQueryParams(document.location.search);

        if (typeof(params['start']) === "undefined") {
            this.mindate = moment().add(1, 'day').format('YYYY-MM-DD');
            //Default start time
            if (this.cart.start == '') {
                this.cart.start = this.defaultDateTime;
            }
        } else {
            this.cart.start = params['start'];
        }

        if (typeof(params['finish']) === "undefined") {
            //Default end time
            if (this.cart.finish == '') {
                //let tomorrow  = this.tomorrow;
                //this.cart.finish = tomorrow;
                this.cart.finish = this.defaultDateTimeFinish;
            }
        } else {
            this.cart.finish = params['finish'];
        }

        if (typeof(params['location']) !== "undefined") {
            this.cart.location = params['location'];
        }

        this.updateTimeSelect();
    },
    mounted() {
        console.log('Order Widget  Component mounted.')

        if (localStorage.cart_location && this.cart.location == '') {
            this.cart.location = localStorage.cart_location;
        }
        // if (localStorage.cart_start) {
        //     this.cart.start = localStorage.cart_start;
        // }
        // if (localStorage.cart_finish) {
        //     this.cart.finish = localStorage.cart_finish;
        // }

        // Note: These debounce had been comented out
        // When removed multiple API calls are made to each service at startup, rather than a single request
        // so we had 4 requests to load the page. This has been added back in as these were running in pararrel
        // and breaking the card session.

        this.debouncedSync = debounce(this.sync, 500);
        this.productsSync = debounce(this.fetchProducts, 500);
    }
}
</script>
