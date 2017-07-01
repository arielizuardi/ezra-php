<?php
namespace App\Http\Controllers;

use App\ReportSettings;
use Google_Service_Exception;
use Google_Service_Sheets;
use Illuminate\Http\Request;


class ReportController extends Controller
{
    public function get(Request $request)
    {
        $batch = $request->get('batch');
        $year = $request->get('year');
        $session = $request->get('session');
        $presenter_id = $request->get('presenter_id');

        $report = ReportSettings::where('batch', '=', $batch)
            ->where('year', '=', $year)
            ->where('session', '=', $session)
            ->where('presenter_id', '=', $presenter_id)
            ->first();

        if (empty($report)) {
            $response = [
                'success' => true,
                'message' => 'OK',
                'data' => []
            ];
        }

        return response()->json($this->jsonResponse($report));
    }

    public function generate(Request $request)
    {
        /**
         * @var $gclient \Google_Client
         */
        $google_client = session('gclient');
        if (!$google_client) {
            dd('Oops something wrong with client');
        }

        $spr_id = $request->get('spr_id');
        $range = $request->get('range');
        $batch = $request->get('batch');
        $year = $request->get('year');
        $session = $request->get('session');
        $presenter_id = $request->get('presenter_id');

        $idx_peng_materi = $request->get('penguasaan_materi');
        $idx_sis_penyajian = $request->get('sistematika_penyajian');
        $idx_mtd_penyajian = $request->get('metode_penyajian');
        $idx_peng_waktu = $request->get('pengaturan_waktu');
        $idx_alat_bantu = $request->get('alat_bantu');

        try {
            $svc = new Google_Service_Sheets($google_client);
            $result = $svc->spreadsheets_values->get($spr_id, $range);

        } catch (Google_Service_Exception $ex) {
            if ($ex->getCode() == 401) {
                \Auth::guard()->logout();
                $request->session()->flush();
                $request->session()->regenerate();

                return response()->json(['success'=> false, 'error' => $ex->getMessage()])->setStatusCode(401);
            }
        } catch (\Exception $ex) {
            return response()->json(['success'=> false, 'error' => $ex->getMessage()])->setStatusCode(401);
        }

        $sum_peng_materi = 0;
        $sum_sis_penyajian = 0;
        $sum_mtd_penyajian = 0;
        $sum_peng_waktu = 0;
        $sum_alat_bantu = 0;

        $ct = 0;
        foreach ($result->values as $value) {
            $sum_peng_materi += $value[$idx_peng_materi];
            $sum_sis_penyajian += $value[$idx_sis_penyajian];
            $sum_mtd_penyajian += $value[$idx_mtd_penyajian];
            $sum_peng_waktu += $value[$idx_peng_waktu];
            $sum_alat_bantu += $value[$idx_alat_bantu];
            $ct++;
        }

        $avg_peng_materi = $sum_peng_materi/$ct;
        $avg_sis_penyajian = $sum_sis_penyajian/$ct;
        $avg_mtd_penyajian = $sum_mtd_penyajian/$ct;
        $avg_peng_waktu = $sum_peng_waktu/$ct;
        $avg_alat_bantu = $sum_alat_bantu/$ct;

        $report = $this->saveReportSettings([
            'spr_id' => $spr_id,
            'range' => $range,
            'year' => $year,
            'batch' => $batch,
            'session' => $session,
            'presenter_id' => $presenter_id,
            'penguasaan_materi' => $avg_peng_materi,
            'sistematika_penyajian' => $avg_sis_penyajian,
            'metode_penyajian' => $avg_mtd_penyajian,
            'pengaturan_waktu' => $avg_peng_waktu,
            'alat_bantu' => $avg_alat_bantu
        ]);

        return response()->json($this->jsonResponse($report));
    }

    protected function saveReportSettings($data)
    {
        $report = ReportSettings::where('spreadsheets_id', '=', $data['spr_id'])->where('range', '=', $data['range'])->first();

        if (empty($report)) {
            $report = new ReportSettings();
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

        if (!$report->save()){
            throw new \Exception('Failed to save report settings', 500);
        }

        return $report;
    }

    protected function jsonResponse(ReportSettings $report)
    {
        $vals = [
            ['Rata-rata', 'Score'],
            ['Penguasaan Materi', floatval($report->penguasaan_materi)],
            ['Sistematika Penyajian', floatval($report->sistematika_penyajian)],
            ['Metode Penyajian', floatval($report->metode_penyajian)],
            ['Pengaturan Waktu', floatval($report->pengaturan_waktu)],
            ['Penggunaan Alat Bantu', floatval($report->alat_bantu)]
        ];

        $response = [
            'success' => true,
            'message' => 'OK',
            'data' => $vals
        ];

        return $response;
    }
}