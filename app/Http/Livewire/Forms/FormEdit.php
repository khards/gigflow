<?php

namespace App\Http\Livewire\Forms;

use App\Domains\Form\Models\Form;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class FormEdit extends Component
{
    use AuthorizesRequests;

    public Form $form;

    public $name;

    public $form_action = 'logic';

    protected $rules = [
        'form.name' => 'required|string|max:50000',
    ];

    protected $listeners = [
        'formSave' => 'formSave',
    ];

    public function mount($form): void
    {
        $this->form = $form;
        $this->name = $form->name;
    }

    /**
     * Save form and redirect back to the business screen.
     */
    public function formSave($data)
    {
        $this->form->data = $data;

        $this->form->save();

        return redirect()->to('/business/'.$this->form->owner->id.'/forms/');
    }

    public function render()
    {
        $form = $this->form;

        return view('frontend.user.forms.edit-livewire', compact(['form']));
    }
}
