<?php
namespace App\Http\Controllers;

use App\Presenter\Usecase\PresenterUsecase;
use Illuminate\Http\Request;
use Google_Service_Sheets;
use Google_Service_Exception;


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

    public function getPresenterReport($presenter_id)
    {
        $year = $this->request->get('year');
        $batch = $this->request->get('batch');
        $session_id = $this->request->get('session_id');
        $presenter_report = $this->presenter_usecase->getPresenterReport($presenter_id, $year, $batch, $session_id);
        if (empty($presenter_report)) {
            return response()->json($this->http_response(true, 'OK', []));
        }

        return response()->json($this->http_response(true, 'OK', $presenter_report));
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

        list($result, $error_message, $status_code) = $this->getSpreadsheets($spr_id, $range);
        if (empty($result)) {
            return response()->json(['success' => false, 'error' => $error_message], $status_code);
        }

        $report_data = $this->presenter_usecase->getAverageScores($result, $index);

        $report_data['spr_id'] = $spr_id;
        $report_data['range'] = $range;
        $report_data['batch'] = $batch;
        $report_data['year'] = $year;
        $report_data['session'] = $session;
        $report_data['presenter_id'] = $presenter_id;

        if (!$this->presenter_usecase->savePresenterReport($report_data)) {
            throw new \Exception('Failed to save report settings', 500);
        }

        return response()->json(['success' => true], 200);
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
}