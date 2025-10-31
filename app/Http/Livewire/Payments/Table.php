<?php
// @TODO AUTH!

namespace App\Http\Livewire\Payments;

use App\Domains\Order\Order;
use App\Domains\Payment\Models\Transaction;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class Table extends Component
{
    use AuthorizesRequests;

    public Order $order;

    public Collection $transactions;

    public int $confirming = 0;

    protected $rules = [
        'transactions.*.amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        'transactions.*.order_id' => 'required|numeric',
        'transactions.*.note' => 'required|string',
        'transactions.*.method' => 'required|string',
        'transactions.*.currency' => 'required|string',
        'transactions.*.details' => 'required|array',
    ];

    protected $listeners = [];

    public function mount($order): void
    {
        $this->order = $order;
        $this->transactions = $order->transactions;
    }

    public function add() {

        $transaction = new Transaction();

        //Fix livewire add new model to collection,
        // "Queueing collections with multiple model connections is not supported." error
        $transaction->setConnection(env('DB_CONNECTION'));

        $transaction->id = null;
        $transaction->order_id = $this->order->id;
        $transaction->method = 'other';
        $transaction->currency = $this->order->business->currency;
        $transaction->details = ['info' => 'Manual Payment'];
        $transaction->note = '';
        $transaction->amount = 0.00;
        $this->transactions->push($transaction);
    }

    //Fix livewire add new model to collection, "Queueing collections with multiple model connections is not supported." error
    public function hydrate() {
        foreach($this->transactions as $transaction) {
            $transaction->setConnection(env('DB_CONNECTION'));
        }
    }

    public function render()
    {
        $order = $this->order;

        return view('frontend.user.payments.table', ['order' => $order]);
    }

    public function save() {
        $this->transactions->each(function ($item, $key) {
            $item->save();
        });
        $this->emit('childUpdated');
    }

    public function reload() {
        $this->transactions = $this->order->transactions;
    }

    public function confirmDelete(int $id)
    {
        $this->confirming = $id;
    }

    public function kill(int $id)
    {
        $transaction = Transaction::findOrFail($id);
        $this->authorize('delete', $transaction);
        $transaction->delete();
        $this->transactions = $this->order->transactions()->get();
        $this->emit('childUpdated');
    }
}
