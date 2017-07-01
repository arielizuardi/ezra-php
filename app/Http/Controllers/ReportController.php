<?php
namespace App\Http\Controllers;

use Google_Service_Sheets;
use Illuminate\Http\Request;


class ReportController extends Controller
{
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
        $idx_peng_materi = $request->get('penguasaan_materi');
        $idx_sis_penyajian = $request->get('sistematika_penyajian');
        $idx_mtd_penyajian = $request->get('metode_penyajian');
        $idx_peng_waktu = $request->get('pengaturan_waktu');
        $idx_alat_bantu = $request->get('alat_bantu');

        $svc = new Google_Service_Sheets($google_client);
        $result = $svc->spreadsheets_values->get($spr_id, 'RyanJauwena!A1:L231');

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

        $vals = [
            ['Rata-rata', 'Score'],
            ['Penguasaan Materi', $avg_peng_materi],
            ['Sistematika Penyajian', $avg_sis_penyajian],
            ['Metode Penyajian', $avg_mtd_penyajian],
            ['Pengaturan Waktu', $avg_peng_waktu],
            ['Penggunaan Alat Bantu', $avg_alat_bantu]
        ];

        $response = [
            'success' => true,
            'message' => 'OK',
            'data' => $vals
        ];

        return response()->json($response);
    }
}