<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- web page for overtime calling process
         should only be reached by supervisor role -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title id="my-title">{{ config('app.name') }} {{ config('app.app_port') }}</title>

    <!-- Scripts -->
    <!-- don't defer app.js loading, needed to make flash messages work -->
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- uses version 1.0.2, which requires jquery. Note: Upgrading bootstrap may remove jquery -->
    <script type="text/javascript" src="{{ asset('js/mdtimepicker.min.js') }}"></script>

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
                <div>            
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="/img/SBS_WebLogo.png" width="161" height="44" class="d-inline-block align-top" alt="">
                    </a>
                    <div style="font-size:0.7rem;margin-right:1rem;margin-top:0rem;">
                        Port: {{ config('app.app_port') }}
                        @if(config('app.debug'))
                            <span style="color:red;"><b> &nbsp; &nbsp; DEBUG MODE!</b></span>
                        @endif
                    </div>
                </div>

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
                            @if( !Request::is('admins/excel-csv-file-shift-codes'))
                                <a class="dropdown-item" href="{{ url('admins/excel-csv-file-shift-codes') }}">Admin - Shift Codes Import/Export</a>
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
        </nav>
        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <script>
        $('div.alert').not('.alert-important').delay(1100).hide(900);  // closes flash message
        $('#flash-overlay-modal').modal();

        // for countdown timer
        window.onload = function () {
            var fiveMinutes = 60 * 5,
                display = document.querySelector('#time');
            startTimer(fiveMinutes, display);
        };

        // textarea character counter
        // from: https://laracasts.com/discuss/channels/general-discussion/suggestions-counting-characters-in-a-textarea
        (function($) {
            $.fn.extend( {
                limiter: function(limit, elem) {
                    $(this).on("keyup focus", function() {
                        setCount(this, elem);
                    });
                    function setCount(src, elem) {
                        var chars = src.value.length;
                        if (chars > limit) {
                            src.value = src.value.substr(0, limit);
                            chars = limit;
                        }
                        elem.html( limit - chars );
                    }
                    setCount($(this)[0], elem);
                }
            });
        })(jQuery);

        var elem = $("#available_count");
        $("#msg_ot").limiter(140, elem);

    </script> 

</body>
</html>