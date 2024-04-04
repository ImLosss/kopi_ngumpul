<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
  <div class="container-fluid py-1 px-3">
      @yield('breadcrumb')
      <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <!-- Search bar -->
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
              <div class="input-group">
                  <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                  <input type="text" class="form-control" placeholder="Type here...">
              </div>
          </div>
          <!-- End of Search bar -->
          <ul class="navbar-nav  justify-content-end">
              <!-- User Links -->
              <li class="nav-item dropdown pe-2 d-flex align-items-center">
                  <a href="javascript:;" id="dropdownMenuButton" class="nav-link dropdown-toggle text-body font-weight-bold px-0" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-user me-sm-1"></i>
                      @auth
                          <span class="d-sm-inline d-none">{{ Auth::user()->name }}</span>
                      @else
                          <span class="d-sm-inline d-none">Sign In</span>
                      @endauth
                  </a>
                  <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                      <li class="mb-2">
                          <a class="dropdown-item border-radius-md" href="">
                              <div class="d-flex py-1">
                                  <div class="d-flex flex-column justify-content-center">
                                      <h6 class="text-sm font-weight-normal mb-1">
                                          <i class="fa-solid fa-user"></i>
                                          <span class="font-weight-bold">&nbsp;&nbsp;Profile</span>
                                      </h6>
                                  </div>
                              </div>
                          </a>
                      </li>
                      <li class="mb-2">
                          <a class="dropdown-item border-radius-md" href=""
                             onclick="event.preventDefault();
                                                       document.getElementById('logout-form').submit();">
                              <div class="d-flex py-1">
                                  <div class="d-flex flex-column justify-content-center">
                                      <h6 class="text-sm font-weight-normal mb-1">
                                          <i class="fa-solid fa-right-from-bracket"></i>
                                          <span class="font-weight-bold">&nbsp;&nbsp;Logout</span>
                                      </h6>
                                  </div>
                              </div>
                          </a>
                          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                              @csrf
                          </form>
                      </li>
                  </ul>
              </li>
          </ul>
      </div>
  </div>
</nav>
<!-- End Navbar -->
