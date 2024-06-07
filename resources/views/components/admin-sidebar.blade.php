<!-- Side-Bar -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 " id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="" target="_blank">
            <img src="{{ asset('assets/img/header.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">{{ Auth::user()->roles->first()->name }} - {{ Auth::user()->name }}</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            @role('admin')
                <li class="nav-item">
                    <a class="nav-link {{ (Request::is('/') ? 'active' : '') }}" href="{{ route('home') }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <title>shop </title>
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                        <g transform="translate(1716.000000, 291.000000)">
                                            <g transform="translate(0.000000, 148.000000)">
                                                <path class="color-background opacity-6" d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z"></path>
                                                <path class="color-background" d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z"></path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text ms-1">Dashboard</span>
                    </a>
                </li>
            @endrole

            @can('cashierAccess')
                <li class="nav-item">
                    <a class="nav-link {{ (Request::is('cashier') ? 'active' : '') }}" href="{{ route('cashier') }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-money-coins {{ (Request::is('cashier') ? '' : 'text-dark') }} text-sm"></i>                    
                        </div>
                        <span class="nav-link-text ms-1">Cashier</span>
                    </a>
                </li>
            @endcan

            @can('orderAccess')
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#documentSideMenu" class="nav-link {{ (Request::is('order','order/*', 'payment', 'payment/*') ? 'active' : 'collapsed') }}" aria-controls="documentSideMenu"
                    role="button" aria-expanded="{{ (Request::is('order','order/*', 'payment', 'payment/*') ? 'true' : 'false') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-clipboard-list {{ (Request::is('order','order/*', 'payment', 'payment/*') ? '' : 'text-dark') }} text-sm"></i>
                    </div>
                    <span class="nav-link-text ms-1">Pesanan</span>
                </a>
                <div class="collapse {{ (Request::is('order','order/*', 'payment', 'payment/*') ? 'show' : '') }}" id="documentSideMenu" style="">
                    <ul class="nav ms-4 ps-3">
                        <li class="nav-item ">
                            <a class="nav-link {{ (Request::is('order', 'order/*') ? 'active' : '') }}" href="{{ route('order.index') }}">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal"> List Order </span>
                            </a>
                        </li>
                        @can('paymentAccess')
                        <li class="nav-item ">
                            <a class="nav-link {{ (Request::is('payment', 'payment/*') ? 'active' : '') }}" href="{{ route('payment') }}">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal"> Pembayaran </span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan

            @can('productAccess')
                <li class="nav-item">
                    <a class="nav-link {{ (Request::is('product','product/*') ? 'active' : '') }}" href="{{ route('product') }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-cart {{ (Request::is('product','product/*') ? '' : 'text-dark') }} text-sm"></i>                    
                        </div>
                        <span class="nav-link-text ms-1">Product</span>
                    </a>
                </li>
            @endcan
            
            @can('discountAccess')
                <li class="nav-item">
                    <a class="nav-link {{ (Request::is('discount','discount/*') ? 'active' : '') }}" href="{{ route('discount') }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-tags {{ (Request::is('discount','discount/*') ? '' : 'text-dark') }} text-sm"></i>                
                        </div>
                        <span class="nav-link-text ms-1">Discount</span>
                    </a>
                </li>
            @endcan
            
            @role('admin')
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">SETTINGS</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ (Request::is('user','user/*') ? 'active' : '') }}" href="{{ route('user') }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <svg width="32px" height="32px" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path class="color-background opacity-6" d="M15.5385 11.4899C17.7949 11.4899 19.641 9.65316 19.641 7.40826C19.641 5.16336 17.7949 3.32663 15.5385 3.32663C15.4359 3.32663 15.3334 3.32663 15.2308 3.32663C15.8462 4.34704 16.2564 5.57153 16.2564 6.79602C16.2564 8.53071 15.5385 10.1634 14.4103 11.3879C14.718 11.4899 15.1282 11.4899 15.5385 11.4899Z" fill="#030D45"/>
                                <path class="color-background opacity-6" d="M17.2821 13.6326H16.2565C17.7949 14.9591 18.8206 17 18.8206 19.2448C18.8206 19.7551 18.718 20.1632 18.6154 20.5714C19.9488 20.3673 20.7693 20.0612 21.2821 19.7551C21.7949 19.4489 22.0001 18.9387 22.0001 18.3265C22.0001 15.7755 19.8462 13.6326 17.2821 13.6326Z" fill="#030D45"/>
                                <path class="color-background" d="M9.38459 11.4898C10.6154 11.4898 11.641 11.0817 12.5641 10.2654C13.5897 9.44903 14.1025 8.1225 14.1025 6.79597C14.1025 5.77556 13.7948 4.75515 13.1795 4.04087C12.3589 2.81638 11.0256 2.00005 9.38459 2.00005C6.82049 2.00005 4.66664 4.14291 4.66664 6.69393C4.66664 9.34699 6.82049 11.4898 9.38459 11.4898Z" fill="#030D45"/>
                                <path class="color-background" d="M12.1538 13.9389C11.8462 13.9389 11.641 13.8369 11.3333 13.8369H7.4359C4.46154 13.8369 2 16.2859 2 19.245C2 19.9593 2.30769 20.4695 2.82051 20.8777C3.64103 21.3879 5.58974 22.0001 9.38461 22.0001C13.1795 22.0001 15.0256 21.3879 15.9487 20.8777C15.9487 20.8777 16.0513 20.7757 16.1538 20.7757C16.5641 20.4695 16.8718 19.9593 16.8718 19.245C16.7692 16.592 14.8205 14.3471 12.1538 13.9389Z" fill="#030D45"/>
                            </svg>
                        </div>
                        <span class="nav-link-text ms-1">Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ (Request::is('role','role/*') ? 'active' : '') }}" href="{{ route('role') }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <svg width="48x" height="48px" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                                <g fill="none" fill-rule="evenodd" transform="translate(0 3)">
                                    <path class="color-background opacity-6" fill="#000" d="M5.83245745,3.12541039 C6.47698077,0.721248552 7.36320033,-0.240416184 8.49111613,0.240416184 C10.1829898,0.961664736 10.9080786,0.721248552 11.6331673,0.240416184 C12.1165598,-0.080138728 12.8416485,-0.080138728 13.8084335,0.240416184 L17.1921809,1.68291329 C18.1589659,2.0034682 18.8034892,3.12541039 19.1257509,5.04873986 C19.4480125,6.97206934 19.6897088,8.25428898 19.8508396,8.89539881 C22.267802,9.85706354 23.6374141,11.2995606 23.9596758,13.2228901 C24.4430682,16.1078843 20.5759283,18.9928785 12.5999523,17.5503814 C4.62397623,16.1078843 -0.451644897,11.5399768 0.0317475914,8.89539881 C0.35400925,7.13234679 1.88475213,6.25082078 4.62397623,6.25082078 L5.83245745,3.12541039 Z"/>
                                    <path  fill="#808080" d="M4.88723097,5.53846154 C6.34892389,8.04517453 9.02869424,9.61187015 12.926542,10.2385484 C16.8243898,10.8652266 19.0981343,10.2385484 19.7477756,8.35851365 C20.0254081,9.7752343 20.0254081,10.7152517 19.7477756,11.1785658 C18.8331342,12.7049226 16.3357442,13.1465566 13.6573885,12.8235962 C9.7595407,12.3535875 6.91736002,11.100231 5.13084646,9.06352668 C4.64361549,8.43684843 4.4,7.88850497 4.4,7.41849628 C4.4,6.9484876 4.56241032,6.32180935 4.88723097,5.53846154 Z"/>
                                    <path class="color-background" fill="#000" d="M5.83245745,3.12541039 C6.47698077,0.721248552 7.36320033,-0.240416184 8.49111613,0.240416184 C10.1829898,0.961664736 10.9080786,0.721248552 11.6331673,0.240416184 C12.1165598,-0.080138728 12.8416485,-0.080138728 13.8084335,0.240416184 L17.1921809,1.68291329 C18.1589659,2.0034682 18.8034892,3.12541039 19.1257509,5.04873986 C19.4480125,6.97206934 19.6897088,8.25428898 19.8508396,8.89539881 C22.267802,9.85706354 23.6374141,11.2995606 23.9596758,13.2228901 C24.4430682,16.1078843 20.5759283,18.9928785 12.5999523,17.5503814 C4.62397623,16.1078843 -0.451644897,11.5399768 0.0317475914,8.89539881 C0.35400925,7.13234679 1.88475213,6.25082078 4.62397623,6.25082078 L5.83245745,3.12541039 Z"/>
                                    <path  fill="#808080" d="M4.88723097,5.53846154 C6.34892389,8.04517453 9.02869424,9.61187015 12.926542,10.2385484 C16.8243898,10.8652266 19.0981343,10.2385484 19.7477756,8.35851365 C20.0254081,9.7752343 20.0254081,10.7152517 19.7477756,11.1785658 C18.8331342,12.7049226 16.3357442,13.1465566 13.6573885,12.8235962 C9.7595407,12.3535875 6.91736002,11.100231 5.13084646,9.06352668 C4.64361549,8.43684843 4.4,7.88850497 4.4,7.41849628 C4.4,6.9484876 4.56241032,6.32180935 4.88723097,5.53846154 Z"/>
                                </g>
                            </svg>
                        </div>
                        <span class="nav-link-text ms-1">Roles</span>
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="#">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <svg class="text-dark" width="16px" height="16px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>settings</title> <g id="Basic-Elements" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Rounded-Icons" transform="translate(-2020.000000, -442.000000)" fill="#FFFFFF" fill-rule="nonzero"> <g id="Icons-with-opacity" transform="translate(1716.000000, 291.000000)"> <g id="settings" transform="translate(304.000000, 151.000000)"> <polygon class="color-background" id="Path" opacity="0.596981957" points="18.0883333 15.7316667 11.1783333 8.82166667 13.3333333 6.66666667 6.66666667 0 0 6.66666667 6.66666667 13.3333333 8.82166667 11.1783333 15.315 17.6716667"></polygon> <path class="color-background" d="M31.5666667,23.2333333 C31.0516667,23.2933333 30.53,23.3333333 30,23.3333333 C29.4916667,23.3333333 28.9866667,23.3033333 28.48,23.245 L22.4116667,30.7433333 L29.9416667,38.2733333 C32.2433333,40.575 35.9733333,40.575 38.275,38.2733333 L38.275,38.2733333 C40.5766667,35.9716667 40.5766667,32.2416667 38.275,29.94 L31.5666667,23.2333333 Z" id="Path" opacity="0.596981957"></path> <path class="color-background" d="M33.785,11.285 L28.715,6.215 L34.0616667,0.868333333 C32.82,0.315 31.4483333,0 30,0 C24.4766667,0 20,4.47666667 20,10 C20,10.99 20.1483333,11.9433333 20.4166667,12.8466667 L2.435,27.3966667 C0.95,28.7083333 0.0633333333,30.595 0.00333333333,32.5733333 C-0.0583333333,34.5533333 0.71,36.4916667 2.11,37.89 C3.47,39.2516667 5.27833333,40 7.20166667,40 C9.26666667,40 11.2366667,39.1133333 12.6033333,37.565 L27.1533333,19.5833333 C28.0566667,19.8516667 29.01,20 30,20 C35.5233333,20 40,15.5233333 40,10 C40,8.55166667 39.685,7.18 39.1316667,5.93666667 L33.785,11.285 Z" id="Path"></path> </g> </g> </g> </g> </svg>
                        </div>
                        <span class="nav-link-text ms-1">Permissions</span>
                    </a>
                </li> --}}
            @endrole
        </ul>
    </div>
</aside>
<!-- End of Side-Bar -->
