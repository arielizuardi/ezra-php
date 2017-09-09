<?php
namespace App\Presenter\Usecase;

use App\Presenter\ArrayResponse;
use App\PresenterReport;
use Google_Service_Sheets_ValueRange;

class PresenterUsecase
{
    public $keys = [
        'nama_partisipan',
        'date_partisipan',
        'penguasaan_materi',
        'sistematika_penyajian',
        'metode_penyajian',
        'pengaturan_waktu',
        'alat_bantu',
        'hal_yang_disuka',
        'hal_yang_diharapkan',
    ];

    public $string_value_keys = ['nama_partisipan', 'date_partisipan', 'hal_yang_disuka', 'hal_yang_diharapkan'];

    public function getAverageReportPresentersData($batch, $year)
    {
        $avg_report_presenters = \DB::table('presenter_report')
            ->selectRaw('
                AVG(penguasaan_materi) as penguasaan_materi,
                AVG(sistematika_penyajian) as sistematika_penyajian,
                AVG(metode_penyajian) as metode_penyajian,
                AVG(pengaturan_waktu) as pengaturan_waktu,
                AVG(alat_bantu) as alat_bantu
            ')
            ->where('batch', '=', $batch)
            ->where('year', '=', $year)
            ->first();

        if (empty($avg_report_presenters)) {
            return [];
        }

        $value = [
            ['Rata-rata', 'Average All Presenter Score'],
            ['Penguasaan Materi', floatval($avg_report_presenters->penguasaan_materi)],
            ['Sistematika Penyajian', floatval($avg_report_presenters->sistematika_penyajian)],
            ['Metode Penyajian', floatval($avg_report_presenters->metode_penyajian)],
            ['Pengaturan Waktu', floatval($avg_report_presenters->pengaturan_waktu)],
            ['Penggunaan Alat Bantu', floatval($avg_report_presenters->alat_bantu)]
        ];

        return $value;
    }

    public function getAverageScores(Google_Service_Sheets_ValueRange $valueRange, array $index)
    {
        $avg = [];
        $sum = [];

        $keys = array_diff($this->keys, $this->string_value_keys);
        foreach ($keys as $key) {
            $avg[$key] = 0;
            $sum[$key] = 0;
            $result[$key] = 0;
        }

        $ct = 0;

        foreach ($valueRange->values as $value) {
            foreach ($keys as $key) {
                $sum[$key] += $this->cleanScoreValue($value[$index[$key]]);
            }
            $ct++;
        }

        foreach ($keys as $key) {
            $avg[$key] = $sum[$key]/$ct;
        }

        return $avg;
    }

    public function getComments(Google_Service_Sheets_ValueRange $valueRange, array $index)
    {
        $comments = [];
        foreach ($valueRange->values as $i => $value) {

            if ($i == 0) {
                continue;
            }

            if (isset($value[$index['hal_yang_disuka']]) and strlen($value[$index['hal_yang_disuka']]) > 0) {
                $temp = [];
                $temp['nama'] = ucwords($value[$index['nama_partisipan']]);
                $temp['date'] = ucwords($value[$index['date_partisipan']]);
                $temp['hal_yang_disuka'] = ucwords($value[$index['hal_yang_disuka']], '.');
                $comments['hal_yang_disuka'][] = $temp;
            }

            if (isset($value[$index['hal_yang_diharapkan']]) and strlen($value[$index['hal_yang_diharapkan']]) > 0) {
                $temp = [];
                $temp['nama'] = ucwords($value[$index['nama_partisipan']]);
                $temp['date'] = ucwords($value[$index['date_partisipan']]);
                $temp['hal_yang_diharapkan'] = ucwords($value[$index['hal_yang_diharapkan']],'.');
                $comments['hal_yang_diharapkan'][] = $temp;
            }
        }

        return $comments;
    }

    public function savePresenterReport($data)
    {
        $report = PresenterReport::where('spreadsheets_id', '=', $data['spr_id'])->where('range', '=', $data['range'])->first();

        if (empty($report)) {
            $report = new PresenterReport();
            $report->spreadsheets_id = $data['spr_id'];
            $report->range = $data['range'];
        }

        $report->year = $data['year'];
        $report->batch = $data['batch'];
        $report->session = $data['session'];
        $report->presenter_id = $data['presenter_id'];
        $report->penguasaan_materi = $data['penguasaan_materi'];
        $report->sistematika_penyajian = $data['sistematika_penyajian'];
        $report->metode_penyajian = $data['metode_penyajian'];
        $report->pengaturan_waktu = $data['pengaturan_waktu'];
        $report->alat_bantu = $data['alat_bantu'];
        $report->raw_like_comments = $data['raw_like_comments'];
        $report->raw_wish_comments = $data['raw_wish_comments'];

        return $report->save();
    }

    protected function cleanScoreValue($value)
    {
        if (!is_int($value)) {
            $v = substr($value, 0, 1);
            return intval($v);
        }

        return $value;
    }

    public function fetchPresenterReport($presenter_id, $session, $from_year, $to_year)
    {
        $reports = PresenterReport::where('presenter_id', '=', $presenter_id)
            ->where('session', '=', $session)
            ->whereBetween('year', [$from_year, $to_year])
            ->orderBy('year', 'ASC')
            ->orderBy('batch', 'ASC')
            ->get();

        if ($reports->isEmpty()) {
            return [];
        }

        $response = new ArrayResponse($reports);
        return $response->toArray();
    }

    public function fetchComment($presenter_id, $session, $from_year, $to_year)
    {
        $presenter_reports = PresenterReport::where('presenter_id', '=', $presenter_id)
            ->where('session', $session)
            ->whereBetween('year', [$from_year, $to_year])
            ->orderBy('year', 'ASC')
            ->orderBy('batch', 'ASC')
            ->get();

        if ($presenter_reports->isEmpty()) {
            return [];
        }

        $response = [];

        foreach ($presenter_reports as $i => $item)
        {
            $key = $item->batch.':'.$item->year;
            if (!empty($item->raw_like_comments)) {
                $raw_like_comments = json_decode($item->raw_like_comments);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response[$key]['raw_like_comments'] = $raw_like_comments;
                }
            }

            if (!empty($item->raw_wish_comments)) {
                $raw_wish_comments = json_decode($item->raw_wish_comments);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response[$key]['raw_wish_comments'] = $raw_wish_comments;
                }
            }

        }

        return $response;
    }

}