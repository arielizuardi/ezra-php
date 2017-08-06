@extends('dashboard.layouts.master')
@section('content')

    <div class="ui grid statistics">
        <div class="ui four wide column huge horizontal statistic">
            <div class="value">
                2,204
            </div>
            <div class="label">
                Views
            </div>
        </div>

        <div class="ui four wide column huge horizontal statistic">
            <div class="value">
                2,204
            </div>
            <div class="label">
                Views
            </div>
        </div>

        <div class="ui four wide column huge horizontal statistic">
            <div class="value">
                2,204
            </div>
            <div class="label">
                Views
            </div>
        </div>
    </div>

    <div class="ui grid">
        <div class="row">
            <div id="curve_chart" style="width: 900px; height: 500px"></div>
        </div>
        <div class="row">
            <div id="chart_div" style="width: 900px; height: 500px"></div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        google.charts.setOnLoadCallback(drawVisualization);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Batch/Year', 'Confirmed', 'Hadir Sesi Pertama', 'Complete'],
                ['2/2014',  254, 223, 204],
                ['3/2014',  249, 212, 197],
                ['1/2015',  263, 222, 212],
                ['2/2015',  315, 273, 245]
            ]);

            var options = {
                title: 'Kehadiran',
                curveType: 'function',
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
            chart.draw(data, options);
        }

        function drawVisualization() {
            // Some raw data (not necessarily accurate)
            var data = google.visualization.arrayToDataTable([
                ['Batch/Year', 'Materi Pembelajaran', 'Tempat & Fasilitas', 'Fasilitator', 'Presenter', 'Rekomendasi Peserta', 'Average'],
                ['3/2014', 3.59, 3.41, 3.72, 3.69, 3.86, (3.59 + 3.41 + 3.72 + 3.69 + 3.86)/5],
                ['1/2015', 3.58, 3.50, 3.65, 3.65, 3.82, (3.58 + 3.50 + 3.65 + 3.65 + 3.82)/5],
                ['2/2015', 3.65, 3.51, 3.66, 3.62, 3.90, (3.65 + 3.51 + 3.66 + 3.62 + 3.90)/5]
            ]);

            var options = {
                title: 'Summary Feedback Peserta',
                vAxis: {title: 'Average Score'},
                hAxis: {title: 'Batch/Year'},
                seriesType: 'bars',
                series: {5: {type: 'line'}}
            };

            var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }

    </script>
@endsection