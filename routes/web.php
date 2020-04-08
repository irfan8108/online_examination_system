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

Route::get('admin', 'Admin\FrontController@index')->name('welcome');

Route::match(['get','post'], 'admin/crud/{type}/{cmd?}/{cmd_id?}', 'Admin\FrontController@crud')->name('crud');
Route::match(['get','post'], 'admin/make_exam_live', 'Admin\FrontController@makeExamLive')->name('make_exam_live');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('admin/home', 'Admin\FrontController@index')->name('admin.welcome')->middleware('is_admin');


Route::get('welcome', 'User\FrontController@index')->name('welcome');
Route::get('exam_instruction/{exam_id}', 'User\FrontController@examInstruction')->name('exam_instructions');
Route::post('exam', 'User\FrontController@exam')->name('exam');
Route::post('examSession', 'ProcessController@examSession')->name('exam_session');
Route::post('postAnswer', 'ProcessController@postAnswer')->name('post_answer');
Route::post('finish_exam', 'User\FrontController@finishExam')->name('submit_exam');

Route::get('exam_design', 'User\FrontController@examDesign')->name('exam_design');