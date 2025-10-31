<template>
    <div>
        <h2><slot></slot></h2>
        <div class="row p-1" v-for="groupId in productGroups">
            <image-check v-for="product in getProductGroup(groupId)" v-bind:key="product.id"
                :force="product.force"
                :id='product.id'
                :name="product.name"
                :checked="productChecked(product.id)"
                :title='product.title'
                :price='currency() + product.price'
                :info-href="product.url"
                v-on:update:ImageCheckChange="imageCheckChange"
                :image-url="product.image"></image-check>
        </div>
    </div>
</template>

<script>

import ImageCheck from "./ImageCheck";

export default {
    props: ['product_type'],
    data: function () {
        return {
        }
    },
    components: {
        ImageCheck,
    },
    mounted() {
        console.log('Component Order Items mounted.')
    },
    methods: {
        getProductGroup: function(groupId) {
            return this.products.slice(groupId * this.productsPerRow, (groupId + 1) * this.productsPerRow);
        },
        imageCheckChange: function(params) {
            this.$emit('update:selected_products', params);
        },
        productChecked: function (id) {
            let selectedProducts = this.$parent.cart.selectedProducts;
            for(let x=0; x < selectedProducts.length; x++) {
                if(selectedProducts[x].id == id) {
                    return true;
                }
            }
            return false;
        },
        currency: function() {
            return this.$parent.currency_symbol;
        }
    },
    computed: {
        productGroups () {
            return Array.from(Array(Math.ceil(this.products.length / this.productsPerRow)).keys())
        },
        productsPerRow() {
            return this.$parent.productsPerRow;
        },
        products() {
            const products = this.$parent.products;

            if(this.product_type == 'extra') {
                return products.filter(product => product.extra === true);
            }

            return products.filter(product => product.extra !== true);
         },
        cart() {
            return this.$parent.cart;
        }
    },
}
</script>
