@extends('layouts.admin-layout')

@section('title')
    - Order
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}"@endrole>Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('order.index') }}">Orders</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Show</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">List Pesanan</h5>
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
                        <h6>Meja {{ $order->no_meja }}</h6>
                    </div>
                    @if ($order->partner)
                        <div class="col-6 d-flex align-items-center justify-content-end" id="textPartner">
                            Partner
                        </div>
                    @endif
                    <div class="col-6 text-end" id="btnUpdate">
                        
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <form id="updateOrDelete" action="{{ route('admin.pesanan.updateOrDelete') }}" enctype="multipart/form-data" method="post">
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status pembayaran</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Note</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last update By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let table;

    function updateOrDelete() {
        $('#updateOrDelete').submit();
    }

    function updateStatus(key) {
        $('#formUpdate_'+key).submit();
    }
    
    function submit(key) {
        $('#formDelete_'+key).submit();
    }

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
            let code = `<button class="btn bg-gradient-success mb-1" id="btnUpdate2" onclick="modalUpdateStatusAll()">Update status</button>
            <button class="btn bg-gradient-danger mb-1" id="btnHapus" onclick="modalHapusAll()">Hapus</button>`;
            if ($('input[name="selectPesan[]"]:checked').length > 0) {
                if($('#textPartner').length) {
                    $('#btnUpdate').removeClass('col-6').addClass('col-3');
                    $('#textPartner').removeClass('col-6').addClass('col-3');
                } else {
                    $('#btnUpdate').removeClass('col-3').addClass('col-6');
                }
                $('#btnUpdate').empty();
                $('#btnUpdate').append(code);

                var anyChecked = $('.selectPesan[data-payment="true"]:checked').length > 0;
                
                // console.log(anyChecked);
                if (anyChecked) {
                    $('#btnHapus').attr('disabled', 'disabled');
                } else {
                    $('#btnHapus').removeAttr('disabled');
                    // $('#btnHapus').attr('disabled', 'disabled');
                }

                $('.selectPesan:checked').each(function() {
                    // Menggunakan .data()
                    var status = $(this).data('status');
                    
                    $('#btnUpdate2').removeAttr('disabled');

                    if(status == 'two') {
                        @cannot('updateStatusTwo') 
                            $('#btnUpdate2').attr('disabled', 'disabled');
                            return false;
                        @endcannot
                    } else if(status == 'three') {
                        @cannot('updateStatusThree') 
                            $('#btnUpdate2').attr('disabled', 'disabled');
                            return false;
                        @endcannot
                    } else if(status == 'fourth') {
                        @cannot('updateStatusFourth') 
                            $('#btnUpdate2').attr('disabled', 'disabled');
                            return false;
                        @endcannot
                    }
                });

                $('.selectPesan:checked').each(function() {
                    // Menggunakan .data()
                    var hapus = $(this).data('hapus');
                    
                    console.log(hapus);
                    
                    if(hapus == false) {
                        $('#btnHapus').attr('disabled', 'disabled');
                        return false;
                    }
                });
                
            } else {
                $('#textPartner').removeClass('col-3').addClass('col-6');
                $('#btnUpdate').empty();
            }
        }

        table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.dataTable.getPesanan', $order->id) }}",
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
                    data: 'status_pembayaran',
                    name: 'status_pembayaran'
                },
                {
                    data: 'note',
                    name: 'note'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'update_status_by',
                    name: 'update_status_by'
                },
                {
                    data: 'action',
                    name: 'action'
                },
                { data: 'colspan', name: 'colspan', visible: false }
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
                }, 20000); // 10000 milidetik = 10 detik
                
            },
            headerCallback: function(thead, data, start, end, display) {
                $(thead).find('th').css('text-align', 'left'); // pastikan align header tetap di tengah
            },
            rowCallback: function(row, data, index) {

                    // Periksa nilai colspan dari data
                    if (data.colspan && data.colspan > 1) {
                        // Tambahkan colspan ke kolom menu dan kosongkan kolom lainnya
                        $('td:eq(0)', row).attr('colspan', data.colspan);
                        $('td:eq(0)', row).text(`Deleted Order(${ data.menu })`);
                        $('td:eq(0)', row).addClass('text-center');
                        // $(`td:eq(0)`, row).remove();
                        for (let i = 1; i < 7; i++) {
                            $(`td:eq(1)`, row).remove();
                        }
                    }
                },
            columnDefs: [
                { width: '150px', targets: 4 },
                { width: '40px', targets: 0 }
            ],
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
                // submit(id);
                $.ajax({
                    url: '/pesanan/delete/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        if(!response.status) {
                            if(response.message == 'payment') return alert('info', 'Tidak bisa menghapus pesanan yang sudah lunas');
                            return alert('error', 'Something Error');
                        }
                        if(response.index) { 
                            alert('success', 'Order berhasil di hapus')
                            .then(() => {
                                return window.location.replace('{{ route('order.index') }}');
                            })
                        };
                        table.ajax.reload()
                        alert('success', 'Order berhasil di hapus');
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        table.ajax.reload();
                        alert('error', 'Something Error');
                    }
                });
            }
        });
    }

    function modalHapusAll() {
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
                // $('#action').val('hapus');
                // updateOrDelete();
                var anyChecked = $('.selectPesan[data-payment="true"]:checked').length > 0;

                if (anyChecked) alert('info', 'Tidak bisa menghapus pesanan yang sudah lunas');
                var selectedValues = [];
                $('input[name="selectPesan[]"]:checked').each(function() {
                    selectedValues.push($(this).val());
                });

                if (selectedValues.length == 0) return alert('info', 'Tidak ada pesanan yang terpilih');

                $.ajax({
                    url: '/pesanan/updateOrDelete', // Ganti dengan URL route Anda
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // CSRF token untuk keamanan
                        selectPesan: selectedValues,
                        action: 'hapus'
                    },
                    success: function(response) {
                        if(!response.status) {
                            if(response.message == 'payment') return alert('info', 'Tidak bisa menghapus pesanan yang sudah lunas');
                            return alert('error', 'Something Error');
                        }
                        if(response.index) { 
                            alert('success', 'Status berhasil di update')
                            .then(() => {
                                return window.location.replace('{{ route('order.index') }}');
                            })
                        };
                        table.ajax.reload()
                        alert('success', 'Status berhasil di update');
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        table.ajax.reload();
                        alert('error', 'Something Error');
                    }
                });
            }
        });
    }

    function modalUpdateStatusAll() {
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
                // $('#action').val('update');
                // updateOrDelete();
                var selectedValues = [];
                $('input[name="selectPesan[]"]:checked').each(function() {
                    selectedValues.push($(this).val());
                });

                if (selectedValues.length == 0) return alert('info', 'Tidak ada pesanan yang terpilih');

                $.ajax({
                    url: '/pesanan/updateOrDelete', // Ganti dengan URL route Anda
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // CSRF token untuk keamanan
                        selectPesan: selectedValues,
                        action: 'update'
                    },
                    success: function(response) {
                        if(!response.status) return alert('error', 'Something Error');
                        if(response.index) { 
                            alert('success', 'Status berhasil di update')
                            .then(() => {
                                return window.location.replace('{{ route('order.index') }}');
                            })
                        };
                        table.ajax.reload()
                        alert('success', 'Status berhasil di update');
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        table.ajax.reload();
                        alert('error', 'Something Error');
                    }
                });
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
                // updateStatus(id);
                $.ajax({
                    url: '/pesanan/update/' + id,
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        if(!response.status) return alert('error', 'Something Error');
                        if(response.index) { 
                            alert('success', 'Status berhasil di update')
                            .then(() => {
                                return window.location.replace('{{ route('order.index') }}');
                            })
                        };
                        table.ajax.reload()
                        alert('success', 'Status berhasil di update');
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        table.ajax.reload();
                        alert('error', 'Something Error');
                    }
                });
            }
        });
    }
</script>
@endsection