@extends('dashboard.layouts.master')
@section('content')

    <h2 class="ui header">Presenter Report Importer
        <div class="sub header">Import your Google Spreadsheets Documents</div>
    </h2>
    <div class="ui hidden negative message">
        <i class="close icon"></i>
        <div class="header">Whoops!</div>
        <p class="content"></p>
        <b><p class="todo"></p></b>
    </div>
    <div class="ui hidden info message">
        <i class="close icon"></i>
        <div class="header">Info</div>
        <p class="content">
        </p>
    </div>
    <div class="ui divider"></div>

    <form class="ui form sheets-form">
        {{ csrf_field() }}
        <div class="fields">
            <div class="one field">
                <label>Session</label>
                <input type="number" name="session" id="session" min="1" max="20">
            </div>

            <div class="one field">
                <label>Batch</label>
                <input type="number" name="batch" id="batch" min="1" max="20">
            </div>

            <div class="one field">
                <label>Year</label>
                <input type="number" name="year" id="year" min="1900" max="2100" >
            </div>

        </div>

        <div class="fields">
            <div class="six field">
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
                <input type="text" name="spr_id" id="spr_id" placeholder="e.g 1KlNr_ziiiznhOrbdQrMvXGQ-gLW2pCVc3wphN1kp44g">
            </div>
            <div class="six wide field">
                <label>Range</label>
                <input type="text" name="range" id="range" placeholder="e.g Form Responses 1!A1:L231">
            </div>
        </div>

        <h3 class="ui dividing header">Index - Column Location</h3>

        <div class="fields">
            <div class="one field">
                <label>Penguasaan Materi</label>
                <input type="number" name="penguasaan_materi" id="penguasaan_materi" min="0" max="20">
            </div>

            <div class="one field">
                <label>Sistematika Penyajian</label>
                <input type="number" name="sistematika_penyajian" id="sistematika_penyajian" min="0" max="20">
            </div>

            <div class="one field">
                <label>Gaya atau Metode Penyajian</label>
                <input type="number" name="metode_penyajian" id="metode_penyajian" min="0" max="20">
            </div>

            <div class="one field">
                <label>Pengaturan Waktu</label>
                <input type="number" name="pengaturan_waktu" id="pengaturan_waktu" min="0" max="20">
            </div>

            <div class="one field">
                <label>Penggunaan Alat Bantu</label>
                <input type="number" name="alat_bantu" id="alat_bantu" min="0" max="20">
            </div>
        </div>

        <button class="ui button submit-spreadsheet-btn" type="submit">Submit</button>
    </form>
@endsection

@section('script')
    <script type="text/javascript">

        $('.submit-spreadsheet-btn').api({
            action: 'save presenter report',
            serializeForm: true,
            method: 'POST',
            on: 'click',
            beforeSend: function (settings) {
                var presenter_id = $('#presenter_id').val();
                settings.urlData.presenter_id = presenter_id;
                // form data is editable in before send
                $('#report_dimmer').addClass('active');
                return settings;
            },
            onResponse: function (response) {
                if (response.success == true) {
                    $('.info .content').after('Import Success!');
                    $('.info').removeClass('hidden');
                    $('.info').addClass('visible');
                }

                $('#report_dimmer').removeClass('active');
                return response;
            },
            onError: function (errorMessage, element, xhr) {

                if (xhr.status == 401) {
                    $('.negative .content').after(errorMessage);
                    $('.negative .todo').after('Please re-login <a href="/">here</a>');
                } else {
                    $('.negative .content').after('Whoops something went wrong. Contact your administrator.');
                }

                $('.negative').removeClass('hidden');
                $('.negative').addClass('visible');
                $('#report_dimmer').removeClass('active');
            }
        });

        var dropdownUrl = '{{ url('/v1/presenter') }}';
        $('.ui.dropdown').dropdown({
            apiSettings: {
                url: dropdownUrl,
                cache: false
            },
            filterRemoteData: true
        });

        $('.message .close').on('click', function () {
            $(this).closest('.message').transition('fade');
        });

    </script>
@endsection