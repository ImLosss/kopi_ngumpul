@extends('layouts.admin-layout')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Products</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Product</h5>
    </nav>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col-8 d-flex align-items-center">
                        <h6>All Products</h6>
                    </div>
                    <div class="col-2 text-end">
                        <Select class="form-control">
                            <option value="">Pilih Kategori</option>
                        </Select>
                    </div>
                    <div class="col-2 text-end">
                        <a class="btn bg-gradient-dark mb-0" href="#"><i class="fas fa-plus"></i>&nbsp;&nbsp;Add Product</a>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table  align-items-center mb-0" id="dataTable3">
                        <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Nama</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Kategori</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Jumlah</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Rate</th>
                        </tr>
                        </thead>
                        <tbody>
                        
                        </tbody>
                    </table>
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

    $(document).ready( function () {
        var table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.dataTable.getProduct') }}"
            },
            columns: [
                {
                    data: 'no',
                    name: 'no'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'jumlah',
                    name: 'jumlah'
                },
                {
                    data: 'rate',
                    name: 'rate'
                }
            ]
        });

        // function reloadTable() {
        //     table.ajax.reload(null, false); // Reload data without resetting pagination
        // }

        // // Set interval to reload table every 5 seconds
        // setInterval(reloadTable, 10000);
    } );
</script>
@endsection