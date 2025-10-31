<?php
    $labels = $labels = new \App\Domains\Order\OrderStatus();
?>
<div>
    <select wire:model='order.status'>
        @foreach ($labels::$labels as $key => $label)
            <option value='{{ $key }}'>{{ $label }}</option>
        @endforeach
    </select>
</div>
