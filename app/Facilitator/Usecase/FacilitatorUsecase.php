<?php
namespace App\Facilitator\Usecase;

use App\Facilitator;
use App\Facilitator\ArrayResponse;
use App\FacilitatorReport;
use Google_Service_Sheets_ValueRange;


class FacilitatorUsecase
{
    public $keys = [
        'nama_partisipan',
        'date_partisipan',
        'nama',
        'menjelaskan_tujuan',
        'membangun_hubungan',
        'mengajak_berdiskusi',
        'memimpin_proses_diskusi',
        'mampu_menjawab_pertanyaan',
        'kedalaman_materi',
        'penampilan',
        'masukan',
    ];

    public $string_value_keys = ['nama_partisipan', 'date_partisipan', 'nama', 'masukan'];

    public function saveFacilitator($name)
    {
        $facilitator = Facilitator::where('name', '=', $name)->first();
        if (empty($facilitator)) {
            $facilitator = new Facilitator();
            $facilitator->name = strtoupper($name);
            if (!$facilitator->save()) {
                return null;
            }
        }

        return $facilitator;
    }

    public function saveFacilitatorReport($data)
    {
        $facilitator_report = new FacilitatorReport($data);
        if (!$facilitator_report->save()) {
            return null;
        }

        return $facilitator_report;
    }

    public function getAverageScores(array $facilitator_scores_by_name)
    {
        $sum = [];
        $avg = [];

        $feedback_score_keys = array_diff($this->keys, $this->string_value_keys);
        foreach ($feedback_score_keys as $key) {
            $sum[$key] = 0;
            $avg[$key] = 0;
        }

        $ct = 0;
        foreach ($facilitator_scores_by_name as $facilitator_score_by_name) {
            foreach ($feedback_score_keys as $key) {
                $sum[$key] += $facilitator_score_by_name[$key];
            }
            $ct++;
        }

        foreach ($feedback_score_keys as $key) {
            $avg[$key] = $sum[$key]/$ct;
        }

        return $avg;
    }

    public function fetchComment($facilitator_id,$from_year, $to_year)
    {
        $facilitator_reports = FacilitatorReport::where('facilitator_id', '=', $facilitator_id)
            ->whereBetween('year', [$from_year, $to_year])
            ->orderBy('year', 'ASC')
            ->orderBy('batch', 'ASC')
            ->get();

        if ($facilitator_reports->isEmpty()) {
            return [];
        }

        $response = [];

        foreach ($facilitator_reports as $i => $item)
        {
            $key = $item->batch.':'.$item->year;
            if (!empty($item->comments)){
                $comments = json_decode($item->comments);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response[$key] = $comments;
                } else {
                    $comments = [];
                    $temps = explode('-', $item->comments);
                    foreach ($temps as $temp) {
                        if (!empty($temp)) {
                            $r = [];
                            $r['nama'] = 'Partisipan batch '.$item->batch.' tahun '.$item->year;
                            $r['date'] = '-';
                            $r['masukan'] = trim($temp,"\n");
                            $comments[] = $r;
                        }
                    }
                    $response[$key] = $comments;
                }
            } else {
                $response[$key] = [];
            }
        }

        return $response;
    }

    public function getComments(array $facilitator_scores_by_name)
    {
        $comments = [];
        foreach ($facilitator_scores_by_name as $facilitator_score_by_name) {
            if (!empty($facilitator_score_by_name['masukan'])) {
                $comments[] = [
                    'nama' => ucwords($facilitator_score_by_name['nama_partisipan']),
                    'date' => ucwords($facilitator_score_by_name['date_partisipan']),
                    'masukan' => ucwords(ucwords($facilitator_score_by_name['masukan'], '.'), '!')
                ];
            }
        }

        return json_encode($comments);
    }

    public function fetchFacilitatorScores(Google_Service_Sheets_ValueRange $valueRange, array $index)
    {
        $values = [];
        foreach ($valueRange->values as $i => $value) {

            if ($i == 0) {
                continue;
            }

            $temp = [];
            foreach ($this->keys as $key) {
                if (in_array($key, $this->string_value_keys)) {
                    $temp[$key] = isset($value[$index[$key]]) ? $value[$index[$key]]: '';
                } else {
                    $temp[$key] = isset($value[$index[$key]]) ? floatval($value[$index[$key]]): 0;
                }
            }

            $values[] = $temp;
        }

        return collect($values);
    }

    public function fetchFacilitatorReport($facilitator_id,$from_year, $to_year)
    {
        $facilitator_reports = FacilitatorReport::where('facilitator_id', '=', $facilitator_id)
                ->whereBetween('year', [$from_year, $to_year])
                ->orderBy('year', 'ASC')
                ->orderBy('batch', 'ASC')
                ->get();

        if ($facilitator_reports->isEmpty()) {
            return [];
        }

        $response = new ArrayResponse($facilitator_reports);
        return $response->toArray();
    }
}