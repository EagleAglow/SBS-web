<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <!-- use the following line to force new favicon -->
        <link rel="shortcut icon" href="/favicon.ico?v=5" />
        <!-- maybe use this, but needs IP address or domain name -->
<!--        <link rel="icon" href="http://192.168.1.31/favicon.ico?v=3" />
-->  
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title id="my-title">{{ config('app.name') }} {{ config('app.app_port') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Roboto', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                display: flex;
                align-items: top;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 1.3rem;
                top:  1.3rem;
            }

            .content {
                text-align: center;
                width:93%;
            }

            .title {
                font-size: 45px;
                color:black;
                font-weight:300;
                margin-left:10%;
                margin-right:10%;
                padding:15px 20px 5px 20px;
                border: 1px solid red;
                border-radius: 15px;
            }

            .links > a {
                color: #fff;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 20px;
                margin-top: 30px;
            }

            .btn {
                display: inline-block;
                font-weight: 400;
                color: #fff;
                text-align: center;
                vertical-align: middle;
                -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                        user-select: none;
                background-color: transparent;
                border: 1px solid transparent;
                padding: 0.375rem 0.75rem;
                font-size: 0.9rem;
                line-height: 1.6;
                border-radius: 0.25rem;
                transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                }

                @media (prefers-reduced-motion: reduce) {
            .btn {
                    transition: none;
                }
                }

                .btn:hover {
                color: #212529;
                text-decoration: none;
                }

                .btn:focus,
                .btn.focus {
                outline: 0;
                box-shadow: 0 0 0 0.2rem rgba(52, 144, 220, 0.25);
                }

            .btn-primary {
                color: #fff;
                background-color: #3490dc;
                border-color: #3490dc;
                }

                .btn-primary:hover {
                color: #fff;
                background-color: #227dc7;
                border-color: #2176bd;
                }

                .btn-primary:focus,
                .btn-primary.focus {
                color: #fff;
                background-color: #227dc7;
                border-color: #2176bd;
                box-shadow: 0 0 0 0.2rem rgba(82, 161, 225, 0.5);
                }


        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <div class="content">
                <div class="title m-b-md">
                    <img src="/img/SBS_WebLogoOriginal.png" width="258" height="90" alt="">
                </div>

                @if (Route::has('login'))
                    <div class="center links btn btn-primary">
                        @auth
                            <a href="{{ url('/home') }}">Home</a>
                        @else
                            <a href="{{ route('login') }}">Login</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}">Register</a>
                            @endif
                        @endauth
                    </div>
                @endif
                @if(config('app.debug'))
                    <div><h4 style="color:red;">WARNING - This site is in DEBUG MODE!</h4></div>
                @endif 
                <div><h4>Port: {{ config('app.app_port') }}</h4></div>
            </div>
        </div>
    </body>
</html>