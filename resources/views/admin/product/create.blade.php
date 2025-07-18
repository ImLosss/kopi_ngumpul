@extends('layouts.admin-layout')

@section('title')
    - Add Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('product') }}">Products</a></li>
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
            <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Nama produk') }}</label>
                            <div class="@error('name')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="Name" name="name" value="{{ old('name') }}">
                                @error('name')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kategori" class="form-control-label">{{ __('Kategori') }}</label>
                            <div class="@error('kategori')border border-danger rounded-3 @enderror">
                                <select name="kategori" id="" class="form-control">
                                    @if ($data->isEmpty())
                                        <option value="" selected disabled>Atur kategori terlebih dahulu</option>
                                    @else
                                        <option value="" selected disabled>Pilih Kategori</option>
                                        @foreach ($data as $item)
                                            <option value="{{ $item->id }}" {{ old('kategori') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    @endif
                                    
                                </select>
                                @error('kategori')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-control-label">{{ __('Modal') }}</label>
                            <div class="@error('modal')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="modal" id="modalView" oninput="formatNumberInput()" value="{{ number_format(old('modal'), 0, ',', '.') }}">
                                <input class="form-control" type="hidden" id="modal" name="modal" value="{{ old('modal') }}">
                                @error('modal')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-control-label">{{ __('Harga') }}</label>
                            <div class="@error('harga')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="harga" id="hargaView" oninput="formatNumberInput()" value="{{ number_format(old('harga'), 0, ',', '.') }}">
                                <input class="form-control" type="hidden" id="harga" name="harga" value="{{ old('harga') }}">
                                @error('harga')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-control-label">{{ __('Stock') }}</label>
                            <div class="@error('stock')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="number" placeholder="Stock" name="stock" value="{{ old('stock') }}">
                                @error('stock')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Add Product' }}</button>
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
