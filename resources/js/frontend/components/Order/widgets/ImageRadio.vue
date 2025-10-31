<!-- unused -->
<template>
    <div class="col-sm-4 image-radio">
        <div v-if="infoHref" class="info-container"><a :href="infoHref" target="_blank" class="cil-info" style="color: white"></a> </div>

        <div class="thumbnail p-1">
            <!-- v-on:click.stop.prevent=""  -->
            <a v-on:click="click()" v-on:click.stop.prevent="preventDefault" class="btn-info btn btn-lg active" href="#">

                <h4 class="caption">{{title}} {{price}}
                    <input class="" type="radio" :id="id" :name="name" :value="value" v-bind:checked="curr_state">
                </h4>
                <img class="img-thumbnail" :src="imageUrl"  style="min-height: 100px; min-width: 100px"/>
            </a>
            <div class="text-justify radio-select-description">{{ description }}</div>
        </div>

    </div>
</template>

<script>
export default {
    props: ['infoHref', 'imageUrl', 'title', 'price', 'name', 'value', 'checked', 'description', 'id'],

    data: function() {
        return {
            curr_state: null,
        }
    },
    watch: {
        curr_state: function() {
            this.$emit('update:ImageCheckChange', this.id);
        }
    },
    methods: {
        preventDefault: function (event) {
            this.curr_state = true;
            event.preventDefault();
        },
        click: function (value) {
            this.curr_state = true;
        }
    },
    mounted() {
        this.curr_state = this.checked;
    },
    computed: {
    }
}
</script>

<style>
.image-radio img {
    width: 280px;
    height: 256px;
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
    position: absolute;
    width: 50px;
    height: 50px;
    left: 25px;
    bottom: 2px;
    text-align: center;
}

</style>
