<?php

namespace App\Http\Livewire\Schedules;

use App\Booking\Business;
use App\Booking\Availability\Schedule;
use App\Domains\Order\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class SchedulesTable.
 */
class SchedulesTable extends DataTableComponent
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

    public int $confirming = 0;

    /**
     * Event listeners.
     *
     * @var string[]
     */
    protected $listeners = ['Schedule:created' => '$refresh'];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function confirmDelete(int $id)
    {
        $this->confirming = $id;
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        $this->authorize('create', [Schedule::class, $this->business]);

        return $this->business->schedule()->getQuery();
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

            Column::make(__('Summary'))
                ->searchable()
                ->sortable(),

            Column::make('Type', 'rrule')//->isHidden(),
                ->format(function ($str, $schedule, $column) {
                    return $schedule->rrule ? 'Scheduled' : 'Special';
                })->sortable(),

            Column::make(__('Actions'), 'id')
                ->label(function ($model, $column) {
                    //dd($model);
                    //return view('frontend.user.forms.includes.actions', ['model' => $model]);

                    return view('frontend.user.schedules.includes.actions', [
                        'model' => $model,
                        'confirming' => $this->confirming,
                    ]);
                }),
//                ->searchable(function (Builder $builder, string $value){
//                    //dd(str_contains('scheduled', strtolower($value)));
//                    //dd($builder->toSql());
//                    if (str_contains('scheduled', strtolower($value))) {
//                        $b = $builder->whereNotNull('rrule');
//                        dd($b->toSql(), $b->getBindings());
//                    } elseif (str_contains('special', strtolower($value))) {
//                        return $builder->whereNull('rrule');
//                    }
//                })


        ];
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);

        $this->authorize('view', $schedule);

        return redirect()->route('frontend.user.schedule.edit', $schedule->id);
    }

    public function kill($id)
    {
        $schedule = Schedule::findOrFail($id);

        $this->authorize('delete', $schedule);

        $schedule->delete();
    }

//
//    public function edit($formId)
//    {
//        $form = Form::findOrFail($formId);
//
//        $this->authorize('view', $form);
//
//        return redirect()->route('frontend.user.form.edit', $form->id);
//    }
//
//    public function delete(FormManager $formManager, $formId)
//    {
//        $form = Form::findOrFail($formId);
//
//        $this->authorize('delete', $form);
//
//        $formManager->delete($form);
//    }

}
