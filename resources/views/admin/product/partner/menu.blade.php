<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="{{ asset('./assets/img/logo.png') }}">
    <title>Kopi Ngumpul - Print Menu</title>
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
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }
        .container {
            width: 90%;
            margin: 0 auto;
            background-color: white;
            padding-top: 20px;
        }
        h1 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 50px;
            color: #4a4a4a;
        }
        .menu-section {
            margin-bottom: 40px;
        }
        .menu-section h2 {
            font-size: 28px;
            color: #333;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .menu-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .menu-item .item-name {
            font-size: 18px;
            color: #555;
        }
        .menu-item .item-price {
            font-size: 18px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kopingumpul Menu</h1>

        <!-- Coffee Section -->
        @foreach ($data as $category)
            <div class="menu-section">
                <h2>{{ $category->name }}</h2>
                @foreach ($category->product as $product)
                    <div class="menu-item">
                        <span class="item-name">{{ $product->name }}</span>
                        <span class="item-price">Rp{{ number_format($product->harga + $product->partnerProduct->up_price) }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach
        
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
