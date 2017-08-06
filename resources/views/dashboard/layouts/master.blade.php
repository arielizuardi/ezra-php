<html>
<head>
    <title>

    </title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/semantic.min.css') }}">

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
        'get presenter report': '/v1/presenter/{presenter_id}/report',
        'save presenter report': '/v1/presenter/{presenter_id}/report',
        'save all facilitator report': '/v1/facilitator-report'
    };
</script>
@yield('script')
</body>
</html>