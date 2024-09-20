@extends('layouts.admin-layout')

@section('title')
    - Add Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}@endrole">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('partnerProduct') }}">Up Harga</a></li>
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
            <form action="{{ route('product.partner.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kategori" class="form-control-label">{{ __('Category Menu') }}</label>
                            <div class="@error('category_id')border border-danger rounded-3 @enderror">
                                <select name="category_id" id="menuSelect" class="form-control">
                                    <option value="" selected disabled>-Pilih Category Menu-</option>
                                    @if ($categories->isEmpty())
                                        <option value="" selected disabled>Atur menu terlebih dahulu</option>
                                    @else
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('category_id')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-control-label">{{ __('Up Harga') }}</label>
                            <div class="@error('upHarga')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="Selisih antara harga Jual dengan harga Asli" oninput="formatNumberInput(this.value)" id="upHargaView" value="{{ old('upHarga') }}">
                                <input class="form-control" type="hidden" placeholder="Selisih antara harga Jual dengan harga Asli" name="upHarga" id="upHarga">
                                @error('upHarga')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <label for="">Pilih Menu</label>
                    <div id="form-menu">
                        <label for="">.....</label>
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
    
        $('#menuSelect').change(function() {
        var categoryId = $(this).val();
        $.ajax({
            url: '/get-partner-menu-by-category/' + categoryId,
            type: 'GET',
            success: function(data) {
                if(!data) $('#form-menu').empty().append('Semua produk pada category ini telah diatur');
                let products = data.productList;

                let code = '';
                products.forEach(item => {
                    code+=`<div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="selectedProducts[]" id="inlineCheckbox${ item.id }" value="${ item.id }">
                        <label class="form-check-label" for="inlineCheckbox${ item.id }">${ item.name } (${ item.harga })</label>
                    </div>`
                });

                $('#form-menu').empty().append(code);
            }
        });
    });

    </script>
@endsection
