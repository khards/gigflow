<div>
    <table class="table">
        <thead>
        <tr>
            <th>Id</th>
            <th>Order Id</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Note</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($transactions as $key => $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item['order_id'] }}</td>
            <td>£
                <input wire:model="transactions.{{ $key }}.amount" type="number" step="0.01" placeholder="0.00">
            </td>
            <td>
                <select wire:model="transactions.{{ $key }}.method">
                    <option value="other">Other</option>
                    <option value="paypal">PayPal</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                </select>
            </td>
            <td>
                <input wire:model="transactions.{{ $key }}.note" type="text">
            </td>
            <td>
                @if($confirming === $item['id'])
                    <button
                        wire:click="kill({{ $item['id'] }})"
                        class="btn btn-danger btn-sm">Sure?</button>

                    <button
                        wire:click="confirmDelete(0)"
                        class="btn btn-success btn-sm">Cancel</button>
                @else
                    @if($item->id)
                    <button
                        wire:click="confirmDelete({{ $item->id }})"
                        class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                    @endif
{{--                    <a wire:click="edit({{ $item['id'] }})"--}}
{{--                       href="#"--}}
{{--                       class="btn btn-primary btn-sm">--}}
{{--                        <i class="fas fa-pencil-alt"></i> Edit</a>--}}
                @endif
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div>
        <button
            wire:click="add()"
            class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add</button>
        <button
            wire:click="save()"
            class="btn btn-danger btn-sm"><i class="fas fa-save"></i> Save</button>
        <button
            wire:click="reload()"
            class="btn btn-warning btn-sm"><i class="fas fa-file"></i> Cancel</button>

    </div>
</div>
