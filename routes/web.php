<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|*/
ini_set('error_reporting', E_ALL);
set_time_limit(0);

Route::get('/', 'IndexController@index');


Route::middleware(['auth'])->group(function () {

    Route::get('/secret/adminPage/', 'IndexController@indexAdmin');
    Route::get('/secret/adminPage/sort/{column}/{type}/', 'IndexController@sort')->where('type', 'asc|desc');
    Route::get('/secret/adminPage/search/{searchString}/', 'IndexController@search');

    Route::post('/secret/adminPage/sort/{column}/{type}/', 'IndexController@sortAJAX')->where('type', 'asc|desc');
    Route::post('/secret/adminPage/search/{searchString}/', 'IndexController@searchAJAX');
    Route::post('/secret/adminPage/searchBosses', 'IndexController@searchBossesAJAX');

    Route::post('/secret/adminPage/addEmployee', 'IndexController@addEmployee');
    Route::post('/secret/adminPage/updateEmployee', 'IndexController@updateEmployee');
    Route::post('/secret/adminPage/getDataForEditForm', 'IndexController@getDataForEditForm');
    Route::post('/secret/adminPage/remove_avatar', 'IndexController@remove_avatar');
    Route::get('/secret/adminPage/fired/{employee_id}', 'IndexController@fired');

    Route::post('/secret/adminPage/changeBoss', 'IndexController@changeBossAJAX');

    Route::post('/secret/adminPage/reset_filters', 'IndexController@resetFiltersAJAX');
    
    Route::post('/secret/adminPage/getTree', 'IndexController@getTreeAJAX');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
