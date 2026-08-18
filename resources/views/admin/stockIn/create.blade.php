@extends('layouts.admin-layout')

@section('title')
    - Add Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('stock-in.index') }}">Stock In</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Add</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Stock In</h5>
    </nav>
@stop

@section('content')
<div class="col-lg mb-lg-0 mb-4">
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h5 class="mb-0">{{ __('Add Stock In') }}</h5>
        </div>
        <div class="card-body pt-4 p-3">
            <form action="{{ route('stock-in.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Product Name') }}</label>
                            <div class="@error('name')border border-danger rounded-3 @enderror">
                                <select class="form-control" name="name" autofocus>
                                    <option value="" disabled selected>= Select Product =</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ old('name') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('name')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Qty') }}</label>
                            <div class="@error('qty')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="Qty" name="qty" value="{{ old('qty') }}" autofocus>
                                @error('qty')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Date') }}</label>
                            <div class="@error('date')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="date" placeholder="Date" name="date" value="{{ old('date') }}" autofocus>
                                @error('date')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Batch Code') }}</label>
                            <div class="@error('batch_code')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="Batch Code" name="batch_code" value="{{ old('batch_code') }}" autofocus>
                                @error('batch_code')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Add Stock In' }}</button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@section('script')

@endsection
