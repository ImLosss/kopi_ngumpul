@extends('layouts.admin-layout')

@section('content')
<div class="row mb-3">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Today's Money</p>
                <h5 class="font-weight-bolder mb-0">
                  $53,000
                  <span class="text-success text-sm font-weight-bolder">+55%</span>
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
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Today's Users</p>
                <h5 class="font-weight-bolder mb-0">
                  2,300
                  <span class="text-success text-sm font-weight-bolder">+3%</span>
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
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">New Clients</p>
                <h5 class="font-weight-bolder mb-0">
                  +3,462
                  <span class="text-danger text-sm font-weight-bolder">-2%</span>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Sales</p>
                <h5 class="font-weight-bolder mb-0">
                  $103,430
                  <span class="text-success text-sm font-weight-bolder">+5%</span>
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
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
    
  },
  colors: ['#00E396'],
  series: [{
    name: 'Rate(%)',
    data: [100,96,97,76,44,32,88,21]
  }],
  xaxis: {
    categories: ['Indomie ayam geprek',1992,1993,1994,1995,1996,1997, 1998,1999],
  }
}

var chartRating = new ApexCharts(document.querySelector("#chartRating"), optionsRating);
var chartLine = new ApexCharts(document.querySelector('#chartPenjualan'), optionsLine);
chartLine.render();
chartRating.render();
</script>
@endsection