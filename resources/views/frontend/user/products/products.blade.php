@extends('frontend.layouts.app')

@section('title', $business->name)

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <x-frontend.card>

                    <x-slot name="header">
                        {{ $business->name }} - Products
                    </x-slot>

                    <x-slot name="body">

                        <div class="mb-2 container">
                            <div class="row">
                                <div class="col"></div>
                                <div class="col"></div>
                                <div class="col"></div>
                                <div class="col">
                                    <form action="{{ route('frontend.user.product.create', ['businessId' => $business->id]) }}" method="post">
                                        {{ csrf_field() }}
                                    <button
                                        type="submit"
                                        class="float-right btn btn-pill btn-success">
                                        <span class="cil-plus btn-icon mr-2"></span>Add</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <table class="table">
                            <thead>
                            <tr>
                                <th scope="col"></th>
                                <th scope="col">Id</th>
                                <th scope="col">State</th>
                                <th scope="col">Name</th>
                                <th scope="col">Category</th>
                                <th scope="col">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>@todo</td>
                                    <th scope="row">{{ $product->id }}</th>
                                    <td>{{ $product->state }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>@todo</td>
                                    <td>

                                        <form style="display: inline;" action="{{ route('frontend.user.product.delete', ['productId' => $product->id]) }}" method="post">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button
                                                type="submit"
                                                class="btn btn-pill btn-danger">
                                                <span class="cil-trash btn-icon mr-2"></span>Delete</button>

                                        </form>

                                        <a  href="{{ route('frontend.user.product.edit', ['productId' => $product->id]) }}"
                                            class="btn btn-pill btn-warning">
                                            <span class="cil-pencil btn-icon mr-2"></span>Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </x-slot>
                </x-frontend.card>
            </div><!--col-md-10-->
        </div><!--row-->
    </div><!--container-->
@endsection
