<template>
    <div style="max-width: 750px">
        <div v-if="this.paymentRequired">
            <div style="border: 1px dashed rgb(221, 221, 221);background: #fbfbfb;">
                <h2>Thank you for your booking.</h2>
                <p style="margin-bottom: 0;">To ensure that your booking is secured, please ensure that you have paid your deposit or full amount.</p>
            </div>
            <h2>Select payment:</h2>
            <ul class="list-group">
                <li style="cursor: pointer;font-size: 22px" v-for="item in items" :key="item.id" @click="select(item.id)" :class="selectedType === item.id ? 'list-group-item active font-weight-bold':'list-group-item'">
                    <p>{{item.text}}</p>
                </li>
            </ul>

            <div class="panel panel-default p-4">
                <div>
                    <h2 class="panel-body">{{ currentStatus }}
                        <input v-if="this.selectedType == 'other'" v-model="other" type="number" min="0.00" max="90000.00" step="0.01" />
                        <div v-if="this.selectedType == 'bank'">

                            <div class="container mt-1">
                                <div class="row">
                                    <div class="col-sm">Bank Name</div>
                                    <div class="col-sm">{{ bank.name}}</div>
                                </div>

                                <div class="row">
                                    <div class="col-sm">Account</div>
                                    <div class="col-sm">{{ bank.account}}</div>
                                </div>

                                <div class="row">
                                    <div class="col-sm">Sort Code</div>
                                    <div class="col-sm">{{ bank.sortcode}}</div>
                                </div>

                                <div class="row">
                                    <div class="col-sm">Reference</div>
                                    <div class="col-sm">{{reference}}</div>
                                </div>
                            </div>

                        </div>
                    </h2>
                </div>
            </div>
            <div :class="this.selectedType !== 'bank' ? 'd-block':'d-none'" id="paypal-button-container" class="text-center"></div>
        </div>

        <div v-else>
            <div style="border: 1px dashed rgb(221, 221, 221);background: #fbfbfb;">
                <h2>Thank you for your payment</h2>
                <hr>
                <h3>You have been send a receipt by email.</h3>
                <p>If making a new booking you will have also been sent a booking confirmation by email.</p>
                <p><b>If you can't find the emails, please check your junk or spam folder</b></p>
            </div>
        </div>
    </div>
</template>


<script>
import { loadScript } from '@paypal/paypal-js';

export default ({
    data: function() {
        return {
            items: [
                {id: 'deposit', text: '> Pay deposit: ' + window.payment_config.currency_symbol_html  + '' + window.payment_config.deposit},
                {id: 'full', text: '> Pay in full: ' + window.payment_config.currency_symbol_html + '' + window.payment_config.amount},
                {id: 'other', text: '> Pay other amount'},
                {id: 'bank', text: '> Pay by bank transfer'},
            ],
            status: '',
            other: '0.00',
            selectedType : 'deposit',
            displayCategory: false,
            paypalSdk: null,
            bank: '',
            reference: '',
            paymentRequired: true,
        }
    },

    async mounted() {
        this.bank = window.payment_config.bank;
        this.reference = window.payment_config.reference;
        window.paypalSdk = await loadScript({
            'client-id': window.payment_config.clientId,
            currency: window.payment_config.currency,
        });

        this.initPaypal();
    },
    computed: {
        currentStatus: function() {
            switch(this.selectedType) {
                case 'deposit':
                    this.status = 'Make deposit payment of: ' + window.payment_config.deposit + ' ' + window.payment_config.currency;
                    break;

                case 'full':
                    this.status = 'Make full payment of: ' + window.payment_config.amount + ' ' + window.payment_config.currency;
                    break;

                case 'other':
                    this.status = 'Enter Amount';
                    break;

                case 'bank':
                    this.status = 'Details:';
                    break;
            }
            return this.status;
        }
    },
    methods: {
        select: function (item_id) {
            this.selectedType = item_id;
        },
        createOrder: function (data, actions) {
            let amount = 0;

            switch(this.selectedType) {
                case 'deposit':
                    amount = window.payment_config.deposit;
                    break;

                case 'full':
                    amount = window.payment_config.amount;
                    break;

                case 'other':
                    amount = this.other;
                    break;
            }

            return actions.order.create({
                purchase_units: [{
                    soft_descriptor: window.payment_config.descriptor, // 22 max
                    reference_id: window.payment_config.order_id,//256 max
                    description: window.payment_config.description, //127 max
                    custom_id: window.payment_config.order_id, //127 max

                    amount: {
                        value: amount
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            const parent = this;
            return actions.order.capture().then(function(details) {
                parent.status = 'Transaction completed';
                parent.paymentRequired = false;
            });
        },
        onCancel: function(date) {
            this.status = "Please try again.";
        },
        initPaypal() {
            window.paypalSdk.Buttons({
                createOrder: this.createOrder,
                onApprove: this.onApprove,
                onCancel: this.onCancel,
            }).render('#paypal-button-container');
        }
    }
});

</script>
