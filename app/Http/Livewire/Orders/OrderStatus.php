<?php

namespace App\Http\Livewire\Orders;

use App\Booking\Contracts\OrderManager;
use App\Domains\Order\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * Class OrderStatus.
 */
class OrderStatus extends Component
{
    use AuthorizesRequests;

    protected array $rules = ['order.status' => 'string'];

    public Order $order;

    public function updated(string $key, string $value)
    {
        if ($key === 'order.status') {
            $this->authorize('update', [Order::class, $this->order]);
            $this->orderManager()->updateStatus($this->order, $value);
        }
        $this->emit('Order:updated');
    }

    /**
     * Render select widget.
     *
     * @return string
     */
    public function render()
    {
        return view('frontend.user.orders.includes.status-select', ['order' => $this->order]);
    }

    /**
     * Get the order manager.
     *
     * (Livewire 2.2 can't DI into action.)
     *
     * @return OrderManager
     */
    private function orderManager()
    {
        return resolve(OrderManager::class);
    }
}
