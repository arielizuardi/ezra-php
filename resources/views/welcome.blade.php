<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="google-signin-client_id" content="{{ env('GOOGLE_CLIENT_ID') }}">
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
                <div id="my-signin2"></div>
            </div>
    </div>


    <script>
        function onSuccess(googleUser) {
            var profile = googleUser.getBasicProfile();
            console.log('ID: ' + profile.getId());
            console.log('Name: ' + profile.getName());
            console.log('Image URL: ' + profile.getImageUrl());
            console.log('Email: ' + profile.getEmail());
            console.log('Auth Response: ' + googleUser.getAuthResponse());

            var id_token = googleUser.getAuthResponse().id_token;

            var signInUrl = '{{ url('/v1/signin') }}';
            var csrf = '{{ csrf_token() }}';
            $.post(signInUrl, {
                _token: csrf,
                id: profile.getId(),
                name: profile.getName(),
                avatar: profile.getImageUrl(),
                email: profile.getEmail(),
                id_token: id_token,
                auth_response: googleUser.getAuthResponse(true)
            })
                .done(function (data, success, response) {
                    if (response.status == 200) {
                        var redirect_to = data.redirect_to;
                        window.location.href = redirect_to;
                    }
                })
                .fail(function (xhr) {
                    var statusCode = xhr.status;

                    if (statusCode == 401) {
                        alert('Anda belum terdaftar sebagai user. Silahkan hubungi administrator untuk melakukan registrasi.');
                    }

                    if (statusCode == 500) {
                        alert('Whoops. Internal Server Error. Silahkan hubungi administrator.');
                    }
                 });
        }

        function onFailure(error) {
            alert('Gagal melakukan otorisasi menggunakan Google Sign In');
            console.log(error);
        }

        function renderButton() {
            gapi.signin2.render('my-signin2', {
                'include_granted_scopes': true,
                'scope': 'profile email https://www.googleapis.com/auth/spreadsheets.readonly',
                'width': 240,
                'height': 50,
                'longtitle': true,
                'theme': 'dark',
                'onsuccess': onSuccess,
                'onfailure': onFailure
            });
        }

    </script>
    <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>

    <script>
        @if (session('flash_message'))
        $('.ui.modal').modal('show');
        @endif
    </script>
    </body>
</html>
