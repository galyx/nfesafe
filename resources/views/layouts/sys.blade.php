<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('menu_title', 'Sistema NFESAFE')</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <link rel="stylesheet" href="{{asset('plugin/bootstrap-4.6.0/css/bootstrap.min.css')}}">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{asset('plugin/fontawesome-free/css/all.min.css')}}">
        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <!-- DateRangerPicker -->
        <link rel="stylesheet" href="{{asset('plugin/daterangepicker/daterangepicker.css')}}">
        <!-- Select2 -->
        <link rel="stylesheet" href="{{asset('plugin/select2/css/select2.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugin/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
        <!-- DataTables -->
        <link rel="stylesheet" href="{{asset('plugin/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugin/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
        <link rel="stylesheet" href="{{asset('/plugin/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset('css/sys.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/custom.min.css')}}">
        <!-- overlayScrollbars -->
        <link rel="stylesheet" href="{{asset('plugin/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    </head>
    <body class="hold-transition sidebar-mini layout-fixed text-sm sidebar-collapse">
        <div class="wrapper">
            <input type="hidden" id="user_id" value="1">
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>
                    @if (Request::is('notas'))
                        <li class="nav-item d-none d-sm-inline-block">
                            <select class="form-control form-control-sm select2 buscaNotas" data-autoBusca="true" name="company">
                                <option value="">Selecione uma Empresa</option>
                                @foreach ($companies as $company)
                                    <option value="{{$company->id}}">{{$company->corporate_name}}</option>
                                @endforeach
                            </select>
                        </li>
                        <li class="nav-item d-none d-sm-inline-block carregando"></li>
                        <li class="nav-item d-none d-sm-inline-block">
                            <button type="button" class="btn btn-success btn-sm d-none" id="notasRefresh"><i class="fas fa-sync"></i> Atualizar Notas</button>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out-alt"></i> Sair</a>
                    </li>
                </ul>
            </nav>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <!-- Brand Logo -->
                <a href="{{asset('/')}}" class="brand-link">
                    <span class="brand-text font-weight-light">Painel NFE</span>
                </a>

                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Sidebar user panel (optional) -->
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="image pl-1">
                            <span class="img-circle elevation-2" id="image_perfil" alt="User Image"></span>
                        </div>
                        <div class="info mt-auto">
                            <span class="d-block" id="user_name">Usuario Teste</span>
                            {{-- <span class="d-block" id="user_name">{{ auth()->user()->name }}</span> --}}
                        </div>
                    </div>

                    <!-- SidebarSearch Form -->
                    <div class="form-inline">
                        <div class="input-group" data-widget="sidebar-search">
                            <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-sidebar">
                                    <i class="fas fa-search fa-fw"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar Menu -->
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column nav-flat nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                            <li class="nav-item">
                                <a href="{{asset('/')}}" class="nav-link @if(Request::is('/')) active @endif">
                                    <i class="nav-icon fas fa-tachometer-alt"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{asset('notas')}}" class="nav-link @if(Request::is('notas')) active @endif">
                                    <i class="nav-icon fas fa-archive"></i>
                                    <p>Notas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{asset('empresas')}}" class="nav-link @if(Request::is('empresas')) active @endif">
                                    <i class="nav-icon fas fa-building"></i>
                                    <p>Empresas</p>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <!-- /.sidebar-menu -->
                </div>
                <!-- /.sidebar -->
            </aside>

            <div class="content-wrapper">
                @yield('container')
            </div>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>

        <!-- jQuery -->
        <script src="{{asset('plugin/jquery-3.6.0.min.js')}}"></script>
        <!-- bootstrap-4.6.0 -->
        <script src="{{asset('plugin/bootstrap-4.6.0/js/bootstrap.bundle.min.js')}}"></script>
        <!-- SweetALert 2 -->
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <!-- Moment -->
        <script src="{{asset('plugin/moment/moment.min.js')}}"></script>
        <!-- DateRangerPicker -->
        <script src="{{asset('plugin/daterangepicker/daterangepicker.js')}}"></script>
        <!-- Select2 -->
        <script src="{{asset('plugin/select2/js/select2.full.min.js')}}"></script>
        <!-- DataTables  & Plugins -->
        <script src="{{asset('plugin/datatables/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('plugin/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
        <script src="{{asset('plugin/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{asset('plugin/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
        <script src="{{asset('plugin/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
        <script src="{{asset('plugin/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
        <script src="{{asset('plugin/jszip/jszip.min.js')}}"></script>
        <script src="{{asset('plugin/pdfmake/pdfmake.min.js')}}"></script>
        <script src="{{asset('plugin/pdfmake/vfs_fonts.js')}}"></script>
        <script src="{{asset('plugin/datatables-buttons/js/buttons.html5.min.js')}}"></script>
        <script src="{{asset('plugin/datatables-buttons/js/buttons.print.min.js')}}"></script>
        <script src="{{asset('plugin/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
        <!-- overlayScrollbars -->
        <script src="{{asset('plugin/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
        <!-- AdminLTE App -->
        <script src="{{asset('js/sys.min.js')}}"></script>
        <!-- Funções -->
        <script src="{{asset('js/funcao.min.js')}}"></script>

        @yield('customjs')
    </body>
</html>