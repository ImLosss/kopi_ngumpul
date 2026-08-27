@extends('layouts.admin-layout')
@section('title')
    - Dashboard
@endsection
@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark"
                    @role('admin')href="{{ route('home') }}"@endrole>Home</a></li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Dashboard</h5>
    </nav>
@endsection
@section('content')
    <div class="row mb-3">
        <div class="col-xl-5 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Pemasukan</p>
                                <h5 class="font-weight-bolder mb-0">
                                    Rp{{ number_format($pemasukan, 0, ',', '.') }}
                                    <span
                                        class="text-success text-sm font-weight-bolder">+Rp{{ number_format($pemasukanHariIni, 0, ',', '.') }}</span>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Penjualan Hari Ini</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $penjualanHariIni }}
                                    {{-- <span class="text-success text-sm font-weight-bolder">+Rp50000</span> --}}
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
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Staff</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $staff }}
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
    @if (0 > 0)
        <div class="row mb-3">
            <div class="col-xl-12">
                <div class="card pb-0 p-3">
                    <div class="row align-items-center">
                        <div class="col-10">
                            <h6>Terdapat 5 Stock yang telah habis. Segera restock!</h6>
                        </div>
                        <div class="col-2 d-flex justify-content-end">
                            <a class="btn bg-gradient-secondary" href="#">CEK</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if (0 > 0 && $habis->count() == 0)
        <div class="row mb-3">
            <div class="col-xl-12">
                <div class="card pb-0 p-3">
                    <div class="row align-items-center">
                        <div class="col-10">
                            <h6>Terdapat 5 Stock yang akan habis. Segera restock!</h6>
                        </div>
                        <div class="col-2 d-flex justify-content-end">
                            <a class="btn bg-gradient-secondary" href="#">CEK</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="row mb-3">
        <div class="col-12">
            <div class="card mb-1 p-3">
                <div class="card-header pb-3">
                    <div class="row">
                        <div class="col d-flex align-items-center">
                            <h6>Prediction</h6>
                        </div>
                        <div class="col-6 d-flex justify-content-end">
                        <!-- PILIHAN BULAN SEBELUMNYA -->
                        <div class="col-6 d-flex justify-content-end">
                            <!-- PILIHAN PERIODE -->
                            <div class="input-group input-group-sm w-auto">
                                <label class="input-group-text text-xs" for="predictionMonth">Periode:</label>
                                <select class="form-select text-xs font-weight-bold" id="predictionMonth">
                                    <optgroup label="Masa Depan (Prediksi)">
                                        <option value="1" selected>1 Bulan ke Depan</option>
                                    </optgroup>
                                    <optgroup label="Masa Lalu (Evaluasi)">
                                        <option value="-1">1 Bulan Sebelumnya</option>
                                        <option value="-2">2 Bulan Sebelumnya</option>
                                        <option value="-3">3 Bulan Sebelumnya</option>
                                    </optgroup>
                                </select>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">
                                        Nama</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">
                                        Trend Least</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">
                                        Holt</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-1">
                                        Hybrid</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Penjualan Per Bulan</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
        new Chart(monthlySalesCtx, {
            type: 'line',
            data: {
                labels: @json($monthlySalesLabels),
                datasets: [
                    {
                        label: 'Qty Terjual',
                        data: @json($monthlySalesValues),
                        borderColor: '#5e72e4',
                        backgroundColor: 'rgba(94,114,228,0.15)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'yQty'
                    },
                    {
                        label: 'Total Pemasukan (Rp)',
                        data: @json($monthlyRevenueValues),
                        borderColor: '#2eca6a',
                        backgroundColor: 'rgba(46,202,106,0.15)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'yRevenue'
                    }
                ]
            },
            options: {
                plugins: { legend: { display: true } },
                scales: {
                    yQty: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        title: { display: true, text: 'Qty' }
                    },
                    yRevenue: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        title: { display: true, text: 'Rupiah' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });

        $(document).ready(function() {
            var table = $('#prediksi').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route('admin.dataTable.getPrediction') }}",
                    data: function (d) {
                        // TAMBAHAN INI: Mengirimkan nilai dropdown ke Controller Laravel via Ajax
                        d.month_ahead = $('#predictionMonth').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.log('An error occurred while fetching data.');
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'trendLeast', name: 'trendLeast' },
                    { data: 'holt', name: 'holt' },
                    { data: 'hybrid', name: 'hybrid' },
                ],
                language: {
                    emptyTable: "Not Available"
                },
                headerCallback: function(thead, data, start, end, display) {
                    $(thead).find('th').css('text-align', 'left');
                }
            });

            // TAMBAHAN INI: Otomatis reload tabel saat dropdown periode diganti
            $('#predictionMonth').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
