/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('../bootstrap');
require('../plugins');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('schedule-component', require('./components/ScheduleComponent.vue').default);
Vue.component('recurrence', require('./components/RecurrenceComponent.vue').default);
Vue.component('product-editor', require('./components/ProductEditor/ProductEditor.vue').default);
Vue.component('paypal-buttons', require('./components/Payments/Paypal.vue').default);


// Components that are external to the app
Vue.component('order-widget', require('./components/Order/OrderWidget').default);


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});


/******************************************************************************************************/

///My account / calendar integration
window.copyOutgoingUrl = function() {
    /* Get the text field */
    var copyText = document.getElementById("outgoing_calendar_url");

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /*For mobile devices*/

    /* Copy the text inside the text field */
    document.execCommand("copy");
}
