<?php
namespace App\Http\Controllers;

use App\Facilitator;
use App\FacilitatorReport;
use App\SpreadsheetsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class FacilitatorReportController extends Controller
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function top10Facilitators()
    {
        $batch = $this->request->get('batch');
        $year = $this->request->get('year');

        $query = 'facilitator.id as facilitator_id, facilitator.name as facilitator_name, 
        (menjelaskan_tujuan + membangun_hubungan + mengajak_berdiskusi + 
        memimpin_proses_diskusi + mampu_menjawab_pertanyaan + kedalaman_materi + 
        penampilan)/7 as avg_score';

        $result = DB::table('facilitator_report')
            ->selectRaw($query)
            ->join('facilitator', 'facilitator.id', '=', 'facilitator_report.facilitator_id')
            ->where('batch', $batch)
            ->where('year', $year)
            ->orderBy('avg_score', 'DESC')
            ->limit(10)
            ->get();

        return response()->json($result, 200);
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

        $query =
            'AVG(menjelaskan_tujuan) as menjelaskan_tujuan,
            AVG(membangun_hubungan) as membangun_hubungan,
            AVG(mengajak_berdiskusi) as mengajak_berdiskusi,
            AVG(memimpin_proses_diskusi) as memimpin_proses_diskusi,
            AVG(mampu_menjawab_pertanyaan) as mampu_menjawab_pertanyaan,
            AVG(kedalaman_materi) as kedalaman_materi,
            AVG(penampilan) as penampilan';

        $avg_all_class = DB::table('facilitator_report')->selectRaw($query)
            ->where('batch', '=', $batch)
            ->where('year', '=', $year)
            ->first();

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

    public function generateReportFacilitator()
    {
        $spr_id = $this->request->get('spr_id');
        $range = $this->request->get('range');
        $batch = $this->request->get('batch');
        $year = $this->request->get('year');

        $idx_nama_facilitator = $this->request->get('nama_facilitator');
        $idx_menjelaskan_tujuan = $this->request->get('menjelaskan_tujuan');
        $idx_membangun_hubungan = $this->request->get('membangun_hubungan');
        $idx_mengajak_berdiskusi = $this->request->get('mengajak_berdiskusi');
        $idx_memimpin_proses_diskusi = $this->request->get('memimpin_proses_diskusi');
        $idx_mampu_menjawab_pertanyaan = $this->request->get('mampu_menjawab_pertanyaan');
        $idx_kedalaman_materi = $this->request->get('kedalaman_materi');
        $idx_penampilan = $this->request->get('penampilan');

        list($result, $error_message, $status_code) = SpreadsheetsHelper::getSpreadsheets($this->request, $spr_id, $range);
        if (empty($result)) {
            return response()->json(['success' => false, 'error' => $error_message], $status_code);
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
}