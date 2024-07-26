@extends('layouts.admin-layout')

@section('title')
    - Add Table
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('table') }}">Tables</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Add Table</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Table</h5>
    </nav>
@stop

@section('content')
    <div class="card">
        <div class="card-header pb-0 px-3">
            <h5 class="mb-0">{{ __('Add Table') }}</h5>
        </div>
        <div class="card-body pt-4 p-3">
            <form action="{{ route('table.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_meja" class="form-control-label">{{ __('Nomor Meja') }}</label>
                            <div class="@error('category')border border-danger rounded-3 @enderror">
                                <input class="form-control" type="number" placeholder="no meja" name="no_meja" value="{{ old('no_meja') }}">
                                @error('no_meja')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category" class="form-control-label">{{ __('Status') }}</label>
                            <div class="@error('status')border border-danger rounded-3 @enderror">
                                <select name="status" class="form-control">
                                    <option value="" disabled selected>Pilih Status</option>
                                    <option value="kosong" {{ old('status') == 'kosong' ? 'selected' : '' }}>Kosong</option>
                                    <option value="terpakai" {{ old('status') == 'terpakai' ? 'selected' : '' }}>Terpakai</option>
                                </select>
                                @error('status')
                                    <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Add Table' }}</button>
                </div>
            </form>

        </div>
    </div>

@endsection


@section('script')
<script>
    $('#discount').change(function() {
        var jumlah = $('#discount').val();
        if(jumlah <= 0) $('#discount').val(1);
        if(jumlah > 100) $('#discount').val(100);
    });
</script>
@endsection