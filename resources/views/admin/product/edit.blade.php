@extends('layouts.admin-layout')

@section('title')
    - Add Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('stock') }}">Products</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Add Product</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Product</h5>
    </nav>
@stop

@section('content')
<div class="card">
    <div class="card-header pb-0 px-3">
        <h5 class="mb-0">{{ __('Add Product') }}</h5>
    </div>
    <div class="card-body pt-4 p-3">
        <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="col">
                    <div class="form-group has-validation">
                        <label for="user-name" class="form-control-label">{{ __('Nama Product') }}</label>
                        <div class="@error('name')border border-danger rounded-3 @enderror">
                            <input class="form-control" type="text" placeholder="Name" name="name" value="{{ $product->name }}" autofocus>
                            @error('name')
                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group has-validation">
                        <label for="user-name" class="form-control-label">{{ __('Category') }}</label>
                        <div class="@error('category_id')border border-danger rounded-3 @enderror">
                            <select name="category_id" class="form-control" autofocus>
                                <option value="" disabled>- Pilih Kategori -</option>
                                @forelse ($categories as $item)
                                    <option value="{{ $item->id }}" {{ $product->category_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                @empty
                                    <option>Kategori Belum diatur</option>
                                @endforelse
                            </select>
                            @error('category_id')
                            <p class="text-danger text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Edit Product' }}</button>
            </div>
        </form>

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
