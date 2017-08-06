<?php
namespace App\Facilitator\Usecase;

use App\Facilitator;
use App\Facilitator\ArrayResponse;
use App\FacilitatorReport;
use Google_Service_Sheets_ValueRange;


class FacilitatorUsecase
{
    public $keys = [
        'nama',
        'menjelaskan_tujuan',
        'membangun_hubungan',
        'mengajak_berdiskusi',
        'memimpin_proses_diskusi',
        'mampu_menjawab_pertanyaan',
        'kedalaman_materi',
        'penampilan'
    ];

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

        foreach ($this->keys as $key) {
            $sum[$key] = 0;
            $avg[$key] = 0;
        }

        $ct = 0;
        foreach ($facilitator_scores_by_name as $facilitator_score_by_name) {
            foreach ($this->keys as $key) {
                if ($key == 'nama') {
                    continue;
                }
                $sum[$key] += $facilitator_score_by_name[$key];
            }
            $ct++;
        }

        foreach ($this->keys as $key) {
            $avg[$key] = $sum[$key]/$ct;
        }

        return $avg;
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
                if ($key == 'nama') {
                    $temp['nama'] =  isset($value[$index['nama']]) ? $value[$index['nama']]: '';
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