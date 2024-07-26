@extends('layouts.admin-layout')

@section('title')
    - Add Discount
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('discount') }}">Discounts</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Add Discount</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Discount</h5>
    </nav>
@stop

@section('content')
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h5 class="mb-0">{{ __('Add Discount') }}</h5>
        </div>
        <div class="card-body pt-4 p-3">
            <form action="{{ route('discount.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kategori" class="form-control-label">{{ __('Menu') }}</label>
                            <div class="@error('menu')border border-danger rounded-3 @enderror">
                                <select name="menu" id="menuSelect" class="form-control">
                                    @if ($categories->isEmpty())
                                        <option value="" selected disabled>Atur menu terlebih dahulu</option>
                                    @else
                                        @foreach ($categories as $category)
                                            @if ($category->product->isEmpty())
                                                <optgroup label="{{ $category->name }}">
                                                    <option value="" disabled>Belum ada menu {{ $category->name }} yang diatur</option>
                                                </optgroup>
                                            @else
                                                <optgroup label="{{ $category->name }}">
                                                    @foreach ($category->product as $item)
                                                        <option value="{{ $item->id }}" {{ $item->jumlah == 0 ? 'disabled' : '' }}>{{ $item->name }} {{ $item->jumlah == 0 ? '(kosong)' : '' }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                @error('menu')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="disc_name" class="form-control-label">{{ __('Discount Name') }}</label>
                            <div class="@error('disc_name')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="text" placeholder="Diskon Mahasiswa" name="disc_name" value="{{ old('disc_name') }}">
                                @error('disc_name')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount" class="form-control-label">{{ __('Discount (%)') }}</label>
                            <div class="@error('discount')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="number" placeholder="20" name="discount" value="{{ old('discount') }}" id="discount">
                                @error('discount')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kategori" class="form-control-label">{{ __('Status') }}</label>
                            <div class="@error('status')border border-danger rounded-3 @enderror">
                                <select name="status" id="" class="form-control">
                                    <option selected disabled>- Status -</option>
                                    <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Tidak aktif" {{ old('status') == 'Tidak aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                                @error('status')
                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Add Discount' }}</button>
                </div>
            </form>

        </div>
    </div>

@endsection


@section('script')
<script>
    $('#menuSelect').select2();
    $('#discount').change(function() {
        var jumlah = $('#discount').val();
        if(jumlah <= 0) $('#discount').val(1);
        if(jumlah > 100) $('#discount').val(100);
    });
</script>
@endsection