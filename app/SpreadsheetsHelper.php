<?php
namespace App;

use Illuminate\Http\Request;
use Google_Service_Sheets;
use Google_Service_Exception;
use Google_Client;


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
            /**
             * @var $gc $google_client \Google_Client
             */
            $access_token = \Auth::user()->provider_access_token;
            $google_client = new Google_Client();
            $google_client->setAccessToken($access_token);

            $svc = new Google_Service_Sheets($google_client);
            $result = $svc->spreadsheets_values->get($spreadsheets_id, $range);
            return [$result, '', 200];

        } catch (Google_Service_Exception $ex) {
            \Log::error($ex);
            if ($ex->getCode() == 401) {
                \Auth::guard()->logout();
                request()->session()->flush();
                request()->session()->regenerate();
                return [[], $ex->getMessage(), 401];
            }

            return [[], $ex->getMessage(), $ex->getCode()];
        } catch (\Exception $ex) {
            \Log::error($ex);
            return [[], $ex->getMessage(), 500];
        }

    }
}