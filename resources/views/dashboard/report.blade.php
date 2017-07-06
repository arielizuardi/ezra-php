@extends('dashboard.layouts.master')
@section('content')
    <div class="ui grid">
        <div class="row">
            <div class="twelve wide column">

                <form class="ui form sheets-form">
                    {{ csrf_field() }}
                    <div class="fields">
                        <div class="one field">
                            <label>Session</label>
                            <input type="text" name="session" id="session" maxlength="2" placeholder="Session">
                        </div>

                        <div class="one field">
                            <label>Batch</label>
                            <input type="text" name="batch" id="batch" maxlength="2" placeholder="Batch">
                        </div>

                        <div class="one field">
                            <label>Year</label>
                            <input type="text" name="year" id="year" maxlength="4" placeholder="Year">
                        </div>

                    </div>

                    <div class="fields">
                        <div class="four field">
                            <label>Presenter</label>
                            <div class="ui search selection dropdown">
                                <input type="hidden" name="presenter_id"/>
                                <i class="dropdown icon"></i>
                                <input type="text" class="search"/>
                                <div class="default text">
                                    Select presenter ...
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="ui button submit-spreadsheet-btn" type="submit">Submit</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="ten wide column">
                <div id="chart_div" style="width: 1000px; height: 500px;"></div>
                <div class="ui">
                    <div class="ui centered inline loader" id="report_dimmer">
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('script')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages: ['corechart', 'bar']});

        function drawMaterial(google_visualization_data) {
            var materialOptions = {
                chart: {
                    title: 'Report Presenter'
                },
                hAxis: {
                    title: 'Score',
                    minValue: 0,
                    format: 'decimal',
                },
                vAxis: {
                    title: 'Rata-rata'
                },
                bars: 'horizontal'
            };
            var materialChart = new google.charts.Bar(document.getElementById('chart_div'));
            materialChart.draw(google_visualization_data, materialOptions);
        }

        $('.submit-spreadsheet-btn').api({
            action: 'get report',
            serializeForm: true,
            method: 'GET',
            on: 'click',
            beforeSend: function (settings) {
                // form data is editable in before send
                $('#report_dimmer').addClass('active');
                return settings;
            },
            onResponse: function (response) {
                var data = response.data;
                // make some adjustments to response
                var gdata = google.visualization.arrayToDataTable(data);

                google.charts.setOnLoadCallback(drawMaterial(gdata));

                $('#report_dimmer').removeClass('active');
                return response;
            },
            onError: function (errorMessage, element, xhr) {
                alert('Whoops something went wrong. Contact your administrator.');
                console.log(xhr.status);
                console.log(errorMessage);
                window.location.href = '{{ url('/') }}';
            }
        });

        $('.ui.dropdown').dropdown({
            apiSettings: {
                url: '//ezra.dev/v1/presenter'
            }
        });
    </script>
@endsection