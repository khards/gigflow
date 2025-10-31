<?php

namespace App\Http\Livewire\Forms;

use App\Booking\Business;
use App\Domains\Form\Contracts\FormManager;
use App\Domains\Form\Models\Form;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class FormLogic extends Component
{
    use AuthorizesRequests;

    /**
     * @var Form
     */
    public $form;

    /**
     * @var Collection
     */
    public $forms;

    /**
     * @var Business
     */
    public $business;

    /**
     * @var string - The form action
     */
    public $form_action = 'logic';

    public $bind_logic = [];

    /**
     * Validation rules, required for each wire:model.
     */
    protected $rules = [
        'form.required' => 'required|bool',                         // required 1 or 0
        'form.action.type' => 'string|null',                        // empty or 'next' (show next form)

        'form.action.logic_question_name' => 'string|null',         // Question name to perform logic on
        'form.action.logic_response' => 'string|null',              // Unused?
        'form.action.logic_form.*' => 'string|null',                // When "Skip to next form" not based on response, this is the form to skip to.

        'bind_logic.*'  => 'string|null',  /*             'logic' => array (
                                                                'wedding' => '20',
                                                                'civil ceremony' => '20',
                                                                'birthday' => '21',
                                                                'corporate' => '45',
                                                                'other' => '45',
                                                            ),
*/
    ];

    /**
     * Event listeners.
     *
     * @var string[]
     */
    protected $listeners = [
        'FormLogic:show' => 'show',
        'FormLogic:hide' => 'hide',
        'FormLogic:save' => 'save',
    ];

    public $visible = false;

    public function mount(FormManager $formManager)
    {
        $this->authorize('create', [Form::class, $this->business]);

        $this->forms = $formManager->all($this->business->id);
    }

    public function getQuestionsProperty()
    {
        $this->authorize('create', [Form::class, $this->business]);

        if (! $this->form) {
            return collect([]);
        }

        return $this->form->getQuestionsWithFixedResponses();
    }

    public function getResponseValuesProperty()
    {
        $this->authorize('create', [Form::class, $this->business]);

        if (! $this->form) {
            return collect([]);
        }

        if (! isset($this->form->action['logic_question_name'])) {
            return collect([]);
        }

        return $this->form->getResponseValuesForQuestion($this->form->action['logic_question_name']);

        return collect([]);
    }

    public function show(FormManager $formManager, $formId)
    {
        $this->authorize('create', [Form::class, $this->business]);
        $this->form = $formManager->read($formId);
        $this->visible = true;

        $this->bind_logic = isset($this->form->action['logic']) ? $this->form->action['logic'] : [];
    }

    public function hide()
    {
        $this->visible = false;
    }

    public function save(FormManager $formManager)
    {
        $this->authorize('create', [Form::class, $this->business]);

        $cloned = clone $this->form->action;

        $cloned['logic'] = $this->bind_logic;

        $formManager->update($this->form, [
            'action' => $cloned,
        ]);

        $this->visible = false;
    }

    public function render()
    {
        $css = $this->visible ? 'display:block' : 'display:none';

        return view('frontend.user.forms.logic', ['css' => $css]);
    }
}
