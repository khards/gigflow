<?php

namespace App\Http\Livewire\Forms;

use App\Booking\Business;
use App\Domains\Form\Contracts\FormManager;
use App\Domains\Form\Models\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class FormsTable.
 */
class FormsTable extends DataTableComponent
{

    use AuthorizesRequests;

    /**
     * @var string
     */
    public $sortField = 'name';

    /**
     * @var Business
     */
    public $business;

    /**
     * Event listeners.
     *
     * @var string[]
     */
    protected $listeners = ['Form:created' => '$refresh'];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        $this->authorize('create', [Form::class, $this->business]);

        return $this->business->forms()->getQuery();
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Id'))
                ->searchable()
                ->sortable(),

            Column::make(__('Name'))
                ->searchable()
                ->sortable(),

            Column::make(__('Actions'), 'id')
                ->label(function ($model, $column) {
                    //dd($model);
                    return view('frontend.user.forms.includes.actions', ['model' => $model]);
                }),
        ];
    }

    public function edit($formId)
    {
        $form = Form::findOrFail($formId);

        $this->authorize('view', $form);

        return redirect()->route('frontend.user.form.edit', $form->id);
    }

    public function delete(FormManager $formManager, $formId)
    {
        $form = Form::findOrFail($formId);

        $this->authorize('delete', $form);
        
        $formManager->delete($form);
    }

}
