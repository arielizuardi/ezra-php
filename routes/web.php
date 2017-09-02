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

Route::prefix('dashboard')->middleware('google')->group(function () {
    Route::get('/', function () {
        return view('dashboard.welcome');
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

    Route::get('/facilitator/{base64id}/report', function ($base64id) {
        $decoded_facilitator_id = base64_decode($base64id);
        $temp = explode(':', $decoded_facilitator_id);
        return view('dashboard.view_facilitator_report', ['facilitator_id' => $temp[1]]);
    });

    Route::get('/presenter/{base64id}/report', function ($base64id) {
        $decoded_presenter_id = base64_decode($base64id);
        $temp = explode(':', $decoded_presenter_id);
        return view('dashboard.view_presenter_report', ['presenter_id' => $temp[1]]);
    });

    //Route::get('importall', 'ReportController@importAllReportFacilitator');
});

Route::prefix('v1')->middleware('cors')->group(function() {
    Route::get('presenter/{presenter_id}/report', 'PresenterController@fetchPresenterReport');
    Route::post('presenter/{presenter_id}/report',  'PresenterController@savePresenterReport');
    Route::get('facilitator/{facilitator_id}/report', 'FacilitatorController@fetchFacilitatorReport');
    Route::get('facilitator/{facilitator_id}/comment', 'FacilitatorController@fetchComment');

    Route::post('facilitator-report', 'FacilitatorController@saveFacilitatorReports');

    Route::get('presenter', function(Request $request) {
        $fields = App\Presenter::where('id','>','0')->orderBy('name', 'ASC')->get();
        $data = [];
        foreach ($fields as $field) {
            $data[] = ['name' => $field->name , 'value' => $field->id , 'text' => $field->name];
        }
        $response['success'] = true;
        $response['results'] = $data;

        return response()->json($response);
    });

    Route::get('facilitator', function(Request $request) {
        $fields = App\Facilitator::where('id','>','0')->orderBy('name', 'ASC')->get();
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

    Route::post('signin', 'Auth\GoogleLoginController@signInWithGoogle');
    Route::post('signout', 'Auth\GoogleLoginController@signOutGoogle');
});