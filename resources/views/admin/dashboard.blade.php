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
                  9
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
  {{-- <div class="row mb-3">
    <div class="col-xl-6">
      <div class="card mb-3 p-2">
        <div class="card-body px-0 pt-0 pb-0">
          <div id="chartPenjualan"></div>
        </div>
      </div>
    </div>
    <div class="col-xl-6">
      <div class="card">
        <div class="card-header pb-0">
          <div class="row">
            <div class="col">
                <div class="d-flex justify-content-end flex-wrap">
                    <div class="mb-2" style="margin-right: 20px">
                      <select class="form-control" id="categorySelect">
                              <option value="121">Option 1</option>
                      </Select>
                    </div>
                </div>
            </div>
          </div>
        </div> 
        <div class="card-body px-0 pt-0 pb-0">
          <div id="chartRating"></div>
        </div>
      </div>
    </div>
  </div> --}}
  
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

// Menghitung tinggi chart berdasarkan jumlah data
var ratingBaseHeight = 180; // Tinggi minimum chart
var ratingHeightPerData = 30; // Tinggi tambahan per data
var ratingChartHeight = ratingBaseHeight + (ratingSeries[0].data.length * ratingHeightPerData);

var lineBaseHeight = 180; 
var lineHeightPerData = 9; 
var lineChartHeight = lineBaseHeight + (series.length * lineHeightPerData);

var optionsLine = {
  chart: {
    type: 'line',
    zoom: {
      enabled: false
    },
    height: lineChartHeight
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
    height: ratingChartHeight // Mengatur tinggi chart dinamis
  },
  title: {
    text: 'Rating',
    align: 'left',
    offsetX: 20
  },
  subtitle: {
    text: 'Weakly',
    offsetY: 20,
    offsetX: 20
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 4,
      borderRadiusApplication: 'end',
    }
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
      rotate: -45,
      offsetY: -10
    }
  },
  tooltip: {
      y: {
        formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
            let ratingVal = w.globals.initialSeries[seriesIndex].data[dataPointIndex];

            if (ratingVal > 79) return 'Sangat Direkomendasikan';
            else if(ratingVal > 49) return 'Direkomendasikan';
            else if(ratingVal > 29) return 'Dipertimbangkan Kembali';
            else if(ratingVal > 0) return 'Tidak direkomendasikan';
        }
      },
      x: {
        formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
            return ratingName[dataPointIndex] + '(' + ratingPenjualan[dataPointIndex] + ')'; // Menampilkan nama kategori penuh
        }
      }
  }
};

var optionsPemasukan = {
  chart: {
    id: 'spark1',
    group: 'sparks',
    type: 'line',
    height: 80,
    sparkline: {
      enabled: true
    },
    dropShadow: {
      enabled: true,
      top: 1,
      left: 1,
      blur: 1,
      opacity: 1,
    }
  },
  series: pemasukanSeries,
  xaxis: {
    categories: pemasukanDates,
  },
  stroke: {
    curve: 'smooth'
  },
  colors: ['#00E396'],
  tooltip: {
    x: {
      show: true,
    },
    y: {
      title: {
        formatter: function formatter(val) {
          return '';
        }
      },
      formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
        let ratingVal = w.globals.initialSeries[seriesIndex].data[dataPointIndex];

        let formattedNumber = ratingVal.toLocaleString('id-ID');
        return 'Rp' + formattedNumber
      }
    }
  }
}

var optionsProfit = {
  chart: {
    id: 'spark2',
    group: 'sparks',
    type: 'line',
    height: 80,
    sparkline: {
      enabled: true
    },
    dropShadow: {
      enabled: true,
      top: 1,
      left: 1,
      blur: 1,
      opacity: 1,
    }
  },
  series: profitSeries,
  xaxis: {
    categories: profitDates,
  },
  stroke: {
    curve: 'smooth'
  },
  colors: ['#00E396'],
  tooltip: {
    x: {
      show: true,
    },
    y: {
      title: {
        formatter: function formatter(val) {
          return '';
        }
      },
      formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
        let ratingVal = w.globals.initialSeries[seriesIndex].data[dataPointIndex];

        let formattedNumber = ratingVal.toLocaleString('id-ID');
        return 'Rp' + formattedNumber
      }
    }
  }
}

var chartRating = new ApexCharts(document.querySelector("#chartRating"), optionsRating);
var chartPemasukan = new ApexCharts(document.querySelector("#chartPemasukan"), optionsPemasukan);
var chartProfit = new ApexCharts(document.querySelector("#chartProfit"), optionsProfit);
var chartLine = new ApexCharts(document.querySelector('#chartPenjualan'), optionsLine);
chartLine.render();
chartRating.render();
chartPemasukan.render();
chartProfit.render();


$(document).ready(function() {
    $('#categorySelect').on('change', function() {
        // Get the selected value
        var selectedValue = $(this).val();
        // console.log(selectedValue);

        // Perform an AJAX GET request
        $.ajax({
            url: '{{ route("filterRating") }}',  // Replace with your endpoint
            type: 'GET',
            data: { option: selectedValue },
            success: function(data) {
              // Menghitung tinggi chart berdasarkan jumlah data
              ratingChartHeight = ratingBaseHeight + (data.series[0].data.length * ratingHeightPerData);

              chartRating.updateSeries(data.series);
              chartRating.updateOptions({
                chart: {
                  height: ratingChartHeight
                },
                xaxis: {categories: data.name},
                tooltip: {
                  x: {
                    formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
                        return data.name[dataPointIndex] + '(' + data.penjualan[dataPointIndex] + ')';
                    },
                  },
                  y: {
                    formatter: function(value, { series, seriesIndex, dataPointIndex, w }) {
                      // return w.globals.labels[dataPointIndex] + ' : ' + w.globals.initialSeries[seriesIndex].data[dataPointIndex] + '%';
                      let ratingVal = w.globals.initialSeries[seriesIndex].data[dataPointIndex];

                      if (ratingVal > 79) return 'Sangat Direkomendasikan';
                      else if(ratingVal > 49) return 'Direkomendasikan';
                      else if(ratingVal > 29) return 'Dipertimbangkan Kembali';
                      else if(ratingVal > 0) return 'Tidak direkomendasikan';
                    },
                  },
                }
              });
            },
            error: function(xhr, status, error) {
                // Handle errors
                $('#result').html('Error occurred: ' + error);
            }
        });
    });
});
</script>
@endsection