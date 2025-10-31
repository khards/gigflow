<template>
    <div>
        <div id="form-builder-editor"></div>
    </div>
</template>

<script>
export default {
    props: {
        current_form_data: null,
    },
    data: function() {
        return {
            form: null,
        };
    },
    methods: {
        getCurrentFormsData: function () {
            const formData = this.form.userData;
            const json = formData;//window.JSON.stringify();
            return json;
            // return window.JSON.parse(json);
        }
    },
    watch: {
        current_form_data: {
            handler() {

                // Load next form
                var formRenderOptions = {
                    formData: this.current_form_data
                };

                let formBuilderElement = $('#form-builder-editor');

                //Form renderer
                let formRender = formBuilderElement.formRender(formRenderOptions);

                //Store for 'Ron
                this.form = formRender;


                // Validation errors.
                // These need to be highlighted after render, else they would be applied to the old form then rednerd over.

            }
        }
    },
    mounted() {
        console.log('Component Custom mounted.')

        // this.$attrs.current_form_data

        var formBuilderOptions = {
            defaultFields: this.current_form_data
        };

        var formRenderOptions = {
            formData: this.current_form_data
        };

        let formBuilderElement = $('#form-builder-editor');

        //Form builder
        //let formBuilder = formBuilderElement.formBuilder(formBuilderOptions);

        //Form renderer
        let formRender = formBuilderElement.formRender(formRenderOptions);

        //
       /// Wrong!!! formRender.userData(this.current_form_data);

        //Store for 'Ron
        this.form = formRender;

        //Get data...
//        formBuilderElement.userData();

    },
}
</script>
