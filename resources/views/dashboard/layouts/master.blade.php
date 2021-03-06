<html>
<head>
    <title>

    </title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/semantic.min.css') }}">
    <meta name="google-signin-client_id" content="{{ env('GOOGLE_CLIENT_ID') }}">
</head>
<body>
    @include('dashboard.layouts.sidebar')
<script
        src="https://code.jquery.com/jquery-3.1.1.min.js"
        integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
        crossorigin="anonymous"></script>
<script src="{{ asset('js/semantic.min.js') }}"></script>
<script src="{{ asset('js/jquery-serialize-object.js') }}"></script>
<script>
    /* Two required variables */
    $.fn.api.settings.api = {
        'get feedback field' : '/v1/feedback/field',
        'get data' : '/v1/data',
        'generate report': '/v1/report',
        'get facilitator report': '/v1/facilitator/{facilitator_id}/report',
        'get facilitator comment': '/v1/facilitator/{facilitator_id}/comment',
        'get presenter report': '/v1/presenter/{presenter_id}/report',
        'get presenter comment': '/v1/presenter/{presenter_id}/comment',
        'save presenter report': '/v1/presenter/{presenter_id}/report',
        'save all facilitator report': '/v1/facilitator-report'
    };
</script>
<script>
    function onLoad() {
        gapi.load('auth2', function() {
            gapi.auth2.init();
        });
    }

    function signOut() {
        var token = '{{ csrf_token() }}';
        var logoutUrl = '{{ url('/v1/signout') }}';
        $.post(logoutUrl, {
            _token: token
        })
            .done(function (data) {
                var auth2 = gapi.auth2.getAuthInstance();
                auth2.signOut();
            }).fail(function (xhr){
            console.log(xhr.status);
        }).always(function () {
            window.location.href = "/";
        });
    }
</script>
<script src="https://apis.google.com/js/platform.js?onload=onLoad" async defer></script>
@yield('script')
</body>
</html>