<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ezra</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/semantic.min.css') }}">

        <!-- Styles -->
        <style>
            body {
                background: url(images/main.jpg) no-repeat center center fixed;
                -webkit-background-size: cover;
                -moz-background-size: cover;
                -o-background-size: cover;
                background-size: cover;
            }

            body > .grid {
                height: 100%;
            }

            body:after{
                position:fixed;
                content:"";
                top:0;
                left:0;
                right:0;
                bottom:0;
                background:rgba(0,0,0,0.7);
                z-index:-1;
            }

        </style>

        <script
                src="https://code.jquery.com/jquery-3.1.1.min.js"
                integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
                crossorigin="anonymous"></script>
        <script src="{{ asset('js/semantic.min.js') }}"></script>
    </head>
    <body>
    @if (session('flash_message'))
    <div class="ui modal">
        <div class="header">Message</div>
        <div class="content">
            {{ session('flash_message') }}
        </div>
    </div>
    @endif

    <div class="ui middle aligned left aligned container grid">
            <div class="column">
                <h1 class="ui inverted header">
                    <div class="ui inverted sub header">Welcome to, </div>
                    Community Of Leaders &nbsp;|&nbsp; Jakarta Praise Community Church
                </h1>
                <a style="background-color: #4285F4" class="ui google plus huge button" href="{{ url('auth/google') }}">
                    <i class="google icon"></i>
                    Sign in with Google
                </a>
        </div>
    </div>

    <script>
        @if (session('flash_message'))
        $('.ui.modal').modal('show');
        @endif
    </script>
    </body>
</html>
