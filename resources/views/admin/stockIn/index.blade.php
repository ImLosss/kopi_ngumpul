@extends('layouts.admin-layout')

@section('title')
    - Records
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}"@endrole>Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Stock In</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Stock In</h5>
    </nav>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col d-flex align-items-center">
                        <h6>All Stock In</h6>
                    </div>
                    <div class="col">

                        <div class="d-flex justify-content-end flex-wrap">
                            <div class="mb-2" style="margin-right: 20px">
                                <input type="text"
                                       class="form-control form-control-sm"
                                       name="dateRange"
                                       id="dateRange"
                                       placeholder="Select date range"
                                       readonly>
                            </div>
                            <div>
                                <a class="btn bg-gradient-dark mb-0" href="{{ route('stock-in.create') }}"><i class="fas fa-plus"></i>&nbsp;&nbsp;Tambah Stock</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="row">
                    <div class="table-responsive p-3">
                        <input type="date" class="form-control" name="startDate" id="startDateVal" readonly hidden>
                        <input type="date" class="form-control" name="endDate" id="endDateVal" readonly hidden>
                        <input type="text" class="form-control" name="signatoryName" id="signatory" readonly hidden>
                        @csrf
                        <table class="table" id="dataTable3">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qty</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="d-flex flex-wrap">
                            <a class="btn bg-gradient-secondary mt-2" href="#" id="btnPrint" style="margin-right: 10px"><i class="fa-solid fa-print text-md"></i> Print</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<!-- Date Range Picker CSS & JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    var table;
    $(document).ready(function() {
        // Initialize Date Range Picker
        $('#dateRange').daterangepicker({
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Apply',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                weekLabel: 'W',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            }
        });

        // Handle date range change
        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $('#startDateVal').val(picker.startDate.format('YYYY-MM-DD'));
            $('#endDateVal').val(picker.endDate.format('YYYY-MM-DD'));
            submitFilter();
        });

        // Initialize DataTable
        table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.dataTable.getStockIn') }}",
                data: function (d) {
                    d.dateRange = $('#dateRange').val();
                },
                error: function(xhr, error, thrown){
                    $('#dataTable3').DataTable().clear().draw();
                }
            },
            columns: [
                {
                    data: 'product',
                    name: 'product'
                },
                {
                    data: 'qty',
                    name: 'qty'
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
            language: {
                emptyTable: "Tidak ada histori ditemukan",
                loadingRecords: "Loading..."
            },
            headerCallback: function(thead, data, start, end, display) {
                $(thead).find('th').css('text-align', 'left');
            }
        });

        $('#btnPrint').on('click', function (e) {
            e.preventDefault();

            let dateRange = $('#dateRange').val() || '';
            let search = (table && typeof table.search === 'function') ? (table.search() || '') : '';

            let url = "{{ route('stock-in.print') }}";
            url += "?dateRange=" + encodeURIComponent(dateRange) + "&search=" + encodeURIComponent(search);

            window.open(url, '_blank');
        });
    });

    function submitFilter() {
        let dateRange = $('#dateRange').val();

        if(!dateRange) return;

        table.ajax.reload();
    }

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

    function submit(key) {
        $('#form_'+key).submit();
    }
</script>
@endsection
