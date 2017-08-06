@extends('dashboard.layouts.master')
@section('content')
    <div class="ui grid">
        <div class="row">
            <div class="twelve wide column">

                <form class="ui form sheets-form">
                    {{ csrf_field() }}
                    <div class="fields">
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
                        <div class="six wide field">
                            <label>Spreadsheets ID </label>
                            <input type="text" name="spr_id" id="spr_id" placeholder="Spreadsheets ID" value="1KlNr_ziiiznhOrbdQrMvXGQ-gLW2pCVc3wphN1kp44g">
                        </div>
                        <div class="six wide field">
                            <label>Range</label>
                            <input type="text" name="range" id="range" placeholder="Range" value="Form Responses 1!A1:M554">
                        </div>
                    </div>

                    <div class="fields">
                        <div class="one field">
                            <label>Nama Facilitator</label>
                            <input type="text" name="nama" id="nama" maxlength="2"
                                   placeholder="Index of nama_facilitator">
                        </div>

                        <div class="one field">
                            <label>Mampu menjelaskan tujuan dan manfaat kelas ini dengan baik</label>
                            <input type="text" name="menjelaskan_tujuan" id="menjelaskan_tujuan" maxlength="2"
                                   placeholder="Index of menjelaskan_tujuan">
                        </div>

                        <div class="one field">
                            <label>Membangun hubungan baik dengan saya</label>
                            <input type="text" name="membangun_hubungan" id="membangun_hubungan" maxlength="2"
                                   placeholder="Index of membangun_hubungan">
                        </div>

                        <div class="one field">
                            <label>Mampu mengajak peserta untuk berdiskusi</label>
                            <input type="text" name="mengajak_berdiskusi" id="mengajak_berdiskusi" maxlength="2"
                                   placeholder="Index of mengajak_berdiskusi">
                        </div>
                    </div>

                    <div class="fields">
                        <div class="one field">
                            <label>Mampu membuat proses diskusi berjalan dengan baik</label>
                            <input type="text" name="memimpin_proses_diskusi" id="memimpin_proses_diskusi" maxlength="2"
                                   placeholder="Index of memimpin_proses_diskusi">
                        </div>

                        <div class="one field">
                            <label>Mampu menjawab pertanyaan / concern yang ada selama diskusi kelompok & memberikan
                                feedback yang bermanfaat</label>
                            <input type="text" name="mampu_menjawab_pertanyaan" id="mampu_menjawab_pertanyaan"
                                   maxlength="2"
                                   placeholder="Index of mampu_menjawab_pertanyaan">
                        </div>

                        <div class="one field">
                            <label>Memiliki kedalaman materi yang dibutuhkan</label>
                            <input type="text" name="kedalaman_materi" id="kedalaman_materi" maxlength="2"
                                   placeholder="Index of kedalaman_materi">
                        </div>

                        <div class="one field">
                            <label>Bersikap profesional, berbusana rapi serta berperilaku & bertutur kata sopan </label>
                            <input type="text" name="penampilan" id="penampilan" maxlength="2"
                                   placeholder="Index of penampilan">
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
                    title: 'Report Facilitator'
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
            action: 'save all facilitator report',
            serializeForm: true,
            method: 'POST',
            on: 'click',
            beforeSend: function (settings) {
                // form data is editable in before send
                return settings;
            },
            onResponse: function (response) {
                alert('Successfully import facilitator feedback');
                    // make some adjustments to response
                return response;
            },
            onError: function (errorMessage, element, xhr) {

                if (xhr.status == 401) {
                    console.log(errorMessage);
                    //window.location.href = '{{ url('auth/google') }}';
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