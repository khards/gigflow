<?php

namespace App\Http\Livewire\Forms;

use App\Domains\Form\Contracts\FormManager;
use App\Domains\Form\Models\Form;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class FormStatus extends Component
{
    use AuthorizesRequests;

    public $business;

    public $form;

    public $status;

    public function updated()
    {
        $this->authorize('index', [Form::class, $this->business]);

        $this->formManager()->update($this->form, [
            'status' => $this->status,
        ]);

        $this->emit('Form:updated');
    }

    /**
     * @param  string  $status
     */
    public function mount(): void
    {
        $this->status = $this->form->status;
    }

    public function render()
    {
        return <<<'blade'
            <select wire:model='status'>?
                <option value='1'>Enabled</option>
                <option value='0'>Disabled</option>
            </select>
        blade;
    }

    /**
     * Get the form manager.
     *
     * (Livewire 2.2 can't DI into action.)
     *
     * @return FormManager
     */
    private function formManager(): FormManager
    {
        return resolve(FormManager::class);
    }
}
