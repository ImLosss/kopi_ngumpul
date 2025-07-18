@extends('layouts.admin-layout')

@section('title')
    - Order
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}"@endrole>Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Orders</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Order</h5>
    </nav>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <h6>All Orders</h6>
                    </div>
                    {{-- <div class="col-6 text-end">
                        <a class="btn bg-gradient-dark mb-0" href="{{ route('discount.create') }}"><i class="fas fa-plus"></i>&nbsp;&nbsp;Add Discount</a>
                    </div> --}}
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table" id="dataTable3">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama pelanggan</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">No meja</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kasir</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status pembayaran</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Waktu pesan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <form id="formFixOrder" action="{{ route('order.fixStatusOrder') }}" method="GET">
                        @csrf
                    </form>
                    <div class="d-flex flex-wrap">
                        <a class="btn bg-gradient-info mt-2" onclick="modalFixStatusOrder()" style="margin-right: 10px"><i class="fa-solid fa-wrench text-md"></i> fix all order status</a>
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
        $('#formFixOrder').submit();
    }

    $(document).ready( function () {
        var table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.dataTable.getOrder') }}",
                error: function(xhr, error, thrown){
                    // console.log('An error occurred while fetching data.');
                    // Hide the default error message
                    $('#example').DataTable().clear().draw();
                }
            },
            columns: [
                {
                    data: '#',
                    name: '#'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
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
                    data: 'status_pembayaran',
                    name: 'status_pembayaran'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'waktu_pesan',
                    name: 'waktu_pesan'
                }
            ],
            language: {
                emptyTable: "Semua pesanan telah selesai",
                loadingRecords: "Memuat...",
                zeroRecords:    "Tidak ada data ditemukan",
            },
            drawCallback: function(settings) {
                var api = this.api();
                setTimeout(function() {
                    api.ajax.reload(null, false); // user paging is not reset on reload
                }, 10000); // 10000 milidetik = 10 detik
            },
            headerCallback: function(thead, data, start, end, display) {
                $(thead).find('th').css('text-align', 'left'); // pastikan align header tetap di tengah
            }
        });
    } );

    function modalFixStatusOrder() {
        Swal.fire({
            title: "Status order mengalami bug?",
            text: "Gunakan fitur ini untuk memperbaiki order yang rusak atau tidak bisa dibuka!",
            icon: "info",
            showCancelButton: true,
            cancelButtonColor: "#a1a1a1",
            confirmButtonText: "Ya, perbaiki!"
        }).then((result) => {
            if (result.isConfirmed) {
                submit();
            }
        });
    }
</script>
@endsection