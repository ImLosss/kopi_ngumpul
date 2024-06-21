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
                                        <input type="date" class="form-control" name="startDate" id="startDate" onchange="start()">
                                    </div>
                                    <div style="margin-right: 20px">
                                        -
                                    </div>
                                    <div>
                                        <input type="date" class="form-control" name="endDate" id="endDate" onchange="end()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="row">
                    <div class="table-responsive p-3">
                        <form action="{{ route('report.printReport') }}" id="form_report" method="POST">
                            <input type="date" class="form-control" name="startDate" id="startDateVal" readonly hidden>
                            <input type="date" class="form-control" name="endDate" id="endDateVal" readonly hidden>
                            <input type="text" class="form-control" name="signatoryName" id="signatory" readonly hidden>
                            @csrf
                            <table class="table" id="dataTable3">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kasir</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Profit</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu pesan</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th id="totalPendapatan"></th>
                                        <th id="totalProfit"></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="d-flex flex-wrap">
                            <a class="btn bg-gradient-secondary mt-2" href="#" onclick="modal()" style="margin-right: 10px"><i class="fa-solid fa-print text-md"></i> Print</a>
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
    function submit_form() {
        $('#form_report').submit();
    }

    function modal() {
        Swal.fire({
            title: "Full name Signatory",
            input: "text",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Print",
            showLoaderOnConfirm: true,
            preConfirm: async (login) => {
                if(!login) return Swal.showValidationMessage('Nama tidak boleh kosong');

                $('#signatory').val(login);
                return submit_form();
            },
            allowOutsideClick: () => !Swal.isLoading()
        })
    }

    var table;
    $(document).ready( function () {

        table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.dataTable.getAllReport') }}",
                data: function (d) {
                    d.startDate = $('#startDate').val();
                    d.endDate = $('#endDate').val();
                },
                dataSrc: function(json) {
                    $('#totalPendapatan').text(formatRupiah(json.totalPendapatan));
                    $('#totalProfit').text(formatRupiah(json.totalProfit));
                    return json.data;
                }
            },
            columns: [
                {
                    data: '#',
                    name: '#'
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

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number).replace(/\./g, ',');
        }

        // Set interval to reload table every 5 seconds
        // setInterval(reloadTable, 10000);
    } );

    function start() {
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();

        let start = new Date(startDate);
        let end = new Date(endDate);

        if(start > end) $('#endDate').val(startDate);

        submitFilter();
    }

    function end() {
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();

        let start = new Date(startDate);
        let end = new Date(endDate);

        if(end < start) $('#startDate').val(endDate);

        submitFilter();
    }

    function submitFilter() {
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();

        $('#startDateVal').val(startDate);
        $('#endDateVal').val(endDate);

        if(!startDate || !endDate) return;

        table.ajax.reload();
    }
</script>
@endsection