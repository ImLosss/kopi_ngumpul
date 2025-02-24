@extends('layouts.login-layout')

@section('content')
<style>
    .gradient-text {
        /* font-size: 48px; */
        font-weight: bold;
        background: linear-gradient(to right, #aa5b22, #d18956, #A0522D, #F4A460);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .gradient-button {
        font-weight: bold;
        color: white;
        padding: 10px 20px;
        background: linear-gradient(to right, #aa5b22, #d18956, #A0522D, #F4A460);
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .gradient-button:hover {
        background: linear-gradient(to right, #F4A460, #A0522D, #D2691E, #8B4513);
        color: #ffffff;
    }
</style>
    <section>
        <div class="page-header min-vh-100">
            <div class="container">
                <div class="row">
                    <!-- Login Form -->
                    <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                        <div class="card card-plain mt-4 mb-2">
                            <div class="card-header pb-0 text-left bg-transparent">
                                <div class="row mb-3">
                                    {{-- <div class="col-3">
                                        <img src="{{ asset('./assets/img/logo.png') }}" style="width: 75px" alt="">
                                    </div> --}}
                                    <div class="col d-flex align-items-center">
                                        <h3 class="font-weight-bolder gradient-text">Kedai Sarjana</h3>
                                    </div>
                                </div>
                                <p class="mb-0" style="font-size: 15px">Enter your email and password to sign in</p>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('login') }}" role="form">
                                    @csrf
                                    <label>Email</label>
                                    <div class="mb-3">
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" name="email" aria-label="Email" value="{{ old('email') }}" aria-describedby="email-addon">
                                        @error('email')
                                            <span class="invalid-feedback text-xs" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                    <label>Password</label>
                                    <div class="mb-3">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" name="password" aria-label="Password" value="{{ old('password') }}" aria-describedby="password-addon">
                                        @error('password')
                                            <span class="invalid-feedback text-xs" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn gradient-button w-100 mt-4 mb-0">Sign in</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- End of  Login Form -->
                        <p class="text-secondary text-center">
                            Copyright © <script>
                                document.write(new Date().getFullYear())
                            </script> Valdy kadir | Taher
                        </p>
                    </div>
                    {{-- <div class="col-md-6">
                        <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                            <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n10" style="background-image:url('{{ asset('assets/img/bgLogin.jpeg') }}')"></div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </section>
@endsection
