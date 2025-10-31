<?php

namespace App\Http\Livewire\Orders;

use App\Booking\Business;
use App\Booking\Contracts\OrderManager;
use App\Domains\Order\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

/**
 * Class OrdersTable.
 */
class OrdersTable extends DataTableComponent
{
    use AuthorizesRequests;

    public $confirming;

    /**
     * @var string
     */
    public $sortField = 'start';

    /**
     * @var Business
     */
    public $business;

    /**
     * Event listeners.
     *
     * @var string[]
     */
    protected $listeners = ['Order:created' => '$refresh'];

    public function configure(): void
    {
        $this->setPrimaryKey('id');
    }

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        $this->authorize('index', [Order::class, $this->business]);

        return $this->business->orders()->getQuery();
    }

    /**
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::make(__('Id'))//Number
                ->searchable()
                ->sortable(),

            Column::make(__('Status'))
                ->format(function (\App\Domains\Order\OrderStatus $orderStatus, Order $model) {
                    return view(
                        'frontend.user.orders.includes.status',
                        [
                            'model' => $model,
                            'business' => $this->business,
                            'confirming' => $this->confirming,
                        ]
                    );
                }),

            Column::make(__('Start'))
                ->format(function (string $value, Order $model) {
                    return $model->start;
                }),

            Column::make(__('End'))
                ->format(function (string $value, Order $model) {
                    return $model->end;
                }),

            Column::make(__('Location'))
                ->format(function (string $location, Order $model) {
                    return $model->location;
                }),

            Column::make(__('Actions'))
                ->label(function (Order $model, \Rappasoft\LaravelLivewireTables\Views\Column $column) {
                    return view('frontend.user.orders.includes.actions', [
                        'model' => $model,
                        'confirming' => $this->confirming,
                    ]);
                }),
        ];
    }

    public function edit($orderId)
    {
        $order = Order::findOrFail($orderId);

        $this->authorize('view', $order);

        return redirect()->route('frontend.user.order.edit', $order->id);
    }

    /**
     * @param $id
     */
    public function confirmDelete($id)
    {
        $this->confirming = $id;
    }

    // OrderManager $orderManager,
    public function kill($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('delete', $order);
        $this->orderManager()->delete($order);
    }

    /**
     * Get the order manager.
     *
     * (Livewire 2.2 can't DI into action.)
     *
     * @return OrderManager
     */
    private function orderManager(): OrderManager
    {
        return resolve(OrderManager::class);
    }
}
