@push('after-scripts')
<style>
    .form_logic input[type=radio] {
        width: 22px;
        height: 22px;
    }
    .claz {
        vertical-align: sub;
        margin-left: 10px;
    }
    </style>
@endpush

<div>
    <div
        style="{{ $css }}"
        class="modal"
        id="exampleModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="exampleModalLabel">

        <div class="modal-dialog" role="document" style="max-width: 560px">
            <div class="modal-content">

            <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{$this->form?->name}} - Logic</h5>

                    <button
                        wire:click="$emit('FormLogic:hide')"
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label for="selectAction">Form is required when booking?</label>

                        <select id="selectAction" class="form-control" wire:model="form.required">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <hr />

                    <fieldset class="form-group">
                        <legend class="col-form-label">Save responses and then:</legend>

                        <div class="form_logic form-check">
                            <input wire:model="form.action.type" value="" class="form-check-input" id='form.action.type' name="type" type="radio" />
                            <label class="claz form-check-label" for="form.action.type">No additional action</label>
                        </div>

                        <div class="form_logic form-check pt-2">
                            <input wire:model="form.action.type" value="next" class="form-check-input" id='form.action.type.next' name="type" type="radio" />

                            <label class="claz form-check-label" for="form.action.type.next">Show next form:</label>

                            <select wire:model="form.action.logic_form">
                                @foreach($forms as $optform)
                                    <option value='{{ $optform->id }}'>{{ $optform->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form_logic form-check pt-2">

                            <input wire:model="form.action.type" value="logic" name="type" type="radio" id='form.action.type.logic' class="form-check-input"/>

                            <!-- Selected question -->
                            <label class="claz form-check-label" for="form.action.type.logic">If</label>

                            <select style="vertical-align: bottom;" wire:model="form.action.logic_question_name" id="form.action.logic_question_name">
                                <option value=''> - select -</option>

                                @foreach($this->questions as $question)
                                    <option value='{{ $question->name }}'>{{ $question->label}}</option>
                                @endforeach
                            </select>


                            <div class="form-check">
                                @if($this->responseValues !== null)
                                    @foreach($this->responseValues as $responseValue)
                                        @if($responseValue !== null)
                                        <div class="row">
                                            <div class="col-sm-4">Response: {{ $responseValue->label }}</div>
                                            <div class="col-sm-8">
                                                <select wire:model="bind_logic.{{$responseValue->value}}">
                                                    <option value=''>No additional action</option>
                                                    @foreach($forms as $optform)
                                                        <option value='{{ $optform->id }}'>{{ $optform->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                        </div>
                    </fieldset>

                </div>
                <div class="modal-footer">
                    <button
                        wire:click="$emit('FormLogic:hide')"
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">Cancel</button>

                    <button
                        wire:click="$emit('FormLogic:save')"
                        type="button"
                        class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
