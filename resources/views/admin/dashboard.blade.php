@extends('layouts.admin-layout')
@section('title')
  - Dashboard
@endsection
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" @role('admin')href="{{ route('home') }}"@endrole>Home</a></li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Dashboard</h5>
    </nav>
@endsection
@section('content')
  <div class="row mb-3">
    <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Pemasukan</p>
                <h5 class="font-weight-bolder mb-0">
                  Rp{{ number_format($totalPemasukan) }}
                  <span class="text-success text-sm font-weight-bolder">+Rp{{ number_format($pemasukanHariIni) }}</span>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
          <div class="row mt-2">
            <div id="chartPemasukan"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total keuntungan</p>
                <h5 class="font-weight-bolder mb-0">
                  Rp{{ number_format($keuntungan) }}
                  <span class="text-success text-sm font-weight-bolder">+Rp{{ number_format($keuntunganHariIni) }}</span>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="fa-solid fa-hand-holding-dollar text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
          <div class="row mt-2">
            <div id="chartProfit"></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Staff</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $totalUser }}
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="fa-solid fa-users text-lg opacity-10"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @if ($habis->count() > 0)
    <div class="row mb-3">
      <div class="col-xl-12">
        <div class="card pb-0 p-3">
          <div class="row align-items-center">
            <div class="col-10">
              <h6>Terdapat {{ $habis->count() }} Stock yang telah habis. Segera restock!</h6>
            </div>
            <div class="col-2 d-flex justify-content-end">
              <a class="btn bg-gradient-secondary" href="{{ route('stock') }}">CEK</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
  @if($sedikit->count() > 0 && $habis->count() == 0)
    <div class="row mb-3">
      <div class="col-xl-12">
        <div class="card pb-0 p-3">
          <div class="row align-items-center">
            <div class="col-10">
              <h6>Terdapat {{ $sedikit->count() }} Stock yang akan habis. Segera restock!</h6>
            </div>
            <div class="col-2 d-flex justify-content-end">
              <a class="btn bg-gradient-secondary" href="{{ route('stock') }}">CEK</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
  <div class="row mt-3">
    <div class="col-12">
        <div class="card mb-1 p-3">
            <div class="card-header pb-3">
                <div class="row">
                    <div class="col d-flex align-items-center">
                        <h6>Prediction Next Month</h6>
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-end flex-wrap">
                          <div class="mb-2" style="margin-right: 20px">
                            <a class="btn bg-gradient-secondary mt-2" href="{{ route('admin.printPrediction') }}" style="margin-right: 10px"><i class="fa-solid fa-print text-md"></i> Print</a>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table" id="prediksi">
                        <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Nama</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Prediksi Penjualan</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">Bahan</th>
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
$(document).ready(function() {
  var table = $('#prediksi').DataTable({
    processing: true,
    serverSide: true,
    ordering: false,
    ajax: {
        url: "{{ route('admin.dataTable.getPrediction') }}",
        error: function(xhr, error, thrown){
            console.log('An error occurred while fetching data.');
                // Hide the default error message
                $('#example').DataTable().clear().draw();
        }
    },
    columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'name', name: 'name' },
        { data: 'prediction', name: 'prediction' },
        { data: 'bahan', name: 'bahan' }
    ],
    language: {
        emptyTable: "Not Available"
    },
    headerCallback: function(thead, data, start, end, display) {
        $(thead).find('th').css('text-align', 'left'); // pastikan align header tetap di tengah
    },
    rowCallback: function(row, data, index) {
      // Periksa nilai colspan dari data
      if (!data.prediction) {
          // Tambahkan colspan ke kolom menu dan kosongkan kolom lainnya
          $('td:eq(1)', row).attr('colspan', 3);
          $('td:eq(1)', row).text(`Data Kurang (${ data.name })`);
          $('td:eq(1)', row).addClass('text-center');
          for (let i = 1; i < 7; i++) {
              $(`td:eq(2)`, row).remove();
          }
      }
    },
  });
});
</script>
@endsection