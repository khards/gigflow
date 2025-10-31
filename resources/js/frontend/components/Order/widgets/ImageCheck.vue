<template>
    <div class="col-md-4 image-select">
        <div class="thumbnail p-1">
            <a v-on:click="check()" v-on:click.stop="preventHandler" class="btn-info btn btn-lg active" href="#">
                <div>
                    <h4 class="caption">{{title}} {{price}}
                        <input type="checkbox" :id="id" :name="name" :value="value" v-model="checkedState">
                    </h4>
                    <img class="img-thumbnail" :src="imageUrl" style="min-height: 100px; min-width: 100px"/>
                </div>

                <div v-if="infoHref" class="info-container">
                    <div class="info-container-bg">
                        <a :href="infoHref" target="_blank" class="cil-info" style="color: white"></a>
                    </div>
                </div>

            </a>
        </div>
    </div>
</template>

<script>
export default {
    props: ['imageUrl', 'title', 'price', 'name', 'value', 'checked', 'infoHref', 'id', 'force'],
    data () {
        return {
            checkedState: null,
        }
    },
    methods: {
        check: function () {
            if(this.force == true) {
                this.checkedState = true;
            } else {
                this.checkedState = !this.checkedState;
            }
            this.$emit('update:ImageCheckChange', {checked:this.checkedState, id:this.id})
        },

        preventHandler:function(event) {

            //Prevent anything changing the status when we are forcing the value,
            // check() sets the checkstate to true.
            if(this.force == true) {
                event.preventDefault();
                return true;
            }
        },
    },
    mounted() {
        this.checkedState = this.checked;
    }
}
</script>

<style>
.image-select {

}
.image-select img {
    /*width: 280px;*/
    /*height: 256px;*/
}
.image-select input[type=checkbox] {
    width: 22px;
    height: 22px;
    vertical-align: bottom;
}
.image-select .info-container {
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
