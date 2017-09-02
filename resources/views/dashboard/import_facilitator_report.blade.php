@extends('dashboard.layouts.master')
@section('content')
    <h2 class="ui header">Facilitator Report Importer
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
                <label>Batch</label>
                <input type="number" name="batch" id="batch" min="1" max="20">
            </div>

            <div class="one field">
                <label>Year</label>
                <input type="number" name="year" id="year" min="1900" max="2100">
            </div>

        </div>

        <div class="fields">
            <div class="six wide field">
                <label>Spreadsheets ID </label>
                <input type="text" name="spr_id" id="spr_id" placeholder="e.g 1KlNr_ziiiznhOrbdQrMvXGQ-gLW2pCVc3wphN1kp44g">
            </div>
            <div class="six wide field">
                <label>Range</label>
                <input type="text" name="range" id="range" placeholder="e.g Form Responses 1!A1:M554">
            </div>
        </div>

        <h3 class="ui dividing header">Index - Column Location</h3>
        <div class="fields">
            <div class="one field">
                <label data-position="top center" data-tooltip="Nama Partisipan">Nama Partisipan</label>
                <input type="number" name="nama_partisipan" id="nama_partisipan" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="DATE Partisipan">DATE Partisipan</label>
                <input type="number" name="date_partisipan" id="date_partisipan" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Nama Facilitator">Nama facilitator</label>
                <input type="number" name="nama" id="nama" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Mampu menjelaskan tujuan dan manfaat kelas ini dengan baik">Menjelaskan tujuan</label>
                <input type="number" name="menjelaskan_tujuan" id="menjelaskan_tujuan" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Membangun hubungan baik dengan saya">Membangun hubungan</label>
                <input type="number" name="membangun_hubungan" id="membangun_hubungan" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Mampu mengajak peserta untuk berdiskusi">Mengajak berdiskusi</label>
                <input type="number" name="mengajak_berdiskusi" id="mengajak_berdiskusi" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Mampu membuat proses diskusi berjalan dengan baik">Memimpin proses diskusi</label>
                <input type="number" name="memimpin_proses_diskusi" id="memimpin_proses_diskusi" min="0" max="20">
            </div>
        </div>

        <div class="fields">
            <div class="one field">
                <label data-position="right center" data-tooltip="Mampu menjawab pertanyaan / concern yang ada selama diskusi kelompok & memberikan
                    feedback yang bermanfaat">Mampu menjawab pertanyaan</label>
                <input type="number" name="mampu_menjawab_pertanyaan" id="mampu_menjawab_pertanyaan" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Memiliki kedalaman materi yang dibutuhkan">Kedalaman materi</label>
                <input type="number" name="kedalaman_materi" id="kedalaman_materi" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Bersikap profesional, berbusana rapi serta berperilaku & bertutur kata sopan">Penampilan</label>
                <input type="number" name="penampilan" id="penampilan" min="0" max="20">
            </div>

            <div class="one field">
                <label data-position="top center" data-tooltip="Masukan untuk facilitator">Masukan</label>
                <input type="number" name="masukan" id="masukan" min="0" max="20">
            </div>

        </div>
        <button class="ui button submit-spreadsheet-btn" type="submit">Submit</button>
    </form>
@endsection

@section('script')
    <script type="text/javascript">
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
                $('#report_dimmer').removeClass('active')
            }
        });

        $('.message .close').on('click', function () {
            $(this).closest('.message').transition('fade');
        });
    </script>
@endsection