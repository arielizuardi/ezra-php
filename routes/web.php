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

    Route::get('gsheet', function () {
        return view('dashboard.gsheetimporter');
    });

    Route::get('report', function () {
        return view('dashboard.report');
    });

    Route::get('facilitator/gsheet', function () {
        return view('dashboard.facilgsheetimporter');
    });

    Route::get('facilitator/report', function () {
        return view('dashboard.facilreport');
    });

    //Route::get('importall', 'ReportController@importAllReportFacilitator');
});

Route::prefix('v1')->group(function() {
    Route::get('report', 'ReportController@getPresenterReport');
    Route::post('report', 'ReportController@generatePresenterReport');
    Route::get('report/facilitator', 'ReportController@getFacilitatorReport');
    Route::post('report/facilitator', 'ReportController@generateReportFacilitator');

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