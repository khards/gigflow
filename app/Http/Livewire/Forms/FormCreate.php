<?php

namespace App\Http\Livewire\Forms;

use App\Booking\Business;
use App\Domains\Form\Contracts\FormManager;
use App\Domains\Form\Models\Form;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * Class FormsTable.
 */
class FormCreate extends Component
{
    use AuthorizesRequests;

    /**
     * @var Business
     */
    public $business;

    public function create(FormManager $formManager)
    {
        $this->authorize('index', [Form::class, $this->business]);

        $formManager->create($this->business, ['name' => 'New form']);

        $this->emit('Form:created');
    }

    public function render()
    {
        return <<<'blade'
            <div>
            <button
                    wire:click="create"
                    type="submit"
                    class="float-right btn btn-pill btn-success">
                    <span class="cil-plus btn-icon mr-2"></span>Create</button>

            </div>
        blade;
    }
}
