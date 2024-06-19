@extends('layouts.admin-layout')

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
                  Rp{{ number_format($pemasukan) }}
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
                  Rp{{ number_format($profit) }}
                  <span class="text-success text-sm font-weight-bolder">+Rp{{ number_format($profitHariIni) }}</span>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-sm-6">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Staff</p>
                <h5 class="font-weight-bolder mb-0">
                  21
                  {{-- <span class="text-success text-sm font-weight-bolder">+5%</span> --}}
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
  <div class="row mb-3">
    <div class="col-xl-6">
      <div class="card mb-1 p-2">
        <div class="card-body px-0 pt-0 pb-0">
          <div id="chartPenjualan"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-6">
      <div class="card mb-1 p-2">
        {{-- <div class="card-header pb-3">
          <div class="row">
              <div class="col">
                <div class="d-flex justify-content-end flex-wrap">
                    <div style="margin-right: 20px">
                        <select class="form-control" onchange="update(this.value)" id="categorySelect">
                            <option value="" selected>Weakly</option>
                                <option value="asdd">Monthly</option>
                        </Select>
                    </div>
                </div>
            </div>
          </div>
        </div>  --}}
        <div class="card-body px-0 pt-0 pb-0">
          <div id="chartRating"></div>
        </div>
      </div>
    </div>
  </div>
  @if ($cekstok->count() > 0)
    <div class="row mb-3">
      <div class="col-xl-12">
        <div class="card pb-0 p-3">
          <div class="row align-items-center">
            <div class="col-8">
              <h6>Terdapat {{ $cekstok->count() }} menu yang telah habis. Segera restock!</h6>
            </div>
            <div class="col-4 d-flex justify-content-end">
              <a class="btn bg-gradient-secondary" href="{{ route('product') }}">CEK</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  
  @endif
  @if($habis->count() > 0 && $cekstok->count() == 0)
    <div class="row mb-3">
      <div class="col-xl-12">
        <div class="card pb-0 p-3">
          <div class="row align-items-center">
            <div class="col-8">
              <h6>Terdapat {{ $habis->count() }} menu yang akan habis. Segera restock!</h6>
            </div>
            <div class="col-4 d-flex justify-content-end">
              <a class="btn bg-gradient-secondary" href="{{ route('product') }}">CEK</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif
  {{-- <div class="row">
    <div class="d-flex align-items-center justify-content-center vh-100 text-white">
        @auth
        <h3>Welcome {{ Auth::user()->name }}</h3>
        @endauth
    </div>
  </div> --}}
@endsection

@section('script')
<script>
var labels = @json($datesArr);
var series = @json($series);
var ratingName = @json($ratingChart['name']);
var ratingSeries = @json($ratingChart['series']);
var ratingPenjualan = @json($ratingChart['penjualan']);

console.log(ratingName);
var optionsLine = {
  chart: {
    height: 328,
    type: 'line',
    zoom: {
      enabled: false
    }
  },
  stroke: {
    curve: 'smooth',
    width: 2
  },
  //colors: ["#3F51B5", '#2196F3'],
  series: series,
  title: {
    text: 'Penjualan',
    align: 'left',
    offsetY: 25,
    offsetX: 20
  },
  subtitle: {
    text: 'Weakly',
    offsetY: 45,
    offsetX: 20
  },
  markers: {
    size: 6,
    strokeWidth: 0,
    hover: {
      size: 9
    }
  },
  grid: {
    show: true,
    padding: {
      bottom: 0
    }
  },
  labels: labels,
  xaxis: {
    tooltip: {
      enabled: false
    }
  },
  // legend: {
  //   position: 'top',
  //   horizontalAlign: 'right',
  //   offsetY: -20
  // }
}

var optionsRating = {
  chart: {
    type: 'bar',
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 4,
      borderRadiusApplication: 'end',
    }
  },
  title: {
    text: 'Rating',
    offsetY: 20 ,
    offsetX: 20
  },
  colors: ['#00E396'],
  series: ratingSeries,
  xaxis: {
    categories: ratingName,
  },
  yaxis: {
    labels: {
      formatter: function(value) {
        var maxLength = 10; // Panjang maksimum teks
        if (value.length > maxLength) {
            return value.substring(0, maxLength) + '...';
        } else {
            return value;
        }
      },
      rotate: -45
    }
  },
  tooltip: {
      y: {
        formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
            // return w.globals.labels[dataPointIndex] + ' : ' + w.globals.initialSeries[seriesIndex].data[dataPointIndex] + '%';
            return w.globals.initialSeries[seriesIndex].data[dataPointIndex] + '%';
        },
        // title: {
        //   formatter: function(seriesName) {
        //       return ''; // Menghilangkan title dari series
        //   }
        // }
      },
      x: {
        formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
            return ratingName[dataPointIndex] + '(' + ratingPenjualan[dataPointIndex] + ')'; // Menampilkan nama kategori penuh
        }
      }
  }
}

var chartRating = new ApexCharts(document.querySelector("#chartRating"), optionsRating);
var chartLine = new ApexCharts(document.querySelector('#chartPenjualan'), optionsLine);
chartLine.render();
chartRating.render();

// setTimeout(() => {
//   chartRating.updateSeries([{ name: 'rating', data: [21, 23] }]);
//   chartRating.updateOptions({
//     xaxis: {categories: ['asdas', 'asdad']},
//     tooltip: {
//       x: {
//         formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
//             return ['asdas', 'asdad'][dataPointIndex];
//         },
//       }
//     }
//   });
// }, 3000);
</script>
@endsection