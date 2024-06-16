@extends('layouts.admin-layout')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}"@endrole">Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Cashier</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Cashier</h5>
    </nav>
@stop

@section('content')

<div class="row mt-4">
    <div class="col-lg-8 mb-lg-0 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <form action="{{ route('cashier.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group has-validation">
                                <label for="kategori" class="form-control-label">{{ __('Menu') }}</label>
                                <div class="@error('menu')border border-danger rounded-3 @enderror">
                                    <select name="menu" id="menuSelect" class="form-control">
                                        
                                        @if ($products->isEmpty())
                                            <option>Belum mengatur harga partner</option>
                                        @else
                                            <option value="" selected disabled>Pilih Menu</option>
                                        @endif
                                        @foreach ($products as $item)
                                            <option value="{{ $item->product->id }}" {{ $item->product->jumlah == 0 ? 'disabled' : '' }}>{{ $item->product->name }} {{ $item->product->jumlah == 0 ? '(kosong)' : '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('menu')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user-name" class="form-control-label">{{ __('Jumlah') }}</label>
                                <div class="@error('jumlah')border border-danger rounded-3 @enderror">
                                    <input class="form-control" type="number" min="1" placeholder="Jumlah" name="jumlah" id="jumlah" value="1">
                                    @error('jumlah')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-control-label">{{ __('Harga') }}</label>
                                <div class="@error('harga')border border-danger rounded-3 @enderror">
                                    <input class="form-control" type="number" placeholder="harga" id="harga" name="harga" value="0" readonly>
                                    <input type="number" id="real_price" name="real_price" value="0" readonly hidden>
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
                                    <input class="form-control" type="number" placeholder="Stock" name="stock" id="stock" readonly>
                                    @error('stock')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-control-label">{{ __('Total') }}</label>
                                <div class="@error('total')border border-danger rounded-3 @enderror">
                                    <input class="form-control" type="number" placeholder="Total" name="total" id="total" value="0" readonly>
                                    @error('total')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="diskon">
                        
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="note" class="form-control-label">{{ __('Note') }}</label>
                                <textarea name="note" rows="5" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn bg-gradient-dark btn-md mt-4">{{ 'Tambah ke cart' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-3">
            <div class="row mb-2">
                <div class="col">
                    <h5>Cart</h5>
                </div>
            </div>
            @foreach ($order->carts as $item)
                <div class="row">
                    <div class="col-1">
                        <a type="submit" onclick="submit({{ $item->id }})"><i class="fa-solid fa-circle-minus text-danger"></i></a>
                        
                        {{-- form delete --}}
                        <form action="{{ route('cart.destroy', $item->id) }}" id="form_{{ $item->id }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                    <div class="col">
                        {{$item->menu}} x{{$item->jumlah}}
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-end flex-wrap">
                            Rp. {{number_format($item->partner_total )}}
                        </div>
                    </div>
                </div> 
                @if ($item->diskon_id != null)
                    <div class="row">
                        <div class="col-1">
        
                        </div>
                        <div class="col">
                            Disc ({{ $item->discount->percent }}%)
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-end flex-wrap">
                                (Rp. {{ number_format($item->total_diskon) }})
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            <hr>
            @if ($disc != 0)
                <div class="row">
                    <div class="col font-italic">
                        Anda hemat:
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-end flex-wrap font-weight-bold font-italic">
                            (Rp. {{ number_format($disc) }})
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="col font-italic">
                    Total:
                </div>
                <div class="col">
                    <div class="d-flex justify-content-end flex-wrap font-weight-bold font-italic">
                        Rp. {{ number_format($order->partner_total) }}
                    </div>
                </div>
            </div>
            <form action="{{ route('order.update', $order->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="row mt-3">
                    <div class="col">
                        <div class="form-group has-validation">
                            <label for="kategori" class="form-control-label">{{ __('Nomor Meja : ') }}</label>
                            <div class="@error('no_meja')border border-danger rounded-3 @enderror">
                                <select name="no_meja" class="form-control">
                                    <option value="" selected disabled>Pilih Nomor meja</option>
                                    @foreach ($tables as $table)
                                        <option value="{{ $table->id }}">{{ $table->no_meja }} @if ($table->status == 'terpakai')
                                            ({{ $table->status }})
                                        @endif</option>
                                    @endforeach
                                </select>
                                @error('no_meja')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn bg-gradient-dark btn-md mt-4" {{ $order->carts->count() == 0 ? 'disabled' : '' }}>{{ 'Order' }}</button>
                </div>
            </form> 
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let stock;

    function submit(key) {
        $('#form_'+key).submit();
    }

    $(document).ready(function() {
        $('#menuSelect').change(function() {
            var productId = $(this).val();
            if (productId) {
                $.ajax({
                    url: '/get-detail/' + productId,
                    type: 'GET',
                    success: function(data) {
                        console.log(data);
                        stock = data.stock;
                        let jumlah = $('#jumlah').val();
                        if(jumlah > stock) $('#jumlah').val(stock);
                        $('#harga').val(data.harga);
                        $('#stock').val(data.stock);
                        $('#total').val(data.harga*jumlah);
                        $('#real_price').val(data.real_price);
                    }
                });
            } else {
                $('#harga').val('');
            }
        });

        $('#jumlah').change(function() {
            var jumlah = $('#jumlah').val();
            var harga = $('#harga').val();
            if(jumlah <= 0) $('#jumlah').val(1);
            if(jumlah > stock) $('#jumlah').val(stock);

            var jumlah = $('#jumlah').val();
            $('#total').val(jumlah*harga);
        });
    });
</script>
@endsection