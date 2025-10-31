<template>
    <div>
        <div v-if="hasErrors()" class="alert alert-danger header-message" role="alert" dismissable="true">
            <div v-for="e in errors"> {{ e[0] }}</div>
        </div>

        <div class="mb-2 container">
            <div class="row">
                <div class="col"></div>
                <div class="col"></div>
                <div class="col"></div>
                <div class="col">
                    <button type="button" @click="formSubmit" class="float-right btn btn-pill btn-success">
                        <span v-bind:class="saveButtonCss"></span>Save
                    </button>
                    <button
                        v-if="parent_id !== null"
                        type="button"
                        @click="parent"
                        class="float-right btn btn-pill btn-success">
                        <span class="cil-chevron-left btn-icon mr-2"></span>
                        Parent
                    </button>

                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="product_name">Product Name</label>
            <input
                type="text"
                name="product_name"
                class="form-control"
                v-bind:class="{ 'is-invalid': hasError('product_name') }"
                id="product_name"
                placeholder="Product name"
                v-model="name">
        </div>

        <div class="form-group">
            <label for="product_name">Setup time</label> <small>(Minutes)</small>
            <input
                type="number"
                max="720"
                min="0"
                step="1"
                name="product_setup_time"
                class="form-control"
                v-bind:class="{ 'is-invalid': hasError('product_setup_time') }"
                id="product_setup_time"
                v-model="product_setup_time">
        </div>

        <div class="form-group">
            <label for="productOrService">Product or Service?</label>
            <select
                v-bind:class="{ 'is-invalid': hasError('type') }"
                v-model="type"
                class="form-control"
                id="productOrService"
                name="type">

                <option value="service">Service - (Bookable)</option>
                <option value="product">Product - (Sellable)</option>
            </select>
        </div>


        <div class="form-group">
            <label for="product_state">Product State</label>
            <select
                v-model="state"
                name="product_state"
                class="form-control"
                id="product_state">
                <option value="draft">Inactive</option>
                <option value="active">Active</option>
            </select>
        </div>

        <div class="form-group">
            <label for="addon">Available as an add-on only?</label>
            <select
                v-model="addon"
                name="addon"
                class="form-control"
                id="addon">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="form-group">
            <label for="required">Product must be ordered when booking?</label>
            <select
                v-model="required"
                name="required"
                class="form-control"
                id="required">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>

        <div class="form-group">
            <label for="required">Booking form</label>
            <select
                v-model="form"
                name="form"
                class="form-control"
                id="form">
                <option value=""> - select - </option>
                <option v-for="(item, key) in forms" :value="item.id"> {{item.name}} </option>
            </select>
        </div>

        <!--
        <div class="form-group">
            <label for="product_category">@lang('Product Category')</label>
            <select multiple class="form-control" id="product_category">
                <option>1 @TODO!</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
            </select>
        </div>
    -->

        <div class="form-group">
            <label for="product_description">Product Description</label>
            <textarea
                class="form-control"
                v-bind:class="{ 'is-invalid': hasError('product_description') }"
                name="product_description"
                id="product_description"
                v-model="description"
                rows="3"></textarea>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <p>Product image</p>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-2">
                        <div
                            class=".img-thumbnail imagePreviewWrapper"
                            :style="{ 'background-image': `url(${preview_image})` }"></div>

                    </div>
                    <div class="col-md-10">
                        <div class="mb-3 custom-file">
                            <label class="custom-file-label" for="product_image">Choose product image
                                <input
                                    @input="pickFile"
                                    ref="product_image"
                                    name="product_image"
                                    type="file"
                                    class="custom-file-input"
                                    id="product_image"
                                    accept="image/*" />
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="product_url">Product URL</label>
            <input
                name="product_url"
                type="text"
                class="form-control"
                v-bind:class="{ 'is-invalid': hasError('product_url') }"
                id="product_url"
                placeholder="https://product-url/more-info"
                v-model="url">
        </div>

        <div class="form-group">
            <label for="delivery_methods">Delivery, collection and shipping</label>
            <select
                name="delivery_methods[]"
                v-model="delivery_methods"
                multiple
                class="form-control"
                v-bind:class="{ 'is-invalid': hasError('delivery_methods') }"
                id="delivery_methods">

                <option value="delivered">Delivered - I travel to the customer</option>
                <option value="collected">Collected - Customers visit me</option>
                <option v-if="type=='product'" value="shipped">Shipped - Post the product to the customer</option>
            </select>
        </div>


        <price-editor></price-editor>

        <special-price-editor></special-price-editor>

        <price-adjustments></price-adjustments>

        <exclusions></exclusions>

        <availability-editor></availability-editor>

        <!-- delivery and travelling -->
        <div>
            <div v-if="delivery_methods.indexOf('delivered') !== -1" class="card">
                <div class="card-header">
                    <strong>Delivery &amp; travelling charge</strong>
                </div>
                <div class="card-body">

                    <div class="row">
                        <!-- Price -->
                        <div class="form-group col-sm-4">
                            <label for="delivery_delivered_charge">Charge</label>

                            <input
                                v-model="delivery.delivered.charge"
                                v-bind:class="{ 'is-invalid': hasError('delivery.delivered.charge') }"
                                name="delivery[delivered][charge]"
                                class="form-control"
                                id="delivery_delivered_charge"
                                type="number"
                                min="0.00"
                                max="100000.00"
                                step="0.01"
                                placeholder="Enter delivery charge">
                        </div>

                        <!-- Per -->
                        <div v-if="delivery_methods.indexOf('delivered') !== -1" class="form-group col-sm-4">
                            <label for="delivery_delivered_per">Per</label>
                            <select
                                v-model="delivery.delivered.per"
                                v-bind:class="{ 'is-invalid': hasError('delivery.delivered.per') }"
                                name="delivery[delivered][per]"
                                class="form-control"
                                id="delivery_delivered_per">
                                <option v-if="type=='service'" value="booking">Booking</option>
                                <option v-if="type=='product'" value="order">Order</option>
                                <option v-if="type=='product'" value="item">Item</option>
                                <option value="mile">Mile</option>
                                <option value="kilometer">Kilometer</option>
                            </select>
                        </div>

                        <!-- Price -->
                        <div class="form-group col-sm-4">
                            <label for="delivery_delivered_charge_over">For distances over</label>

                            <input
                                v-model="delivery.delivered.over"
                                v-bind:class="{ 'is-invalid': hasError('delivery.delivered.over') }"
                                name="delivery[delivered][over]"
                                class="form-control"
                                id="delivery_delivered_charge_over"
                                type="number"
                                min="0.00"
                                max="100000.00"
                                step="0.01"
                                placeholder="Enter free distance">
                        </div>
                    </div>

                </div>
            </div>

            <div  v-if="delivery_methods.indexOf('shipped') !== -1" class="card">
                <div class="card-header"><strong>Shipping &amp; postage charge</strong> <small></small></div>
                <div class="card-body">

                    <div class="row">
                        <!-- Price -->
                        <div class="form-group col-sm-4">
                            <label for="delivery_shipped_price">Shipping / Postage charge</label>
                            <input
                                name="delivery[shipped][price]"
                                class="form-control"
                                v-bind:class="{ 'is-invalid': hasError('delivery.shipped.price') }"
                                v-model="delivery.shipped.price"
                                id="delivery_shipped_price"
                                type="number"
                                min="0.00"
                                max="100000.00"
                                step="0.01"
                                placeholder="Enter price">
                        </div>

                        <div class="form-group col-sm-4">
                            <label for="delivery_shipped_per">Per</label>
                            <select
                                v-bind:class="{ 'is-invalid': hasError('delivery.shipped.per') }"
                                v-model="delivery.shipped.per"
                                name="delivery[shipped][per]"
                                class="form-control"
                                id="delivery_shipped_per">
                                <option value="item">Item</option>
                                <option value="order">Order</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <variation-editor ref="variation_editor"></variation-editor>

    </div>
