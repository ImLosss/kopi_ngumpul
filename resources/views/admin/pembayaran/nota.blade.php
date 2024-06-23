<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" type="image/png" href="{{ asset('./assets/img/logo.png') }}">
        <title>Kopi Ngumpul - Print Bill</title>
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
    </head>
    <body>
        <style>* {
            font-size: 12px;
            font-family: 'Times New Roman';
        }
        
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        
        td.description,
        th.description {
            width: 110px;
            max-width: 110px;
        }
        
        td.quantity,
        th.quantity {
            width: 20px;
            max-width: 20px;
            word-break: break-all;
        }
        
        td.price,
        th.price {
            width: 80px;
            max-width: 80px;
            word-break: break-all;
            text-align: end;
        }
        
        .centered {
            text-align: center;
            align-content: center;
        }
        
        .ticket {
            width: 210px;
            max-width: 210px;
        }
        
        img {
            max-width: inherit;
            width: inherit;
        }
        
        @media print {
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
        }</style>
        <div class="ticket">
            <p class="centered"><b>KOPI NGUMPUL</b>
                <br>Jln Perintis Kemerdekaan VII
                <br>Kota Makassar
                <br>Sulawesi Selatan, 90245</p>
            <table>
                <tr>
                    <td style="width: 70px; word-break: break-all;">Title: Cashier</td>
                    <td style="width: 70px; word-break: break-all; text-align:center;">OP: {{ Auth::user()->name }}</td>
                    <td style="width: 70px; word-break: break-all; text-align:end">{{ $kasir }}</td>
                </tr>
            </table>
            <table style="border-bottom: 1px dashed black;">
                @foreach ($order as $key => $item)
                    <tr>
                        <td style="width: 100px; word-break: break-all;">{{ $item->customer_name != null ? $item->customer_name : 'Order ' .  $key+1 }}</td>
                        <td></td>
                        <td style="width: 110px; word-break: break-all; text-align:end">{{ $item->created_at }}</td>
                    </tr>
                @endforeach
            </table>
            <table style="border-bottom: 1px dashed black;">
                <tbody>
                    @foreach ($cart as $item)
                        <tr>
                            <td class="quantity">{{ $item->jumlah }}</td>
                            <td class="description">{{ $item->menu }}</td>
                            <td class="price">{{ $item->partner_total != 0 ? $item->partner_total : 'Rp' . number_format($item->total + $item->total_diskon) }}</td>
                        </tr>
                        @if ($item->diskon_id != null)
                            <td class="quantity"></td>
                            <td class="description">Disc ({{ $item->discount->percent }}%)</td>
                            <td class="price">(Rp{{ number_format($item->total_diskon) }})</td>
                        @endif
                    @endforeach
                </tbody>
            </table>
            <table style="border-bottom: 4px double black;">
                <tbody>
                    @if ($diskon != 0)
                        <tr>
                            <td class="quantity"></td>
                            <td class="description"><b>ANDA HEMAT</b></td>
                            <td class="price"><b>Rp{{ number_format($diskon) }}</b></td>
                        </tr>
                    @endif
                    <tr>
                        <td class="quantity"></td>
                        <td class="description"><b>TOTAL</b></td>
                        <td class="price"><b>Rp{{ number_format($total) }}</b></td>
                    </tr>
                </tbody>
            </table>
            <p class="centered">{{ date('Y-m-d H:i:s') }}
                <br>Thanks for your purchase!
                <br>kopingumpul.store</p>
        </div>
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