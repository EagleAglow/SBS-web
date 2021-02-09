<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- web page for automatic refresh of scoreboard
         should only be reached by admin, supervisor, or superuser -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title id="my-title">Schedule Bid System</title>

    <!-- Scripts -->

    <!-- Scripts -->
    <!-- don't defer loading, needed to make flash messages work -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="/img/leaf-transparent.png" width="40" height="40" class="d-inline-block align-top" alt="">
                    Schedule Bid System
                    @if(config('app.debug'))
                        <span style="color:red;"> &nbsp;DEBUG MODE!</span>
                    @endif 
                </a>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Right Side Of Navbar -->
                    <div class="navbar-nav ml-auto">
                        <!-- Role/Permission Links -->
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle btn-user" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">User: {{ Auth::user()->name }}</button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @role('supervisor')
                                    @if( !Request::is('supervisors/dash'))
                                        <a class="dropdown-item" href="{{ route('supervisors.dash') }}">Supervisor</a>
                                    @endif
                                @endrole
                                @role('admin')
                                    @if( !Request::is('admins/dash'))
                                        <a class="dropdown-item" href="{{ route('admins.dash') }}">Admin - Bidders vs. Schedules</a>
                                    @endif
                                    @if( !Request::is('admins/dashBidding'))
                                        <a class="dropdown-item" href="{{ route('admins.dashBidding') }}" onclick="$('#cover-spin').show(0)">Admin - Manage Bidding</a>
                                    @endif
                                    @if( !Request::is('users'))
                                        <a class="dropdown-item" href="{{ url('users') }}" class="btn btn-primary" onclick="$('#cover-spin').show(0)">Admin - Users</a>
                                    @endif
                                    @if( !Request::is('admins/schedules'))
                                        <a class="dropdown-item" href="{{ url('admins/schedules') }}" class="btn btn-primary">Admin - Schedules</a>
                                    @endif
                                    @if( !Request::is('admins/biddergroups'))
                                        <a class="dropdown-item" href="{{ url('admins/biddergroups') }}" class="btn btn-primary">Admin - Bidder Groups</a>
                                    @endif
                                    @if( !Request::is('admins/linegroups'))
                                        <a class="dropdown-item" href="{{ url('admins/linegroups') }}" class="btn btn-primary">Admin - Line Groups</a>
                                    @endif
                                    @if( !Request::is('admins/shiftcodes'))
                                        <a class="dropdown-item" href="{{ url('admins/shiftcodes') }}" class="btn btn-primary">Admin - Shift Codes</a>
                                    @endif
                                    @if( !Request::is('admins/excel-csv-file-users'))
                                        <a class="dropdown-item" href="{{ url('admins/excel-csv-file-users') }}">Admin - Users Import/Export</a>
                                    @endif
                                    @if( !Request::is('admins/excel-csv-file-schedules'))
                                        <a class="dropdown-item" href="{{ url('admins/excel-csv-file-schedules') }}">Admin - Schedules Import/Export</a>
                                    @endif
                                    @if( !Request::is('admins/logitems'))
                                        <a class="dropdown-item" href="{{ url('admins/logitems') }}">Admin - Log</a>
                                    @endif
                                    @if( !Request::is('admins/settings'))
                                        <a class="dropdown-item" href="{{ url('admins/settings') }}">Admin - Settings</a>
                                    @endif

                                @endrole
                                @role('superuser')
                                    @if( !Request::is('superusers/dash'))
                                        <a class="dropdown-item" href="{{ route('superusers.dash') }}">Superuser</a>
                                    @endif
                                @endrole
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>