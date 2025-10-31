
<?php
    /*
     *      This doesn't work correctly
     *          <livewire:orders.order-status :order="$model"/>
     *      It does change the order status, but breaks the delete function.
     *      To try again after upgrading livewire & tables component.
     *
     */
?>

{{ $model->status }}
