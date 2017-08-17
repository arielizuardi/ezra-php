<?php
namespace App;

use Illuminate\Http\Request;
use Google_Service_Sheets;
use Google_Service_Exception;


class SpreadsheetsHelper
{
    /**
     * @param $request
     * @param $spreadsheets_id
     * @param $range
     * @return array [\Google_Service_Sheets_ValueRange, string, int]
     */
    public static function getSpreadsheets(Request $request, $spreadsheets_id, $range)
    {
        try {
            if (!$request->session()->has('gclient')) {
                return [[], 'Please sign-in with Google', 401];
            }
            /**
             * @var $gc $google_client \Google_Client
             */
            $google_client = session('gclient');
            $svc = new Google_Service_Sheets(unserialize($google_client));
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