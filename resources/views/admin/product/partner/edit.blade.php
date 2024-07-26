@extends('layouts.admin-layout')

@section('title')
    - Edit Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}@endrole">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('partnerProduct') }}">Up Harga</a></li>
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
            <form action="{{ route('product.partner.update', $data->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group has-validation">
                            <label for="user-name" class="form-control-label">{{ __('Nama produk') }}</label>
                            <div class="@error('product_id')border border-danger rounded-3 @enderror">
                                <input type="text" class="form-control" readonly value="{{ $data->product->name }}"> 
                                @error('product_id')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-control-label">{{ __('Up Harga') }}</label>
                            <div class="@error('upHarga')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="upHarga" oninput="formatNumberInput(this.value)" id="upHargaView" value="{{ number_format($data->up_price, 0, ',', '.') }}">
                                <input class="form-control" type="hidden" placeholder="upHarga" name="upHarga" value="{{ $data->up_price }}" id="upHarga">
                                @error('upHarga')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-control-label">{{ __('Total harga setelah up') }}</label>
                            <input class="form-control" type="text" placeholder="Total" value="{{ number_format($data->up_price + $data->product->harga, 0, ',', '.') }}" id="upTotalHargaView" readonly>
                            <input class="form-control" type="hidden" value="{{ $data->up_price + $data->product->harga }}" placeholder="Total" name="upHargaVal" id="upHargaVal">
                            <input type="number" id="productPrice" value="{{ $data->product->harga }}" readonly hidden>
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
        function formatNumberInput(input) {
            // Ambil nilai input dan hapus semua karakter non-digit
            let value = Number(input.replace(/\D/g, ''));

            if(value == NaN) value = 0;

            let productPrice = parseFloat($('#productPrice').val());
            let totalHarga = value + productPrice
            $('#upHargaVal').val(totalHarga);
            $('#upTotalHargaView').val(totalHarga.toLocaleString('id-ID'));
            $('#upHarga').val(value);
            $('#upHargaView').val(value.toLocaleString('id-ID'));
        }

        $('#name').change(function() {
            var productId = $(this).val();
            if (productId) {
                $.ajax({
                    url: '/get-partner-detail/' + productId,
                    type: 'GET',
                    success: function(data) {
                        let upHarga = Number($('#upHarga').val());
                        let hargaData = Number(data.harga);
                        let totalHarga = upHarga + hargaData
                        $('#productPrice').val(hargaData);
                        $('#upTotalHargaView').val(totalHarga.toLocaleString('id-ID'));
                        if(!upHarga) return $('#upHargaVal').val(hargaData);
                        $('#upHargaVal').val(totalHarga);
                    }
                });
            } else {
                $('#harga').val(0);
            }
        });
    </script>
@endsection
