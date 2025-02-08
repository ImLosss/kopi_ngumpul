@extends('layouts.admin-layout')

@section('title')
    - Add Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('stock') }}">Stocks</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Add Stock</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Stock</h5>
    </nav>
@stop

@section('content')
<div class="col-lg-8 mb-lg-0 mb-4">
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h5 class="mb-0">{{ __('Add Stock') }}</h5>
        </div>
        <div class="card-body pt-4 p-3">
            <form action="{{ route('stock.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Nama Stock') }}</label>
                            <div class="@error('name')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="Name" name="name" value="{{ old('name') }}">
                                @error('name')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Add Stock' }}</button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection

@section('script')
    <script>
        function formatNumberInput() {
            // Ambil nilai input dan hapus semua karakter non-digit
            let modalView = Number($('#modalView').val().replace(/\D/g, ''));
            let hargaView = Number($('#hargaView').val().replace(/\D/g, ''));

            $('#modal').val(modalView)
            $('#modalView').val(modalView.toLocaleString('id-ID'));
            $('#harga').val(hargaView)
            $('#hargaView').val(hargaView.toLocaleString('id-ID'));
        }
    </script>
@endsection
