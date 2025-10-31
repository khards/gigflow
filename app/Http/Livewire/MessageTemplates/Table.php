<?php

namespace App\Http\Livewire\MessageTemplates;

use App\Booking\Business;
use App\Domains\Email\Models\MailTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class Table extends DataTableComponent
{
    use AuthorizesRequests;

    /**
     * @var string
     */
    public $sortField = 'mailable';

    /**
     * @var Business
     */
    public $business;

    /**
     * Event listeners.
     *
     * @var string[]
     */
  //  protected $listeners = ['Form:created' => '$refresh'];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    public function builder(): Builder
    {
        $this->authorize('index', [MailTemplate::class, $this->business]);

        return $this->business->mailables()->getQuery();
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make('Type', 'mailable')
                ->label(function (MailTemplate $model) {
                    $bits = explode("\\", $model->mailable);
                    $name = end($bits);
                    return Str::headline($name);
                })
                ->searchable(function (Builder $builder, string $value){
                    $builder->where('mailable', 'like', "%{$value}%");
                })
                ->sortable(),

            Column::make(__('Actions'))
                ->label(function (MailTemplate $model) {
                    return view('frontend.user.message-templates.includes.actions', ['model' => $model]);
                }),
        ];
    }

    public function edit($modelId)
    {
        $messageTemplate = MailTemplate::findOrFail($modelId);

        $this->authorize('view', [MailTemplate::class, $messageTemplate]);

        return redirect()->route('frontend.user.message-template.edit', $messageTemplate->id);
    }

}
