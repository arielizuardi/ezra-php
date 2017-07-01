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

    Route::get('report', function () {
        return view('dashboard.report');
    });
});

Route::prefix('v1')->group(function() {
    Route::post('report', 'ReportController@generate');
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

Route::get('/home', 'HomeController@index')->name('home');
