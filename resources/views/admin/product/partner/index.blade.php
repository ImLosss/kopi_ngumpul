@extends('layouts.admin-layout')

@section('title')
    - Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}@endrole">Home</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Up Harga</li>
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
                    <div class="col d-flex align-items-center">
                        <h6>All Products</h6>
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-end flex-wrap">
                            <div class="mb-2" style="margin-right: 20px">
                                <select class="form-control" onchange="update(this.value)" id="categorySelect">
                                    <option value="" selected disabled>Pilih Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </Select>
                            </div>
                            <div>
                                <a class="btn bg-gradient-dark mb-0" href="{{ route('product.partner.create') }}"><i class="fas fa-plus"></i>&nbsp;&nbsp;Add Product</a>
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
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Nama</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Kategori</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Stock</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Harga</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Action</th>
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

    $(document).ready(function () {
        var table = $('#dataTable3').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.dataTable.getPartnerProduct') }}",
                data: function (d) {
                    d.category_id = $('#categorySelect').val(); // Mengirim category_id ke server
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'category', name: 'category' },
                { data: 'jumlah', name: 'jumlah' },
                { data: 'harga', name: 'harga' },
                { data: 'action', name: 'action' }
            ],
            headerCallback: function(thead, data, start, end, display) {
                $(thead).find('th').css('text-align', 'left'); // pastikan align header tetap di tengah
            },
        });

        // Fungsi update untuk memperbarui tabel berdasarkan kategori yang dipilih
        window.update = function (category_id) {
            $('#dataTable3').DataTable().ajax.reload();
        }
    });
</script>
@endsection