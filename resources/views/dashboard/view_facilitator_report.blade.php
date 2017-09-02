@extends('dashboard.layouts.master')
@section('content')
    <div class="ui grid">
        <div class="row">
            <div class="twelve wide column">

                <form class="ui form sheets-form">
                    {{ csrf_field() }}
                    <div class="fields">
                        <div class="one field">
                            <label>From Year</label>
                            <input type="text" name="from_year" id="from_year" maxlength="4" placeholder="From Year">
                        </div>

                        <div class="one field">
                            <label>To Year</label>
                            <input type="text" name="to_year" id="to_year" maxlength="4" placeholder="To Year">
                        </div>
                    </div>

                    <button class="ui button submit-spreadsheet-btn" type="submit">View Reports</button>
                    <button class="ui button hidden submit-view-comments-btn" type="submit">View Comments</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="twelve wide column">
                <div class="ui comments">
                </div>
                <div class="ui">
                    <div class="ui centered inline loader" id="commment_dimmer">
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="twelve wide column">
                <div id="table_div_header"></div>
                <div id="table_div"></div>
            </div>
        </div>

        {{--<div class="ui comments">--}}
                    {{--<h3 class="ui dividing header">Comments</h3>--}}
                    {{--<div class="comment">--}}
                        {{--<div class="content">--}}
                            {{--<a class="author">Matt</a>--}}
                            {{--<div class="metadata">--}}
                                {{--<span class="date">DATE Rasuna 1</span>--}}
                                {{--<div class="ui star rating" data-rating="4" data-max-rating="4"></div> 3.5 out of 4--}}
                            {{--</div>--}}
                            {{--<div class="text">--}}
                                {{--How artistic!--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}

        <div class="row">
            <div class="ten wide column">
                <div id="chart_div_header"></div>
                <div id="chart_div" style="width: 1000px; height: 500px;"></div>
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
                    title: 'Report Facilitator'
                },
                hAxis: {
                    title: 'Indicator',
                    minValue: 0,
                    format: 'decimal'
                },
                vAxis: {
                    title: 'Average Score'
                },
            };

            var table = new google.visualization.Table(document.getElementById('table_div'));
            var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
            chart.draw(google_visualization_data, materialOptions);
            table.draw(google_visualization_data, {showRowNumber: true, height: '600px'});

            $('.google-visualization-table').css("max-width","");
        }

        $('.submit-view-comments-btn').api({
            action: 'get facilitator comment',
            serializeForm: true,
            method: 'GET',
            on: 'click',
            beforeSend: function (settings) {
                settings.urlData.facilitator_id = {{ $facilitator_id }}
                    // form data is editable in before send
                    $('#commment_dimmer').addClass('active');
                return settings;
            },
            onResponse: function (response) {
                var data = response.data;
                // make some adjustments to response
                $('.comments').empty();
                $('.comments').append('<h3 class="ui dividing header">Comments</h3>');
                var keys = Object.keys(data);
                keys.forEach(function(key, index, arr){
                    data[key].forEach(function (currentItem, p2, p3) {
                        $('.comments').append(
                            '<div class="comment">' +
                                '<div class="content">' +
                                    '<a class="author">' +currentItem.nama + '</a>' +
                                    '<div class="metadata">' +
                                        '<span class="date">'+ currentItem.date +'</span>' +
                                        '<div class="ui star rating" data-rating="4" data-max-rating="4"></div> 4 out of 4' +
                                    '</div>' +
                                    '<div class="text">' +
                                        currentItem.masukan +
                                    '</div>' +
                                '</div>' +
                            '</div>'
                        );
                    });
                });
                $('#commment_dimmer').removeClass('active');
                $('.rating').rating('disable');
                return response;
            },
            onError: function (errorMessage, element, xhr) {
                alert('Whoops something went wrong. Contact your administrator.');
                console.log(xhr.status);
                console.log(errorMessage);
            }
        });

        $('.submit-spreadsheet-btn').api({
            action: 'get facilitator report',
            serializeForm: true,
            method: 'GET',
            on: 'click',
            beforeSend: function (settings) {
                settings.urlData.facilitator_id = {{ $facilitator_id }}
                // form data is editable in before send
                $('#report_dimmer').addClass('active');
                return settings;
            },
            onResponse: function (response) {
                var data = response.data;
                // make some adjustments to response
                var gdata = google.visualization.arrayToDataTable(data);

                $('#table_div_header').empty();
                $('#table_div_header').append('<h3 class="ui dividing header">Report Table</h3><br/>');

                $('#chart_div_header').empty();
                $('#chart_div_header').append('<h3 class="ui dividing header">Chart Visualization</h3>');

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