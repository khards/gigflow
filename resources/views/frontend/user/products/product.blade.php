<script type="text/javascript">
    window.__INITIAL_SCHEDULES__ = "{!! addslashes(json_encode(
        $schedules->map(function ($schedule) {
            return collect($schedule)->only(['id', 'summary', 'state', 'rrule']);
        })
    )) !!}";


    window.__OLD__ = "{!! addslashes(json_encode($app->get('request')->old())) !!}";

    window.__INITIAL_PRODUCT__ = "{!! addslashes(json_encode($product)) !!}";

    window.__INITIAL_ERRORS__ = "{!! addslashes(json_encode($errors->toArray())) !!}";

    window.__INITIAL_VARIATIONS__ =" {!! addslashes(json_encode($variations)) !!}";

    window.__FORMS__ = "{!! addslashes(json_encode($forms)) !!}";

</script>

@extends('frontend.layouts.app')

@section('head')
@endsection

@section('title', $product->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>

                    <x-slot name="header">
                        Edit product
                    </x-slot>

                    <x-slot name="body">

                        <?php
                        /*
                         * Form css documentation:
                         *      https://coreui.io/docs/components/bootstrap/forms/
                        */
                        ?>
                        <form id="edit-product" action="{{ route('frontend.user.product.update', $product->id) }}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div>
                                <product-editor />
                            </div>
                        </form>
                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection
