<template>
    <div>
        <div v-if="step === this.cartSteps.PRODUCTS">
            <button
                :disabled="disabled"
                v-on:click="forward()"
                type="button"
                class="btn btn-primary btn-lg btn-block">Continue..</button>
        </div>

        <div v-else>
            <div class="row">
                <div class="col-sm-6">
                    <button
                        :disabled="disabled"
                        v-on:click="back()"
                        type="button"
                        class="btn btn-secondary btn-lg btn-block">Back..</button>
                </div>
                <div class="col-sm-6">
                    <button
                        :disabled="disabled"
                        v-on:click="forward()"
                        type="button"
                        class="btn btn-primary btn-lg btn-block">Continue..</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: ['step', 'disabled'],

    data: function() {
        return {
            currentFormIndex: 0,
        }
    },
    beforeCreate() {
        this.cartSteps = this.$parent.cartSteps;
    },
    watch: {
        currentFormIndex: function() {
            this.$emit('update:currentFormIndex', this.currentFormIndex);
        }
    },
    created() {

    },
    mounted() {

    },
    methods: {
        _calcNumOptionalProductSteps: function () {
            return this.$parent._getProductsWithOptionalProducts().length;
        },

        /**
         * Returns true if the step state is ok to navigate to
         */
        _stepValid: function(step) {
            switch(step){
                case this.cartSteps.PRODUCTS:
                    //There are always products, return ok
                    return true;
                case this.cartSteps.VARIANTS:
                    // if Selected products have variants, return ok
                    return this._calcNumOptionalProductSteps() > 0;
                case this.cartSteps.EXTRAS:
                    // if there are extra products available
                    const products = this.$parent.products;
                    return products.filter(product => product.extra === true).length > 0;
                case this.cartSteps.CUSTOM_FORM:
                    // There are custom forms available
                    return this.$parent.$data.formData.length > 0;
                case this.cartSteps.SUMMARY:
                    return true;
            }
        },

        /**
         * Forward --->>>>
         *
         *  Products selection
         *      Variants / Product options
         *          Extra Options
         *              Summary
         *                  Custom forms
         *                      Payment.
         */
        forward: function () {
            // From initial product selection
            if(this.step == this.cartSteps.PRODUCTS) {

                //If a selected product has options, then advance to the next product options/variant step
                if(this._stepValid(this.cartSteps.VARIANTS)) {
//@todo - here we need multiple product variant steps, like the custom form steps.
                    this.$emit('update:step', this.cartSteps.VARIANTS);
                    return;
                }

                if(this._stepValid(this.cartSteps.EXTRAS)) {
                    this.$emit('update:step', this.cartSteps.EXTRAS);
                    return;
                }

                if(this._stepValid(this.cartSteps.SUMMARY)) {
                    this.$emit('update:step', this.cartSteps.SUMMARY);
                    return;
                }

                if(this._stepValid(this.cartSteps.CUSTOM_FORM)) {
                    this.$emit('update:step', this.cartSteps.CUSTOM_FORM);
                    return;
                }

            }

            // From product variant selection
            else if(this.step == this.cartSteps.VARIANTS){

                //Navigate to next product options step
                if(this.$parent.optionsPageIdx < (this._calcNumOptionalProductSteps() - 1 )) {
                    // Got to the next product variant
                    this.$parent.optionsPageIdx++;
                    return;
                }

                if(this._stepValid(this.cartSteps.EXTRAS)) {
                    this.$emit('update:step', this.cartSteps.EXTRAS);
                    return;
                }

                if(this._stepValid(this.cartSteps.SUMMARY)) {
                    this.$emit('update:step', this.cartSteps.SUMMARY);
                    return;
                }

                if(this._stepValid(this.cartSteps.CUSTOM_FORM)) {
                    this.$emit('update:step', this.cartSteps.CUSTOM_FORM);
                    return;
                }

            }

            else if(this.step == this.cartSteps.EXTRAS) {
                if(this._stepValid(this.cartSteps.SUMMARY)) {
                    this.$emit('update:step', this.cartSteps.SUMMARY);
                    return;
                }
            }

            else if(this.step == this.cartSteps.SUMMARY) {
                if(this._stepValid(this.cartSteps.CUSTOM_FORM)) {
                    this.$emit('update:step', this.cartSteps.CUSTOM_FORM);
                    return;
                }
            }

            else if(this.step == this.cartSteps.CUSTOM_FORM) {

                // This is triggered when submitting a form.
                // It's asynchronous, so until the cart update has been completed we don't know where to navigate to.
                // All we can do is in the cart response look at the step.
                // If the step is === this.cartSteps.CUSTOM_FORM and the next form ID is null, then move onto the Payment/Create Order steop
                const formData = this.$parent.$refs['custom_form'].getCurrentFormsData();
                this.$emit('update:formData', {formData: formData, navigation_direction: 'forward'});
                return;
            }

        },

        /**
         * Back   <<<------
         */
        back: function () {

            if(this.step == this.cartSteps.CUSTOM_FORM) {

                // Get form data and resubmit it to the cart.
                // The cart response will need to guide navigation from here as there could be form validation errors.
                const formData = this.$parent.$refs['custom_form'].getCurrentFormsData();
                this.$emit('update:formData', {formData: formData, navigation_direction: 'backward'});

                // Can't navigate / update step from here. Has to be done in the form submit callback.
                // This is because if there are form errors we need to remain on the page to get the user to resolve
                // them before continuing.
            }

            if(this.step == this.cartSteps.SUMMARY) {

                if(this._stepValid(this.cartSteps.EXTRAS)) {
                    this.$emit('update:step', this.cartSteps.EXTRAS);
                    return;
                }

                //If a selected product has options, then advance to the next product options/variant step
                if(this._stepValid(this.cartSteps.VARIANTS)) {
                    this.$emit('update:step', this.cartSteps.VARIANTS);
                    return;
                }

                // Default back to initial product selection step if no other steps are suitable.
                this.$emit('update:step', this.cartSteps.PRODUCTS);
                return;
            }
            else if(this.step == this.cartSteps.EXTRAS) {
                //If a selected product has options, then advance to the next product options/variant step
                if(this._stepValid(this.cartSteps.VARIANTS)) {
                    this.$emit('update:step', this.cartSteps.VARIANTS);
                    return;
                }

                // Default back to initial product selection step if no other steps are suitable.
                this.$emit('update:step', this.cartSteps.PRODUCTS);
                return;
            }

            else if(this.step == this.cartSteps.VARIANTS) {
                this.$emit('update:step', this.cartSteps.PRODUCTS);
                return;
            }
        }
    }
}
</script>
