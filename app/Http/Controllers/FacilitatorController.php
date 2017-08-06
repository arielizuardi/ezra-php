<?php
namespace App\Http\Controllers;

use App\Facilitator\Usecase\FacilitatorUsecase;
use App\FacilitatorReport;
use App\SpreadsheetsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FacilitatorController extends Controller
{
    protected $request;
    protected $facilitator_usecase;

    public function __construct(Request $request, FacilitatorUsecase $facilitator_usecase)
    {
        $this->request = $request;
        $this->facilitator_usecase = $facilitator_usecase;
    }

    protected function http_response($success, $message, $data)
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
    }

    public function fetchFacilitatorReport($facilitator_id)
    {
        $from_year = $this->request->get('from_year');
        $to_year = $this->request->get('to_year');
        $facilitator_reports = $this->facilitator_usecase->fetchFacilitatorReport($facilitator_id, $from_year, $to_year);
        if (empty($facilitator_reports)) {
            return response()->json($this->http_response(true, 'OK', []));
        }

        return response()->json($this->http_response(true, 'OK', $facilitator_reports));
    }

    public function saveFacilitatorReports()
    {
        $batch = $this->request->get('batch');
        $year = $this->request->get('year');
        $spr_id = $this->request->get('spr_id');
        $range = $this->request->get('range');
        list($result, $error_message, $status_code) = SpreadsheetsHelper::getSpreadsheets($this->request, $spr_id, $range);
        if (empty($result)) {
            return response()->json(['success' => false, 'error' => $error_message], $status_code);
        }

        $index = [];
        foreach ($this->facilitator_usecase->keys as $key){
            $index[$key] = $this->request->get($key);
        }

        /**
         * @var $facilitator_scores Collection
         */
        $facilitator_scores = $this->facilitator_usecase->fetchFacilitatorScores($result, $index);
        $unprocessed_names = $facilitator_scores->groupBy('nama')->keys();
        foreach ($unprocessed_names as $unprocessed_name) {
            $explodes = explode('=', $unprocessed_name);
            $name = trim($explodes[1]);
            $facilitator = $this->facilitator_usecase->saveFacilitator($name);
            if (empty($facilitator)){
                throw new \Exception(sprintf('Failed to save facilitator : %s', $name), 500);
            }

            $facilitator_score = $facilitator_scores->where('nama', $unprocessed_name)->all();
            $report_data = $this->facilitator_usecase->getAverageScores($facilitator_score);
            $report_data['batch'] = $batch;
            $report_data['year'] = $year;
            $report_data['facilitator_id'] = $facilitator->id;
            $report_data['nama'] = $facilitator->name;

            $facilitator_report = $this->facilitator_usecase->saveFacilitatorReport($report_data);
            if (empty($facilitator_report)){
                throw new \Exception(sprintf('Failed to save facilitator report for : %s', $name), 500);
            }
        }

        return response()->json('', 201);
    }
}