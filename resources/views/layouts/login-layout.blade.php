<!DOCTYPE html>
<html lang="en">

@include('components.login-head')

<body class="">
<div class="container position-sticky z-index-sticky top-0">
    <div class="row">
        <div class="col-12">
            <!-- Navbar -->
            {{-- <x-authnavhead/> --}}
            <!-- End Navbar -->
        </div>
    </div>
</div>
<main class="main-content  mt-0">
    @yield('content')
</main>

@include('components.login-script')
@if (session()->has('alert'))
    <script>
        const Toast = Swal.mixin({
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
          }
          });
          Toast.fire({
            icon: "{{ session('alert') }}",
            title: "{{ session('message') }}"
          });
    </script>
  @endif
</body>

</html>
