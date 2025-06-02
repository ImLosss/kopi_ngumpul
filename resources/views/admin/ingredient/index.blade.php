@extends('layouts.admin-layout')

@section('title')
    - Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Ingredient Transaction</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Ingredient Transaction</h5>
    </nav>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col d-flex align-items-center">
                        <h6>All Transaction</h6>
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-end flex-wrap">
                            {{-- <div class="mb-2" style="margin-right: 20px">
                                @if (!$categories->isEmpty())
                                    <select class="form-control" onchange="update(this.value)" id="categorySelect">
                                        
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div> --}}
                            @can('stockAdd')
                                <div>
                                    <a class="btn bg-gradient-dark mb-0" href="{{ route('ingredient.create') }}"><i class="fas fa-plus"></i>&nbsp;&nbsp;Input Bahan</a>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <form action="{{ route('admin.printIngredientTransaction') }}" id="form_report" method="POST">
                        @csrf
                        <table class="table" id="dataTable3">
                            <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Nama</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Jumlah Gram/Ml</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Modal</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Tipe</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Tanggal</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Action</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </form>
                </div>
                 <div class="row">
                    <div class="col">
                        <div class="d-flex flex-wrap">
                            <a class="btn bg-gradient-secondary mt-2" onclick="$('#form_report').submit()" style="margin-right: 10px"><i class="fa-solid fa-print text-md"></i> Print</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>

    function submit(key) {
        $('#form_'+key).submit();
    }

    $(document).ready(function () {
        var table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.dataTable.getIngredientTransaction') }}",
                error: function(xhr, error, thrown){
                    console.log('An error occurred while fetching data.');
                        // Hide the default error message
                        $('#example').DataTable().clear().draw();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'gram_ml', name: 'gram_ml' },
                { data: 'modal', name: 'modal' },
                { data: 'type', name: 'type' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action' }
            ],
            language: {
                emptyTable: "Data Kosong"
            },
            headerCallback: function(thead, data, start, end, display) {
                $(thead).find('th').css('text-align', 'left'); // pastikan align header tetap di tengah
            },
        });
    });

    function modalHapus(id) {
        Swal.fire({
            title: "Kamu yakin?",
            text: "Kamu tidak akan bisa membatalkannya setelah ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#a1a1a1",
            confirmButtonText: "Ya, hapus saja!"
        }).then((result) => {
            if (result.isConfirmed) {
                submit(id);
            }
        });
    }
</script>
@endsection