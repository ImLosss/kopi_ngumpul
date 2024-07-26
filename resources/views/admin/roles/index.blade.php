@extends('layouts.admin-layout')

@section('title')
    - Roles
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Roles</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Role Management</h5>
    </nav>
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <h6>All Roles</h6>
                    </div>
                    {{-- <div class="col-6 text-end">
                        <a class="btn bg-gradient-dark mb-0" href="#"><i class="fas fa-plus"></i>&nbsp;&nbsp;Add User</a>
                    </div> --}}
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table  align-items-center mb-0">
                        <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Roles</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Permissions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td class="h-auto">
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{ $role->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-content-center">
                                    <div class="row">
                                    @foreach($role->permissions as $permission)
                                        <div class="col-auto">
                                            <span style="border-radius: 1em" class="justify-center px-2 py-1 mr-2 text-xs text-bold text-white bg-gray-500">{{ $permission->name }}</span>
                                        </div>
                                    @endforeach
                                    </div>
                                </td>
                                {{-- <td class="text-center h-auto">
                                    @if(auth()->user()->hasRole($role->name) != $role->name)
                                    <form id="roleDelete" action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        @can('Role edit')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit Role">
                                            <i class="fa-solid fa-wand-magic-sparkles text-secondary"></i>
                                        </a>
                                        @endcan
                                        @can('Role delete')
                                        <button class="cursor-pointer fas fa-trash text-secondary" style="border: none; background: no-repeat;" data-bs-toggle="tooltip" data-bs-original-title="Delete Role"></button>
                                        @endcan
                                    </form>
                                    @endif
                                </td> --}}
                            </tr>
                        @endforeach
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
                url: "{{ route('admin.dataTable.getUser') }}"
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