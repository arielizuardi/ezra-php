<?php
namespace App\Http\Controllers;

use App\Facilitator;
use App\FacilitatorReport;
use App\ReportSettings;
use Google_Service_Exception;
use Google_Service_Sheets;
use Illuminate\Http\Request;


class ReportController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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

    public function getFacilitatorReport(Request $request)
    {
        $batch = $request->get('batch');
        $year = $request->get('year');
        $facilitator_id = $request->get('facilitator_id');

        $facilitator = Facilitator::where('id', '=', $facilitator_id)->first();

        $report = FacilitatorReport::where('batch', '=', $batch)
            ->where('year', '=', $year)
            ->where('facilitator_id', '=', $facilitator_id)
            ->first();

        if (empty($report)) {
            $response = [
                'success' => true,
                'message' => 'OK',
                'data' => []
            ];

            return response()->json($response);
        }

        $avg_all_class = \DB::table('facilitator_report')->selectRaw('
            AVG(menjelaskan_tujuan) as menjelaskan_tujuan,
            AVG(membangun_hubungan) as membangun_hubungan,
            AVG(mengajak_berdiskusi) as mengajak_berdiskusi,
            AVG(memimpin_proses_diskusi) as memimpin_proses_diskusi,
            AVG(mampu_menjawab_pertanyaan) as mampu_menjawab_pertanyaan,
            AVG(kedalaman_materi) as kedalaman_materi,
            AVG(penampilan) as penampilan
        ')->where('batch', '=', $batch)->where('year', '=', $year)->first();

        $vals = [
            ['Metrics', $facilitator->name.' Score', 'Average Class Facilitator Score'],
            ['Mampu menjelaskan tujuan dan manfaat kelas ini dengan baik', floatval($report->menjelaskan_tujuan), floatval($avg_all_class->menjelaskan_tujuan)],
            ['Membangun hubungan baik dengan saya', floatval($report->membangun_hubungan), floatval($avg_all_class->membangun_hubungan)],
            ['Mampu mengajak peserta untuk berdiskusi', floatval($report->mengajak_berdiskusi), floatval($avg_all_class->mengajak_berdiskusi)],
            ['Mampu membuat proses diskusi berjalan dengan baik', floatval($report->memimpin_proses_diskusi), floatval($avg_all_class->memimpin_proses_diskusi)],
            ['Mampu menjawab pertanyaan concern yang ada selama diskusi kelompok', floatval($report->mampu_menjawab_pertanyaan), floatval($avg_all_class->mampu_menjawab_pertanyaan)],
            ['Memiliki kedalaman materi yang dibutuhkan', floatval($report->kedalaman_materi),  floatval($avg_all_class->kedalaman_materi)],
            ['Bersikap profesional, berbusana rapi serta berperilaku & bertutur kata sopan', floatval($report->penampilan), floatval($avg_all_class->penampilan)]
        ];

        $response = [
            'success' => true,
            'message' => 'OK',
            'data' => $vals
        ];

        return response()->json($response);
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

//        try {
//            $svc = new Google_Service_Sheets($google_client);
//            $result = $svc->spreadsheets_values->get($spr_id, $range);
//
//        } catch (Google_Service_Exception $ex) {
//            if ($ex->getCode() == 401) {
//                \Auth::guard()->logout();
//                $request->session()->flush();
//                $request->session()->regenerate();
//
//                return response()->json(['success'=> false, 'error' => $ex->getMessage()])->setStatusCode(401);
//            }
//        } catch (\Exception $ex) {
//            return response()->json(['success'=> false, 'error' => $ex->getMessage()])->setStatusCode(401);
//        }

        list($result, $error_message, $status_code) = $this->getSpreadsheets($spr_id, $range);
        if (empty($result)) {
            return response()->json(['success' => false, 'error' => $error_message], $status_code);
        }

        $sum_peng_materi = 0;
        $sum_sis_penyajian = 0;
        $sum_mtd_penyajian = 0;
        $sum_peng_waktu = 0;
        $sum_alat_bantu = 0;

        $ct = 0;
        foreach ($result->values as $value) {
            $sum_peng_materi += $this->cleanScoreValue($value[$idx_peng_materi]);
            $sum_sis_penyajian += $this->cleanScoreValue($value[$idx_sis_penyajian]);
            $sum_mtd_penyajian += $this->cleanScoreValue($value[$idx_mtd_penyajian]);
            $sum_peng_waktu += $this->cleanScoreValue($value[$idx_peng_waktu]);
            $sum_alat_bantu += $this->cleanScoreValue($value[$idx_alat_bantu]);
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

    public function generateReportFacilitator(Request $request)
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

        $idx_nama_facilitator = $request->get('nama_facilitator');
        $idx_menjelaskan_tujuan = $request->get('menjelaskan_tujuan');
        $idx_membangun_hubungan = $request->get('membangun_hubungan');
        $idx_mengajak_berdiskusi = $request->get('mengajak_berdiskusi');
        $idx_memimpin_proses_diskusi = $request->get('memimpin_proses_diskusi');
        $idx_mampu_menjawab_pertanyaan = $request->get('mampu_menjawab_pertanyaan');
        $idx_kedalaman_materi = $request->get('kedalaman_materi');
        $idx_penampilan = $request->get('penampilan');

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

        $values = [];

        foreach ($result->values as $i => $value) {

            if ($i == 0) {
                continue;
            }

            $values[] = [
                'nama' => $value[$idx_nama_facilitator],
                'menjelaskan_tujuan' => isset($value[$idx_menjelaskan_tujuan]) ? $value[$idx_menjelaskan_tujuan]: 0,
                'membangun_hubungan' => isset($value[$idx_membangun_hubungan]) ? $value[$idx_membangun_hubungan] : 0 ,
                'mengajak_berdiskusi' => isset($value[$idx_mengajak_berdiskusi]) ? $value[$idx_mengajak_berdiskusi] : 0,
                'memimpin_proses_diskusi' => isset($value[$idx_memimpin_proses_diskusi]) ? $value[$idx_memimpin_proses_diskusi] : 0 ,
                'mampu_menjawab_pertanyaan' => isset($value[$idx_mampu_menjawab_pertanyaan]) ? $value[$idx_mampu_menjawab_pertanyaan] : 0,
                'kedalaman_materi' => isset($value[$idx_kedalaman_materi]) ? $value[$idx_kedalaman_materi] : 0,
                'penampilan' => isset($value[$idx_penampilan]) ? $value[$idx_penampilan] : 0
            ];
        }

        $collection = collect($values);
        $keys = $collection->groupBy('nama')->keys();
        foreach ($keys as $key) {
            $explodes = explode('=', $key);
            $name = trim($explodes[1]);
            $facilitator = Facilitator::where('name', '=', $name)->first();
            if (empty($facilitator)) {
                $facilitator = new Facilitator();
                $facilitator->name = $name;
                if (!$facilitator->save()) {
                    throw new \Exception('Failed to save facilitator', 500);
                }
            }
        }

        $response = [];
        foreach ($keys as $key) {
            $explodes = explode('=', $key);
            $name = trim($explodes[1]);

            $facilitator_scores = $collection->where('nama', $key)->all();

            $ct = 0;
            $sum_menjelaskan_tujuan = 0;
            $sum_membangun_hubungan = 0;
            $sum_mengajak_berdiskusi = 0;
            $sum_memimpin_proses_diskusi = 0;
            $sum_mampu_menjawab_pertanyaan = 0;
            $sum_kedalaman_materi = 0;
            $sum_penampilan = 0;

            foreach ($facilitator_scores as $facilitator_score) {
                $sum_menjelaskan_tujuan += $facilitator_score['menjelaskan_tujuan'];
                $sum_membangun_hubungan += $facilitator_score['membangun_hubungan'];
                $sum_mengajak_berdiskusi += $facilitator_score['mengajak_berdiskusi'];
                $sum_memimpin_proses_diskusi += $facilitator_score['memimpin_proses_diskusi'];
                $sum_mampu_menjawab_pertanyaan += $facilitator_score['mampu_menjawab_pertanyaan'];
                $sum_kedalaman_materi += $facilitator_score['kedalaman_materi'];
                $sum_penampilan += $facilitator_score['penampilan'];
                $ct++;
            }

            $facilitator = Facilitator::where('name', '=', $name)->first();
            $response[$name] = [
                'batch' => $batch,
                'year' => $year,
                'facilitator_id' => $facilitator->id,
                'menjelaskan_tujuan' => floatval($sum_menjelaskan_tujuan/$ct),
                'membangun_hubungan' => floatval( $sum_membangun_hubungan/$ct),
                'mengajak_berdiskusi' => floatval($sum_mengajak_berdiskusi/$ct),
                'memimpin_proses_diskusi' => floatval($sum_memimpin_proses_diskusi/$ct),
                'mampu_menjawab_pertanyaan' => floatval($sum_mampu_menjawab_pertanyaan/$ct),
                'kedalaman_materi' => floatval($sum_kedalaman_materi/$ct),
                'penampilan' => floatval($sum_penampilan/$ct)
            ];


            $facilitator_report = new FacilitatorReport($response[$name]);
            if (!$facilitator_report->save()) {
                throw new \Exception('Failed to save facilitator report', 500);
            }
        }

        return response('', 201);
    }

    /**
     * @param $spreadsheets_id
     * @param $range
     * @return array [\Google_Service_Sheets_ValueRange, string, int]
     */
    protected function getSpreadsheets($spreadsheets_id, $range)
    {
        try {
            if (!$this->request->session()->has('gclient')) {
                return [[], 'Please sign-in with Google', 401];
            }
            /**
             * @var $gc$google_clientlient \Google_Client
             */
            $google_client = session('gclient');
            $svc = new Google_Service_Sheets($google_client);
            $result = $svc->spreadsheets_values->get($spreadsheets_id, $range);
            return [$result, '', 200];

        } catch (Google_Service_Exception $ex) {
            if ($ex->getCode() == 401) {
                \Auth::guard()->logout();
                request()->session()->flush();
                request()->session()->regenerate();
                return [[], $ex->getMessage(), 401];
            }

            return [[], $ex->getMessage(), $ex->getCode()];
        } catch (\Exception $ex) {
            return [[], $ex->getMessage(), 500];
        }
    }

    protected function cleanScoreValue($value) {
        if (!is_int($value)) {
            $v = substr($value, 0, 1);
            return intval($v);
        }

        return $value;
    }
}