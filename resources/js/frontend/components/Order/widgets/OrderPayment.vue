<template>
    <div id="payment-page">
        <div class="row">
            <div class="col-md-12">
                <h2>Order Payment</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <h3>Paypal: </h3>
            </div>
            <div class="col-sm-9">
                <button>Pay here!</button>
                <p>I guess that this page shouldn't be here.<br />
                    It should <br />
                    1, create the order <br />
                    2, redirect to the customers account via a login token where they can pay and view bank payment details<br />
                </p>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    data: function () {
        return {

        }
    },
    components: {

    },
    mounted() {
        console.log('Payment mounted.')
    },
    methods: {

    },
    computed: {

    }

}
</script>
<style>
#payment-page .package-list {
    padding-inline-start: 0;
    list-style: none;
}
</style>
