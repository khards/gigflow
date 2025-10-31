<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    Form editor
                </x-slot>

                <x-slot name="body">
                    <div>Form name: <input wire:model="form.name" /></div>
                    <hr />
                    <form wire:ignore id='form-builder-editor' autocomplete='off'></form>
                </x-slot>
            </x-frontend.card>
        </div><!--col-md-10-->
    </div><!--row-->
</div><!--container-->

@push('after-scripts')
<script>
    function do_form_builder(myFormData) {
        var formBuilderOptions = {
            defaultFields: myFormData,
            disabledActionButtons: ['data'],
            onSave: function(evt, formData) {
                window.livewire.emit('formSave', formData);
            }
        };
        var formRenderOptions = {
            formData: myFormData
        };

        let formBuilderElement = $('#form-builder-editor');
        window.formBuilder = formBuilderElement.formBuilder(formBuilderOptions);

        $('form').attr('autocomplete', 'off');
    }
    let myFormData = {!! $form->data !!};
    do_form_builder(myFormData);
 </script>
@endpush
