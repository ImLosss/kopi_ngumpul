@extends('layouts.admin-layout')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}"@endrole>Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Reports</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Report</h5>
    </nav>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <h6>All Reports</h6>
                    </div>
                    <div class="col">
                        <div class="row">
                            <div class="col">
                                <div class="d-flex justify-content-end">
                                    <div style="margin-right: 20px">
                                        <input type="date" class="form-control" name="startDate">
                                    </div>
                                    <div style="margin-right: 20px">
                                        -
                                    </div>
                                    <div>
                                        <input type="date" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table" id="dataTable3">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No meja</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kasir</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Profit</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu pesan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
            responsive: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.dataTable.getAllReport') }}"
            },
            columns: [
                {
                    data: '#',
                    name: '#'
                },
                {
                    data: 'no_meja',
                    name: 'no_meja'
                },
                {
                    data: 'kasir',
                    name: 'kasir'
                },
                {
                    data: 'total',
                    name: 'total'
                }
                ,
                {
                    data: 'profit',
                    name: 'profit'
                },
                {
                    data: 'waktu_pesan',
                    name: 'waktu_pesan'
                }
            ],
            language: {
                emptyTable: "Tidak ada histori ditemukan",
                loadingRecords: "Memuat..."
            },
            columnDefs: [
                { width: '250px', targets: 0 }
            ],
        });

        function reloadTable() {
            table.ajax.reload(null, false); // Reload data without resetting pagination
        }

        // Set interval to reload table every 5 seconds
        // setInterval(reloadTable, 10000);
    } );
</script>
@endsection