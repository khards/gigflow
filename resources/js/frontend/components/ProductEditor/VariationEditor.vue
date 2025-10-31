<template>
    <div>
        <div class="card">
            <div class="card-header"><strong>Variations</strong> <small></small></div>
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-sm-1">Id</div>
                    <div class="form-group col-sm-3">Name</div>
                    <div class="form-group col-sm-2">Has own stock?</div>
                    <div class="form-group col-sm-2">Default</div>
                    <div class="form-group col-sm-4">
                        <div class="d-inline">Actions</div>
                        <button
                                v-on:click="add()"
                                type="button"
                                class="d-inline float-right btn-sm btn-pill btn-success">
                            <span class="cil-plus btn-icon mr-2"></span>Add
                        </button>
                    </div>
                </div>
                <!-- Variation row(s) -->
                <div class="row" v-for="(variation, index) in variations">

                    <div class="form-group col-sm-1">
                        {{ variation.id }}
                    </div>

                    <!-- Name -->
                    <div class="form-group col-sm-3">
                        <input type="text" class="form-control" v-model="variation.name" />
                    </div>

                    <!-- Has own stock -->
                    <div class="form-group col-sm-2">
                        <select v-model="variation.stock_from_parent" class="form-control">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- Default variation -->
                    <div class="form-group col-sm-2 text-left">
                        <div class="radio" style="padding-top: 0.5em;padding-left: 0.5em;">
                            <input
                                type="radio"
                                style="transform: scale(2.0)"
                                name="default_variation"
                                v-model="default_selected"
                                v-on:click="changeDefault(variation.id)"
                                :value="variation.id" />
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="form-group col-sm-4">

                        <button v-on:click="detach(index)"
                            type="button" class="btn-sm btn-pill btn-danger">
                            <span class="cil-trash btn-icon mr-2"></span>Detach
                        </button>

                        <button type="button" class="btn-sm btn-pill btn-secondary">
                            <span class="cil-cog btn-icon mr-2"></span>
                                <a v-on:click="editProduct(variation.id)">Edit</a>
                        </button>

                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios";

    export default {
        props: [],

        created: function () {
            if(window.__INITIAL_VARIATIONS__) {
                const variations = JSON.parse(window.__INITIAL_VARIATIONS__) || {};

                for(let idx in variations) {
                    if(variations[idx].pivot.is_default == "1") {
                        this.default_selected = variations[idx].id;
                    }

                    this.variations.push({
                        id: variations[idx].id,
                        name: variations[idx].name,
                        stock_from_parent: variations[idx].pivot.stock_from_parent,
                        is_default: variations[idx].pivot.is_default,
                    });
                }

                if(this.default_selected == null) {
                    for(let variation of variations) {
                        this.default_selected = variation.id;
                        break;
                    }
                }

            }
        },
        methods: {
            changeDefault: function (id) {
                this.default_selected = id;
                for(let v of this.variations) {
                    if(v.id == id) {
                        v.is_default = 1;
                    } else {
                        v.is_default = 0;
                    }
                }
            },
            hasError: function(name) {
                return this.errors[name] != undefined;
            },
            editProduct(id) {
                // Save this product
                this.$parent.save().then(result => {
                    window.location = '/product/' + id;
                });
            },
            detach: function(index) {

                //Make ajax request to detach..

                //Remove from list
                this.variations.splice(index, 1);
            },
            duplicate: function() { // resolve, reject
                let initial_product = JSON.parse(window.__INITIAL_PRODUCT__);
                return axios.post('/product/create/variation/' + initial_product.id, []);
            },
            add: function(event) {

                const index = this.variations.length;
                //const self = this;

                // Save this product
                this.$parent.save().then(result => {

                    // Send request to duplicate this product.
                    this.duplicate(this.$parent.id).then(new_product => {

                        // Add extra variation
                        this.variations.splice(index + 1, 0, {
                            id: new_product.data.id,
                            name: new_product.data.name,
                            stock_from_parent: new_product.data.stock_from_parent,
                            is_default: 0,
                        });
                    });
                }).catch(function (error){
                    window.scrollTo(0,0);
                });
            },
        },
        data: function() {
            return {
                default_selected: null,
                variations: [],
            }
        },
        computed: {

        },
        mounted() {
            console.log('Variation component mounted.');
        }
    }
</script>

