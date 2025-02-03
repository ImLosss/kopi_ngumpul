@extends('layouts.admin-layout')

@section('title')
    - Edit Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('product') }}">Products</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Edit Product</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Product</h5>
    </nav>
@stop

@section('content')
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h5 class="mb-0">{{ __('Edit Product') }}</h5>
        </div>
        <div class="card-body pt-4 p-3">
            <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Nama produk') }}</label>
                            <div class="@error('name')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="Name" name="name" value="{{ $product->name }}" @role('dapur') disabled @endrole>
                                @role('dapur') <input type="hidden" name="name" value="{{ $product->name }}"> @endrole
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
                                <select name="kategori" id="" class="form-control" @role('dapur') disabled @endrole>
                                    <option value="" disabled>Pilih Kategori</option>
                                    @foreach ($categories as $item)
                                        <option value="{{ $item->id }}" {{ $product->category_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @role('dapur')<input name="kategori" type="hidden" class="form-control" value="{{ $product->category_id }}"> @endrole
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
                                <input class="form-control" type="text" placeholder="modal" id="modalView" oninput="formatNumberInput()" value="{{ number_format($product->modal, 0, ',', '.') }}" @role('dapur') disabled @endrole>
                                <input class="form-control" type="hidden" placeholder="modal" id="modal" name="modal" value="{{ $product->modal }}" @role('dapur') disabled @endrole>
                                @role('dapur') <input type="hidden" name="modal" value="{{ $product->modal }}"> @endrole
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
                                <input class="form-control" type="text" placeholder="harga" id="hargaView" oninput="formatNumberInput()" value="{{ number_format($product->harga, 0, ',', '.') }}" @role('dapur') disabled @endrole>
                                <input class="form-control" type="hidden" placeholder="harga" id="harga" name="harga" value="{{ $product->harga }}" @role('dapur') disabled @endrole>
                                @role('dapur') <input type="hidden" name="harga" value="{{ $product->harga }}"> @endrole
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
                                <input class="form-control" type="number" placeholder="Stock" name="stock" value="{{ $product->jumlah }}">
                                @error('stock')
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
