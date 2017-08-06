<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard.index');
    });

    Route::get('import-presenter-report', function () {
        return view('dashboard.import_presenter_report');
    });

    Route::get('presenter-report', function () {
        return view('dashboard.presenter_report');
    });

    Route::get('import-facilitator-report', function () {
        return view('dashboard.import_facilitator_report');
    });

    Route::get('facilitator-report', function () {
        return view('dashboard.facilitator_report');
    });

    //Route::get('importall', 'ReportController@importAllReportFacilitator');
});

Route::prefix('v1')->group(function() {
    Route::get('presenter/{presenter_id}/report', 'PresenterController@fetchPresenterReport');
    Route::post('presenter/{presenter_id}/report',  'PresenterController@savePresenterReport');
    Route::get('facilitator/{facilitator_id}/report', 'FacilitatorController@fetchFacilitatorReport');
    Route::post('facilitator-report', 'FacilitatorController@saveFacilitatorReports');

    Route::get('presenter', function(Request $request) {
        $fields = App\Presenter::all();
        $data = [];
        foreach ($fields as $field) {
            $data[] = ['name' => $field->name , 'value' => $field->id , 'text' => $field->name];
        }
        $response['success'] = true;
        $response['results'] = $data;

        return response()->json($response);
    });

    Route::get('facilitator', function(Request $request) {
        $fields = App\Facilitator::all();
        $data = [];
        foreach ($fields as $field) {
            $data[] = ['name' => $field->name , 'value' => $field->id , 'text' => $field->name];
        }
        $response['success'] = true;
        $response['results'] = $data;

        return response()->json($response);
    });

    Route::get('feedback/field', function(Request $request) {
        $fields = App\FeedbackField::all();
        $data = [];
        foreach ($fields as $field) {
            $data[] = ['name' => $field->name , 'value' => $field->id , 'text' => $field->name];
        }
        $response['success'] = true;
        $response['results'] = $data;

        return response()->json($response);
    });
});

Route::get('auth/google', 'Auth\LoginController@redirectToProvider');
Route::get('auth/google/callback', 'Auth\LoginController@handleProviderCallback');

Auth::routes();