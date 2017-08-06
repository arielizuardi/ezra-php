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
                                <input type="hidden" name="presenter_id" id="presenter_id"/>
                                <i class="dropdown icon"></i>
                                <input type="text" class="search"/>
                                <div class="default text">
                                    Select presenter ...
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fields">
                        <div class="six wide field">
                            <label>Spreadsheets ID </label>
                            <input type="text" name="spr_id" id="spr_id" placeholder="Spreadsheets ID">
                        </div>
                        <div class="six wide field">
                            <label>Range</label>
                            <input type="text" name="range" id="range" placeholder="Range" value="RyanJauwena!A1:L231">
                        </div>
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
            action: 'save presenter report',
            serializeForm: true,
            method: 'POST',
            on: 'click',
            beforeSend: function (settings) {
                settings.urlData.presenter_id = $('#presenter_id').val();
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

                if (xhr.status == 401) {
                    console.log(errorMessage);
                    window.location.href = '{{ url('auth/google') }}';
                } else {
                    alert('Whoops something went wrong. Contact your administrator.');
                    console.log(xhr.status);
                    console.log(errorMessage);
                    //window.location.href = '{{ url('/') }}';
                }
            }
        });

        $('.ui.dropdown').dropdown({
            apiSettings: {
                url: '//ezra.dev/v1/presenter'
            }
        });
    </script>
@endsection