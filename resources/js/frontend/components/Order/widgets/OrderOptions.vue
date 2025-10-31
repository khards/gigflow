<template>
    <div>
        <h2>{{ parent_product_name }} Options</h2>
        <div class="row p-1" v-for="groupId in productGroups">

            <div v-for="product in getProductGroup(groupId)" class="col-sm-4 image-radio">
                <div class="thumbnail p-1">
                    <a v-on:click="linkClick(product.id)" class="btn-info btn btn-lg active" href="#">
                        <div>
                            <h4 class="caption">{{product.title}} {{ currency() }}{{product.price}}
                                <input class="" type="radio" :id="product.id" name="optionRadio" :value="product.id" v-model="current_selection">
                            </h4>
                            <img class="img-thumbnail" :src="product.image"  style="min-height: 100px; min-width: 100px"/>
                        </div>
                    </a>
                    <div v-if="product.url" class="info-container">
                        <div class="info-container-bg">
                            <a :href="product.url" target="_blank" class="cil-info" style="color: white"></a>
                        </div>
                    </div>
                </div>
                <div class="text-justify radio-select-description">{{ product.description }}</div>
            </div>
        </div>
    </div>
</template>

<script>


export default {
    props: ['name', 'page', 'parent_product_id'],
    components:{

    },
    data: function() {
        return {
            current_selection: null,
        }
    },
    watch: {
        current_selection: function() {

            //Array containing the selected and deselected products
            let selectOptions = [];
            for(const product of this.products) {
                selectOptions.push({id: product.id, checked: (this.current_selection == product.id)});
            }

            this.$emit('update:product_options', selectOptions);
        }
    },
    methods: {
        linkClick: function(id) {
            this.current_selection = id;
        },
        getProductGroup: function(groupId) {
            return this.products.slice(groupId * this.productsPerRow, (groupId + 1) * this.productsPerRow);
        },
        productChecked: function (id) {
            //Search all selected products for id
            let selectedProducts = this.$parent.cart.selectedProducts;
            for(let x=0; x < selectedProducts.length; x++) {
                if(selectedProducts[x].id == id) {
                    return true;
                }
            }
            return false;
        },
        _parent_product: function() {
            for(let x = 0; x < this.$parent.products.length; x++) {
                if(this.$parent.products[x].id == this.parent_product_id) {
                    return this.$parent.products[x];
                }
            }
        },
        currency: function() {
            return this.$parent.currency_symbol;
        }
    },
    computed: {
        parent_product_name() {
            return this._parent_product().title;
        },
        productGroups() {
            return Array.from(Array(Math.ceil(this.products.length / this.productsPerRow)).keys())
        },
        productsPerRow() {
            return this.$parent.productsPerRow;
        },
        products() {
            return this._parent_product().products;
        },
        cart() {
            return this.$parent.cart;
        },
    },
    mounted() {

        // Restore selected products
        for(const groupId in this.productGroups) {
            for(const product of this.getProductGroup(groupId)) {
                if(this.productChecked(product.id)) {
                    this.current_selection = product.id;
                    break;
                }
            }
        }

        // We have a selected option so carry on.
        if(this.current_selection !== null) {
            return;
        }

        // We have no default selected option, so select the first product.
        this.current_selection = this.products[0].id;
    },
}
</script>

<style>
.image-radio img {
    /*width: 280px;*/
    /*height: 256px;*/
}
.image-radio input[type=radio] {
    width: 22px;
    height: 22px;
    vertical-align: bottom;
}
.image-radio .info-container {
    color: white;
    margin-left: 5px;
    font-size: 38px;
    background: #23200173;
    border-radius: 13px;
    position: relative;
    width: 50px;
    height: 0px;
    left: 0px;
    bottom: 56px;
    text-align: center;
}
.info-container-bg {
    background: #23200173;
    border-radius: 13px;
    height: 50px;
}
.image-select .info-container a {
    text-decoration: none;
    cursor: pointer;
}

</style>

