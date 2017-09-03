<?php
namespace App\Http\Controllers;

use App\Presenter\Usecase\PresenterUsecase;
use App\SpreadsheetsHelper;
use Illuminate\Http\Request;


class PresenterController extends Controller
{
    protected $request;
    protected $presenter_usecase;

    public function __construct(Request $request, PresenterUsecase $presenter_usecase)
    {
        $this->request = $request;
        $this->presenter_usecase = $presenter_usecase;
    }

    protected function http_response($success, $message, $data)
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
    }

    public function getAveragePresentersReport()
    {
        $year = $this->request->get('year');
        $batch = $this->request->get('batch');
        $avg_presenter_report = $this->presenter_usecase->getAverageReportPresentersData($batch, $year);
        if (empty($avg_presenter_report)) {
            return response()->json($this->http_response(true, 'OK', []));
        }

        return response()->json($this->http_response(true, 'OK', $avg_presenter_report));
    }

    public function fetchPresenterReport($presenter_id)
    {
        $from_year = $this->request->get('from_year');
        $to_year = $this->request->get('to_year');
        $session_id = $this->request->get('session');
        $presenter_reports = $this->presenter_usecase->fetchPresenterReport($presenter_id, $session_id, $from_year, $to_year);
        if (empty($presenter_reports)) {
            return response()->json($this->http_response(true, 'OK', []));
        }

        return response()->json($this->http_response(true, 'OK', $presenter_reports));
    }

    public function savePresenterReport($presenter_id)
    {
        $spr_id = $this->request->get('spr_id');
        $range = $this->request->get('range');
        $batch = $this->request->get('batch');
        $year = $this->request->get('year');
        $session = $this->request->get('session');

        $index = [];
        foreach ($this->presenter_usecase->keys as $key)
        {
            $index[$key] = $this->request->get($key);
        }

        list($result, $error_message, $status_code) = SpreadsheetsHelper::getSpreadsheets($this->request, $spr_id, $range);
        if (empty($result)) {
            return response()->json(['success' => false, 'error' => $error_message], $status_code);
        }

        $report_data = $this->presenter_usecase->getAverageScores($result, $index);
        $comments = $this->presenter_usecase->getComments($result, $index);

        $report_data['spr_id'] = $spr_id;
        $report_data['range'] = $range;
        $report_data['batch'] = $batch;
        $report_data['year'] = $year;
        $report_data['session'] = $session;
        $report_data['presenter_id'] = $presenter_id;
        $report_data['raw_like_comments'] = isset($comments['hal_yang_disuka']) ? json_encode($comments['hal_yang_disuka']) : '';
        $report_data['raw_wish_comments'] = isset($comments['hal_yang_diharapkan']) ? json_encode($comments['hal_yang_diharapkan']): '';

        \Log::info($report_data);

        if (!$this->presenter_usecase->savePresenterReport($report_data)) {
            throw new \Exception('Failed to save report settings', 500);
        }

        return response()->json(['success' => true], 200);
    }
}