<?php

namespace App\Policies;

use App\Domains\Auth\Models\User;
use App\Domains\Payment\Models\Transaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user) {
        return $user->hasAllAccess() ? true : false;
    }

    // View all
    public function index(User $user, Transaction $transaction) {
        $validBusinesses = $user->businesses()->get();
        return $validBusinesses->find($transaction->order->business);
    }

    public function view(User $user, Transaction $transaction) {
        $validBusinesses = $user->businesses()->get();
        return $validBusinesses->find($transaction->order->business);
    }

    public function create(User $user, Transaction $transaction) {
        $validBusinesses = $user->businesses()->get();
        return $validBusinesses->find($transaction->order->business);
    }

    public function update(User $user, Transaction $transaction) {
        $validBusinesses = $user->businesses()->get();
        return $validBusinesses->find($transaction->order->business);
    }

    public function delete(User $user, Transaction $transaction) {
        $validBusinesses = $user->businesses()->get();
        return $validBusinesses->find($transaction->order->business);
    }

    public function restore(User $user, Transaction $transaction) {
        return false;
    }

    public function forceDelete(User $user, Transaction $transaction) {
        return false;
    }
}