</template>

<script>
    import PriceEditor from "./PriceEditor";
    import AvailabilityEditor from "./AvailabilityEditor";
    import VariationEditor from "./VariationEditor";
    import SpecialPriceEditor from "./SpecialPriceEditor";
    import PriceAdjustments from "./PriceAdjustments";

    import axios from 'axios'
    import Exclusions from "./Exclusions";

    export default {
        components: {
            Exclusions,
            PriceEditor,
            AvailabilityEditor,
            VariationEditor,
            SpecialPriceEditor,
            PriceAdjustments,
        },
        props: [],

        created: function () {
            let initial_product = JSON.parse(window.__INITIAL_PRODUCT__);
            this.type = initial_product.type;
            if(this.type !== 'product' || this.type !== 'service') {
                this.type = 'service';
            }
            this.state = initial_product.state;
            this.required = initial_product.is_required ? 1 : 0;
            this.addon =  initial_product.is_addon ? 1 : 0;
            this.name = initial_product.name;
            this.description = initial_product.description;
            this.image_path = initial_product.settings.image_path || null;
            this.preview_image = this.image_path;
            this.url = initial_product.settings.url || null;
            this.product_setup_time = initial_product.setup_time || 0;

            // Calculate delivery method option.
            this.initDeliveryMethod(initial_product);
            this.delivery = initial_product.settings.delivery;
            this.parent_id = (initial_product.parent && initial_product.parent.length) ? initial_product.parent[0].id : null;

            // Forms.
            this.form = initial_product.form_id === null ? '' : initial_product.form_id;
            this.forms = JSON.parse(window.__FORMS__);
        },
        data: function() {
            return {
                errors: {},
                type: 'service',
                parent_id: null,
                state: 'active',
                addon: false,
                required: false,
                name: 'Product name',
                description: 'Product description',
                image_path: '',
                url: '',
                product_setup_time: 0,
                delivery: {
                    delivered: {
                        charge: 0,
                        per: 'mile',
                        over: 0,
                    },
                    shipped: {
                        price: 0,
                        per: 'order'
                    }
                },
                delivery_methods: [],
                preview_image: '',
                form: '',
                forms: [],
                saving: false,
            }
        },
        methods: {
            parent: function () {

                // Save this product
                this.save().then(result => {

                    //Then redirect to parent
                    window.location = '/product/' + this.parent_id;
                });
            },
            formSubmit: function() {
                this.save(function(){}, function(){});
            },
            save: function(resolve, reject) {
                const self = this;
                return new Promise(function(resolve, reject) {
                    let form = document.getElementById('edit-product');
                    var formData = new FormData(form);
                    let initial_product = JSON.parse(window.__INITIAL_PRODUCT__);
                    let currentObj = self;

                    currentObj.saving = true;
                    currentObj.errors = [];

                    //Add variations to form as a json object called variations.
                    formData.append('variations', JSON.stringify(self.$refs['variation_editor'].$data.variations));

                    let result = axios.post('/product/' + initial_product.id, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }).then(function (response) {
                        // Common functionality
                        currentObj.saving = false;

                        resolve(response);
                    }).catch(function (error) {

                        // Common functionality
                        currentObj.saving = false;
                        currentObj.errors = error.response.data.errors;

                        reject(error);
                    });
                });
            },
            hasErrors: function() {
                return this.errors && Object.keys(this.errors).length;
            },
            hasError: function(name) {
                if (this.errors[name] != undefined) {
                    return true;
                }

                function findValueByPrefix(object, prefix) {
                    for (var property in object) {
                        if (object.hasOwnProperty(property) &&
                            property.toString().startsWith(prefix)) {
                            return object[property];
                        }
                    }
                }
                if(findValueByPrefix(this.errors, name)) {
                    return true;
                }
                return false;
            },
            pickFile () {
                let input = this.$refs.product_image;
                let file = input.files;
                if (file && file[0]) {
                    let reader = new FileReader
                    reader.onload = e => {
                        this.preview_image = e.target.result
                        this.image_path = null;
                    }
                    reader.readAsDataURL(file[0])
                    this.$emit('input', file[0])
                }
            },
            initDeliveryMethod(initial_product) {
                const methodmap = {
                    1: 'delivered',
                    2: 'collected',
                    4: 'shipped'
                };

                let delivery_methods = new Array();
                let index = 0;
                for (let i = 0; i <= 2; i++) {
                    if (initial_product.delivery_method & (1 << i)) {
                        delivery_methods[index] = methodmap[(1 << i)];
                        index++;
                    }
                }
                this.delivery_methods = delivery_methods;
            }
        },
        computed: {
            saveButtonCss: function() {
                let defaultCss = "cil-save btn-icon mr-2";
                let savingCss = "spinner-border spinner-border-sm";
                if(this.saving) {
                    return savingCss;
                }
                return  defaultCss;
            },
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>

<style scoped lang="scss">
    .imagePreviewWrapper {
        width: 125px;
        height: 125px;
        display: block;
        cursor: pointer;
        margin: 0 auto 30px;
        background-size: cover;
        background-position: center center;
    }
</style>
