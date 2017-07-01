@extends('dashboard.layouts.master')
@section('content')
    <div class="ui grid">
        <div class="row">
            <div class="twelve wide column">

                <form class="ui form sheets-form">
                    {{ csrf_field() }}
                    <div class="six wide field">
                        <label>Spreadsheets ID </label>
                        <input type="text" name="spr_id" id="spr_id" placeholder="Spreadsheets ID">
                    </div>

                    <div class="fields">
                        <div class="one field">
                            <label>Penguasaan Materi</label>
                            <input type="text" name="penguasaan_materi" id="penguasaan_materi" maxlength="2"
                                   placeholder="Index of penguasaan materi">
                        </div>

                        <div class="one field">
                            <label>Sistematika Penyajian</label>
                            <input type="text" name="sistematika_penyajian" id="sistematika_penyajian" maxlength="2"
                                   placeholder="Index of sistematika penyajian">
                        </div>

                        <div class="one field">
                            <label>Gaya atau Metode Penyajian</label>
                            <input type="text" name="metode_penyajian" id="metode_penyajian" maxlength="2"
                                   placeholder="Index of metode penyajian">
                        </div>

                        <div class="one field">
                            <label>Pengaturan Waktu</label>
                            <input type="text" name="pengaturan_waktu" id="pengaturan_waktu" maxlength="2"
                                   placeholder="Index of pengaturan waktu">
                        </div>

                        <div class="one field">
                            <label>Penggunaan Alat Bantu</label>
                            <input type="text" name="alat_bantu" id="alat_bantu" maxlength="2"
                                   placeholder="Index of penggunaan alat bantu">
                        </div>
                    </div>

                    <button class="ui button submit-spreadsheet-btn" type="submit">Submit</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="ten wide column">
                <div id="chart_div"></div>
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
            action: 'generate report',
            serializeForm: true,
            method: 'POST',
            on: 'click',
            beforeSend: function (settings) {
                // form data is editable in before send
                $('#report_dimmer').addClass('active')
                return settings;
            },
            onResponse: function (response) {
                var data = response.data;
                console.log(data);
                // make some adjustments to response

                var gdata = google.visualization.arrayToDataTable(data);

                google.charts.setOnLoadCallback(drawMaterial(gdata));

                $('#report_dimmer').removeClass('active');
                return response;
            }
        });
    </script>



@endsection

@section('_script')


    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        // Load the Visualization API and the corechart package.
        google.charts.load('current', {'packages': ['corechart']});

        // Set a callback to run when the Google Visualization API is loaded.
        google.charts.setOnLoadCallback(drawChart);

        // Callback that creates and populates a data table,
        // instantiates the pie chart, passes in the data and
        // draws it.
        function drawChart() {

            $('#chart_div').api({
                action: 'get data',
                on: 'now',
                onSuccess: function (response) {
                    var values = response.values
                    var data = new google.visualization.DataTable();

                    var d = [];
                    values.forEach(function (item, index, p3) {
                        if (index == 0) {
                            // Create the data table.
                            data.addColumn('string', item[0]);
                            data.addColumn('number', item[1]);

                        } else {
                            d.push(item);
                        }

                    });

                    data.addRows(d);

                    // Set chart options
                    var options = {
                        'title': 'How Much Pizza I Ate Last Night',
                        'width': 400,
                        'height': 300
                    };

                    // Instantiate and draw our chart, passing in some options.
                    var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
                    chart.draw(data, options);
                }
            });
        }
    </script>
    <script>
        $('.submit-spreadsheet-btn').api({
            action: 'generate report',
            serializeForm: true,
            method: 'POST',
            on: 'click',
            onResponse: function (response) {
                var data = response.data;
                console.log(data);
                // make some adjustments to response
                return response;
            }
        });


        //    $('.sheets-form').submit(function (e) {
        //        e.preventDefault();
        //
        //        var spr_id = $('#spr_id').val();
        //        var peng_materi = $('#penguasaan_materi').val();
        //        var sis_penyajian = $('#sistematika_penyajian').val();
        //        var mtd_penyajian = $('#metode_penyajian').val();
        //        var peng_waktu = $('#pengaturan_waktu').val();
        //        var alat_bantu = $('#alat_bantu').val();
        //
        //
        //
        //
        //    });


        //    $('.ui.dropdown').api({
        //        action: 'get feedback field',
        //        on: 'now',
        //        onSuccess: function (response) {
        //            console.log(response)
        //        }
        //    });

        //    $('.ui.dropdown').dropdown({
        //        apiSettings: {
        //            url: '//ezra.dev/v1/feedback/field'
        //        }
        //    });

    </script>

@endsection