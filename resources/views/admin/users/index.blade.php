@extends('layouts.admin-layout')

@section('title')
    - User
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Users</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">User Management</h5>
    </nav>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <h6>All Users</h6>
                    </div>
                    <div class="col-6 text-end">
                        <a class="btn bg-gradient-dark mb-0" href="{{ route('user.create') }}"><i class="fas fa-plus"></i>&nbsp;&nbsp;Add User</a>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table" id="dataTable3">
                        <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Email</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Phone</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Since</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
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


    $(document).ready( function () {
        var table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.dataTable.getUser') }}",
                error: function(xhr, error, thrown){
                    // console.log('An error occurred while fetching data.');
                    // Hide the default error message
                    $('#example').DataTable().clear().draw();
                }
            },
            columns: [
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'role',
                    name: 'role'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'since',
                    name: 'since'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
            headerCallback: function(thead, data, start, end, display) {
                $(thead).find('th').css('text-align', 'left'); // pastikan align header tetap di tengah
            },
        });

        var lastUpdate = {{ Cache::get('user_updated_at') ? Cache::get('user_updated_at') : null }};

        function checkUpdates() {
            $.ajax({
                url: '/users/updates',
                method: 'GET',
                success: function(response) {
                    if (lastUpdate && lastUpdate !== response.timestamp) {
                        table.ajax.reload(); // Reload DataTables jika ada perubahan
                    }
                    lastUpdate = response.timestamp;
                },
                complete: function() {
                    setTimeout(checkUpdates, 5000); // Polling setiap 5 detik
                }
            });
        }

        checkUpdates(); // Mulai polling saat halaman dimuat

        // function reloadTable() {
        //     table.ajax.reload(null, false); // Reload data without resetting pagination
        // }

        // // Set interval to reload table every 5 seconds
        // setInterval(reloadTable, 10000);
    } );
</script>
@endsection