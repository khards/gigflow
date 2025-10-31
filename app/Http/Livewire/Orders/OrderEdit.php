<?php

namespace App\Http\Livewire\Orders;

use App\Domains\Order\Models\OrderNotes;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * Class OrderEdit.
 */
class OrderEdit extends Component
{
    use AuthorizesRequests;

    public $order;
    public $ordernotes;

    protected $rules = [
        'order.location' => 'required|string|max:50000',
        'order.start' => 'required|datetime',
        'order.end' => 'required|datetime',
        'ordernotes' => 'string',
    ];

    protected $listeners = [
        'orderSave' => 'orderSave',
        'childUpdated' => 'childUpdated',
    ];

    public function childUpdated() {
        $this->order->refresh();
    }

    public function mount($order): void
    {
        $this->order = $order;
        $n = $this->order->notes()->first();
        $this->ordernotes = $n?->value ?? '';
    }

    public function getBookingsProperty()
    {
        return $this->order->bookings->all();
    }

    public function getOrderItemsProperty()
    {
        return $this->order->items->all();
    }

    public function updated(string $key, string $value) {

        switch ($key) {
            case 'ordernotes':
                if($note = $this->order->notes()->first()) {
                    $note->value = $value;
                    $note->save();
                } else {
                    $note = OrderNotes::make(['value' => $value]);
                    $this->order->notes()->save($note);
                }

            break;
        }

    }
    /**
     * Save order and redirect back to the business screen.
     */
    public function orderSave($data)
    {
        //$this->order->data = $data;

        $this->order->save();

        return redirect()->to('/business/'.$this->order->business_id.'/orders/');
    }

    public function render()
    {
        $order = $this->order;

        return view('frontend.user.orders.edit-livewire', compact(['order']));
    }
}
