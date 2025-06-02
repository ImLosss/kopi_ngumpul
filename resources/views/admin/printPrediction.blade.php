<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="{{ asset('./assets/img/logo.png') }}">
    <title>Kopi Ngumpul - Print Laporan</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('./assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('./assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="{{ asset('./assets/js/plugins/fontawesome.js') }}" crossorigin="anonymous"></script>
    <link href="{{ asset('./assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- CSS Files -->

    {{-- jQuery --}}
    <script src="{{ asset('assets/js/jquery-1.11.1.min.js') }}"
    crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            font-size: 10px;
            font-family: 'Times New Roman';
        }
        .table-responsive {
            margin-top: 10px;
        }
        .report-date {
            text-align: right;
        }

        
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col text-center">
                <h2>Laporan Prediksi Bulan {{ $nextMonth }}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mt-3">
                <table class="table table-borderless">
                    
                </table>
            </div>
            
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Prediksi Penjualan</th>
                        <th>Bahan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $no => $item)
                        <tr>
                            <td>{{ $no+=1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>
                                @foreach ($prediction as $p)
                                    @if ($p['product_id'] == $item->id)
                                        {{ $p['prediction'] }}
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach ($prediction as $p)
                                    @if ($p['product_id'] == $item->id)
                                        @php
                                            $strBahan = '';
                                            foreach ($item->stocks as $stock) {
                                                $strBahan .= '- ' . $stock->name . ' : ' . number_format(($stock->pivot->gram_ml * $p['prediction'])) . ' - ' . number_format($stock->jumlah_gr) . ' (gudang)' . '<br>';
                                            }
                                            echo $strBahan;
                                        @endphp
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- <div class="row">
            <div class="col-11">
                <div class="d-flex justify-content-end">
                    <div class="text-left">
                        Makassar, sa<br>Owner,<br><br><br><div style="text-decoration: underline">asd</div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    @include('components.admin-script')
    <script>
        $(document).ready( function () {
            // Pemanggilan window.print() akan melakukan pencetakan saat halaman dimuat
            window.onload = function() {
                window.print();
            }

            // Deteksi ketika pencetakan dibatalkan (dalam beberapa kasus)
            window.onafterprint = function() {
                // Kembali ke halaman sebelumnya jika pencetakan dibatalkan
                window.location.href = document.referrer;
            }
        });
    </script>
</body>
</html>
