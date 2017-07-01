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

Route::get('/dashboard', function() {
    return view('dashboard.index');
});

Route::post('v1/report', 'ReportController@generate');

Route::get('/dashboard/report', function() {
    return view('dashboard.report');
});

Route::get('v1/feedback/field', function(Request $request) {
    $fields = App\FeedbackField::all();
    $data = [];
    foreach ($fields as $field) {
        $data[] = ['name' => $field->name , 'value' => $field->id , 'text' => $field->name];
    }
    $response['success'] = true;
    $response['results'] = $data;

    return response()->json($response);
});


Route::get('login/google', 'Auth\LoginController@redirectToProvider');
Route::get('login/google/callback', 'Auth\LoginController@handleProviderCallback');