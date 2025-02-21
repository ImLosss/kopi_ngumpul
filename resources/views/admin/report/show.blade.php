@extends('layouts.admin-layout')

@section('title')
    - Laporan
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}"@endrole>Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('report') }}">Reports</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Show</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Report</h5>
    </nav>
@endsection
@section('content')
<style>
    .custom-checkbox {
        /* Tambahkan gaya kustom sesuai kebutuhan */
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
</style>
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <h6>Order {{ $order->id }}</h6>
                    </div>
                    <div class="col-6 text-end">
                        Waktu pesan: {{ $order->created_at }}
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <form action="{{ route('payment.billOrUpdate') }}" method="POST" enctype="multipart/form-data" id="formCetakNota">
                        @csrf
                        <input type="text" name="action" id="action" hidden>
                        <table class="table" id="dataTable3">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        <div class="form-check">
                                            <input class="form-check-input custom-checkbox" type="checkbox" value="" id="selectPesanAll">
                                        </div>
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Menu</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Harga</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Payment</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Update Payment By</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="d-flex flex-wrap">
                        <button class="btn bg-gradient-dark mt-2" href="#" id="btnCetakNota" disabled>Cetak nota</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- <style>
    .h{
        background-color: #a1a1a1
    }
</style> --}}

@section('script')
<script>

    function submit(key) {
        $('#form_'+key).submit();
    }

    function updateStatus(key) {
        $('#formUpdate_'+key).submit();
    }

    function submit_form() {
        $('#formCetakNota').submit();
    }

    $('#btnCetakNota').on('click', function() {
        $('#action').val('printBill');
        submit_form()
    });

    $(document).ready( function () {
        // Handle select all checkbox
        $('#selectPesanAll').on('click', function() {
            $('input[name="selectPesan[]"]').prop('checked', this.checked);
            checkSelected();
        });

        // Handle individual checkbox to update select all checkbox state
        $(document).on('click', 'input[name="selectPesan[]"]', function() {
            if ($('input[name="selectPesan[]"]:checked').length == $('input[name="selectPesan[]"]').length) {
                $('#selectPesanAll').prop('checked', true);
            } else {
                $('#selectPesanAll').prop('checked', false);
            }
            checkSelected()
        });

        function checkSelected() {
            if ($('input[name="selectPesan[]"]:checked').length > 0) {
                // Jika ada, hilangkan attribute disabled dari button
                $('#btnCetakNota').removeAttr('disabled');
            } else {
                // Jika tidak ada, tambahkan attribute disabled ke button
                $('#btnCetakNota').attr('disabled', 'disabled');
            }
        }

        var table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.dataTable.getReport', $order->id) }}",
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
                    data: 'menu',
                    name: 'menu'
                },
                {
                    data: 'jumlah',
                    name: 'jumlah'
                },
                {
                    data: 'harga',
                    name: 'harga'
                },
                {
                    data: 'total',
                    name: 'total'
                },
                {
                    data: 'payment_method',
                    name: 'payment_method'
                },
                {
                    data: 'payment_update_by',
                    name: 'payment_update_by'
                },
            ],
            language: {
                emptyTable: "Semua pesanan telah selesai",
                loadingRecords: "Memuat..."
            },
            // drawCallback: function(settings) {
            //     var api = this.api();
            //     setTimeout(function() {
            //         api.ajax.reload(null, false); // user paging is not reset on reload
            //     }, 20000); // 10000 milidetik = 10 detik
            // },
            headerCallback: function(thead, data, start, end, display) {
                $(thead).find('th').css('text-align', 'left'); // pastikan align header tetap di tengah
            }
        });
    } );

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

    function modalUpdateStatus(id) {
        Swal.fire({
            title: "Kamu yakin?",
            text: "Kamu tidak akan bisa membatalkannya setelah ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, selesaikan!"
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatus(id);
            }
        });
    }
</script>
@endsection