<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <x-frontend.card>
                <x-slot name="header">
                    Order:
                </x-slot>

                <x-slot name="body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th scope="row">Id</th>
                            <td> {{ $order->id }} - {{ $order->number }}</td>
                        </tr>

                        <tr>
                            <th scope="row">Status</th>
                            <td><livewire:orders.order-status :order="$order"/></td>
                        </tr>

                        <tr>
                            <th scope="row">Customer</th>
                            <td>
                                <table class="table">
                                    <tbody>
                                    <!--
                                        <tr>
                                            <td>Type: {{ $order->user->type }}</td>
                                        </tr>
                                        -->
                                        <tr>
                                            <td>Name: {{ $order->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Email: {{ $order->user->email }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">Order location</th>
                            <td><input wire:model="order.location" /></td>
                        </tr>
                        <tr>
                            <th scope="row">Start</th>
                            <td><input wire:model="order.start" /></td>
                        </tr>
                        <tr>
                            <th scope="row">Ordered at</th>
                            <td>{{ $order->created_at }}</td>
                        </tr>

                        <tr>
                            <th scope="row">End</th>
                            <td><input wire:model="order.end" /></td>
                        </tr>
                        <tr>
                            <th scope="row">Notes</th>
                            <td>

                                <div class="form-group">
                                    <label for="ordernotes">Notes</label>
                                    <textarea
                                        id="ordernotes" rows="3"
                                        class="form-control"
                                        wire:model="ordernotes"></textarea>

                                </div>
                            </td>
                        </tr>

                        <tr><th scope="row">dispatchPrice</th><td>{{ $order->dispatchPrice }}</td></tr>
                        <tr><th scope="row">totalProductPrice</th><td>{{ $order->totalProductPrice }}</td></tr>
                        <tr><th scope="row">adjustments</th><td>{{ $order->adjustments }}</td></tr>
                        <tr><th scope="row">totalPrice</th><td>{{ $order->totalPrice }}</td></tr>
                        <tr><th scope="row">deposit</th><td>{{ $order->deposit }}</td></tr>
                        <tr><th scope="row">Paid</th><td>{{ $order->amountPaid() }}</td></tr>
                        <tr><th scope="row">Outstanding</th><td>{{ $order->amountOutstanding() }}</td></tr>

                        </tbody>
                    </table>


                    <?php /*
                     "billpayer_id" integer null,
                     "shipping_address_id" integer null,
                     "is_addon" tinyint(1) not null default '0',
                     "is_required" tinyint(1) not null default '0',
                     business_id int,
                    */?>

                    <hr />

                    <h3>Bookings:</h3>
                    <div>
                        <table class="table">
                            <thead>
                                <th>Booking Id</th>
                                <th>Order Item Id</th>
                                <th>Start</th>
                                <th>End</th>
                            </thead>
                            <tbody>
                            @foreach($this->bookings as $booking)
                                <tr>
                                    <td>{{ $booking->id }}</td>
                                    <td>{{ $booking->orderItem->name }}</td>
                                    <td>{{ $booking->calendar->start }}</td>
                                    <td>{{ $booking->calendar->end }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr />

                    <h3>Items:</h3>
                    <div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Product Id</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->orderItems as $orderItem)
                                <tr>
                                    <td>{{ $orderItem->id }}</td>
                                    <td>{{ $orderItem->product_id }}</td>
                                    <td>{{ $orderItem->name }}</td>
                                    <td>{{ $orderItem->product_type }}</td>
                                    <td>{{ $orderItem->quantity }}</td>
                                    <td>£{{ $orderItem->price }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <hr />
                    <h3>Forms:</h3>
                    <?php /*
                    @foreach ($order->formResponse as $formResponse)
                        @foreach($formResponse->form as $item)

                                @if(isset($item['userData']))
                                    @foreach($item['userData'] as $userData)
                                        {{ $userData }}
                                    @endforeach
                                @endif

                            <hr />
                        @endforeach
                    @endforeach
                    */?>
                    <div wire:ignore id='form-builder-editor'></div>
                    <hr />

                    <h3>Payments:</h3>
                    <hr />
                    <livewire:payments.table :order="$order"/>

                    <div wire:ignore id='order-builder-editor'></div>
                </x-slot>
            </x-frontend.card>
        </div><!--col-md-10-->
    </div><!--row-->
</div><!--container-->


@push('after-scripts')
    <script>

        // function do_form_builder(myFormData) {
        //     var formBuilderOptions = {
        //         defaultFields: myFormData,
        //         disabledActionButtons: ['data'],
        //         onSave: function(evt, formData) {
        //             window.livewire.emit('formSave', formData);
        //         }
        //     };
        //     var formRenderOptions = {
        //         formData: myFormData
        //     };
        //
        //     let formBuilderElement = $('#form-builder-editor');
        //     window.formBuilder = formBuilderElement.formBuilder(formBuilderOptions);
        // }

        <?php
        $forms = null;
        foreach ($order->formResponse as $formResponse) {
            $forms[$formResponse->form_id] = $formResponse->form;
        }
        ?>

        let myFormData = {!! json_encode($forms) !!};
        let cnt=0;
        for (const formIdx in myFormData) {
            cnt++;
            const $markup = $("<div/>");

            // Append markup prior to formrender as textarea responses won't be rendered
            // Seems to need to be added to the screen to work as it's not working prior.
            $('#form-builder-editor').append($markup);

            // Render the form
            $markup.formRender({
                formData: myFormData[formIdx]
            });

            // Add HR between forms
            if(cnt < Object.keys(myFormData).length) {
                $('#form-builder-editor').append('<hr style="border: 1px dotted grey;"/>');
            }
        }
    </script>
@endpush
