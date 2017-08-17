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
                            <label>From Year</label>
                            <input type="text" name="from_year" id="from_year" maxlength="4" placeholder="From Year">
                        </div>

                        <div class="one field">
                            <label>To Year</label>
                            <input type="text" name="to_year" id="to_year" maxlength="4" placeholder="To Year">
                        </div>

                    </div>
                    <button class="ui button submit-spreadsheet-btn" type="submit">Submit</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="one wide column"></div>
            <div class="eight wide column">
                <div id="table_div"></div>
            </div>
        </div>
        <div class="row">
            <div class="eight wide column">
                <div id="chart_div" style="width: 900px; height: 500px;"></div>
                <div class="ui">
                    <div class="ui inline loader" id="report_dimmer">
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('script')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages: ['corechart', 'bar', 'table']});

        function drawMaterial(google_visualization_data) {
            var materialOptions = {
                chart: {
                    title: 'Presenter Report'
                },
                hAxis: {
                    title: 'Indicator',
                    minValue: 0,
                    format: 'decimal',
                },
                vAxis: {
                    title: 'Average Score'
                },
                bars: 'horizontal',
                point: {visible : true}
            };
//            var materialChart = new google.charts.Bar(document.getElementById('chart_div'));
//            materialChart.draw(google_visualization_data, materialOptions);

            var table = new google.visualization.Table(document.getElementById('table_div'));
            var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
            chart.draw(google_visualization_data, materialOptions);
            table.draw(google_visualization_data, {showRowNumber: true, width: '100%', height: '300px'});
        }

        $('.submit-spreadsheet-btn').api({
            action: 'get presenter report',
            serializeForm: true,
            method: 'GET',
            on: 'click',
            beforeSend: function (settings) {
                settings.urlData.presenter_id = {{ $presenter_id }}
                // form data is editable in before send
                $('#report_dimmer').addClass('active');
                return settings;
            },
            onResponse: function (response) {
                var data = response.data;
                // make some adjustments to response
                if (data.length == 0) {
                    $('#report_dimmer').removeClass('active');
                    alert('Data not found');
                    $('#chart_div').empty();

                    return response;
                }

                var gdata = google.visualization.arrayToDataTable(data);
                google.charts.setOnLoadCallback(drawMaterial(gdata));

                $('.google-visualization-table-table').addClass('ui').addClass('table');
                $('#report_dimmer').removeClass('active');
                return response;
            },
            onError: function (errorMessage, element, xhr) {
                alert('Whoops something went wrong. Contact your administrator.');
                console.log(xhr.status);
                console.log(errorMessage);
            }
        });
    </script>
@endsection